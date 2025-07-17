import { Component, OnInit, Input, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router, ActivatedRoute } from '@angular/router';
import { GraphQLService, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { NotFoundComponent } from '../../not-found/not-found.component';
import { CommentsComponent } from '../../comments/comments.component';

// Define interfaces for the post data structure
interface Category {
  name: string;
  uri: string;
}

interface Tag {
  name: string;
  uri: string;
}

interface Author {
  node: {
    name: string;
    avatar?: {
      url: string;
    };
  };
}

interface FeaturedImage {
  node: {
    sourceUrl: string;
    altText?: string;
  };
}

interface Post {
  id: string;
  databaseId: number;
  title: string;
  date: string;
  content: string;
  commentCount: number;
  categories?: {
    nodes: Category[];
  };
  tags?: {
    nodes: Tag[];
  };
  author?: Author;
  featuredImage?: FeaturedImage;
}

interface PostResponse {
  post: Post;
}

@Component({
  selector: 'app-singular',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    NotFoundComponent,
    CommentsComponent
  ],
  templateUrl: './singular.component.html',
  styleUrl: './singular.component.scss'
})
export class SingularComponent implements OnInit {
  @Input() templateData?: any;
  @Input() seedQuery?: any;

  // State signals
  data = signal<PostResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

  // GraphQL Query
  private POST_QUERY = gql`
    query GetPost($slug: ID!) {
      post(id: $slug, idType: SLUG) {
        id
        databaseId
        title
        date
        content
        commentCount
        categories {
          nodes {
            name
            uri
          }
        }
        tags {
          nodes {
            name
            uri
          }
        }
        author {
          node {
            name
            avatar {
              url
            }
          }
        }
        featuredImage {
          node {
            sourceUrl
            altText
          }
        }
      }
    }
  `;

  // Computed properties using Angular signals
  post = computed(() => {
    return this.data()?.post || null;
  });

  postId = computed(() => {
    return this.post()?.databaseId || null;
  });

  constructor(
    private graphqlService: GraphQLService,
    private router: Router,
    private route: ActivatedRoute
  ) {}

  ngOnInit(): void {
    console.log('📄 Singular component initialized');
    
    // Use seed query if available
    if (this.seedQuery?.post) {
      console.log('📋 Using seed query data for post');
      this.data.set({ post: this.seedQuery.post });
      this.loading.set(false);
    } else {
      this.loadPost();
    }
  }

  private loadPost(): void {
    // Get slug from route or template data
    let slug = '';
    
    if (this.templateData?.slug) {
      slug = this.templateData.slug;
    } else {
      // Extract slug from current URL path
      const path = this.router.url;
      const segments = path.split('/').filter(segment => segment);
      slug = segments[segments.length - 1] || '';
    }
    
    console.log('🔍 Loading post for slug:', slug);
    
    this.loading.set(true);
    this.error.set(null);

    this.graphqlService.query<PostResponse>(this.POST_QUERY, { slug }).subscribe({
      next: (response) => {
        console.log('✅ Post data loaded:', response);
        this.data.set(response);
        this.loading.set(false);
      },
      error: (error) => {
        console.error('❌ Error loading post:', error);
        this.error.set(error);
        this.loading.set(false);
      }
    });
  }

  /**
   * Format date to readable format
   */
  formatDate(dateString: string): string {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    });
  }

  /**
   * Check if post has categories
   */
  hasCategories(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.categories?.nodes && currentPost.categories.nodes.length > 0);
  }

  /**
   * Check if post has tags
   */
  hasTags(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.tags?.nodes && currentPost.tags.nodes.length > 0);
  }

  /**
   * Check if post has featured image
   */
  hasFeaturedImage(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.featuredImage?.node?.sourceUrl);
  }

  /**
   * Check if post has author
   */
  hasAuthor(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.author?.node);
  }

  /**
   * Check if author has avatar
   */
  hasAuthorAvatar(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.author?.node?.avatar?.url);
  }

  /**
   * Retry loading the post
   */
  retry(): void {
    this.loadPost();
  }

  /**
   * Get category link URL
   */
  getCategoryLink(category: Category): string {
    return category.uri || `/category/${category.name.toLowerCase()}`;
  }

  /**
   * Get tag link URL
   */
  getTagLink(tag: Tag): string {
    return tag.uri || `/tag/${tag.name.toLowerCase()}`;
  }

  /**
   * Check if it's the last item in array
   */
  isLastItem(index: number, array: any[]): boolean {
    return index === array.length - 1;
  }
}