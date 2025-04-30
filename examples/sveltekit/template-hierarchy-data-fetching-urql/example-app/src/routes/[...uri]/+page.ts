import type { PageLoad } from "./$types";
import type { Component } from "svelte";
import { type TemplateQueries } from "$lib/queryHandler";

type WordPressTemplate = {
  default: Component;
  queries?: TemplateQueries;
};

export const load: PageLoad = async (event) => {
  const { data } = event;

  const template: WordPressTemplate = await import(
    `$wp/${data.templateData.template.id}.svelte`
  );

  return {
    ...data,
    template: template.default,
  };
};
