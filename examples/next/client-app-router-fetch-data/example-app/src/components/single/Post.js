"use client";

import { FeaturedImage } from "../image/FeaturedImage";
import { formatDate } from "@/lib/utils";
import Comments from "../comment/Comments";
import CommentForm from "../comment/CommentForm";
import { useState } from "react";
import { fetchGraphQL } from "@/lib/client";

const AddCommentToPostMutation = `
mutation AddCommentToPostQuery($author: String!, $authorEmail: String!, $commentOn: Int!, $content: String! = "") {
  createComment(input: { author: $author, authorEmail: $authorEmail, commentOn: $commentOn, content: $content }) {
    success
  }
}`;

export default function Post({ data }) {
  const { title, author, content, date, comments, databaseId } = data ?? {};
  const commentsList = comments?.edges;

  const [commentStatus, setCommentStatus] = useState({
    loading: false,
    error: null,
    success: false,
  });

  const addComment = async (inputs) => {
    setCommentStatus({ loading: true, error: null, success: false });

    try {
      const result = await fetchGraphQL(AddCommentToPostMutation, {
        ...inputs,
        commentOn: databaseId,
      });

      if (result.errors) {
        throw new Error(
          result.errors[0]?.message || "Error submitting comment",
        );
      }

      setCommentStatus({
        loading: false,
        error: null,
        success: result?.createComment?.success,
      });
    } catch (error) {
      setCommentStatus({
        loading: false,
        error: error.message,
        success: false,
      });
    }
  };

  return (
    <article className="max-w-2xl px-6 py-24 mx-auto space-y-12">
      <div className="w-full mx-auto space-y-4 text-center">
        <h1 className="text-4xl font-bold leading-tight md:text-5xl">
          {title}
        </h1>

        <p className=" text-gray-600">
          {"by "}
          <span className="text-orange-600" itemProp="name">
            {author?.node?.name}
          </span>
          {" on "}
          <time dateTime={date} className=" text-gray-600">
            {formatDate(date)}
          </time>
        </p>

        <FeaturedImage
          post={data}
          title={title}
          classNames="h-48 my-9 relative opacity-80 hover:opacity-100 transition-opacity ease-in-out"
        />
      </div>
      <div
        className="text-gray-800 prose prose-p:my-4 max-w-none wp-content text-xl"
        dangerouslySetInnerHTML={{ __html: content }}
      />
      <Comments comments={commentsList} />

      <CommentForm
        onSubmit={addComment}
        isLoading={commentStatus.loading}
        errorMessage={commentStatus.error}
        isSuccessful={commentStatus.success}
      />
    </article>
  );
}
