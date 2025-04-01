export default function Page({ data }) {
  const { title, content } = data ?? {};

  return (
    <article className="max-w-4xl px-6 py-24 mx-auto space-y-12 ">
      <div className="w-full mx-auto space-y-4 text-center">
        <h1 className="text-4xl font-bold leading-tight md:text-5xl">
          {title}
        </h1>
      </div>
      <div
        className="text-gray-800 prose prose-p:my-4 max-w-none wp-content text-xl"
        dangerouslySetInnerHTML={{ __html: content }}
      />
    </article>
  );
}
