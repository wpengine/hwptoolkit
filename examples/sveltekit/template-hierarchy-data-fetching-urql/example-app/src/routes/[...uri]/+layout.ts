import type { LayoutLoad } from "./$types";
import { fetchQueries } from "$lib/queryHandler";

import { query as NavQuery } from "$components/Nav.svelte";

export const load: LayoutLoad = async (event) => {
  const queryResults = await fetchQueries({ queries: [NavQuery], event });

  return {
    layoutData: queryResults,
  };
};
