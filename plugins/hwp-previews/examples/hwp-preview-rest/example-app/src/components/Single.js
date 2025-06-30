export default function Single({ data, featuredImage }) {
  const { title, content } = data ?? {};

  return (
    <article className='max-w-2xl px-6 py-24 mx-auto space-y-12 '>
      <div className='w-full mx-auto space-y-4 text-center'>
        <h1
          className='text-4xl font-bold leading-tight md:text-5xl'
          dangerouslySetInnerHTML={{ __html: title.rendered }}
        />
      </div>

      {featuredImage && (
        <img src={featuredImage.source_url} alt='' className='w-full h-72 object-cover rounded-lg mb-4' />
      )}

      <div className='text-gray-800' dangerouslySetInnerHTML={{ __html: content.rendered }} />
    </article>
  );
}
