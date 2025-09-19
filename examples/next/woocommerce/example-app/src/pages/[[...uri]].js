import { uriToTemplate } from "@/lib/templates/templateHierarchy";
import { RouteDataProvider } from "@/lib/templates/context";
import availableTemplates from "@/wp-templates";
import { fetchQueries } from "@/lib/templates/queryHandler";
import { useQuery, gql } from "@apollo/client";
import {navData} from "@/lib/navigation";

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
  //fix for template IDs with dashes to camelCase
  if (templateData.template?.id && templateData.template.id.includes("-")) {
    templateData.template.id = templateData.template.id
      .split("-")
      .map((word, index) => index === 0 ? word : word.charAt(0).toUpperCase() + word.slice(1))
      .join("");
  }
  const PageTemplate = availableTemplates[templateData.template?.id];

  const component = await PageTemplate.render.preload();
  const headerData = await navData();
  const graphqlData = await fetchQueries({
    queries: component.default.queries,
    context,
    props: {
      uri,
      templateData
    },
  });
  return {
    props: {
      uri,
      // https://github.com/vercel/next.js/discussions/11209#discussioncomment-35915
      templateData: JSON.parse(JSON.stringify(templateData)),
      graphqlData: JSON.parse(JSON.stringify(graphqlData)),
      headerData: JSON.parse(JSON.stringify(headerData)),
    },
  };
}
