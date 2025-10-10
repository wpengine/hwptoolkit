'use client';

import { toolbar } from '@/lib/toolbar';
import { useToolbarState } from '@wpengine/hwp-toolbar/react';
import { getCurrentUser, getPosts } from '@/lib/wordpress';
import { useState } from 'react';

export default function Home() {
  const state = useToolbarState(toolbar);
  const [posts, setPosts] = useState<any[]>([]);

  const handleConnect = async () => {
    try {
      // Test WordPress connection by fetching posts
      await getPosts();

      // Connection successful - set mock user for demo
      const user = await getCurrentUser();
      toolbar.setWordPressContext({
        user: {
          id: user.id,
          name: user.name,
          email: user.email
        },
        site: {
          url: process.env.NEXT_PUBLIC_WP_URL,
          adminUrl: `${process.env.NEXT_PUBLIC_WP_URL}/wp-admin`,
        },
      });

      console.log('User logged in:', user.name);
    } catch (error) {
      console.error('Error connecting to WordPress:', error);
      const message = error instanceof Error ? error.message : 'Unknown error occurred';
      alert(`Error connecting to WordPress:\n\n${message}`);
    }
  };

  const handleLogout = () => {
    toolbar.setWordPressContext({
      user: null,
      post: null,
      site: null,
    });
    toolbar.setState({ preview: false });
    setPosts([]);
    console.log('User logged out');
  };

  const handleFetchPosts = async () => {
    try {
      const wpPosts = await getPosts();
      console.log('Fetched posts:', wpPosts);

      if (wpPosts.length > 0) {
        setPosts(wpPosts);
      }
    } catch (error) {
      console.error('Error fetching posts:', error);
      const message = error instanceof Error ? error.message : 'Unknown error occurred';
      alert(`Error fetching posts:\n\n${message}`);
    }
  };

  const loadPost = (post: any) => {
    toolbar.setWordPressContext({
      post: {
        id: post.id,
        title: post.title.rendered,
        type: post.type,
        status: post.status,
        slug: post.slug,
      },
    });
    console.log('Post loaded:', post.title.rendered);
  };

  return (
    <main className="container">
      <h1>Headless WordPress Toolbar Demo</h1>
      <p>A framework-agnostic toolbar for headless WordPress sites</p>

      <section className="controls">
        <h2>Demo Controls</h2>
        <button onClick={handleConnect}>Login</button>
        <button onClick={handleLogout}>Logout</button>
        <button onClick={handleFetchPosts}>Fetch Posts</button>
        <div id="wordpress-posts">
          {posts.length > 0 && (
            <>
              <h3>WordPress Posts:</h3>
              {posts.map((post) => (
                <button key={post.id} onClick={() => loadPost(post)}>
                  {post.title.rendered}
                </button>
              ))}
            </>
          )}
        </div>
      </section>

      <section className="state">
        <h2>Current State</h2>
        <pre id="state">{JSON.stringify(state, null, 2)}</pre>
      </section>

      <section className="info">
        <h2>Try This</h2>
        <ol>
          <li>Click "Login" to authenticate with WordPress</li>
          <li>Click "Fetch Posts" to load posts from WordPress</li>
          <li>Click a post to view toolbar controls</li>
          <li>Check the state below to see how it updates</li>
        </ol>
      </section>
    </main>
  );
}
