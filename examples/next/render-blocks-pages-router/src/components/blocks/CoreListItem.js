import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreListItem({ attributes, children, customParser }) {
  const { content, className, style } = attributes ?? {};
  const hasCustomParser = typeof customParser === "function";
  const styles = getInlineStyles(style);

  if (!content) {
    return null;
  }

  const ownContent = content ? content.split("\n")[0] : "";

  return (
    <li style={styles} className={className}>
      {hasCustomParser ? customParser(content) : <div dangerouslySetInnerHTML={{ __html: ownContent }} />}

      {children}
    </li>
  );
}

CoreListItem.fragments = {
  key: `CoreListItemFragment`,
  entry: `
    fragment CoreListItemFragment on CoreListItem {
      attributes {
        content
        anchor
        backgroundColor
        borderColor
        className
        fontFamily
        fontSize
        gradient
        lock
        metadata
        placeholder
        style
        textColor
      }
    }
  `,
};
