export interface SeedNode {
  __typename?: string;
  uri?: string;
  id?: string;
  databaseId?: string;
  mimeType?: string;
  name?: string;
  isFrontPage?: boolean;
  isPostsPage?: boolean;
  isTermNode?: boolean;
  slug?: string;
  taxonomyName?: string;
  isContentNode?: boolean;
  contentType?: {
    node?: {
      name?: string;
    };
  };
  template?: {
    templateName?: string;
  };
  userId?: number;
}

export interface WordPressTemplate {
  id: string;
  path: string;
}

/**
 * Used in uriToTemplate function to find the correct template for each page visit.
 * The template hierarchy follows WordPress conventions for template selection.
 *
 * @param node - The SeedNode object (from seedQuery.js) to analyze for template possibilities
 * @returns An array of possible template names, ordered from most specific to least specific
 *
 * @remarks
 * The function handles various node types including:
 * - Custom template assignments
 * - Front page nodes
 * - Blog/posts page nodes
 * - Custom post type archive pages
 * - Taxonomy term pages (categories, tags, custom taxonomies)
 * - Author archive pages
 * - Singular content nodes (posts, pages, custom post types)
 *
 * All template arrays end with "index" as the fallback template.
 */

export function getPossibleTemplates(node: SeedNode) {
  let possibleTemplates: string[] = [];

  if (node.template?.templateName && node.template.templateName !== "Default") {
    possibleTemplates.push(`template-${node.template.templateName}`);
  }

  // Front page
  if (node.isFrontPage) {
    possibleTemplates.push("home");
  }

  // Blog page
  if (node.isPostsPage) {
    possibleTemplates.push("blog");
  }

  // CPT archive page
  // eslint-disable-next-line no-underscore-dangle
  if (node.__typename === "ContentType" && node.isPostsPage === false) {
    if (node.name) {
      possibleTemplates.push(`archive-${node.name}`);
    }

    possibleTemplates.push("archive");
  }

  // Archive Page
  if (node.isTermNode) {
    const { taxonomyName } = node;

    switch (taxonomyName) {
      case "category": {
        if (node.slug) {
          possibleTemplates.push(`category-${node.slug}`);
        }

        if (node.databaseId) {
          possibleTemplates.push(`category-${node.databaseId}`);
        }

        possibleTemplates.push(`category`);

        break;
      }
      case "post_tag": {
        if (node.slug) {
          possibleTemplates.push(`tag-${node.slug}`);
        }

        if (node.databaseId) {
          possibleTemplates.push(`tag-${node.databaseId}`);
        }

        possibleTemplates.push(`tag`);

        break;
      }
      default: {
        if (taxonomyName) {
          if (node.slug) {
            possibleTemplates.push(`taxonomy-${taxonomyName}-${node.slug}`);
          }

          if (node.databaseId) {
            possibleTemplates.push(
              `taxonomy-${taxonomyName}-${node.databaseId}`
            );
          }

          possibleTemplates.push(`taxonomy-${taxonomyName}`);
        }

        possibleTemplates.push(`taxonomy`);
      }
    }

    possibleTemplates.push(`archive`);
  }

  if (node.userId) {
    if (node.name) {
      possibleTemplates.push(`author-${node.name?.toLocaleLowerCase()}`);
    }

    possibleTemplates.push(`author-${node.userId}`);
    possibleTemplates.push(`author`);
    possibleTemplates.push(`archive`);
  }

  // Singular page
  if (node.isContentNode) {
    if (
      node?.contentType?.node?.name !== "page" &&
      node?.contentType?.node?.name !== "post"
    ) {
      if (node.contentType?.node?.name && node.slug) {
        possibleTemplates.push(
          `single-${node.contentType?.node?.name}-${node.slug}`
        );
      }

      if (node.contentType?.node?.name) {
        possibleTemplates.push(`single-${node.contentType?.node?.name}`);
      }
    }

    if (node?.contentType?.node?.name === "page") {
      if (node.slug) {
        possibleTemplates.push(`page-${node.slug}`);
      }

      if (node.databaseId) {
        possibleTemplates.push(`page-${node.databaseId}`);
      }

      possibleTemplates.push(`page`);
    }

    if (node?.contentType?.node?.name === "post") {
      if (node.slug) {
        possibleTemplates.push(
          `single-${node.contentType.node.name}-${node.slug}`
        );
      }

      possibleTemplates.push(`single-${node.contentType.node.name}`);
      possibleTemplates.push(`single`);
    }

    possibleTemplates.push(`singular`);
  }

  possibleTemplates.push("index");

  return possibleTemplates;
}
/**
 * Searches for the correct template in the available templates based on the possible templates.
 * @param availableTemplates
 * @param possibleTemplates
 * @returns {Object} with id and path - correct template from available templates based on possible templates
 */
export function getTemplate(
  availableTemplates: WordPressTemplate[] | undefined,
  possibleTemplates: string[] = []
): WordPressTemplate | undefined {
  // eslint-disable-next-line no-plusplus
  for (const possibleTemplate of possibleTemplates) {
    const templateFromConfig = availableTemplates?.find(
      (template) => template.id === possibleTemplate
    );

    if (!templateFromConfig) {
      continue;
    }

    return templateFromConfig;
  }
}
