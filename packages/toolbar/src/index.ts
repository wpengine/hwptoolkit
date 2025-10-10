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

export interface ToolbarBranding {
  logo?: string;
  title?: string;
  url?: string;
  position?: 'left' | 'center' | 'right';
}

export interface ToolbarTheme {
  variables?: Record<string, string>;
  className?: string;
}

export interface ToolbarConfig {
  position?: 'top' | 'bottom';
  branding?: ToolbarBranding;
  theme?: ToolbarTheme;
  onPreviewChange?: (enabled: boolean) => void;
}

export type NodeCallback = () => void;
export type LabelFunction = () => string;
export type NodeType = 'button' | 'link' | 'image' | 'dropdown' | 'divider' | 'custom';
export type NodePosition = 'left' | 'center' | 'right';
export type CustomRenderFunction = (state: ToolbarState) => HTMLElement;

export interface ToolbarNode {
  id: string;
  type?: NodeType;
  label?: string | LabelFunction;
  onClick?: NodeCallback;
  href?: string;
  target?: string;
  src?: string;
  alt?: string;
  items?: ToolbarNode[];
  render?: CustomRenderFunction;
  className?: string;
  position?: NodePosition;
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
    this.config = {
      position: 'bottom',
      ...config
    };
    this.registerDefaultNodes();
  }

  getConfig(): ToolbarConfig {
    return { ...this.config };
  }

  setConfig(updates: Partial<ToolbarConfig>): this {
    this.config = { ...this.config, ...updates };
    this.notify();
    return this;
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

  register(id: string, labelOrNode: string | LabelFunction | Partial<ToolbarNode>, onClick?: NodeCallback): this {
    if (typeof labelOrNode === 'object') {
      // register(id, nodeConfig)
      this.nodes.set(id, {
        id,
        type: 'button',
        ...labelOrNode
      } as ToolbarNode);
    } else if (typeof labelOrNode === 'function' && !onClick) {
      // register(id, onClick)
      this.nodes.set(id, { id, type: 'button', label: id, onClick: labelOrNode });
    } else {
      // register(id, label, onClick)
      this.nodes.set(id, {
        id,
        type: 'button',
        label: labelOrNode,
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
  private config: ToolbarConfig;

  constructor(toolbar: Toolbar, elementOrId: string | HTMLElement) {
    this.toolbar = toolbar;
    this.config = (toolbar as any).config || {};

    if (typeof elementOrId === 'string') {
      this.element = document.getElementById(elementOrId);
      if (!this.element) throw new Error(`Element "${elementOrId}" not found`);
    } else {
      this.element = elementOrId;
    }

    this.applyTheme();
    this.unsubscribe = this.toolbar.subscribe((nodes, state) => {
      this.render(nodes, state);
    });
  }

  private applyTheme(): void {
    if (!this.element) return;

    if (this.config.theme?.className) {
      this.element.classList.add(this.config.theme.className);
    }

    if (this.config.theme?.variables) {
      Object.entries(this.config.theme.variables).forEach(([key, value]) => {
        this.element!.style.setProperty(key, value);
      });
    }
  }

  private render(nodes: ToolbarNode[], state: ToolbarState): void {
    if (!this.element) return;
    this.element.innerHTML = '';
    const position = this.config.position || 'bottom';
    this.element.className = `hwp-toolbar hwp-toolbar-${position}`;
    if (this.config.theme?.className) {
      this.element.classList.add(this.config.theme.className);
    }

    const leftSection = document.createElement('div');
    leftSection.className = 'hwp-toolbar-section hwp-toolbar-left';

    const centerSection = document.createElement('div');
    centerSection.className = 'hwp-toolbar-section hwp-toolbar-center';

    const rightSection = document.createElement('div');
    rightSection.className = 'hwp-toolbar-section hwp-toolbar-right';

    // Render branding
    if (this.config.branding) {
      const brandingEl = this.createBrandingElement(this.config.branding);
      const position = this.config.branding.position || 'left';
      if (position === 'left') leftSection.appendChild(brandingEl);
      else if (position === 'center') centerSection.appendChild(brandingEl);
      else rightSection.appendChild(brandingEl);
    }

    // Render nodes by position
    nodes.forEach(node => {
      const element = this.createNodeElement(node, state);
      if (!element) return;

      const position = node.position || 'left';
      if (position === 'left') leftSection.appendChild(element);
      else if (position === 'center') centerSection.appendChild(element);
      else rightSection.appendChild(element);
    });

    // User button (always right)
    if (state.user) {
      const userBtn = this.createUserButton(state);
      rightSection.appendChild(userBtn);
    }

    this.element.appendChild(leftSection);
    this.element.appendChild(centerSection);
    this.element.appendChild(rightSection);
  }

  private createBrandingElement(branding: ToolbarBranding): HTMLElement {
    const container = document.createElement('div');
    container.className = 'hwp-toolbar-branding';

    if (branding.logo) {
      const img = document.createElement('img');
      img.src = branding.logo;
      img.alt = branding.title || 'Logo';
      img.className = 'hwp-toolbar-logo';
      container.appendChild(img);
    }

    if (branding.title) {
      const title = document.createElement('span');
      title.textContent = branding.title;
      title.className = 'hwp-toolbar-brand-title';
      container.appendChild(title);
    }

    if (branding.url) {
      const wrapper = document.createElement('a');
      wrapper.href = branding.url;
      wrapper.className = 'hwp-toolbar-branding-link';
      wrapper.appendChild(container);
      return wrapper;
    }

    return container;
  }

  private createNodeElement(node: ToolbarNode, state: ToolbarState): HTMLElement | null {
    const type = node.type || 'button';

    if (type === 'divider') {
      const divider = document.createElement('div');
      divider.className = 'hwp-toolbar-divider';
      return divider;
    }

    if (type === 'custom' && node.render) {
      return node.render(state);
    }

    if (type === 'image' && node.src) {
      const img = document.createElement('img');
      img.src = node.src;
      img.alt = node.alt || '';
      img.className = `hwp-toolbar-image ${node.className || ''}`.trim();
      if (node.onClick) img.onclick = () => node.onClick!();
      return img;
    }

    if (type === 'link' && node.href) {
      const link = document.createElement('a');
      link.href = node.href;
      link.target = node.target || '_blank';
      link.className = `hwp-toolbar-link ${node.className || ''}`.trim();
      const labelText = typeof node.label === 'function' ? node.label() : node.label || '';
      link.textContent = labelText;
      return link;
    }

    if (type === 'dropdown' && node.items) {
      return this.createDropdown(node, state);
    }

    // Default: button
    const btn = document.createElement('button');
    btn.className = `hwp-toolbar-button ${node.className || ''}`.trim();
    const labelText = typeof node.label === 'function' ? node.label() : node.label || '';
    btn.textContent = labelText;

    if (node.id === 'preview' && state.preview) {
      btn.classList.add('hwp-toolbar-button-active');
    }

    if (node.onClick) btn.onclick = () => node.onClick!();
    return btn;
  }

  private createDropdown(node: ToolbarNode, state: ToolbarState): HTMLElement {
    const container = document.createElement('div');
    container.className = 'hwp-toolbar-dropdown';

    const trigger = document.createElement('button');
    trigger.className = 'hwp-toolbar-dropdown-trigger hwp-toolbar-button';
    const labelText = typeof node.label === 'function' ? node.label() : node.label || '';
    trigger.textContent = labelText + ' ▾';
    trigger.setAttribute('aria-haspopup', 'true');
    trigger.setAttribute('aria-expanded', 'false');

    const menu = document.createElement('div');
    menu.className = 'hwp-toolbar-dropdown-menu';
    menu.setAttribute('role', 'menu');
    menu.style.display = 'none';

    node.items!.forEach(item => {
      const itemEl = document.createElement('button');
      itemEl.className = 'hwp-toolbar-dropdown-item';
      itemEl.setAttribute('role', 'menuitem');
      const itemLabel = typeof item.label === 'function' ? item.label() : item.label || '';
      itemEl.textContent = itemLabel;
      if (item.onClick) {
        itemEl.onclick = () => {
          item.onClick!();
          closeMenu();
        };
      }
      menu.appendChild(itemEl);
    });

    const toggleMenu = () => {
      const isOpen = menu.style.display === 'block';
      menu.style.display = isOpen ? 'none' : 'block';
      trigger.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    };

    const closeMenu = () => {
      menu.style.display = 'none';
      trigger.setAttribute('aria-expanded', 'false');
    };

    // Click toggle
    trigger.onclick = toggleMenu;

    // Keyboard navigation
    trigger.onkeydown = (e: KeyboardEvent) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleMenu();
      } else if (e.key === 'Escape') {
        closeMenu();
      }
    };

    // Click outside to close
    const handleClickOutside = (e: MouseEvent) => {
      if (!container.contains(e.target as Node)) {
        closeMenu();
      }
    };

    trigger.addEventListener('click', () => {
      if (menu.style.display === 'block') {
        document.addEventListener('click', handleClickOutside);
      } else {
        document.removeEventListener('click', handleClickOutside);
      }
    });

    container.appendChild(trigger);
    container.appendChild(menu);
    return container;
  }

  private createUserButton(state: ToolbarState): HTMLElement {
    const userBtn = document.createElement('button');
    userBtn.className = 'hwp-toolbar-button hwp-toolbar-user';
    userBtn.textContent = `User: ${state.user!.name}`;
    userBtn.onclick = () => {
      if (confirm(`Logged in as: ${state.user!.name}\n\nLogout?`)) {
        this.toolbar.setState({ user: null, post: null, site: null });
        this.toolbar.clear();
      }
    };
    return userBtn;
  }

  destroy(): void {
    if (this.unsubscribe) this.unsubscribe();
    if (this.element) this.element.innerHTML = '';
  }
}
