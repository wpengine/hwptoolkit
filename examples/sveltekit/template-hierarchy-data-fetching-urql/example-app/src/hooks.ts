import type { Reroute } from "@sveltejs/kit";
import { uriToTemplate } from "$lib/templateHierarchy";

const excludeReroutes: string[] = ["not-wordpress-example"];

export const reroute: Reroute = async ({ url, fetch }) => {
  //
  if (!url.searchParams.has("reroute")) {
    return url.pathname;
  }

  // If the path is a WordPress template, we want to block direct access to it
  if (url.pathname.startsWith("/wp-templates/")) {
    console.log("Blocking direct access to wp-template path:", url.pathname);
    return "/404";
  }

  // To avoid calling WordPress for every route, we can add exemptions
  if (excludeReroutes.includes(url.pathname)) {
    console.log("Excluding reroute for path:", url.pathname);
    return url.pathname;
  }

  // Now we can reroute the path to the WordPress template
  const { uri, template } = await uriToTemplate({ fetch, uri: url.pathname });

  console.log("Data from WordPress:", template);

  console.log("Rerouting:", uri, "to", template?.path);
  return template?.path;
};
