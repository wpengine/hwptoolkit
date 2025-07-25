import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  GraphQLStateService
} from '../../utils/graphql.service';
import { Post, PostsResponse  } from '../../interfaces/post.interface';
import { POSTS_QUERY } from '../../utils/postQuery';

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
      POSTS_QUERY,
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

    updatePosts();
  }

  loadMorePosts() {
    this.postsQuery.setVariables({ first: 20 }).subscribe({
      next: (result: any) => console.log('Loaded more posts:', result),
      error: (err: any) => console.error('Error loading more posts:', err),
    });
  }

  refreshPosts() {
    this.postsQuery.refetch().subscribe({
      next: (result: any) => console.log('Refreshed posts:', result),
      error: (err: any) => console.error('Error refreshing posts:', err),
    });
  }
}
