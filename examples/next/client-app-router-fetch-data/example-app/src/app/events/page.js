import { EventListFragment } from "@/lib/fragments/EventListFragment";
import { CustomPostTypeTemplate } from "@/components/cpt/CustomPostTypeTemplate";

const EVENT_LIST_QUERY = `
${EventListFragment}
query GetEvents($after: String, $first: Int = 5) {
  events(where: {status: PUBLISH}, after: $after, first: $first) {
    edges {
      node {
        ...EventListFragment
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
`;

export default async function EventsPage(params) {
  return CustomPostTypeTemplate(EVENT_LIST_QUERY, "events", "Events");
}
