import React from "react";

export function CoreParagraph({ attributes, customParser }) {
  const { anchor, cssClassName, content, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";

  const props = {
    style,
    id: anchor,
    className: cssClassName,
  };

  if (hasCustomParser) {
    return <p {...props}>{customParser(content)}</p>;
  }

  return <p {...props} dangerouslySetInnerHTML={{ __html: content || "" }} />;
}

CoreParagraph.fragments = {
  key: `CoreParagraphBlockFragment`,
  entry: `
    fragment CoreParagraphBlockFragment on CoreParagraph {
      attributes {
        cssClassName
        backgroundColor
        content
        style
        textColor
        fontSize
        fontFamily
        direction
        dropCap
        gradient
        align
        anchor
      }
    }
  `,
};
