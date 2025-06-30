"use client";

import Link from "next/link";
import { useState } from "react";
import LoginModal from "./LoginModal";
import { useAuth } from "@/lib/AuthProvider";

export default function Header() {
  const [isOpen, setIsOpen] = useState(false);
  const { isLogged, logout } = useAuth();

  const toggleOpen = () => setIsOpen((prev) => !prev);

  return (
    <>
      <header className='bg-gray-800 text-white py-4 px-8 mb-8'>
        <div className='flex justify-between items-center max-w-4xl mx-auto'>
          <div className='text-3xl font-semibold'>
            <Link href='/'>Headless WordPress</Link>
          </div>

          <nav className='space-x-6'>
            <Link href='/' className='text-lg hover:underline'>
              Home
            </Link>

            {isLogged ? (
              <button onClick={logout} className='text-lg hover:underline cursor-pointer'>
                Logout
              </button>
            ) : (
              <button onClick={toggleOpen} className='text-lg hover:underline cursor-pointer'>
                Login
              </button>
            )}
          </nav>
        </div>
      </header>

      <LoginModal open={isOpen} onClose={toggleOpen} />
    </>
  );
}
