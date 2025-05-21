import { buildClientSchema, IntrospectionQuery } from "graphql";
import introspectionResult from "./schema.json";
export const schema = buildClientSchema(introspectionResult as unknown as IntrospectionQuery);