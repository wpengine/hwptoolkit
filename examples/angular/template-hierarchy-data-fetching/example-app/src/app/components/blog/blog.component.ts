import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  GraphQLStateService,
  gql,
} from '../../utils/graphql.service';
import { Post, PostsResponse  } from '../../interfaces/post.interface';

@Component({
  selector: 'app-blog',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './blog.component.html',
  styleUrl: './blog.component.css',
})
export class BlogComponent implements OnInit {
  postsData = signal<PostsResponse | null>(null);
  loading = signal<boolean>(false);
  error = signal<string | null>(null);

  posts = signal<Post[]>([]);

  private postsQuery: any;

  constructor(
    private graphqlState: GraphQLStateService,
  ) {}

  ngOnInit() {
    this.postsQuery = this.graphqlState.createQuery<PostsResponse>(
      gql`
        query GetPosts(
          $first: Int = 9
          $after: String
          $category: String
          $tag: String
        ) {
          posts(
            first: $first
            after: $after
            where: { categoryName: $category, tag: $tag }
          ) {
            pageInfo {
              hasNextPage
              endCursor
            }
            edges {
              cursor
              node {
                id
                title
                date
                excerpt
                uri
                slug
                featuredImage {
                  node {
                    sourceUrl
                    altText
                  }
                }
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
                author {
                  node {
                    name
                    avatar {
                      url
                    }
                  }
                }
              }
            }
          }
        }
      `,
      { first: 10 },
    );


    this.postsData = this.postsQuery.data;
    this.loading = this.postsQuery.loading;
    this.error = this.postsQuery.error;

    this.setupPostsExtraction();
  }

  private setupPostsExtraction() {
    // Use effect to update posts when data changes
    // For now, we'll do this manually in a simple way
    // You could use Angular's effect() here for more reactive approach
    const updatePosts = () => {
      const data = this.postsData();
      if (data?.posts?.edges) {
        const extractedPosts = data.posts.edges.map((edge) => edge.node);
        this.posts.set(extractedPosts);
      } else {
        this.posts.set([]);
      }
    };

    // Update posts when data changes
    // For now, we'll call this periodically or on data updates
    updatePosts();
  }

  loadMorePosts() {
    // Example of refetching with new variables
    this.postsQuery.setVariables({ first: 20 }).subscribe({
      next: (result: any) => console.log('Loaded more posts:', result),
      error: (err: any) => console.error('Error loading more posts:', err),
    });
  }

  refreshPosts() {
    // Example of refetching with same variables
    this.postsQuery.refetch().subscribe({
      next: (result: any) => console.log('Refreshed posts:', result),
      error: (err: any) => console.error('Error refreshing posts:', err),
    });
  }
}
