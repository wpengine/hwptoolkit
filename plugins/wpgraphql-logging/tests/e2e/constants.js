export const PLUGIN_SLUG = "wpgraphql-logging";
export const RESET_HELPER_PLUGIN_SLUG = "reset-wpgraphql-logging-settings";

export const GET_POSTS_QUERY = `
	query GetPosts {
		posts(first: 5) {
			nodes {
				id
				title
				date
				excerpt
				author {
					node {
						id
						name
					}
				}
			}
		}
	}
`;
