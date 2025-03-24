import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreColumns({ attributes, children }) {
  const { style, cssClassName } = attributes ?? {};
  const styles = getInlineStyles(style);

  return (
    <div style={styles} className={cssClassName}>
      {children}
    </div>
  );
}

CoreColumns.fragments = {
  key: `CoreColumnsBlockFragment`,
  entry: `
    fragment CoreColumnsBlockFragment on CoreColumns {
      attributes {
        align
        anchor
        layout
        cssClassName
        isStackedOnMobile
        verticalAlignment
        borderColor
        backgroundColor
        fontSize
        fontFamily
        style
        textColor
        gradient
      }
    }
  `,
};
