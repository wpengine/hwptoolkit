import Link from 'next/link';
import Image from 'next/image';
import { formatDate } from '@/lib/utils';

export default function BlogPostItem({ post }) {

  const { title, excerpt, uri } = post;

  return (
    <article className='container max-w-4xl px-10 py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4'>
      <time dateTime={post.date} className='text-sm text-gray-600'>{formatDate(post.date)}</time>

      <h2 className='mt-3'>
        <Link href={uri} title={title} className='text-2xl font-bold hover:underline'>
          {title}
        </Link>
      </h2>

      <div className="flex items-center mr-4">
          <span>by {post.author.node.name}</span>
      </div>

      {post.featuredImage?.node?.sourceUrl && (
        <div className="h-48 my-9 relative">
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

      <div className='mt-2 mb-4' dangerouslySetInnerHTML={{ __html: excerpt }} />

      <Link href={uri} title="Read more" className='hover:underline text-orange-600 mt-4'>
        Read more
      </Link>
    </article>
  );
}
