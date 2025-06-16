import Link from "next/link";
import { getApolloClient } from "@/lib/client";
import { gql } from "@apollo/client";

export default function Home({ posts }) {
  return (
    <div className="min-h-screen p-8 pb-20 sm:p-20">
      <main className="max-w-4xl mx-auto">
        <h1 className="text-4xl font-bold mb-8">WordPress Webhooks ISR Demo</h1>
        <p className="text-gray-600 mb-8">
          This example demonstrates Next.js ISR (Incremental Static Regeneration) with WordPress webhooks.
          When you update a post in WordPress, the webhook triggers revalidation of the specific page.
        </p>
        
        <h2 className="text-2xl font-semibold mb-6">Recent Posts</h2>
        
        {posts && posts.length > 0 ? (
          <div className="space-y-6">
            {posts.map((edge) => {
              const post = edge.node;
              return (
                <article key={post.id} className="border rounded-lg p-6 hover:shadow-lg transition-shadow">
                  <Link href={`/${post.uri}`}>
                    <h3 className="text-xl font-semibold mb-2 text-blue-600 hover:text-blue-800">
                      {post.title}
                    </h3>
                  </Link>
                  <p className="text-gray-600 text-sm mb-2">
                    By {post.author?.node?.name || 'Unknown'} on {new Date(post.date).toLocaleDateString()}
                  </p>
                  {post.excerpt && (
                    <div 
                      className="text-gray-700 line-clamp-3"
                      dangerouslySetInnerHTML={{ __html: post.excerpt }}
                    />
                  )}
                  <Link 
                    href={`/${post.uri}`}
                    className="inline-block mt-4 text-blue-600 hover:text-blue-800 font-medium"
                  >
                    Read more →
                  </Link>
                </article>
              );
            })}
          </div>
        ) : (
          <p className="text-gray-600">No posts found. Create some posts in WordPress admin.</p>
        )}
        
        <div className="mt-12 p-6 bg-gray-100 rounded-lg">
          <h3 className="font-semibold mb-2">Quick Links:</h3>
          <ul className="space-y-2 text-sm">
            <li>
              <a 
                href="http://localhost:8888/wp-admin/" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:text-blue-800"
              >
                WordPress Admin →
              </a> (username: admin, password: password)
            </li>
            <li>
              <a 
                href="http://localhost:8888/wp-admin/options-general.php?page=graphql-webhooks" 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:text-blue-800"
              >
                Webhooks Settings →
              </a>
            </li>
          </ul>
        </div>
      </main>
    </div>
  );
}

const GET_POSTS = gql`
  query GetPosts {
    posts(first: 10) {
      edges {
        node {
          id
          title
          uri
          date
          excerpt
          author {
            node {
              name
            }
          }
        }
      }
    }
  }
`;

export async function getStaticProps() {
  try {
    const { data } = await getApolloClient().query({
      query: GET_POSTS,
    });

    return {
      props: {
        posts: data?.posts?.edges || [],
      },
      revalidate: 60, // ISR: revalidate every 60 seconds
    };
  } catch (error) {
    console.error("Error fetching posts:", error);
    return {
      props: {
        posts: [],
      },
      revalidate: 60,
    };
  }
}
