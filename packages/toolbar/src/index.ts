/**
 * Headless WordPress Toolbar
 * Framework-agnostic toolbar for headless WordPress applications
 * @package @wpengine/hwp-toolbar
 */

// ============================================================================
// TYPES
// ============================================================================

export interface WordPressUser {
  id: number;
  name: string;
  email?: string;
  avatar?: string;
}

export interface WordPressPost {
  id: number;
  title: string;
  type: string;
  status: string;
  slug: string;
}

export interface WordPressSite {
  url: string;
  adminUrl: string;
}

export interface ToolbarState {
  user: WordPressUser | null;
  post: WordPressPost | null;
  site: WordPressSite | null;
  preview: boolean;
  isHeadless: boolean;
}

export interface ToolbarConfig {
  onPreviewChange?: (enabled: boolean) => void;
}

export type NodeCallback = () => void;
export type LabelFunction = () => string;

export interface ToolbarNode {
  id: string;
  label: string | LabelFunction;
  onClick: NodeCallback;
}

// ============================================================================
// TOOLBAR CLASS
// ============================================================================

export class Toolbar {
  private nodes: Map<string, ToolbarNode> = new Map();
  private state: ToolbarState = {
    user: null,
    post: null,
    site: null,
    preview: false,
    isHeadless: true
  };
  private listeners: Set<(nodes: ToolbarNode[], state: ToolbarState) => void> = new Set();
  private config: ToolbarConfig;

  constructor(config: ToolbarConfig = {}) {
    this.config = config;
    this.registerDefaultNodes();
  }

  private registerDefaultNodes(): void {
    // Edit Post
    this.register('edit-post', () => {
      const p = this.state.post;
      return p ? `Edit ${p.type}` : 'Edit';
    }, () => {
      const { post, site } = this.state;
      if (post && site) {
        window.open(`${site.adminUrl}/post.php?post=${post.id}&action=edit`, '_blank');
      }
    });

    // WP Admin
    this.register('wp-admin', 'WP Admin', () => {
      if (this.state.site?.adminUrl) {
        window.open(this.state.site.adminUrl, '_blank');
      }
    });

    // Preview Toggle
    this.register('preview', () => {
      return this.state.preview ? 'Exit Preview' : 'Preview';
    }, () => {
      this.setState({ preview: !this.state.preview });
      if (this.config.onPreviewChange) {
        this.config.onPreviewChange(this.state.preview);
      }
    });
  }

  register(id: string, label: string | LabelFunction, onClick?: NodeCallback): this {
    if (typeof label === 'function' && !onClick) {
      // register(id, onClick)
      this.nodes.set(id, { id, label: id, onClick: label });
    } else {
      // register(id, label, onClick)
      this.nodes.set(id, {
        id,
        label,
        onClick: onClick || (() => {})
      });
    }
    this.notify();
    return this;
  }

  unregister(id: string): this {
    this.nodes.delete(id);
    this.notify();
    return this;
  }

  clear(): this {
    this.nodes.clear();
    this.registerDefaultNodes();
    this.notify();
    return this;
  }

  setState(updates: Partial<ToolbarState>): this {
    this.state = { ...this.state, ...updates };
    this.notify();
    return this;
  }

  setWordPressContext(context: {
    user?: WordPressUser | null;
    post?: WordPressPost | null;
    site?: WordPressSite | null;
  }): this {
    return this.setState(context);
  }

  getState(): ToolbarState {
    return { ...this.state };
  }

  getVisibleNodes(): ToolbarNode[] {
    return Array.from(this.nodes.values()).filter(node => {
      if (node.id === 'edit-post') return this.state.post && this.state.user;
      if (node.id === 'wp-admin') return this.state.user;
      if (node.id === 'preview') return this.state.post || this.state.user;
      return true;
    }).map(node => ({
      ...node,
      label: typeof node.label === 'function' ? node.label() : node.label
    }));
  }

  subscribe(callback: (nodes: ToolbarNode[], state: ToolbarState) => void): () => void {
    this.listeners.add(callback);
    callback(this.getVisibleNodes(), this.getState());
    return () => this.listeners.delete(callback);
  }

  private notify(): void {
    const nodes = this.getVisibleNodes();
    const state = this.getState();
    this.listeners.forEach(cb => {
      try {
        cb(nodes, state);
      } catch (error) {
        console.error('Toolbar error:', error);
      }
    });
  }

  destroy(): void {
    this.nodes.clear();
    this.listeners.clear();
  }
}

// ============================================================================
// VANILLA RENDERER
// ============================================================================

export class VanillaRenderer {
  private toolbar: Toolbar;
  private element: HTMLElement | null;
  private unsubscribe: (() => void) | null = null;

  constructor(toolbar: Toolbar, elementOrId: string | HTMLElement) {
    this.toolbar = toolbar;

    if (typeof elementOrId === 'string') {
      this.element = document.getElementById(elementOrId);
      if (!this.element) throw new Error(`Element "${elementOrId}" not found`);
    } else {
      this.element = elementOrId;
    }

    this.unsubscribe = this.toolbar.subscribe((nodes, state) => {
      this.render(nodes, state);
    });
  }

  private render(nodes: ToolbarNode[], state: ToolbarState): void {
    if (!this.element) return;
    this.element.innerHTML = '';
    this.element.className = 'hwp-toolbar';

    // Render nodes
    nodes.forEach(node => {
      const btn = document.createElement('button');
      btn.className = 'hwp-toolbar-button';
      const labelText = typeof node.label === 'function' ? node.label() : node.label;
      btn.textContent = labelText;

      if (node.id === 'preview' && state.preview) {
        btn.classList.add('hwp-toolbar-button-active');
      }

      btn.onclick = () => node.onClick();
      this.element!.appendChild(btn);
    });

    // User button
    if (state.user) {
      const userBtn = document.createElement('button');
      userBtn.className = 'hwp-toolbar-button hwp-toolbar-user';
      userBtn.textContent = `User: ${state.user.name}`;
      userBtn.onclick = () => {
        if (confirm(`Logged in as: ${state.user!.name}\n\nLogout?`)) {
          this.toolbar.setState({ user: null, post: null, site: null });
          this.toolbar.clear();
        }
      };
      this.element!.appendChild(userBtn);
    }
  }

  destroy(): void {
    if (this.unsubscribe) this.unsubscribe();
    if (this.element) this.element.innerHTML = '';
  }
}
