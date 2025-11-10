export interface NavigationItem {
	id: string;
	label: string;
	uri: string;
	parentId?: string;
	target?: string;
	cssClasses?: string[];
	title?: string;
	description?: string;
	icon?: string;
	children?: NavigationItem[];
}

export interface HeaderData {
	navigation: {
		menu?: {
			menuItems?: {
				nodes: NavigationItem[];
			};
		};
	};
	settings: {
		generalSettings: {
			title: string;
		};
	};
}
export interface FooterData {
	settings: {
		generalSettings: {
			title: string;
		};
	};
}
