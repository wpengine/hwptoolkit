import Image from 'next/image';
import { formatDate } from '@/lib/utils';
import Comments from '../comment/Comments';

export default function Post({ data }) {
  const { title, author, content, date, comments } = data ?? {};
  console.log(data);
  const commentsList = comments?.edges;

    return (
      <article className='max-w-2xl px-6 py-24 mx-auto space-y-12 '>
        <div className='w-full mx-auto space-y-4 text-center'>
          <h1 className='text-4xl font-bold leading-tight md:text-5xl'>{title}</h1>

          <p className=' text-gray-600'>
            {"by "}
            <span className='text-orange-600' itemProp='name'>
              {author?.node?.name}
            </span>
            {" on "}
            <time dateTime={date} className=' text-gray-600'>{formatDate(date)}</time>
          </p>

          {data.featuredImage?.node?.sourceUrl && (
            <div className="h-48 my-9 relative opacity-80 hover:opacity-100 transition-opacity ease-in-out">
                <Image
                  src={data.featuredImage.node.sourceUrl}
                  alt={data.featuredImage.node.altText || post.title}
                  fill
                  sizes="(max-width: 768px) 100vw, (max-width: 1200px) 50vw, 33vw"
                  className="object-cover"
                />
            </div>
          )}
        </div>
        <div className="text-gray-800 prose prose-p:my-4 max-w-none wp-content text-xl" dangerouslySetInnerHTML={{ __html: content }} />
        <Comments comments={commentsList} />
      </article>
    );
  }
