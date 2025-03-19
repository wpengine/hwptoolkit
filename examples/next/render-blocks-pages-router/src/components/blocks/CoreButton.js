import React from "react";
import Link from "next/link.js";

export function CoreButton({ attributes }) {
  const { anchor, cssClassName, text, style, linkClassName, url, rel, linkTarget } = attributes ?? {};

  const wrapperProps = {
    "id": anchor,
    "className": cssClassName,
    "aria-label": text,
  };

  const linkElement = (
    <a target={linkTarget ? "_blank" : undefined} className={linkClassName} rel={rel} style={style}>
      <span>{text}</span>
    </a>
  );

  if (url) {
    return (
      <div {...wrapperProps}>
        <Link legacyBehavior href={url}>
          {linkElement}
        </Link>
      </div>
    );
  }

  return <div {...wrapperProps}>{linkElement}</div>;
}

CoreButton.fragments = {
  key: `CoreButtonBlockFragment`,
  entry: `
    fragment CoreButtonBlockFragment on CoreButton {
      attributes {
        anchor
        gradient
        text
        textAlign
        textColor
        style
        fontSize
        fontFamily
        linkTarget
        rel
        url
        backgroundColor
        cssClassName
        linkClassName
      }
    }
  `,
};
