import blockComponents from "@/components/blocks";
import DefaultBlock from "./DefaultBlock";

export function BlockRenderer({ blocks, defaultBlock, customParser }) {
  // If blocks is not an array, don't render anything
  if (!Array.isArray(blocks)) {
    return null;
  }

  return blocks.map((block) => {
    const { id, children, __typename, attributes } = block ?? {};

    // If there is no __typename, we can't determine which block to render
    if (!__typename) {
      console.error("Block is missing __typename. Please add it to your query.", block);

      return null;
    }

    const BlockComponent = blockComponents[__typename];

    // If there is no BlockComponent, render the default block
    if (!BlockComponent) {
      const CustomDefaultBlock = defaultBlock;

      return defaultBlock ? (
        <CustomDefaultBlock key={id} block={block} />
      ) : (
        <DefaultBlock key={id} renderedHtml={block.renderedHtml} />
      );
    }

    return (
      <BlockComponent key={id} attributes={attributes} customParser={customParser}>
        {children && <BlockRenderer blocks={children} defaultBlock={defaultBlock} customParser={customParser} />}
      </BlockComponent>
    );
  });
}
