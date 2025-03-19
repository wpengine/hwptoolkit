import { getInlineStyles } from "@/utils/getInlineStyles";
import Image from "next/image.js";

export function CoreImage({ attributes }) {
  const { anchor, cssClassName, caption, width, height, src, style, alt, title } = attributes ?? {};
  const hasDimensions = typeof width === "number" && typeof height === "number";
  const styles = getInlineStyles(style);

  if (!src) {
    return null;
  }

  return (
    <figure id={anchor ?? undefined} className={cssClassName}>
      <LinkWrapper attributes={attributes}>
        {hasDimensions ? (
          <Image style={styles} src={src} width={width} height={height} alt={alt ?? ""} title={title ?? undefined} />
        ) : (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={src} style={styles} alt={alt ?? ""} title={title ?? undefined} />
        )}
      </LinkWrapper>

      {caption && <figcaption className='wp-element-caption'>{caption}</figcaption>}
    </figure>
  );
}

function LinkWrapper({ attributes, children }) {
  const { href, linkTarget, linkClass, rel } = attributes ?? {};

  if (!href) {
    return <>{children}</>;
  }

  return (
    <a href={href} target={linkTarget} className={linkClass} rel={rel}>
      {children}
    </a>
  );
}

CoreImage.fragments = {
  key: `CoreImageBlockFragment`,
  entry: `
    fragment CoreImageBlockFragment on CoreImage {
      attributes {
        align
        alt
        anchor
        borderColor
        caption
        className
        width
        url
        title
        style
        src
        sizeSlug
        rel
        lock
        linkTarget
        linkDestination
        linkClass
        href
        height
        cssClassName
      }
    }
  `,
};
