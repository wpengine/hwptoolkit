import Head from "next/head";
import { BlogPostItem } from "../components/BlogPostItem";
import SearchInput from "../components/SearchInput";
import { client } from "../lib/client";
import { gql, useLazyQuery } from "@apollo/client";
import debounce from "debounce";

// Define the GraphQL query to list blog posts
const LIST_POSTS = gql`
  query ListPosts($after: String, $search: String = "") {
    posts(after: $after, first: 10, where: { search: $search }) {
      edges {
        node {
          id
          title
          uri
          excerpt
          date
          featuredImage {
            node {
              sourceUrl(size: LARGE)
              caption
            }
          }
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export default function Blog({ serverSideData }) {
  // Use Apollo Client's useLazyQuery hook to fetch posts on demand
  const [getPosts, { data: clientSideData, fetchMore }] = useLazyQuery(LIST_POSTS, {
    fetchPolicy: "cache-and-network",
  });

  // Use client-side data if available, otherwise fall back to server-side data
  const data = clientSideData ?? serverSideData;

  const { endCursor, hasNextPage } = data?.posts?.pageInfo ?? {};

  // Handle search input with debouncing
  const handleSearch = (searchString) =>
    getPosts({
      variables: {
        search: searchString,
      },
    });

  // Load more posts when the "Load more" button is clicked
  const loadMore = () => fetchMore({ variables: { after: endCursor } });

  return (
    <>
      <Head>
        <title>Home</title>
      </Head>

      <SearchInput onSearch={debounce(handleSearch, 200)} />

      {data?.posts?.edges?.map((item) => {
        const post = item.node;

        return <BlogPostItem key={post.id} post={post} />;
      })}

      {/* Only show the "Load more" button if there are more posts to fetch */}
      {hasNextPage && (
        <button
          onClick={loadMore}
          type='button'
          className='px-8 py-3 font-semibold rounded bg-gray-800 hover:bg-gray-700 text-gray-100 mx-auto block mt-8'>
          Load more
        </button>
      )}
    </>
  );
}

// Fetch the initial list of posts at request time using getServerSideProps
export async function getServerSideProps() {
  const { data } = await client.query({ query: LIST_POSTS });

  return {
    props: {
      serverSideData: data,
    },
  };
}
