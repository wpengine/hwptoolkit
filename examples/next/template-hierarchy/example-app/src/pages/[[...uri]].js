export default function Page({ params }) {
  return (
    <div>
      <h1>Dynamic Route Example</h1>
      <p>URI: {"/" + params.uri.join("/") + "/"}</p>
    </div>
  );
}

export async function getServerSideProps(context) {
  const { params } = context;
  return {
    props: {
      params,
    },
  };
}
