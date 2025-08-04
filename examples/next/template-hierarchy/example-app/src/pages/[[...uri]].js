import { uriToTemplate } from "@/lib/templateHierarchy";
import { RouteDataProvider } from "@/lib/context";
import availableTemplates from "@/wp-templates";
import { fetchQueries } from "@/lib/queryHandler";

export default function Page(props) {
  const { templateData } = props;

  const PageTemplate = availableTemplates[templateData.template?.id];

  return (
    <RouteDataProvider value={props}>
      <PageTemplate {...props} />
    </RouteDataProvider>
  );
}

export async function getServerSideProps(context) {
  const { params } = context;
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

  const PageTemplate = availableTemplates[templateData.template?.id];

  const component = await PageTemplate.render.preload();

  const graphqlData = await fetchQueries({
    queries: component.default.queries,
    context,
    props: {
      uri,
      templateData,
    },
  });

  return {
    props: {
      uri,
      // https://github.com/vercel/next.js/discussions/11209#discussioncomment-35915
      templateData: JSON.parse(JSON.stringify(templateData)),
      graphqlData: JSON.parse(JSON.stringify(graphqlData)),
    },
  };
}
