import { Toolbar, VanillaRenderer } from '@wpengine/hwp-toolbar';
import '@wpengine/hwp-toolbar/styles';
import './style.css';

const WP_URL = import.meta.env.VITE_WP_URL;

/**
 * Initialize Toolbar
 */
const toolbar = new Toolbar({
  onPreviewChange: (enabled) => {
    console.log('Preview mode:', enabled);
    alert(`Preview mode: ${enabled ? 'ON' : 'OFF'}\n\nIn production, this would trigger Next.js/framework preview mode`);
  }
});

const renderer = new VanillaRenderer(toolbar, 'toolbar');

/**
 * Register Custom Nodes
 */
toolbar.register('home', 'Home', () => {
  window.location.href = '/';
});

/**
 * State Management
 */
toolbar.subscribe((nodes, state) => {
  document.getElementById('state').textContent = JSON.stringify(state, null, 2);
});

/**
 * Demo Actions
 */
window.login = async () => {
  try {
    const response = await fetch(`${WP_URL}/?rest_route=/wp/v2/users/1`);

    if (response.ok) {
      const user = await response.json();

      // Use setWordPressContext for WordPress-specific state
      toolbar.setWordPressContext({
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
    } else if (response.status === 404) {
      console.error('User not found:', response.status);
      alert(`User not found (404). WordPress may need initial setup.\n\nVisit: ${WP_URL}/wp-admin`);
    } else if (response.status >= 500) {
      console.error('WordPress server error:', response.status);
      alert(`WordPress server error (${response.status}).\n\nCheck that WordPress is running:\nnpm run wp:start`);
    } else {
      console.error('WordPress API error:', response.status);
      alert(`WordPress API returned ${response.status}.\n\nCheck WordPress configuration.`);
    }
  } catch (error) {
    console.error('Error connecting to WordPress:', error);

    if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
      alert(`Cannot connect to WordPress at ${WP_URL}\n\nPossible issues:\n• WordPress is not running (run: npm run wp:start)\n• CORS is not configured\n• Wrong WordPress URL\n\nCheck the console for details.`);
    } else {
      alert(`Error: ${error.message}\n\nCheck the console for details.`);
    }
  }
};

window.logout = () => {
  // Clear WordPress context
  toolbar.setWordPressContext({
    user: null,
    post: null,
    site: null
  });

  // Reset preview mode
  toolbar.setState({ preview: false });

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
  // Use setWordPressContext for WordPress post data
  toolbar.setWordPressContext({
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

// Console greeting
console.log('%cHeadless WordPress Toolbar', 'font-size: 18px; font-weight: bold; color: #0073aa;');
console.log('WordPress at:', WP_URL);
console.log('');
console.log('Try this flow:');
console.log('1. Click "Login" to fetch real WP user');
console.log('2. Click "Fetch Posts" to load real posts');
console.log('3. Click a post to add it to toolbar');
console.log('4. See WordPress controls appear in toolbar');
