import React from "react";

export function CoreHeading({ attributes, customParser }) {
  const { anchor, cssClassName, content, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";

  // Determine the heading level
  const Component = `h${attributes?.level ?? 1}`;

  const props = {
    style,
    id: anchor,
    className: cssClassName,
  };

  if (hasCustomParser) {
    return <Component {...props}>{customParser(content)}</Component>;
  }

  return <Component {...props} dangerouslySetInnerHTML={{ __html: content || "" }} />;
}

CoreHeading.fragments = {
  key: `CoreHeadingBlockFragment`,
  entry: `
    fragment CoreHeadingBlockFragment on CoreHeading {
      attributes {
        align
        anchor
        backgroundColor
        content
        fontFamily
        fontSize
        gradient
        level
        style
        textAlign
        textColor
        cssClassName
      }
    }
  `,
};
