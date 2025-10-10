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

  const leftNodes = nodes.filter((node) => !node.position || node.position === 'left');
  const centerNodes = nodes.filter((node) => node.position === 'center');
  const rightNodes = nodes.filter((node) => node.position === 'right');

  const renderNode = (node: any) => {
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
  };

  return (
    <div className={`hwp-toolbar hwp-toolbar-${position}`}>
      <div className="hwp-toolbar-section hwp-toolbar-left">
        {leftNodes.map(renderNode)}
      </div>

      <div className="hwp-toolbar-section hwp-toolbar-center">
        {centerNodes.map(renderNode)}
      </div>

      <div className="hwp-toolbar-section hwp-toolbar-right">
        {rightNodes.map(renderNode)}
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
