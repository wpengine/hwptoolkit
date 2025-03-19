import React from "react";

export function CoreList({ attributes, children }) {
  const { values, cssClassName, ordered, reversed, start, style } = attributes ?? {};

  if (!values) {
    return null;
  }

  const Component = ordered ? "ol" : "ul";

  return (
    <Component
      style={style}
      className={cssClassName}
      reversed={ordered && reversed ? true : undefined}
      start={ordered && start ? start : undefined}>
      {children}
    </Component>
  );
}

CoreList.fragments = {
  key: `CoreListBlockFragment`,
  entry: `
    fragment CoreListBlockFragment on CoreList {
      attributes {
        anchor
        backgroundColor
        className
        fontFamily
        fontSize
        gradient
        lock
        ordered
        reversed
        start
        style
        textColor
        type
        values
        cssClassName
      }
    }
  `,
};
