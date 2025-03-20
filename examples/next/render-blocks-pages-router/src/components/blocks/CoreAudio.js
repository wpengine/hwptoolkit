import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";

export function CoreAudio({ attributes }) {
  const { autoplay, caption, className, loop, src, style } = attributes ?? {};
  const styles = getInlineStyles(style);

  return (
    <figure className={className} style={styles}>
      {caption && <figcaption>{caption}</figcaption>}

      <audio controls autoPlay={autoplay} loop={loop} src={src}>
        Your browser does not support the audio element.
      </audio>
    </figure>
  );
}

CoreAudio.fragments = {
  key: `CoreAudioBlockFragment`,
  entry: `
    fragment CoreAudioBlockFragment on CoreAudio {
      attributes {
        align
        anchor
        autoplay
        blob
        caption
        className
        id
        lock
        loop
        metadata
        src
        style
      }
    }
  `,
};
