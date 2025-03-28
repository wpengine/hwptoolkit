import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CorePreformatted({ attributes, customParser }) {
  const { cssClassName, content, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";
  const styles = getInlineStyles(style);

  const props = {
    style: styles,
    className: cssClassName,
  };

  if (hasCustomParser) {
    return <pre {...props}>{customParser(content)}</pre>;
  }

  return <pre {...props} dangerouslySetInnerHTML={{ __html: content || "" }} />;
}

CorePreformatted.fragments = {
  key: `CorePreformattedBlockFragment`,
  entry: `
    fragment CorePreformattedBlockFragment on CorePreformatted {
      attributes {
        anchor
        borderColor
        backgroundColor
        className
        content
        fontFamily
        fontSize
        lock
        gradient
        metadata
        style
        textColor
      }
    }
  `,
};
