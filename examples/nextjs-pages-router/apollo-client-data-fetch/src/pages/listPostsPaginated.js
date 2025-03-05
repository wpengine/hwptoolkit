import { BlogPostItem } from "@/components/BlogPostItem";
import Loading from "@/components/Loading";
import { gql, useQuery } from "@apollo/client";

const LIST_POSTS_PAGINATED = gql`
  query ListPostsPaginatedQuery($after: String) {
    posts(after: $after, first: 10) {
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

export default function ListPostsPaginated() {
  const { loading, data, fetchMore } = useQuery(LIST_POSTS_PAGINATED);
  const { endCursor, hasNextPage } = data?.posts?.pageInfo ?? {};

  if (loading) return <Loading />;

  const loadMore = () => fetchMore({ variables: { after: endCursor } });

  return (
    <>
      <section>
        {data?.posts?.edges?.map((item) => {
          const post = item.node;

          return <BlogPostItem key={post.id} post={post} />;
        })}
      </section>

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
