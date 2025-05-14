'use client';

import React from 'react';
import { DocumentNode, print } from 'graphql';

type QueryDebuggerProps = {
  query: DocumentNode;
  complexity?: number | string | null;
};

export default function QueryDebugger({ query, complexity }: QueryDebuggerProps) {
  const queryString = print(query);

  return (
    <div
      style={{
        background: '#f9f9f9',
        border: '1px solid #ddd',
        padding: '1rem',
        borderRadius: '8px',
        marginBottom: '2rem',
      }}
    >
      <h2 style={{ marginTop: 0 }}>📝 GraphQL Query Debugger</h2>

      <pre
        style={{
          background: '#f4f4f4',
          padding: '1rem',
          borderRadius: '6px',
          overflowX: 'auto',
          fontSize: '0.9rem',
        }}
      >
        {queryString}
      </pre>

      <p>
        <strong>Query Complexity:</strong>{' '}
        {complexity !== undefined && complexity !== null ? complexity : 'N/A'}
      </p>
    </div>
  );
}
