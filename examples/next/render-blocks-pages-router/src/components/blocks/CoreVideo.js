import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";
import { Caption } from "../Caption";

export function CoreVideo({ attributes, customParser }) {
  const { autoplay, caption, className, controls, muted, preload, poster, loop, playsInline, src, style } =
    attributes ?? {};
  const styles = getInlineStyles(style);

  return (
    <figure className={className} style={styles}>
      <video
        src={src}
        controls={controls}
        muted={muted}
        loop={loop}
        preload={preload}
        poster={poster}
        autoPlay={autoplay}
        playsInline={playsInline}>
        Your browser does not support the video element.
      </video>

      <Caption caption={caption} customParser={customParser} />
    </figure>
  );
}

CoreVideo.fragments = {
  key: `CoreVideoBlockFragment`,
  entry: `
    fragment CoreVideoBlockFragment on CoreVideo {
      attributes {
        anchor
        autoplay
        caption
        className
        controls
        id
        lock
        loop
        muted
        playsInline
        poster
        preload
        src
        style
      }
    }
  `,
};
