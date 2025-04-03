import { useState } from "react";

export default function CommentForm({ onSubmit = () => {}, isLoading, isSuccessful, errorMessage }) {
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
    <fieldset className='max-w-2xl p-6 mx-auto space-y-12'>
      <h2 className='text-2xl font-semibold text-center'>Leave a Comment</h2>
      <form onSubmit={handleSubmit} className='space-y-4'>
        <input
          type='text'
          name='author'
          placeholder='Your Name'
          value={comment.author}
          onChange={handleChange}
          required
          className='w-full p-3 bg-gray-50 shadow-sm rounded-md'
        />
        <input
          type='email'
          name='authorEmail'
          placeholder='Your Email'
          value={comment.authorEmail}
          onChange={handleChange}
          required
          className='w-full p-3 bg-gray-50 shadow-sm rounded-md'
        />
        <textarea
          name='content'
          placeholder='Your Comment'
          value={comment.content}
          onChange={handleChange}
          required
          rows='4'
          className='w-full p-3 bg-gray-50 shadow-sm rounded-md'></textarea>

        {errorMessage && <p className='text-red-600 text-sm'>{errorMessage}</p>}

        {isSuccessful && <p className='text-green-600 text-sm'>Comment added successfully!</p>}

        <button
          type='submit'
          className='w-full bg-orange-600 text-white p-3 cursor-pointer rounded-md hover:bg-orange-700 transition disabled:bg-orange-700'
          disabled={isLoading}>
          {isLoading ? "Sending" : "Submit Comment"}
        </button>
      </form>
    </fieldset>
  );
}
