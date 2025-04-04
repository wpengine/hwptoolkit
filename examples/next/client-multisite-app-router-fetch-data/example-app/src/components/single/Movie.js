import { FeaturedImage } from "../image/FeaturedImage";
import { Button } from "../button/Button";

export default function Movie({ data }) {
  const { content, movieShowTimes, title, uri } = data;
  const { daysOfTheWeek, screenTimes } = movieShowTimes || {};

  const screenTimesArray = screenTimes
    ? screenTimes.split(",").map((time) => time.trim())
    : [];

  return (
    <article className="max-w-4xl px-6 py-24 mx-auto space-y-12">
      <div className="w-full mx-auto space-y-4 text-center">
        <h1 className="text-4xl font-bold leading-tight md:text-5xl">
          {title}
        </h1>
      </div>

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

      <FeaturedImage
        post={data}
        title={title}
        classNames="h-48 my-9 relative opacity-80 hover:opacity-100 transition-opacity ease-in-out"
      />

      <div
        className="text-gray-800 prose prose-p:my-4 max-w-none wp-content text-xl"
        dangerouslySetInnerHTML={{ __html: content }}
      />

      <div className="flex flex-col items-center">
        <Button text="Book Show" href="/bookings" />
      </div>
    </article>
  );
}
