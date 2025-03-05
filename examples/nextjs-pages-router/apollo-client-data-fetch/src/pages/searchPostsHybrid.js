import { BlogPostItem } from "@/components/BlogPostItem";
import SearchInput from "@/components/SearchInput";
import { client } from "@/lib/client";
import { gql, useLazyQuery } from "@apollo/client";
import debounce from "debounce";

const SEARCH_POSTS = gql`
  query SearchPostsQuery($search: String = "") {
    posts(where: { search: $search }) {
      edges {
        node {
          id
          title
          uri
          excerpt
          date
        }
      }
    }
  }
`;

export default function SearchPostsHybrid({ initialPosts }) {
  const [getPosts, { data }] = useLazyQuery(SEARCH_POSTS, {
    fetchPolicy: "cache-and-network",
  });

  const posts = data ? data?.posts : initialPosts;

  const onSearch = (searchString) =>
    getPosts({
      variables: {
        search: searchString,
      },
    });

  return (
    <>
      <SearchInput onSearch={debounce(onSearch, 200)} />

      {posts?.edges?.map((item) => {
        const post = item.node;

        return <BlogPostItem key={post.id} post={post} />;
      })}
    </>
  );
}

export async function getServerSideProps() {
  const { data } = await client.query({ query: SEARCH_POSTS });
  const posts = data?.posts ?? [];

  return {
    props: {
      initialPosts: posts,
    },
  };
}
