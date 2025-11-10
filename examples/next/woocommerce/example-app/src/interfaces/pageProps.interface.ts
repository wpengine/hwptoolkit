import { HeaderData } from "@/interfaces/navigation.interface";
import { ReactNode } from "react";

export interface PageProps {
	children: ReactNode;
	pageProps: {
		uri: string;
		templateData: TemplateData | null;
		graphqlData: GraphQL | null;
		headerData: HeaderData | null;
	};
}
export interface TemplateData {
	availableTemplates: Template[];
	possibleTemplates: string[];
	seedQuery: string;
	template?: Template | null;
}
export interface GraphQL {
	[key: string]: any;
}
interface Template {
	id: string;
	path: string;
}
