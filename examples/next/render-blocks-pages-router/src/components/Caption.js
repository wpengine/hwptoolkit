export function Caption({ as = "figcaption", caption, customParser }) {
  const Component = as;
  const hasCustomParser = typeof customParser === "function";

  const className = "wp-element-caption";

  if (!caption) {
    return null;
  }

  if (hasCustomParser) {
    return <Component className={className}>{customParser(caption)}</Component>;
  }

  return <Component className={className} dangerouslySetInnerHTML={{ __html: caption }} />;
}
