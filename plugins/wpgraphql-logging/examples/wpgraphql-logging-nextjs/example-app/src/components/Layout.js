import Header from "./Header";

export default function Layout({ children }) {
  return (
    <main className="bg-stone-100 text-gray-800 pb-16 min-h-screen">
      <Header />
      {children}
    </main>
  );
}
