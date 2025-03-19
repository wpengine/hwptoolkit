import React from "react";

export function CoreSeparator({ attributes }) {
  const { cssClassName, anchor, style } = attributes ?? {};

  return <hr id={anchor} style={style} className={cssClassName} />;
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
