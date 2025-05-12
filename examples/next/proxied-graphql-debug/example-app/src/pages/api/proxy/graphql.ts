import type { NextApiRequest, NextApiResponse } from "next";
import type { NextHttpProxyMiddlewareOptions } from "next-http-proxy-middleware";
import httpProxyMiddleware from "next-http-proxy-middleware";
import {
  parse,
  validate,
  visit,
  Kind,
  specifiedRules,
  GraphQLSchema,
} from "graphql";
import { schema } from "@/lib/graphql/getSchema";
import zlib from "node:zlib";
import chalk from "chalk";
import { getGraphqlPath } from "@/lib/client";

const isDevelopment = process.env.NODE_ENV !== "production";

export const config = {
  api: {
    externalResolver: true,
  },
};

type ComplexityOptions = {
  variables?: Record<string, any>;
  schema?: GraphQLSchema;
};

export const estimateComplexity = (
  query: string,
  options: ComplexityOptions = {}
) => {
  const ast = parse(query);
  let complexity = 0;

  visit(ast, {
    Field(node) {
      // Default: count every field
      let include = true;

      // Handle @skip and @include if schema is passed in
      if (options.schema && node.directives) {
        for (const directive of node.directives) {
          const name = directive.name.value;
          const ifArg = directive.arguments?.find(
            (arg) => arg.name.value === "if"
          );
          const ifValue =
            ifArg?.value.kind === Kind.VARIABLE
              ? options.variables?.[ifArg.value.name.value]
              : ifArg?.value.kind === Kind.BOOLEAN
              ? ifArg.value.value
              : true;

          if (name === "skip" && ifValue === true) {
            include = false;
          }
          if (name === "include" && ifValue === false) {
            include = false;
          }
        }
      }

      if (include) {
        complexity += 1;
      }
    },
    FragmentSpread() {
      complexity += 1;
    },
    InlineFragment() {
      complexity += 1;
    },
  });

  return complexity;
};

const handleProxyInit: NextHttpProxyMiddlewareOptions["onProxyInit"] = (
  proxy
) => {
  proxy.on("proxyRes", (proxyRes, req, res) => {
    let responseData: Buffer[] = [];

    proxyRes.on("data", (chunk) => {
      responseData.push(chunk);
    });

    proxyRes.on("end", () => {
      const buffer = Buffer.concat(responseData);
      const encoding = proxyRes.headers["content-encoding"];

      const handleDecompressed = (err: Error | null, decompressed: Buffer) => {
        if (err) {
          console.error(chalk.red.bold("❌ Decompression error:"), err);
          res.writeHead(proxyRes.statusCode || 500);
          return res.end(buffer);
        }

        try {
          const parsed = JSON.parse(decompressed.toString("utf8"));

          // Calculate query complexity if query is present
          let complexityValue = null;

          const body = JSON.parse((req as NextApiRequest).body); // Get the body from the request

          if (body && body.query) {
            const ast = parse(body.query);
            const errors = validate(schema, ast, specifiedRules);

            if (errors.length === 0) {
              try {
                const cost = estimateComplexity(body.query, {schema});
                console.log("Estimated query cost:", cost);
                complexityValue = cost;
              } catch (e) {
                console.error("Could not calculate complexity", e.message);
              }
            } else {
              console.warn(
                chalk.yellow(
                  "GraphQL validation errors, skipping complexity calc"
                )
              );
            }
          }

          // Inject extensions with query complexity
          parsed.extensions = {
            ...(parsed.extensions || {}),
            queryComplexity: {
              value: complexityValue,
              note:
                complexityValue !== null
                  ? "Calculated at proxy level"
                  : "Could not compute complexity",
            },
          };

          const updatedBody = JSON.stringify(parsed);
          const updatedBuffer = Buffer.from(updatedBody, "utf8");

          const sendResponse = (finalBuffer: Buffer) => {
            // Clean up content-length since content size changed
            const headers = { ...proxyRes.headers };
            delete headers["content-length"];

            res.writeHead(proxyRes.statusCode || 200, headers);
            res.end(finalBuffer);
          };

          // Handle different compression types
          if (encoding === "gzip") {
            zlib.gzip(updatedBuffer, (err, compressed) => {
              if (err) {
                console.error(chalk.red("❌ Gzip compression error:"), err);
                return sendResponse(updatedBuffer);
              }
              sendResponse(compressed);
            });
          } else if (encoding === "deflate") {
            zlib.deflate(updatedBuffer, (err, compressed) => {
              if (err) {
                console.error(chalk.red("❌ Deflate compression error:"), err);
                return sendResponse(updatedBuffer);
              }
              sendResponse(compressed);
            });
          } else {
            sendResponse(updatedBuffer);
          }
        } catch (e) {
          console.warn(
            chalk.yellow.bold(
              "⚠️ Non-JSON or invalid decompressed response received"
            )
          );
          res.writeHead(proxyRes.statusCode || 500);
          res.end(decompressed);
        }
      };

      // Decompress based on encoding type
      if (encoding === "gzip") {
        zlib.gunzip(buffer, handleDecompressed);
      } else if (encoding === "deflate") {
        zlib.inflate(buffer, handleDecompressed);
      } else {
        handleDecompressed(null, buffer);
      }
    });
  });
};

export default (req: NextApiRequest, res: NextApiResponse) => {
  if (!isDevelopment) {
    return res.status(404).send(null);
  }

  return httpProxyMiddleware(req, res, {
    target: getGraphqlPath(),
    changeOrigin: true,
    pathRewrite: [
      {
        patternStr: "^/api/proxy/graphql",
        replaceStr: "",
      },
    ],
    selfHandleResponse: true,
    onProxyInit: handleProxyInit,
  });
};
