import { Component, OnInit, signal, Input, computed } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule } from '@angular/router';
import { GraphQLService, gql } from '../../../utils/graphql.service';
import { LoadingComponent } from '../../loading/loading.component';
import { EmptyStateComponent } from '../../empty-state/empty-state.component';
import { PostListingComponent } from '../../post-listing/post-listing.component';

interface GeneralSettings {
  title: string;
  description: string;
}

interface Author {
  node: {
    name: string;
    avatar: {
      url: string;
    };
  };
}

interface Category {
  name: string;
  slug: string;
}

interface FeaturedImage {
  node: {
    sourceUrl: string;
    altText: string;
  };
}

interface Post {
  id: string;
  title: string;
  date: string;
  uri: string;
  slug: string;
  excerpt: string;
  featuredImage?: FeaturedImage;
  author?: Author;
  categories?: {
    nodes: Category[];
  };
}

interface HomeSettingsResponse {
  generalSettings: GeneralSettings;
}

interface HomeBlogPostsResponse {
  posts: {
    nodes: Post[];
  };
}

@Component({
  selector: 'app-front-page',
  standalone: true,
  imports: [
    CommonModule, 
    RouterModule, 
    LoadingComponent, 
    EmptyStateComponent, 
    PostListingComponent
  ],
  templateUrl: './front-page.component.html',
  styleUrl: './front-page.component.scss'
})
export class FrontPageComponent implements OnInit {
  // Signals for reactive state
  settingsLoading = signal(true);
  settingsError = signal<any>(null);
  settingsData = signal<HomeSettingsResponse | null>(null);
  
  blogLoading = signal(true);
  blogError = signal<any>(null);
  blogData = signal<HomeBlogPostsResponse | null>(null);

  // GraphQL Queries
  private HOME_SETTINGS_QUERY = gql`
    query HomeSettingsQuery {
      generalSettings {
        title
        description
      }
    }
  `;

  private HOME_BLOG_POSTS_QUERY = gql`
    query HomeBlogPostsQuery {
      posts(first: 4) {
        nodes {
          id
          title
          date
          uri
          slug
          excerpt
          featuredImage {
            node {
              sourceUrl
              altText
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
          categories {
            nodes {
              name
              slug
            }
          }
        }
      }
    }
  `;

  // Computed properties using Angular signals
  posts = computed(() => {
    return this.blogData()?.posts?.nodes || [];
  });

  siteInfo = computed(() => {
    const settings = this.settingsData()?.generalSettings;
    return {
      title: settings?.title || 'My WordPress Site',
      description: settings?.description || 'Welcome to my site'
    };
  });

  constructor(private graphqlService: GraphQLService) {}

  ngOnInit() {
    this.loadSiteSettings();
    this.loadBlogPosts();
  }

  private loadSiteSettings() {
    console.log('🔍 Loading site settings...');
    
    this.settingsLoading.set(true);
    this.settingsError.set(null);

    this.graphqlService.query<HomeSettingsResponse>(this.HOME_SETTINGS_QUERY, {}).subscribe({
      next: (data) => {
        console.log('✅ Home Settings loaded:', data);
        this.settingsData.set(data);
        this.settingsLoading.set(false);
      },
      error: (error) => {
        console.error('❌ Error loading home settings:', error);
        this.settingsError.set(error);
        this.settingsLoading.set(false);
      }
    });
  }

  private loadBlogPosts() {
    console.log('🔍 Loading recent blog posts...');
    
    this.blogLoading.set(true);
    this.blogError.set(null);

    this.graphqlService.query<HomeBlogPostsResponse>(this.HOME_BLOG_POSTS_QUERY, {}).subscribe({
      next: (data) => {
        console.log('✅ Home Blog Posts loaded:', data);
        this.blogData.set(data);
        this.blogLoading.set(false);
      },
      error: (error) => {
        console.error('❌ Error loading blog posts:', error);
        this.blogError.set(error);
        this.blogLoading.set(false);
      }
    });
  }

  refreshData() {
    this.loadSiteSettings();
    this.loadBlogPosts();
  }
}