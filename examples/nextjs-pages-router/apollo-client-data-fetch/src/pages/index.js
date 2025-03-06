import { BlogPostItem } from "@/components/BlogPostItem";
import SearchInput from "@/components/SearchInput";
import { client } from "@/lib/client";
import { gql, useLazyQuery } from "@apollo/client";
import debounce from "debounce";

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
  const [getPosts, { data: clientSideData, fetchMore }] = useLazyQuery(LIST_POSTS, {
    fetchPolicy: "cache-and-network",
  });

  const data = clientSideData ?? serverSideData;

  const { endCursor, hasNextPage } = data?.posts?.pageInfo ?? {};

  const handleSearch = (searchString) =>
    getPosts({
      variables: {
        search: searchString,
      },
    });

  const loadMore = () => fetchMore({ variables: { after: endCursor } });

  return (
    <>
      <SearchInput onSearch={debounce(handleSearch, 200)} />

      {data?.posts?.edges?.map((item) => {
        const post = item.node;

        return <BlogPostItem key={post.id} post={post} />;
      })}

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

export async function getServerSideProps() {
  const { data } = await client.query({ query: LIST_POSTS });

  return {
    props: {
      serverSideData: data,
    },
  };
}
