import { Component, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { gql, fetchGraphQLSSR } from '../../../utils/graphql.service';
import {
  Post,
  Author,
  Category,
  Tag,
  FeaturedImage,
} from '../../../interfaces/post.interface';
import { LoadingComponent } from '../../loading/loading.component';
import { CommentsComponent } from '../../comments/comments.component';
import { NotFoundComponent } from '../../not-found/not-found.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { formatDate } from '../../../utils/utils';

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
    CommentsComponent,
    EmptyStateComponent,
  ],
  templateUrl: './singular.component.html',
  styleUrl: './singular.component.scss',
})
export class SingularComponent implements OnInit {
  data = signal<PostResponse | null>(null);
  loading = signal(true);
  error = signal<any>(null);

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

  post = computed(() => {
    return this.data()?.post || null;
  });

  postId = computed(() => {
    return this.post()?.databaseId || null;
  });

  constructor(private router: Router) {}

  ngOnInit(): void {
    this.loadPost();
  }

  private loadPost(): void {
    const slug = this.router.url.split('/').pop() || '';

    console.log('🔍 Loading post for slug:', slug);

    this.loading.set(true);
    this.error.set(null);

    fetchGraphQLSSR<PostResponse>(this.POST_QUERY, { slug })
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

  formatDate(dateString: string): string {
    return formatDate(dateString);
  }

  hasCategories(): boolean {
    const currentPost = this.post();
    return !!(
      currentPost?.categories?.nodes && currentPost.categories.nodes.length > 0
    );
  }

  hasTags(): boolean {
    const currentPost = this.post();
    return !!(currentPost?.tags?.nodes && currentPost.tags.nodes.length > 0);
  }

  hasFeaturedImage(): boolean {
    const currentPost = this.post();
    return !!currentPost?.featuredImage?.node?.sourceUrl;
  }

  hasAuthor(): boolean {
    const currentPost = this.post();
    return !!currentPost?.author?.node;
  }

  hasAuthorAvatar(): boolean {
    const currentPost = this.post();
    return !!currentPost?.author?.node?.avatar?.url;
  }

  getCategoryLink(category: Category): string {
    return category.slug || `/category/${category.name.toLowerCase()}`;
  }

  getTagLink(tag: Tag): string {
    return tag.slug || `/tag/${tag.name.toLowerCase()}`;
  }

  isLastItem(index: number, array: any[]): boolean {
    return index === array.length - 1;
  }
}
