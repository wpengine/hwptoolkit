import { uriToTemplate } from "@/lib/templateHierarchy";
import { RouteDataContext } from "@/lib/context";
import Layout from "@/components/Layout";

export default function Page({ params, uri, templateData }) {
  return (
    <RouteDataContext.Provider value={{ params, uri, templateData }}>
      <Layout>
        <p>
          You shouldn't see this page if the template hierarchy is working
          correctly.
        </p>
      </Layout>
    </RouteDataContext.Provider>
  );
}

export async function getServerSideProps({ params }) {
  const uri = Array.isArray(params.uri)
    ? "/" + params.uri?.join("/") + "/"
    : "/";

  const templateData = await uriToTemplate({ uri });

  return {
    props: {
      params,
      uri,
      // https://github.com/vercel/next.js/discussions/11209#discussioncomment-35915
      templateData: JSON.parse(JSON.stringify(templateData)),
    },
  };
}
