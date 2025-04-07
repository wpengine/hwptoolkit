import { useState } from "react";

export default function CommentForm({
  onSubmit = () => {},
  isLoading,
  isSuccessful,
  errorMessage,
}) {
  const [comment, setComment] = useState({
    author: "",
    authorEmail: "",
    content: "",
  });

  const handleSubmit = (e) => {
    e.preventDefault();
    onSubmit(comment);
  };

  const handleChange = (e) =>
    setComment((prev) => ({
      ...prev,
      [e.target.name]: e.target.value,
    }));

  return (
    <fieldset className="max-w-2xl p-6 mx-auto space-y-12">
      <h2 className="text-2xl font-semibold text-center">Leave a Comment</h2>
      <form onSubmit={handleSubmit} className="space-y-4">
        <input
          type="text"
          name="author"
          placeholder="Your Name"
          value={comment.author}
          onChange={handleChange}
          required
          className="w-full p-3 bg-gray-50 shadow-sm rounded-md"
        />
        <input
          type="email"
          name="authorEmail"
          placeholder="Your Email"
          value={comment.authorEmail}
          onChange={handleChange}
          required
          className="w-full p-3 bg-gray-50 shadow-sm rounded-md"
        />
        <textarea
          name="content"
          placeholder="Your Comment"
          value={comment.content}
          onChange={handleChange}
          required
          rows="4"
          className="w-full p-3 bg-gray-50 shadow-sm rounded-md"
        ></textarea>

        {errorMessage && <p className="text-red-600 text-sm">{errorMessage}</p>}

        {isSuccessful && (
          <div className="flex items-center p-4 mb-4 text-sm rounded-lg bg-green-50 border border-green-200">
            <svg
              className="flex-shrink-0 w-5 h-5 mr-2 text-green-600"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fillRule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clipRule="evenodd"
              ></path>
            </svg>
            <span className="font-medium text-green-700">
              Comment added successfully! It will appear after moderation.
            </span>
          </div>
        )}

        <button
          type="submit"
          className="w-full bg-orange-600 text-white p-3 cursor-pointer rounded-md hover:bg-orange-700 transition disabled:bg-orange-700"
          disabled={isLoading}
        >
          {isLoading ? "Sending" : "Submit Comment"}
        </button>
      </form>
    </fieldset>
  );
}
