import { Component, OnInit, Input, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { GraphQLService, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { PostListingComponent } from '../../post-listing/post-listing.component';

// Define interfaces for the archive data structure
interface Post {
  id: string;
  uri: string;
  title: string;
  excerpt: string;
  date: string;
  featuredImage?: {
    node: {
      sourceUrl: string;
      altText?: string;
    };
  };
  categories?: {
    nodes: Array<{
      name: string;
      slug: string;
    }>;
  };
  tags?: {
    nodes: Array<{
      name: string;
      slug: string;
    }>;
  };
}

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
    PostListingComponent
  ],
  templateUrl: './archive.component.html',
  styleUrl: './archive.component.scss'
})
export class ArchiveComponent implements OnInit {
  @Input() templateData?: any;
  @Input() seedQuery?: any;

  // State signals
  data = signal<ArchiveResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

  // GraphQL Query
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

  constructor(
    private graphqlService: GraphQLService,
    private router: Router
  ) {}

  ngOnInit(): void {
    console.log('🏛️ Archive component initialized');
    
    // Use seed query if available
    if (this.seedQuery?.archive) {
      console.log('📋 Using seed query data for archive');
      this.data.set({ archive: this.seedQuery.archive });
      this.loading.set(false);
    } else {
      this.loadArchive();
    }
  }

  private loadArchive(): void {
    const uri = this.templateData?.uri || this.router.url;
    
    console.log('🔍 Loading archive for URI:', uri);
    
    this.loading.set(true);
    this.error.set(null);

    this.graphqlService.query<ArchiveResponse>(this.archiveQuery, { uri }).subscribe({
      next: (response) => {
        console.log('✅ Archive data loaded:', response);
        this.data.set(response);
        this.loading.set(false);
      },
      error: (error) => {
        console.error('❌ Error loading archive:', error);
        this.error.set(error);
        this.loading.set(false);
      }
    });
  }

  /**
   * Retry loading the archive
   */
  retry(): void {
    this.loadArchive();
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
    return !!(archiveData?.description?.trim());
  }
}