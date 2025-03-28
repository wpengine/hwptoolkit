import Link from "next/link";
import { FeaturedImage } from "../image/FeaturedImage";
import { formatDate, createExcerpt } from "@/lib/utils";

export default function EventListItem({ post }) {
  const { content, eventFields, title, uri } = post;
  const { date, startTime, endTime } = eventFields;

  const locations = post.location?.edges?.map((edge) => edge.node.name) || [];
  return (
    <article className="container max-w-4xl px-4 lg:px-10 py-2 lg:py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4">
      <h2 className="mt-3">
        <Link
          href={uri}
          title={title}
          className="text-2xl font-bold hover:underline"
        >
          {title}
        </Link>
      </h2>

      <div className="flex flex-wrap gap-4 text-sm text-gray-600 my-2">
        {date && (
          <div className="flex items-center">
            <svg
              className="w-4 h-4 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              ></path>
            </svg>
            <time dateTime={date}>{formatDate(date)}</time>
          </div>
        )}

        {startTime && (
          <div className="flex items-center">
            <svg
              className="w-4 h-4 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              ></path>
            </svg>
            <span>
              {startTime}
              {endTime ? ` - ${endTime}` : ""}
            </span>
          </div>
        )}

        {locations.length > 0 && (
          <div className="flex items-center">
            <svg
              className="w-4 h-4 mr-1"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
              ></path>
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth="2"
                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
              ></path>
            </svg>
            <span>{locations.join(", ")}</span>
          </div>
        )}
      </div>

      <FeaturedImage
        post={post}
        uri={uri}
        title={title}
        classNames="h-48 my-6 relative"
      />

      <div className="mt-2 mb-4">
        <p>{createExcerpt(content)}</p>
      </div>

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
