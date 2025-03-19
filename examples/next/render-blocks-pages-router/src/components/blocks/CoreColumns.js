import React from "react";

export function CoreColumns({ attributes, children }) {
  const { style, cssClassName } = attributes ?? {};

  return (
    <div style={style} className={cssClassName}>
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
