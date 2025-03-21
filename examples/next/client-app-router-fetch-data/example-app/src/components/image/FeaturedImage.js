import Link from "next/link";
import Image from "next/image";

export function FeaturedImage({
  post,
  classNames = "h-48 my-9 relative",
  uri = false,
  title = "",
}) {
  if (!post.featuredImage?.node?.sourceUrl) {
    return "";
  }

  return (
    <div className={classNames}>
      {typeof uri === "string" && uri.trim() !== "" ? (
        <Link
          href={uri}
          title={title}
          className="opacity-80 hover:opacity-100 transition-opacity ease-in-out"
        >
          <Image
            src={post.featuredImage.node.sourceUrl}
            alt={post.featuredImage.node.altText || post.title}
            fill
            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
            className="object-cover"
          />
        </Link>
      ) : (
        <Image
          src={post.featuredImage.node.sourceUrl}
          alt={post.featuredImage.node.altText || post.title}
          fill
          sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
          className="object-cover"
        />
      )}
    </div>
  );
}
