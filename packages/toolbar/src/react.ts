import { useState, useEffect, useMemo } from 'react';
import type { Toolbar, ToolbarNode, ToolbarState } from './index';

/**
 * React hook to subscribe to toolbar state changes
 * Similar to Zustand's useStore pattern
 *
 * @example
 * ```tsx
 * function MyComponent() {
 *   const state = useToolbarState(toolbar);
 *   return <div>User: {state.user?.name}</div>;
 * }
 * ```
 */
export function useToolbarState(toolbar: Toolbar): ToolbarState {
  const [state, setState] = useState<ToolbarState>(() => toolbar.getState());

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
 * ```tsx
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
 */
export function useToolbarNodes(toolbar: Toolbar): ToolbarNode[] {
  const [nodes, setNodes] = useState<ToolbarNode[]>(() => toolbar.getVisibleNodes());

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
 * ```tsx
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
 */
export function useToolbar(toolbar: Toolbar): {
  state: ToolbarState;
  nodes: ToolbarNode[];
} {
  const state = useToolbarState(toolbar);
  const nodes = useToolbarNodes(toolbar);

  return useMemo(() => ({ state, nodes }), [state, nodes]);
}
