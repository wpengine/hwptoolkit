import Link from "next/link";
import { FeaturedImage } from "../image/FeaturedImage";
import { formatDate } from "@/lib/utils";

export default function BlogListItem({ post }) {
  const { title, excerpt, uri } = post;

  return (
    <article className="container max-w-4xl px-4 lg:px-10 py-2 lg:py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4">
      <time dateTime={post.date} className="text-sm text-gray-600">
        {formatDate(post.date)}
      </time>

      <h2 className="mt-3">
        <Link
          href={uri}
          title={title}
          className="text-2xl font-bold hover:underline"
        >
          {title}
        </Link>
      </h2>

      <div className="flex items-center mr-4">
        <span>by {post.author.node.name}</span>
      </div>

      <FeaturedImage
        post={post}
        uri={uri}
        title={title}
        classNames="h-48 my-9 relative"
      />

      <div
        className="mt-2 mb-4"
        dangerouslySetInnerHTML={{ __html: excerpt }}
      />

      <Link
        href={uri}
        title="Read more"
        className="hover:underline text-orange-600 mt-4"
      >
        Read more
      </Link>
    </article>
  );
}
