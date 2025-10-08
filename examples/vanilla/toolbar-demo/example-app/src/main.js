import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';
import './style.css';

const WP_URL = 'http://localhost:8888';

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

// Fetch real WordPress user
window.login = async () => {
  try {
    // For demo: just set mock authenticated user
    // In production, you'd authenticate first via WordPress login
    const response = await fetch(`${WP_URL}/?rest_route=/wp/v2/users/1`);

    if (response.ok) {
      const user = await response.json();
      toolbar.setState({
        user: {
          id: user.id,
          name: user.name,
          email: user.email || 'admin@example.com'
        },
        site: {
          adminUrl: `${WP_URL}/wp-admin`,
          url: WP_URL
        }
      });
      console.log('User logged in:', user.name);
    } else {
      console.error('WordPress not available');
      alert('WordPress is not running at ' + WP_URL);
    }
  } catch (error) {
    console.error('Error connecting to WordPress:', error);
    alert('Error: Make sure WordPress is running (npm run wp:start)');
  }
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

// Fetch real posts from WordPress GraphQL
window.fetchPosts = async () => {
  try {
    const response = await fetch(`${WP_URL}/?graphql`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        query: `
          query GetPosts {
            posts(first: 5) {
              nodes {
                databaseId
                title
                slug
                status
              }
            }
          }
        `
      })
    });

    const { data } = await response.json();

    if (data?.posts?.nodes?.length > 0) {
      const posts = data.posts.nodes;
      console.log('Fetched posts:', posts);

      // Display posts in UI
      const postsDiv = document.getElementById('wordpress-posts');
      postsDiv.innerHTML = '<h3>WordPress Posts:</h3>';
      posts.forEach(post => {
        const btn = document.createElement('button');
        btn.textContent = post.title;
        btn.onclick = () => loadPost(post);
        postsDiv.appendChild(btn);
      });
    }
  } catch (error) {
    console.error('Error fetching posts:', error);
    alert('Error fetching posts. Make sure WPGraphQL is installed.');
  }
};

function loadPost(post) {
  toolbar.setState({
    post: {
      id: post.databaseId,
      title: post.title,
      type: 'post',
      status: post.status,
      slug: post.slug
    }
  });
  console.log('Post loaded:', post.title);
}

// Mock data functions (for when WordPress isn't running)
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
  console.log('Mock post added');
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
  console.log('Mock page added');
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
  window.location.href = '/';
});

// Console greeting
console.log('%cHeadless WordPress Toolbar', 'font-size: 18px; font-weight: bold; color: #0073aa;');
console.log('WordPress at:', WP_URL);
console.log('');
console.log('Try this flow:');
console.log('1. Click "Login" to fetch real WP user');
console.log('2. Click "Fetch Posts" to load real posts');
console.log('3. Click a post to add it to toolbar');
console.log('4. See WordPress controls appear in toolbar');
