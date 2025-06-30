"use client";

import React, { useState } from "react";
import { useAuth } from "@/lib/AuthProvider";

export default function LoginModal({ open = true, onClose = () => {} }) {
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const { isLoading, error, login } = useAuth();

  const handleSubmit = async (e) => {
    e.preventDefault();
    const result = await login(username, password);
    if (result.success) {
      onClose();
    }
  };

  if (!open) return null;

  return (
    <div className='fixed inset-0 z-50 flex items-center justify-center bg-gray-950/50'>
      <div className='container max-w-sm px-10 py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4 relative'>
        <button
          type='button'
          className='absolute top-3 right-3 text-gray-400 hover:text-gray-600'
          onClick={onClose}
          aria-label='Close'>
          <svg className='w-5 h-5' fill='none' stroke='currentColor' strokeWidth={2} viewBox='0 0 24 24'>
            <path strokeLinecap='round' strokeLinejoin='round' d='M6 18L18 6M6 6l12 12' />
          </svg>
        </button>
        <h2 className='text-2xl font-bold mb-4 text-center'>Login</h2>
        <form onSubmit={handleSubmit} className='space-y-4'>
          <input
            className='w-full px-3 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-400'
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            placeholder='Username'
            autoFocus
          />
          <input
            type='password'
            className='w-full px-3 py-2 border border-gray-200 rounded focus:outline-none focus:border-gray-400'
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            placeholder='Password'
          />
          <button
            type='submit'
            disabled={isLoading}
            className='w-full py-2 bg-orange-600 text-white rounded hover:bg-orange-700 transition disabled:opacity-50'>
            {isLoading ? "Logging in..." : "Login"}
          </button>
          {error && <div className='text-red-500 text-sm text-center'>{error}</div>}
        </form>
      </div>
    </div>
  );
}
