import { uriToTemplate } from "@/lib/templateHierarchy";
import { RouteDataContext } from "@/lib/context";
import availableTemplates from "@/wp-templates";

export default function Page(props) {
  const { templateData } = props;

  const PageTemplate = availableTemplates[templateData.template?.id];

  return (
    <RouteDataContext.Provider value={props}>
      <PageTemplate {...props} />
    </RouteDataContext.Provider>
  );
}

export async function getServerSideProps({ params }) {
  const uri = Array.isArray(params.uri)
    ? "/" + params.uri?.join("/") + "/"
    : "/";

  const templateData = await uriToTemplate({ uri });

  if (
    !templateData?.template?.id ||
    templateData?.template?.id === "404 Not Found"
  ) {
    return {
      notFound: true,
    };
  }

  return {
    props: {
      params,
      uri,
      // https://github.com/vercel/next.js/discussions/11209#discussioncomment-35915
      templateData: JSON.parse(JSON.stringify(templateData)),
    },
  };
}
