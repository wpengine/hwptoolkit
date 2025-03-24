import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreButtons({ attributes, children }) {
  const { style, anchor, cssClassName } = attributes ?? {};
  const styles = getInlineStyles(style);

  return (
    <div style={styles} id={anchor} className={cssClassName}>
      {children}
    </div>
  );
}

CoreButtons.fragments = {
  key: `CoreButtonsBlockFragment`,
  entry: `
    fragment CoreButtonsBlockFragment on CoreButtons {
      attributes {
        cssClassName
        align
        anchor
        fontFamily
        fontSize
        layout
        style
      }
    }
  `,
};
