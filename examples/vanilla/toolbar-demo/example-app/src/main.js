import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';
import './style.css';

// Create toolbar instance
const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
    alert(`Preview mode: ${enabled ? 'ON' : 'OFF'}\n\nIn production, this would trigger Next.js/framework preview mode`);
  }
});

// Create renderer
const renderer = new VanillaRenderer(toolbar, 'toolbar');

// Subscribe to state changes for display
toolbar.subscribe((nodes, state) => {
  document.getElementById('state').textContent = JSON.stringify(state, null, 2);
});

// Control functions
window.login = () => {
  toolbar.setState({
    user: {
      id: 1,
      name: 'Admin',
      email: 'admin@example.com'
    },
    site: {
      adminUrl: 'http://localhost:8888/wp-admin',
      url: 'http://localhost:8888'
    }
  });
  console.log('User logged in');
};

window.logout = () => {
  toolbar.setState({
    user: null,
    post: null,
    site: null,
    preview: false
  });
  toolbar.clear();
  console.log('User logged out');
};

window.addPost = () => {
  toolbar.setState({
    post: {
      id: 123,
      title: 'Hello World',
      type: 'post',
      status: 'draft',
      slug: 'hello-world'
    }
  });
  console.log('Post context added');
};

window.addPage = () => {
  toolbar.setState({
    post: {
      id: 456,
      title: 'About Us',
      type: 'page',
      status: 'publish',
      slug: 'about'
    }
  });
  console.log('Page context added');
};

window.clearPost = () => {
  toolbar.setState({ post: null, preview: false });
  console.log('Post context cleared');
};

let customNodeCount = 0;
window.addNode = () => {
  customNodeCount++;
  toolbar.register(
    `custom-${customNodeCount}`,
    `Custom ${customNodeCount}`,
    () => {
      console.log(`Custom node ${customNodeCount} clicked`);
      alert(`Custom Action ${customNodeCount}`);
    }
  );
  console.log(`Added custom node ${customNodeCount}`);
};

window.clearAll = () => {
  toolbar.clear();
  toolbar.setState({
    user: null,
    post: null,
    site: null,
    preview: false
  });
  console.log('Toolbar cleared');
};

// Initialize with home button
toolbar.register('home', 'Home', () => {
  console.log('Navigate to home');
  alert('Navigate to Homepage');
});

// Console greeting
console.log('%cHeadless WordPress Toolbar', 'font-size: 18px; font-weight: bold; color: #0073aa;');
console.log('For headless WordPress implementations');
console.log('');
console.log('Try this flow:');
console.log('1. Click "Login" to authenticate');
console.log('2. Click "Add Post" to load content');
console.log('3. See WordPress controls appear in toolbar');
