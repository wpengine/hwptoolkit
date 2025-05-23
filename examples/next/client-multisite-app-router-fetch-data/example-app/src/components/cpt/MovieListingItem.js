import Link from "next/link";
import { FeaturedImage } from "../image/FeaturedImage";
import { createExcerpt } from "@/lib/utils";

export default function MovieListingItem({ post }) {
  // Note: movieShowTimes is the WPGraphQL name assigned under settings for the field group
  const { content, movieShowTimes, title, uri } = post;
  const { daysOfTheWeek, screenTimes } = movieShowTimes || {};

  const screenTimesArray = screenTimes
    ? screenTimes.split(",").map((time) => time.trim())
    : [];

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

      <FeaturedImage
        post={post}
        uri={uri}
        title={title}
        classNames="h-48 my-6 relative"
      />

      {daysOfTheWeek && (
        <div className="flex flex-wrap gap-4 text-sm text-gray-600 my-4 max-w-full">
          <div className="flex items-start w-full">
            <span className="font-bold uppercase w-[140px] flex-shrink-0">
              Showing on:
            </span>
            <ul className="flex gap-2 flex-wrap w-[calc(100%-140px)]">
              {daysOfTheWeek.map((day, index) => (
                <li
                  key={index}
                  className="px-2 py-1 bg-black text-white text-xs font-bold uppercase"
                >
                  {day}
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}

      {daysOfTheWeek && screenTimesArray.length > 0 && (
        <div className="flex flex-wrap gap-4 text-sm text-gray-600 my-4 max-w-full">
          <div className="flex items-start w-full">
            <span className="font-bold uppercase w-[140px] flex-shrink-0">
              Screen Times:
            </span>
            <ul className="flex gap-2 flex-wrap w-[calc(100%-140px)]">
              {screenTimesArray.map((time, index) => (
                <li
                  key={index}
                  className="px-2 py-1 bg-black text-white text-xs font-bold uppercase"
                >
                  {time}
                </li>
              ))}
            </ul>
          </div>
        </div>
      )}

      <div className="mt-2 mb-4">
        <p>{createExcerpt(content)}</p>
      </div>

      <Link
        href={uri}
        title="View Show"
        className="hover:underline text-orange-600 mt-4"
      >
        View Show
      </Link>
    </article>
  );
}
