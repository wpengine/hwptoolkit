import Single from "@/components/Single";
import { getTokenServerSide } from "@/lib/authUtils";
import { fetchWP } from "@/lib/fetchWP";
import { cookies } from "next/headers";
import { notFound } from "next/navigation";

const getPage = async (identifier, isPreview) => {
  const cookieStore = await cookies();
  const jwtToken = getTokenServerSide(cookieStore);
  const data = await fetchWP(isPreview ? `pages/${identifier}` : `pages/?slug=${identifier}`, jwtToken);

  return isPreview ? data : data[0];
};

export default async function Page({ params, searchParams }) {
  const { isPreview } = await searchParams;
  const page = await getPage(await params.identifier, isPreview);

  if (!page) {
    return notFound();
  }

  const featuredImage = await fetchWP(`media/${page.featured_media}`);

  return <Single data={page} featuredImage={featuredImage} />;
}

export async function generateMetadata({ params }) {
  const page = await getPage(await params.identifier);

  if (!page) {
    return {};
  }

  const description = page.excerpt.rendered.replace(/<\/?[^>]+(>|$)/g, "");

  return {
    title: page.title.rendered,
    description,
    openGraph: {
      title: page.title.rendered,
      description,
    },
  };
}
