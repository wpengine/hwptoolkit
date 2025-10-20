/**
 * Headless WordPress Toolbar - Core Class
 * @package @wpengine/hwp-toolbar
 */

/**
 * @typedef {Object} WordPressUser
 * @property {number} id
 * @property {string} name
 * @property {string} [email]
 * @property {string} [avatar]
 */

/**
 * @typedef {Object} WordPressPost
 * @property {number} id
 * @property {string} title
 * @property {string} type
 * @property {string} status
 * @property {string} slug
 */

/**
 * @typedef {Object} WordPressSite
 * @property {string} url
 * @property {string} adminUrl
 */

/**
 * @typedef {Object} ToolbarState
 * @property {WordPressUser|null} user
 * @property {WordPressPost|null} post
 * @property {WordPressSite|null} site
 * @property {boolean} preview
 * @property {boolean} isHeadless
 */

/**
 * @typedef {Object} ToolbarBranding
 * @property {string} [logo]
 * @property {string} [title]
 * @property {string} [url]
 * @property {'left'|'center'|'right'} [position]
 */

/**
 * @typedef {Object} ToolbarTheme
 * @property {Record<string, string>} [variables]
 * @property {string} [className]
 */

/**
 * @typedef {Object} ToolbarConfig
 * @property {'top'|'bottom'} [position]
 * @property {ToolbarBranding} [branding]
 * @property {ToolbarTheme} [theme]
 * @property {(enabled: boolean) => void} [onPreviewChange]
 */

/**
 * @typedef {() => void} NodeCallback
 */

/**
 * @typedef {() => string} LabelFunction
 */

/**
 * @typedef {'button'|'link'|'image'|'dropdown'|'divider'|'custom'} NodeType
 */

/**
 * @typedef {'left'|'center'|'right'} NodePosition
 */

/**
 * @typedef {(state: ToolbarState) => HTMLElement} CustomRenderFunction
 */

/**
 * @typedef {Object} ToolbarNode
 * @property {string} id
 * @property {NodeType} [type]
 * @property {string|LabelFunction} [label]
 * @property {NodeCallback} [onClick]
 * @property {string} [href]
 * @property {string} [target]
 * @property {string} [src]
 * @property {string} [alt]
 * @property {ToolbarNode[]} [items]
 * @property {CustomRenderFunction} [render]
 * @property {string} [className]
 * @property {NodePosition} [position]
 */

/**
 * Core Toolbar Class
 * Framework-agnostic state management for headless WordPress toolbar
 */
export class Toolbar {
  /**
   * @param {ToolbarConfig} [config={}]
   */
  constructor(config = {}) {
    /** @type {Map<string, ToolbarNode>} */
    this.nodes = new Map();

    /** @type {ToolbarState} */
    this.state = {
      user: null,
      post: null,
      site: null,
      preview: false,
      isHeadless: true
    };

    /** @type {Set<(nodes: ToolbarNode[], state: ToolbarState) => void>} */
    this.listeners = new Set();

    /** @type {ToolbarConfig} */
    this.config = {
      position: 'bottom',
      ...config
    };

    this.registerDefaultNodes();
  }

  /**
   * Get current configuration
   * @returns {ToolbarConfig}
   */
  getConfig() {
    return { ...this.config };
  }

  /**
   * Update configuration
   * @param {Partial<ToolbarConfig>} updates
   * @returns {this}
   */
  setConfig(updates) {
    this.config = { ...this.config, ...updates };
    this.notify();
    return this;
  }

  /**
   * Register default WordPress-specific nodes
   * @private
   */
  registerDefaultNodes() {
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

  /**
   * Register a toolbar node
   * @param {string} id
   * @param {string|LabelFunction|Partial<ToolbarNode>} labelOrNode
   * @param {NodeCallback} [onClick]
   * @returns {this}
   */
  register(id, labelOrNode, onClick) {
    if (typeof labelOrNode === 'object') {
      // register(id, nodeConfig)
      this.nodes.set(id, {
        id,
        type: 'button',
        ...labelOrNode
      });
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

  /**
   * Unregister a toolbar node
   * @param {string} id
   * @returns {this}
   */
  unregister(id) {
    this.nodes.delete(id);
    this.notify();
    return this;
  }

  /**
   * Clear all custom nodes and reset to defaults
   * @returns {this}
   */
  clear() {
    this.nodes.clear();
    this.registerDefaultNodes();
    this.notify();
    return this;
  }

  /**
   * Update state
   * @param {Partial<ToolbarState>} updates
   * @returns {this}
   */
  setState(updates) {
    this.state = { ...this.state, ...updates };
    this.notify();
    return this;
  }

  /**
   * Set WordPress-specific context (user, post, site)
   * @param {Object} context
   * @param {WordPressUser|null} [context.user]
   * @param {WordPressPost|null} [context.post]
   * @param {WordPressSite|null} [context.site]
   * @returns {this}
   */
  setWordPressContext(context) {
    return this.setState(context);
  }

  /**
   * Get current state
   * @returns {ToolbarState}
   */
  getState() {
    return { ...this.state };
  }

  /**
   * Get visible nodes based on current state
   * @returns {ToolbarNode[]}
   */
  getVisibleNodes() {
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

  /**
   * Subscribe to state changes
   * @param {(nodes: ToolbarNode[], state: ToolbarState) => void} callback
   * @returns {() => void} Unsubscribe function
   */
  subscribe(callback) {
    this.listeners.add(callback);
    callback(this.getVisibleNodes(), this.getState());
    return () => this.listeners.delete(callback);
  }

  /**
   * Notify all listeners of state changes
   * @private
   */
  notify() {
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

  /**
   * Clean up toolbar instance
   */
  destroy() {
    this.nodes.clear();
    this.listeners.clear();
  }
}
