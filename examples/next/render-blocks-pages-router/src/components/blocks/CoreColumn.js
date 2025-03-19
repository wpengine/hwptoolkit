import React from "react";

export function CoreColumn({ attributes, children }) {
  const { style, cssClassName } = attributes ?? {};

  return (
    <div style={style} className={cssClassName}>
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
