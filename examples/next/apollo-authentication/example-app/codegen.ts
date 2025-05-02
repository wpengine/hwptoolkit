import type { CodegenConfig } from "@graphql-codegen/cli";
import dotenv from "dotenv";
import path from 'path';
dotenv.config({path: path.resolve(process.cwd(), '.env.local')});

const baseUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "https://your-wordpress-site.com";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";
const introspectionToken = process.env.GRAPHQL_INTROSPECTION_TOKEN || "";

const config: CodegenConfig = {
  schema: [
    {
      [`${baseUrl}${graphqlPath}`]: {
        headers: {
          Authorization: introspectionToken,
        },
      },
    },
  ],
  documents: ["src/**/*.jsx"],
  generates: {
    "possibleTypes.json": {
      plugins: ["fragment-matcher"],
      config: {
        module: "commonjs",
        apolloClientVersion: 3,
      },
    },
  },
};
export default config;
