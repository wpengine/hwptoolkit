<script>
  import { page } from "$app/state";

  const template = page.data.templateData;
</script>

<aside class="template-hierarchy-info">
  <section>
    <strong>URI:</strong>
    <code>{page.data.uri}</code>
  </section>
  <section>
    <strong>Possible Templates:</strong>
    <code> {template.possibleTemplates?.join("|")} </code>
  </section>
  <section>
    <strong>Available Templates:</strong>
    <code>
      {template.availableTemplates?.map((template) => template.id).join("|")}
    </code>
  </section>
  <section>
    <strong>Template: </strong>
    <code> {template.template?.id} </code>
  </section>

  <style>
    .template-hierarchy-info {
      display: flex;
      flex-direction: row;
      flex-wrap: wrap;
      background-color: #f2f483;
      padding: 0.5em;
      border-radius: 5px;

      & section {
        padding: 0.5em;
        border-right: 1px solid black;

        &:last-child {
          border-right: none;
        }
      }
    }
  </style>
</aside>
