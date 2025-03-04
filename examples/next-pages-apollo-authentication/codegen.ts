import type { CodegenConfig } from '@graphql-codegen/cli';
 
 const config: CodegenConfig = {
   schema: 'http://myexample.local/graphql',
   documents: ['src/**\/*.jsx'],
   generates: {
     'possibleTypes.json': {
       plugins: ['fragment-matcher'],
       config: {
         module: 'commonjs',
         apolloClientVersion: 3,
       },
     },
   },
 };
 export default config;