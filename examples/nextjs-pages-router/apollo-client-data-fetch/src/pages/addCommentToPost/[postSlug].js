import CommentForm from "@/components/CommentForm";
import Comments from "@/components/Comments";
import CouldNotLoad from "@/components/CouldNotLoad";
import Loading from "@/components/Loading";
import Single from "@/components/Single";
import { gql, useMutation, useQuery } from "@apollo/client";
import { useRouter } from "next/router";

const GET_POST = gql`
  query GetPost($slug: ID!) {
    post(id: $slug, idType: SLUG) {
      ...PostFragment
      content
      comments {
        edges {
          node {
            ...CommentFragment
          }
        }
      }
    }
  }
`;

const ADD_COMMENT_TO_POST = gql`
  mutation AddCommentToPostQuery($author: String!, $authorEmail: String!, $commentOn: Int!, $content: String! = "") {
    createComment(input: { author: $author, authorEmail: $authorEmail, commentOn: $commentOn, content: $content }) {
      success
    }
  }
`;

export default function AddCommentToPost() {
  const router = useRouter();

  const { loading, data, error } = useQuery(GET_POST, {
    variables: { slug: router.query.postSlug },
  });

  const comments = data?.post?.comments?.edges;
  const postDbId = data?.post?.databaseId;

  const [addComment, { data: commentData, loading: addingComment, error: commentError }] = useMutation(
    ADD_COMMENT_TO_POST,
    {
      errorPolicy: "all",
      fetchPolicy: "no-cache",
    }
  );

  if (loading) return <Loading />;

  if (error || !data?.post) return <CouldNotLoad />;

  return (
    <>
      <Single data={data?.post} />

      <Comments comments={comments} />

      <CommentForm
        onSubmit={(inputs) => addComment({ variables: { ...inputs, commentOn: postDbId } })}
        isLoading={addingComment}
        errorMessage={commentError?.message}
        isSuccessful={commentData?.createComment?.success}
      />
    </>
  );
}
