import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreColumn({ attributes, children }) {
  const { style, cssClassName } = attributes ?? {};
  const styles = getInlineStyles(style);

  return (
    <div style={styles} className={cssClassName}>
      {children}
    </div>
  );
}

CoreColumn.fragments = {
  key: `CoreColumnBlockFragment`,
  entry: `
    fragment CoreColumnBlockFragment on CoreColumn {
      attributes {
        anchor
        borderColor
        backgroundColor
        cssClassName
        fontSize
        fontFamily
        gradient
        layout
        style
        textColor
        verticalAlignment
        width
      }
    }
  `,
};
