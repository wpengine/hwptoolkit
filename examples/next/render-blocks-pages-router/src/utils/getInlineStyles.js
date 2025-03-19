import { getCSSRules } from "@wordpress/style-engine";

export function parseInlineStyles(styles) {
  const output = {};

  getCSSRules(styles).forEach((rule) => {
    output[rule.key] = rule.value;
  });

  return output;
}

export function getInlineStyles(style) {
  let styles;

  if (style) {
    try {
      styles = parseInlineStyles(JSON.parse(style));
    } catch (e) {
      console.error("Error parsing inline styles:", style, e);
      return styles;
    }
  }

  return styles;
}
