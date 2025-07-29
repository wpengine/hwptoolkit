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
        <nav>
          <ul>
            <li>
              <a href="/">Home</a>
            </li>
            <li>
              <a href="/hello-world">Sample Post</a>
            </li>
            <li>
              <a href="/sample-page">Sample Page</a>
            </li>
          </ul>
        </nav>
      </header>
      <main>{children}</main>
    </div>
  );
}
