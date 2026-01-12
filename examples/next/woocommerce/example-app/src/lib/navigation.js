import { useRouter } from "next/navigation";
import { gql } from "@apollo/client";
import { client } from "./client";

export const useNextNavigation = () => {
	const router = useRouter();
	return {
		push: (path) => router.push(path),
	};
};
const SETTINGS_QUERY = gql`
	query HeaderSettingsQuery {
		generalSettings {
			title
		}
	}
`;

const NAVIGATION_QUERY = gql`
	query HeaderNavigationQuery {
		menu(id: "primary", idType: LOCATION) {
			menuItems(first: 100) {
				nodes {
					id
					label
					uri
					parentId
					target
					cssClasses
					title
					description
				}
			}
		}
	}
`;
export const navData = async () => {
	const [settingsResult, navigationResult] = await Promise.all([
		client.query({
			query: SETTINGS_QUERY,
			fetchPolicy: "no-cache",
		}),
		client.query({
			query: NAVIGATION_QUERY,
			fetchPolicy: "no-cache",
		}),
	]);
	return {
		settings: settingsResult.data,
		navigation: navigationResult.data,
	};
};
