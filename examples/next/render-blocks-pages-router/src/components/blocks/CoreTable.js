import { getInlineStyles } from "@/utils/getInlineStyles";
import React from "react";
import { Caption } from "../Caption";

export function CoreTable({ attributes, customParser }) {
  const { caption, className, head, foot, body, align, backgroundColor, style } = attributes ?? {};
  const styles = getInlineStyles(style);

  const renderRows = (row, index) => <TableRow key={index} cells={row.cells} customParser={customParser} />;

  return (
    <table className={className} align={align} bgcolor={backgroundColor} style={styles}>
      <Caption as='caption' caption={caption} customParser={customParser} />

      {head && <thead>{head.map(renderRows)}</thead>}
      {body && <tbody>{body.map(renderRows)}</tbody>}
      {foot && <tfoot>{foot.map(renderRows)}</tfoot>}
    </table>
  );
}

function TableRow({ cells, customParser }) {
  const hasCustomParser = typeof customParser === "function";

  return (
    <tr>
      {cells.map((cell, index) => {
        const { align, colspan, content, rowspan, scope, tag: Component } = cell ?? {};

        const props = {
          align,
          colSpan: colspan,
          rowSpan: rowspan,
          scope,
        };

        if (hasCustomParser) {
          return (
            <Component key={index} {...props}>
              {customParser(content)}
            </Component>
          );
        }

        return <Component key={index} {...props} dangerouslySetInnerHTML={{ __html: content }} />;
      })}
    </tr>
  );
}

CoreTable.fragments = {
  key: `CoreTableBlockFragment`,
  entry: `
    fragment CoreTableBlockFragment on CoreTable {
      attributes {
        align
        anchor
        backgroundColor
        body {
          cells {
            align
            colspan
            tag
            rowspan
            content
            scope
          }
        }
        borderColor
        caption
        className
        fontFamily
        fontSize
        foot {
          cells {
            align
            colspan
            content
            rowspan
            scope
            tag
          }
        }
        gradient
        hasFixedLayout
        head {
          cells {
            align
            colspan
            content
            rowspan
            scope
            tag
          }
        }
        lock
        metadata
        style
        textColor
      }
    }
  `,
};
