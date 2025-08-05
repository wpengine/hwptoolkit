import Head from "next/head";

export default function Single({ data }) {
  const { title, content, featuredImage } = data ?? {};

  return (
    <>
      <Head>
        <title>{title}</title>
      </Head>

      <article className='max-w-2xl px-6 py-24 mx-auto space-y-12 '>
        <div className='w-full mx-auto space-y-4 text-center'>
          <h1 className='text-4xl font-bold leading-tight md:text-5xl'>{title}</h1>
        </div>

        {featuredImage && (
          <img src={featuredImage?.node?.sourceUrl} alt='' className='w-full h-72 object-cover rounded-lg mb-4' />
        )}

        <div className='text-gray-800' dangerouslySetInnerHTML={{ __html: content }} />
      </article>
    </>
  );
}