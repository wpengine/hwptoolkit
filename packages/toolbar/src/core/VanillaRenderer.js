/**
 * Vanilla JavaScript DOM Renderer for Toolbar
 * @package @wpengine/hwp-toolbar
 */

import { Toolbar } from './Toolbar.js';

/**
 * Vanilla JavaScript renderer for the Toolbar
 * Renders toolbar using native DOM manipulation
 */
export class VanillaRenderer {
  /**
   * @param {Toolbar} toolbar - Toolbar instance
   * @param {string|HTMLElement} elementOrId - DOM element or element ID
   */
  constructor(toolbar, elementOrId) {
    /** @type {Toolbar} */
    this.toolbar = toolbar;

    /** @type {HTMLElement|null} */
    this.element = null;

    /** @type {(() => void)|null} */
    this.unsubscribe = null;

    /** @type {import('./Toolbar.js').ToolbarConfig} */
    this.config = toolbar.config || {};

    if (typeof elementOrId === 'string') {
      this.element = document.getElementById(elementOrId);
      if (!this.element) throw new Error(`Element "${elementOrId}" not found`);
    } else {
      this.element = elementOrId;
    }

    this.applyTheme();
    this.addBodyClass();
    this.unsubscribe = this.toolbar.subscribe((nodes, state) => {
      this.render(nodes, state);
    });
  }

  /**
   * Add body class for toolbar positioning
   * @private
   */
  addBodyClass() {
    const position = this.config.position || 'bottom';
    document.body.classList.add(`hwp-has-toolbar-${position}`);
  }

  /**
   * Remove body class for toolbar positioning
   * @private
   */
  removeBodyClass() {
    const position = this.config.position || 'bottom';
    document.body.classList.remove(`hwp-has-toolbar-${position}`);
  }

  /**
   * Apply theme configuration to element
   * @private
   */
  applyTheme() {
    if (!this.element) return;

    if (this.config.theme?.className) {
      this.element.classList.add(this.config.theme.className);
    }

    if (this.config.theme?.variables) {
      Object.entries(this.config.theme.variables).forEach(([key, value]) => {
        this.element.style.setProperty(key, value);
      });
    }
  }

  /**
   * Render toolbar to DOM
   * @private
   * @param {import('./Toolbar.js').ToolbarNode[]} nodes
   * @param {import('./Toolbar.js').ToolbarState} state
   */
  render(nodes, state) {
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

  /**
   * Create branding element
   * @private
   * @param {import('./Toolbar.js').ToolbarBranding} branding
   * @returns {HTMLElement}
   */
  createBrandingElement(branding) {
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

  /**
   * Create DOM element for a toolbar node
   * @private
   * @param {import('./Toolbar.js').ToolbarNode} node
   * @param {import('./Toolbar.js').ToolbarState} state
   * @returns {HTMLElement|null}
   */
  createNodeElement(node, state) {
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
      if (node.onClick) img.onclick = () => node.onClick();
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

    if (node.onClick) btn.onclick = () => node.onClick();
    return btn;
  }

  /**
   * Create dropdown menu element
   * @private
   * @param {import('./Toolbar.js').ToolbarNode} node
   * @param {import('./Toolbar.js').ToolbarState} state
   * @returns {HTMLElement}
   */
  createDropdown(node, state) {
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

    node.items.forEach(item => {
      const itemEl = document.createElement('button');
      itemEl.className = 'hwp-toolbar-dropdown-item';
      itemEl.setAttribute('role', 'menuitem');
      const itemLabel = typeof item.label === 'function' ? item.label() : item.label || '';
      itemEl.textContent = itemLabel;
      if (item.onClick) {
        itemEl.onclick = () => {
          item.onClick();
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
    trigger.onkeydown = (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        toggleMenu();
      } else if (e.key === 'Escape') {
        closeMenu();
      }
    };

    // Click outside to close
    const handleClickOutside = (e) => {
      if (!(e.target instanceof Element) || !container.contains(e.target)) {
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

  /**
   * Create user button element
   * @private
   * @param {import('./Toolbar.js').ToolbarState} state
   * @returns {HTMLElement}
   */
  createUserButton(state) {
    const userBtn = document.createElement('button');
    userBtn.className = 'hwp-toolbar-button hwp-toolbar-user';
    userBtn.textContent = `User: ${state.user.name}`;
    userBtn.onclick = () => {
      if (confirm(`Logged in as: ${state.user.name}\n\nLogout?`)) {
        this.toolbar.setState({ user: null, post: null, site: null });
        this.toolbar.clear();
      }
    };
    return userBtn;
  }

  /**
   * Clean up renderer and remove event listeners
   */
  destroy() {
    if (this.unsubscribe) this.unsubscribe();
    if (this.element) this.element.innerHTML = '';
    this.removeBodyClass();
  }
}
