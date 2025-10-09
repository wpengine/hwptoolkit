'use client';

import { useToolbar } from '@wpengine/hwp-toolbar/react';
import { toolbar } from '@/lib/toolbar';
import '@wpengine/hwp-toolbar/styles';
import { useState, useEffect } from 'react';

export function Toolbar() {
  const { state, nodes } = useToolbar(toolbar);
  const [position, setPosition] = useState<'top' | 'bottom'>('bottom');

  useEffect(() => {
    const unsubscribe = toolbar.subscribe(() => {
      const config = toolbar.getConfig();
      setPosition(config?.position || 'bottom');
    });
    return unsubscribe;
  }, []);

  return (
    <div className={`hwp-toolbar hwp-toolbar-${position}`}>
      <div className="hwp-toolbar-section hwp-toolbar-left">
        {nodes
          .filter((node) => node.position !== 'right')
          .map((node) => {
            const label = typeof node.label === 'function' ? node.label() : node.label;

            if (node.type === 'divider') {
              return <div key={node.id} className="hwp-toolbar-divider" />;
            }

            if (node.type === 'link' && node.href) {
              return (
                <a
                  key={node.id}
                  href={node.href}
                  target={node.target}
                  className="hwp-toolbar-link"
                >
                  {label}
                </a>
              );
            }

            return (
              <button
                key={node.id}
                onClick={node.onClick}
                className={`hwp-toolbar-button ${
                  node.id === 'preview' && state.preview ? 'hwp-toolbar-button-active' : ''
                }`}
              >
                {label}
              </button>
            );
          })}
      </div>

      <div className="hwp-toolbar-section hwp-toolbar-center"></div>

      <div className="hwp-toolbar-section hwp-toolbar-right">
        {state.user && (
          <button
            className="hwp-toolbar-button"
            onClick={() => {
              if (confirm(`Logged in as: ${state.user!.name}\n\nLogout?`)) {
                toolbar.setWordPressContext({
                  user: null,
                  post: null,
                  site: null,
                });
              }
            }}
          >
            User: {state.user.name}
          </button>
        )}
      </div>
    </div>
  );
}
