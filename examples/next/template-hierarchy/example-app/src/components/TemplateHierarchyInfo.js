export default function TemplateHierarchyInfo({ template, uri }) {
  return (
    <aside className="template-hierarchy-info">
      <section>
        <strong>URI:&nbsp;</strong>
        <code>{uri}</code>
      </section>
      <section>
        <strong>Possible Templates:&nbsp;</strong>
        <code>{template.possibleTemplates?.join("|")} </code>
      </section>
      <section>
        <strong>Available Templates:&nbsp;</strong>
        <code>
          {template.availableTemplates
            ?.map((template) => template.id)
            .join("|")}
        </code>
      </section>
      <section>
        <strong>Template:&nbsp;</strong>
        <code> {template.template?.id} </code>
      </section>
    </aside>
  );
}
