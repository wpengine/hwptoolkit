import Single from "@/components/Single";
import { getTokenServerSide } from "@/lib/authUtils";
import { fetchWP } from "@/lib/fetchWP";
import { cookies } from "next/headers";
import { notFound } from "next/navigation";

const getPost = async (identifier, isPreview) => {
  const cookieStore = await cookies();
  const jwtToken = getTokenServerSide(cookieStore);
  const data = await fetchWP(isPreview ? `posts/${identifier}` : `posts/?slug=${identifier}`, jwtToken);

  return isPreview ? data : data[0];
};

export default async function Post({ params, searchParams }) {
  const { isPreview } = await searchParams;
  const post = await getPost(await params.identifier, isPreview);

  if (!post) {
    return notFound();
  }

  const featuredImage = await fetchWP(`media/${post.featured_media}`);

  return <Single data={post} featuredImage={featuredImage} />;
}

export async function generateMetadata({ params }) {
  const post = await getPost(await params.identifier);

  if (!post) {
    return {};
  }

  const description = post.excerpt.rendered.replace(/<\/?[^>]+(>|$)/g, "");

  return {
    title: post.title.rendered,
    description,
    openGraph: {
      title: post.title.rendered,
      description,
    },
  };
}
