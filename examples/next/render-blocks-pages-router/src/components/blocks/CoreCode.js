import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreCode({ attributes, customParser }) {
  const { cssClassName, content, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";
  const styles = getInlineStyles(style);

  return (
    <pre style={styles} className={cssClassName}>
      {hasCustomParser ? (
        <code>{customParser(content)}</code>
      ) : (
        <code dangerouslySetInnerHTML={{ __html: content || "" }} />
      )}
    </pre>
  );
}

CoreCode.fragments = {
  key: `CoreCodeBlockFragment`,
  entry: `
    fragment CoreCodeBlockFragment on CoreCode {
      attributes {
        anchor
        backgroundColor
        borderColor
        className
        content
        cssClassName
        fontFamily
        fontSize
        gradient
        lock
        style
        textColor
      }
    }
  `,
};
