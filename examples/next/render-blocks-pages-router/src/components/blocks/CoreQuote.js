import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreQuote({ attributes, customParser }) {
  const { value, cssClassName, citation, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";
  const styles = getInlineStyles(style);

  if (!value) {
    return null;
  }

  const content = citation ? value + `<cite>${citation}</cite>` : value;
  const props = {
    style: styles,
    className: cssClassName,
  };

  if (hasCustomParser) {
    return <blockquote {...props}>{customParser(content)}</blockquote>;
  }

  return <blockquote {...props} dangerouslySetInnerHTML={{ __html: content }} />;
}

CoreQuote.fragments = {
  key: `CoreQuoteBlockFragment`,
  entry: `
    fragment CoreQuoteBlockFragment on CoreQuote {
      attributes {
        textAlign
        anchor
        backgroundColor
        citation
        className
        fontFamily
        fontSize
        gradient
        lock
        style
        textColor
        value
        cssClassName
      }
    }
  `,
};
