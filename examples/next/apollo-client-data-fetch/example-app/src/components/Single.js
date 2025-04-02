import { gql, useMutation } from "@apollo/client";
import CommentForm from "./CommentForm";
import Comments from "./Comments";
import Head from "next/head";

// GraphQL mutation to add a comment to a post
const ADD_COMMENT_TO_POST = gql`
  mutation AddCommentToPostQuery($author: String!, $authorEmail: String!, $commentOn: Int!, $content: String! = "") {
    createComment(input: { author: $author, authorEmail: $authorEmail, commentOn: $commentOn, content: $content }) {
      success
    }
  }
`;

export default function Single({ data }) {
  const { title, author, content, date, comments, databaseId, featuredImage } = data ?? {};

  const commentsList = comments?.edges;

  // useMutation hook to create a function to add a comment and track its state
  const [addComment, { data: commentData, loading: addingComment, error: commentError }] = useMutation(
    ADD_COMMENT_TO_POST,
    {
      errorPolicy: "all", // Handle errors gracefully
      fetchPolicy: "no-cache", // Ensure the mutation result is not cached
    }
  );

  return (
    <>
      <Head>
        <title>{title}</title>
      </Head>

      <article className='max-w-2xl px-6 py-24 mx-auto space-y-12 '>
        <div className='w-full mx-auto space-y-4 text-center'>
          <h1 className='text-4xl font-bold leading-tight md:text-5xl'>{title}</h1>

          {featuredImage && (
            <img src={featuredImage?.node?.sourceUrl} alt='' className='w-full h-72 object-cover rounded-lg mb-4' />
          )}

          <p className='text-sm text-gray-600'>
            {"by "}
            <span className='text-orange-600' itemProp='name'>
              {author?.node?.name}
            </span>
            {" on "}
            <time dateTime={date}>
              {new Date(date).toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
              })}
            </time>
          </p>
        </div>
        <div className='text-gray-800' dangerouslySetInnerHTML={{ __html: content }} />
      </article>

      <Comments comments={commentsList} />

      <CommentForm
        onSubmit={(inputs) => addComment({ variables: { ...inputs, commentOn: databaseId } })}
        isLoading={addingComment}
        errorMessage={commentError?.message}
        isSuccessful={commentData?.createComment?.success}
      />
    </>
  );
}
