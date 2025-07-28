import { Component, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { gql, fetchGraphQLSSR } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { CommentsComponent } from '../../comments/comments.component';
import { NotFoundComponent } from '../../not-found/not-found.component';

interface FeaturedImage {
  node: {
    sourceUrl: string;
    altText: string;
  };
}

interface PageData {
  id: string;
  databaseId: number;
  title: string;
  content: string;
  commentCount: number;
  commentStatus: string;
  featuredImage?: FeaturedImage;
}

interface PageResponse {
  page: PageData;
}

@Component({
  selector: 'app-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    CommentsComponent,
    NotFoundComponent,
  ],
  templateUrl: './page.component.html',
  styleUrl: './page.component.scss',
})
export class PageComponent implements OnInit {
  loading = signal(true);
  error = signal<string | null>(null);
  pageData = signal<PageData | null>(null);

  private PAGE_QUERY = gql`
    query GetPage($slug: ID!) {
      page(id: $slug, idType: URI) {
        id
        databaseId
        title
        content
        commentCount
        commentStatus
        featuredImage {
          node {
            sourceUrl
            altText
          }
        }
      }
    }
  `;

  page = computed(() => this.pageData());

  pageId = computed(() => {
    const page = this.page();
    return page?.databaseId || null;
  });

  commentsEnabled = computed(() => {
    const page = this.page();
    return (
      page?.commentStatus === 'open' ||
      (page?.commentCount && page.commentCount > 0)
    );
  });

  // Check if comments exist but are closed
  commentsClosedButExist = computed(() => {
    const page = this.page();
    return (
      page?.commentCount &&
      page.commentCount > 0 &&
      page.commentStatus !== 'open'
    );
  });

  constructor(private router: Router) {}

  ngOnInit() {
    this.loadPageData();
  }

  private loadPageData() {
    const uri = this.getPageSlug();

    if (!uri) {
      this.error.set('No page slug provided');
      this.loading.set(false);
      return;
    }

    this.loading.set(true);
    fetchGraphQLSSR<PageResponse>(this.PAGE_QUERY, { slug: uri })
      .then((data) => {
        if (data?.page) {
          //console.log('✅ Page data loaded:', data);
          this.pageData.set(data.page);
        } else {
          this.error.set('Page not found');
        }
        this.loading.set(false);
      })
      .catch((error) => {
        //console.error('❌ Error loading page:', error);
        this.error.set(error.message || 'Failed to load page');
        this.loading.set(false);
      });
  }

  private getPageSlug(): string {
    const currentPath = this.router.url;
    return currentPath || '/';
  }
}
