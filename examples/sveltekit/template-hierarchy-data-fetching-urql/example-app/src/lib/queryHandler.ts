import type { ServerLoadEvent } from "@sveltejs/kit";
import { client, fetchAllPaginated } from "./client";
import type { OperationResult, TypedDocumentNode } from "@urql/core";

export interface TemplateQuery {
  name?: string;
  stream?: boolean;
  query: TypedDocumentNode;
  paginate?: (responseData: any) => boolean;
  variables?: (event: ServerLoadEvent) => Record<string, any>;
}

export interface QueryResults {
  name: string;
  response: Promise<OperationResult> | OperationResult;
  variables: Record<string, any>;
}
export type TemplateQueries = TemplateQuery[];

export async function fetchQueries({
  queries,
  event,
}: {
  queries?: TemplateQueries;
  event: ServerLoadEvent;
}) {
  if (!queries || queries.length === 0) {
    console.error("No queries provided");
    return {};
  }

  const { fetch } = event;

  let queryPromises: PromiseLike<QueryResults>[] = [];
  let streamedQueries: QueryResults[] = [];

  for (const query of queries) {
    if (!query.query) {
      console.error("Query is undefined");
      continue;
    }

    const queryName = query.name || query.query.definitions[0].name?.value;

    if (!queryName) {
      console.error("Query name is undefined, skipping query");
      continue;
    }

    const queryVariables = query.variables ? query.variables(event) : {};

    const queryResp = !query.paginate
      ? client.query(query.query, queryVariables)
      : fetchAllPaginated(fetch, query.query, query.paginate);

    if (query.stream) {
      streamedQueries.push({
        name: queryName,
        response: queryResp?.toPromise(),
        variables: queryVariables,
      });
      continue;
    }

    queryPromises.push(
      queryResp.then((response) => ({
        name: queryName,
        response,
        variables: queryVariables,
      }))
    );
  }

  const allSettledQueries = await Promise.all(queryPromises);

  const allResponses: {
    [key: string]: QueryResults;
  } = {};

  for (const query of [...allSettledQueries, ...streamedQueries]) {
    allResponses[query.name] = query;
  }

  return allResponses;
}
