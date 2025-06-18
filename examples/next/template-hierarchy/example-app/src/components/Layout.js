import { useContext } from "react";
import { RouteDataContext } from "@/lib/context";
import TemplateHierarchyInfo from "@/components/TemplateHierarchyInfo";

export default function Layout({ children }) {
  const { templateData, uri } = useContext(RouteDataContext);

  return (
    <div className="layout">
      <TemplateHierarchyInfo template={templateData} uri={uri} />
      <header>
        <h1>Template Hierarchy Example</h1>
      </header>
      <main>{children}</main>
    </div>
  );
}
