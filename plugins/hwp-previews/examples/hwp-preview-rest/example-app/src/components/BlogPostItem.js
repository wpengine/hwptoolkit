import Link from "next/link";

export function BlogPostItem({ post }) {
  const { title, date, excerpt } = post ?? {};
  const uri = `/posts/${post.slug}`;

  return (
    <article className='container max-w-4xl px-10 py-6 mx-auto rounded-lg shadow-sm bg-gray-50 mb-4'>
      <time dateTime={date} className='text-sm text-gray-600'>
        {new Date(date).toLocaleDateString("en-US", {
          year: "numeric",
          month: "long",
        })}
      </time>

      <h2 className='mt-3'>
        <Link
          href={uri}
          className='text-2xl font-bold hover:underline'
          dangerouslySetInnerHTML={{ __html: title.rendered }}
        />
      </h2>

      <div className='mt-2 mb-4' dangerouslySetInnerHTML={{ __html: excerpt.rendered }} />

      <Link href={uri} className='hover:underline text-orange-600 mt-4'>
        Read more
      </Link>
    </article>
  );
}
