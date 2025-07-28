import { Component, OnInit, signal, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { fetchGraphQLSSR, gql } from '../../../utils/graphql.service';
import { getPosts } from '../../../utils/utils';
import { LoadingComponent } from '../../loading/loading.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { PostListingComponent } from '../../post-listing/post-listing.component';
import { Post } from '../../../interfaces/post.interface';
import { POSTS_QUERY } from '../../../utils/postQuery';

interface GeneralSettings {
  title: string;
  description: string;
}

interface HomeSettingsResponse {
  generalSettings: GeneralSettings;
}

@Component({
  selector: 'app-front-page',
  standalone: true,
  imports: [
    CommonModule,
    RouterModule,
    LoadingComponent,
    EmptyStateComponent,
    PostListingComponent,
  ],
  templateUrl: './front-page.component.html',
  styleUrl: './front-page.component.scss',
})
export class FrontPageComponent implements OnInit {
  settingsLoading = signal(true);
  settingsError = signal<any>(null);
  settingsData = signal<HomeSettingsResponse | null>(null);

  blogLoading = signal(true);
  blogError = signal<any>(null);
  blogPosts = signal<Post[]>([]);

  private HOME_SETTINGS_QUERY = gql`
    query HomeSettingsQuery {
      generalSettings {
        title
        description
      }
    }
  `;

  siteInfo = computed(() => {
    const settings = this.settingsData()?.generalSettings;
    return {
      title: settings?.title || 'My WordPress Site',
      description: settings?.description || 'Welcome to my site',
    };
  });

  ngOnInit() {
    this.loadSiteSettings();
    this.loadBlogPosts();
  }

  private loadSiteSettings() {
    this.settingsLoading.set(true);
    this.settingsError.set(null);

    fetchGraphQLSSR<HomeSettingsResponse>(this.HOME_SETTINGS_QUERY, {})
      .then((data) => {
        this.settingsData.set(data);
      })
      .catch((error) => {
        this.settingsError.set(error);
      })
      .finally(() => {
        this.settingsLoading.set(false);
      });
  }

  private async loadBlogPosts(after: string | null = null): Promise<void> {
    try {
      this.blogLoading.set(true);
      this.blogError.set(null);

      const data = await getPosts({
        query: POSTS_QUERY,
        slug: '',
        pageSize: 4,
        after: null,
      });
      if (data?.posts) {
        const newPosts = data.posts.edges.map(
          (edge: { node: Post }) => edge.node
        );
        this.blogPosts.set(newPosts);
      } else {
        this.blogError.set('No posts data received');
      }
    } catch (error: any) {
      this.blogError.set(error.message || 'Failed to load posts');
    } finally {
      this.blogLoading.set(false);
    }
  }
}
