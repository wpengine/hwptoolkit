export default function Footer() {
    return (
      <footer className="bg-gray-800 text-white">
        <div className="container mx-auto px-4 py-6 text-center">
          <p className="text-sm">
            &copy; {new Date().getFullYear()} DemoSite. All rights reserved.
          </p>
          <nav className="mt-4 space-x-4">
            <a href="/privacy" className="hover:text-teal-400">
              Privacy Policy
            </a>
            <a href="/terms" className="hover:text-teal-400">
              Terms of Service
            </a>
          </nav>
        </div>
      </footer>
    );
  }
