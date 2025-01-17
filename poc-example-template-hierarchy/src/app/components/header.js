// components/Header.js
export default function Header() {
    return (
      <header className="bg-gray-800 text-white">
        <div className="container mx-auto px-4 py-4 flex justify-between items-center">
          <a href="/" className="text-2xl font-bold text-teal-400">
            DemoSite
          </a>
          <nav className="space-x-4">
            <a href="/" className="hover:text-teal-400">
              Home
            </a>
            <a href="/about" className="hover:text-teal-400">
              About
            </a>
            <a href="/blog" className="hover:text-teal-400">
              Blog
            </a>
            <a href="/contact" className="hover:text-teal-400">
              Contact
            </a>
          </nav>
        </div>
      </header>
    );
  }
  