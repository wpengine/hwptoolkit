import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreSeparator({ attributes }) {
  const { cssClassName, anchor, style } = attributes ?? {};
  const styles = getInlineStyles(style);

  return <hr id={anchor} style={styles} className={cssClassName} />;
}

CoreSeparator.fragments = {
  key: `CoreSeparatorBlockFragment`,
  entry: `
    fragment CoreSeparatorBlockFragment on CoreSeparator {
      attributes {
        align
        anchor
        opacity
        gradient
        backgroundColor
        style
        cssClassName
      }
    }
  `,
};
