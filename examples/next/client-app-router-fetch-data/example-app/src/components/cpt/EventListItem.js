import Link from 'next/link';
import Image from 'next/image';
import { formatDate } from '@/lib/utils';

export default function EventListItem({ post }) {
  const { author, content, eventFields, featuredImage, title, uri } = post;
  const { date, startTime, endTime } = eventFields;
  
  // Create an excerpt from the content (first 150 characters)
  const excerpt = content
    ? content.replace(/<[^>]*>/g, '').substring(0, 150) + (content.length > 150 ? '...' : '')
    : '';

  return (
    <article className='container max-w-4xl px-10 py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4'>
      <h2 className='mt-3'>
        <Link href={uri} title={title} className='text-2xl font-bold hover:underline'>
          {title}
        </Link>
      </h2>

      <div className="flex items-center mr-4 mb-3">
        <span>by {post.author.node.name}</span>
      </div>

      {/* Moved date and time here, below author, with icons */}
      <div className="flex flex-wrap gap-4 text-sm text-gray-600 mb-4">
        {date && (
          <div className="flex items-center">
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <time dateTime={date}>{formatDate(date)}</time>
          </div>
        )}

        {startTime && (
          <div className="flex items-center">
            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{startTime}{endTime ? ` - ${endTime}` : ''}</span>
          </div>
        )}
      </div>

      {post.featuredImage?.node?.sourceUrl && (
        <div className="h-48 my-6 relative">
          <Link href={uri} title={title} className='opacity-80 hover:opacity-100 transition-opacity ease-in-out'>
            <Image
              src={post.featuredImage.node.sourceUrl}
              alt={post.featuredImage.node.altText || post.title}
              fill
              sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
              className="object-cover"
            />
          </Link>
        </div>
      )}

      <div className='mt-2 mb-4'>
        {/* Display excerpt instead of full content */}
        <p>{excerpt}</p>
      </div>

      <Link href={uri} title="Read more" className='hover:underline text-orange-600 mt-4'>
        Read more
      </Link>
    </article>
  );
}
