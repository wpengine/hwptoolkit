import { Component, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { fetchGraphQL, gql } from '../../../utils/graphql.service';
import { Post } from '../../../interfaces/post.interface';
import { PostListingComponent } from '../../post-listing/post-listing.component';
import { LoadingComponent } from '../../loading/loading.component';
import { NotFoundComponent } from '../../not-found/not-found.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';

interface ArchiveNode {
  __typename: string;
  name: string;
  description?: string;
  contentNodes?: {
    nodes: Post[];
  };
  posts?: {
    nodes: Post[];
  };
}

interface ArchiveResponse {
  archive: ArchiveNode;
}

@Component({
  selector: 'app-archive',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    EmptyStateComponent,
    NotFoundComponent,
    PostListingComponent,
  ],
  templateUrl: './archive.component.html',
  styleUrl: './archive.component.scss',
})
export class ArchiveComponent implements OnInit {
  data = signal<ArchiveResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

  private archiveQuery = gql`
    query ArchiveTemplateNodeQuery($uri: String!) {
      archive: nodeByUri(uri: $uri) {
        __typename

        ... on User {
          contentNodes: posts {
            nodes {
              id
              uri
              title
              excerpt
              date
              featuredImage {
                node {
                  sourceUrl
                  altText
                }
              }
              ... on Post {
                categories {
                  nodes {
                    name
                    slug
                  }
                }
                tags {
                  nodes {
                    name
                    slug
                  }
                }
              }
            }
          }
        }

        ... on TermNode {
          name
          description
        }

        ... on Tag {
          name
          description
          contentNodes {
            nodes {
              id
              uri
              date
              ... on NodeWithTitle {
                title
              }
              ... on NodeWithExcerpt {
                excerpt
              }
              ... on NodeWithFeaturedImage {
                featuredImage {
                  node {
                    sourceUrl
                    altText
                  }
                }
              }
              ... on Post {
                categories {
                  nodes {
                    name
                    slug
                  }
                }
                tags {
                  nodes {
                    name
                    slug
                  }
                }
              }
            }
          }
        }

        ... on Category {
          name
          description
          contentNodes {
            nodes {
              id
              uri
              date
              ... on NodeWithTitle {
                title
              }
              ... on NodeWithExcerpt {
                excerpt
              }
              ... on NodeWithFeaturedImage {
                featuredImage {
                  node {
                    sourceUrl
                    altText
                  }
                }
              }
              ... on Post {
                categories {
                  nodes {
                    name
                    slug
                  }
                }
                tags {
                  nodes {
                    name
                    slug
                  }
                }
              }
            }
          }
        }
      }
    }
  `;

  // Computed properties using Angular signals
  archive = computed(() => {
    return this.data()?.archive || null;
  });

  posts = computed(() => {
    const archiveData = this.archive();
    if (!archiveData) return [];

    // Handle different archive types
    if (archiveData.contentNodes?.nodes) {
      return archiveData.contentNodes.nodes;
    }

    // Handle User archives (posts field)
    if (archiveData.posts?.nodes) {
      return archiveData.posts.nodes;
    }

    return [];
  });

  hasContent = computed(() => {
    return this.posts().length > 0;
  });

  constructor(private router: Router) {}

  ngOnInit(): void {
    this.loadArchive();
  }

  private loadArchive(): void {
    const uri = this.router.url;

    this.loading.set(true);
    this.error.set(null);

    fetchGraphQL<ArchiveResponse>(this.archiveQuery, { uri })
      .then((response) => {
        this.data.set(response);
      })
      .catch((error) => {
        this.error.set(error);
      })
      .finally(() => {
        this.loading.set(false);
      });
  }
  /**
   * Get archive type for display
   */
  getArchiveType(): string {
    const archiveData = this.archive();
    if (!archiveData) return 'Archive';

    switch (archiveData.__typename) {
      case 'Category':
        return 'Category';
      case 'Tag':
        return 'Tag';
      case 'User':
        return 'Author';
      default:
        return 'Archive';
    }
  }

  /**
   * Check if archive has description
   */
  hasDescription(): boolean {
    const archiveData = this.archive();
    return !!archiveData?.description?.trim();
  }
}
