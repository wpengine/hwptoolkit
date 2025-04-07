const { gql } = require("@apollo/client");

export const queries = {
  pages: gql`
    query getPages($first: Int!, $after: String) {
      pages(first: $first, after: $after) {
        nodes {
          uri
          modified
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  posts: gql`
    query getPosts($first: Int!, $after: String) {
      posts(first: $first, after: $after) {
        nodes {
          uri
          modified
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  categories: gql`
    query getCategories($first: Int!, $after: String) {
      categories(first: $first, after: $after) {
        nodes {
          uri
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  tags: gql`
    query getTags($first: Int!, $after: String) {
      tags(first: $first, after: $after) {
        nodes {
          uri
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  buildings: gql`
    query getBuildings($first: Int!, $after: String) {
      buildings(first: $first, after: $after) {
        nodes {
          uri
          modified
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  periods: gql`
    query getPeriods($first: Int!, $after: String) {
      periods(first: $first, after: $after) {
        nodes {
          uri
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
};
