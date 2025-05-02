import { useRouter } from 'next/navigation';

export const useNextNavigation = () => {
  const router = useRouter();
  return {
    push: (path) => router.push(path),
  };
};