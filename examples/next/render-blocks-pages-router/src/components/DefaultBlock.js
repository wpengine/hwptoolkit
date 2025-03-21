import React from "react";

export default function DefaultBlock({ renderedHtml }) {
  return <div dangerouslySetInnerHTML={{ __html: renderedHtml ?? "" }} />;
}
