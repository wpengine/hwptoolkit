export default function Comments({ comments }) {
  return (
    <div className="max-w-2xl p-6 mx-auto space-y-12">
      <h2 className="text-2xl font-semibold text-center">Comments</h2>
      <div className="space-y-6">
        {comments?.length > 0 ? (
          comments.map(({ node: c }, index) => (
            <div
              key={index}
              className="p-4 shadow-sm  mx-auto rounded-lg shadow-sm bg-gray-50 mb-4"
            >
              <p className="text-sm text-gray-500">
                {new Date(c.date).toLocaleDateString("en-US", {
                  year: "numeric",
                  month: "long",
                  day: "numeric",
                })}
              </p>
              <p className="font-semibold mt-1 text-orange-600">
                {c.author?.node?.name}
              </p>

              <div
                className="mt-2 text-gray-800"
                dangerouslySetInnerHTML={{ __html: c.content }}
              />
            </div>
          ))
        ) : (
          <p className="text-gray-500 text-center">No comments yet.</p>
        )}
      </div>
    </div>
  );
}
