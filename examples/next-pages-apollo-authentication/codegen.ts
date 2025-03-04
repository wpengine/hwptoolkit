import type { CodegenConfig } from "@graphql-codegen/cli";
import dotenv from "dotenv";
dotenv.config();

const baseUrl =
  process.env.NEXT_PUBLIC_WORDPRESS_URL || "https://your-wordpress-site.com";
const graphqlPath = process.env.NEXT_PUBLIC_GRAPHQL_PATH || "/graphql";

const config: CodegenConfig = {
  schema: `${baseUrl}${graphqlPath}`,
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
