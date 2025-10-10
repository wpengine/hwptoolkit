/**
 * React Hooks for Headless WordPress Toolbar
 * Zustand-like hook pattern for React integration
 * @package @wpengine/hwp-toolbar
 */

import { useState, useEffect, useMemo } from 'react';

/**
 * React hook to subscribe to toolbar state changes
 * Similar to Zustand's useStore pattern
 *
 * @example
 * ```jsx
 * function MyComponent() {
 *   const state = useToolbarState(toolbar);
 *   return <div>User: {state.user?.name}</div>;
 * }
 * ```
 *
 * @param {import('./core/Toolbar.js').Toolbar} toolbar
 * @returns {import('./core/Toolbar.js').ToolbarState}
 */
export function useToolbarState(toolbar) {
  const [state, setState] = useState(() => toolbar.getState());

  useEffect(() => {
    const unsubscribe = toolbar.subscribe((_, newState) => {
      setState(newState);
    });
    return unsubscribe;
  }, [toolbar]);

  return state;
}

/**
 * React hook to subscribe to toolbar nodes
 * Returns visible nodes based on current state
 *
 * @example
 * ```jsx
 * function ToolbarButtons() {
 *   const nodes = useToolbarNodes(toolbar);
 *   return (
 *     <div>
 *       {nodes.map(node => (
 *         <button key={node.id} onClick={node.onClick}>
 *           {typeof node.label === 'function' ? node.label() : node.label}
 *         </button>
 *       ))}
 *     </div>
 *   );
 * }
 * ```
 *
 * @param {import('./core/Toolbar.js').Toolbar} toolbar
 * @returns {import('./core/Toolbar.js').ToolbarNode[]}
 */
export function useToolbarNodes(toolbar) {
  const [nodes, setNodes] = useState(() => toolbar.getVisibleNodes());

  useEffect(() => {
    const unsubscribe = toolbar.subscribe((newNodes) => {
      setNodes(newNodes);
    });
    return unsubscribe;
  }, [toolbar]);

  return nodes;
}

/**
 * React hook that combines state and nodes
 * Convenience hook for components that need both
 *
 * @example
 * ```jsx
 * function MyToolbar() {
 *   const { state, nodes } = useToolbar(toolbar);
 *
 *   return (
 *     <div className="toolbar">
 *       {nodes.map(node => <Button key={node.id} {...node} />)}
 *       {state.user && <UserButton user={state.user} />}
 *     </div>
 *   );
 * }
 * ```
 *
 * @param {import('./core/Toolbar.js').Toolbar} toolbar
 * @returns {{ state: import('./core/Toolbar.js').ToolbarState, nodes: import('./core/Toolbar.js').ToolbarNode[] }}
 */
export function useToolbar(toolbar) {
  const state = useToolbarState(toolbar);
  const nodes = useToolbarNodes(toolbar);

  return useMemo(() => ({ state, nodes }), [state, nodes]);
}
