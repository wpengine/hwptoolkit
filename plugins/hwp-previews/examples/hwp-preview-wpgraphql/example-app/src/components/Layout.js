import { useRouter } from "next/router";
import Header from "./Header";
import PreviewButton from "./PreviewButton";

export default function Layout({ children }) {
  const router = useRouter();

  return (
    <main className='bg-stone-100 text-gray-800 pb-16 min-h-screen'>
      <Header />

      {children}

      {router.isPreview && <PreviewButton />}
    </main>
  );
}
