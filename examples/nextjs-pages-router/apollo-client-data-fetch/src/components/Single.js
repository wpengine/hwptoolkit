export default function Single({ data }) {
  const { title, author, content, date } = data ?? {};

  return (
    <article className='max-w-2xl px-6 py-24 mx-auto space-y-12 '>
      <div className='w-full mx-auto space-y-4 text-center'>
        <h1 className='text-4xl font-bold leading-tight md:text-5xl'>{title}</h1>
        <p className='text-sm text-gray-600'>
          {"by "}
          <span className='text-orange-600' itemProp='name'>
            {author?.node?.name}
          </span>
          {" on "}
          <time dateTime={date}>
            {new Date(date).toLocaleDateString("en-US", {
              year: "numeric",
              month: "long",
              day: "numeric",
            })}
          </time>
        </p>
      </div>
      <div className='text-gray-800' dangerouslySetInnerHTML={{ __html: content }} />
    </article>
  );
}
