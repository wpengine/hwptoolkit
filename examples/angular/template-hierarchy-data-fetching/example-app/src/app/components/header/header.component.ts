import { Component, Input, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { GraphQLService, gql } from '../../shared/utils/graphql.service';

interface MenuItem {
  id: string;
  label: string;
  uri: string;
  parentId?: string;
  target?: string;
  cssClasses?: string[];
  title?: string;
  description?: string;
  children?: MenuItem[];
}

interface SiteSettings {
  generalSettings: {
    title: string;
  };
}

interface NavigationResponse {
  menu: {
    menuItems: {
      pageInfo: {
        hasNextPage: boolean;
        endCursor: string;
      };
      nodes: MenuItem[];
    };
  };
}

@Component({
  selector: 'app-header',
  standalone: true,
  imports: [CommonModule, RouterModule],
  templateUrl: './header.component.html',
  styleUrl: './header.component.scss'
})
export class HeaderComponent implements OnInit {
  // Reactive signals for component state
  siteInfo = signal<{ title: string }>({ title: 'HeadlessWP Toolkit' });
  menuItems = signal<MenuItem[]>([]);
  settingsLoading = signal<boolean>(false);
  navigationLoading = signal<boolean>(false);
  error = signal<string | null>(null);

  constructor(
    private graphqlService: GraphQLService,
    private router: Router
  ) {}

  ngOnInit() {
    // Load data with a delay to avoid change detection issues
    setTimeout(() => {
      this.loadSiteSettings();
      this.loadNavigation();
    }, 0);
  }

  private loadSiteSettings() {
    if (!this.graphqlService) {
      console.error('GraphQLService is not available');
      return;
    }

    const SETTINGS_QUERY = gql`
      query HeaderSettingsQuery {
        generalSettings {
          title
        }
      }
    `;

    this.settingsLoading.set(true);
    
    this.graphqlService.query<SiteSettings>(SETTINGS_QUERY, {}).subscribe({
      next: (data: SiteSettings) => {
        if (data?.generalSettings?.title) {
          this.siteInfo.set({ title: data.generalSettings.title });
        }
        this.settingsLoading.set(false);
      },
      error: (error: any) => {
        console.error('Error loading site settings:', error);
        this.error.set('Failed to load site settings');
        this.settingsLoading.set(false);
      }
    });
  }

  private loadNavigation() {
    if (!this.graphqlService) {
      console.error('GraphQLService is not available');
      return;
    }

    const NAVIGATION_QUERY = gql`
      query HeaderNavigationQuery($after: String = null) {
        menu(id: "primary", idType: LOCATION) {
          menuItems(first: 100, after: $after) {
            pageInfo {
              hasNextPage
              endCursor
            }
            nodes {
              id
              label
              uri
              parentId
              target
              cssClasses
              title
              description
            }
          }
        }
      }
    `;

    this.navigationLoading.set(true);
    
    this.graphqlService.query<NavigationResponse>(NAVIGATION_QUERY, {}).subscribe({
      next: (data: NavigationResponse) => {
        if (data?.menu?.menuItems?.nodes) {
          const hierarchicalMenu = this.flatListToHierarchical(data.menu.menuItems.nodes);
          this.menuItems.set(hierarchicalMenu);
        }
        this.navigationLoading.set(false);
      },
      error: (error: any) => {
        console.error('Error loading navigation:', error);       
        this.navigationLoading.set(false);
      }
    });
  }

  private flatListToHierarchical(items: MenuItem[]): MenuItem[] {
    const map = new Map<string, MenuItem>();
    const roots: MenuItem[] = [];

    // First pass: create map of all items
    items.forEach(item => {
      map.set(item.id, { ...item, children: [] });
    });

    // Second pass: build hierarchy
    items.forEach(item => {
      const mappedItem = map.get(item.id)!;
      
      if (item.parentId && map.has(item.parentId)) {
        const parent = map.get(item.parentId)!;
        if (!parent.children) {
          parent.children = [];
        }
        parent.children.push(mappedItem);
      } else {
        roots.push(mappedItem);
      }
    });

    return roots;
  }
  isActive(item: MenuItem): boolean {
    if (!item.uri) return false;
    
    // Format the URI for comparison
    let cleanUri = item.uri.startsWith('/') ? item.uri.substring(1) : item.uri;
    cleanUri = cleanUri.endsWith('/') ? cleanUri.slice(0, -1) : cleanUri;
    
    // Compare with current route path
    const currentPath = this.router.url;
    const cleanCurrentPath = currentPath.startsWith('/') ? currentPath.substring(1) : currentPath;
    
    return `/${cleanUri}` === currentPath || cleanUri === cleanCurrentPath;
  }

  onMenuItemClick(item: MenuItem) {
    if (item.uri) {
      // Handle external links
      if (item.target === '_blank' || item.uri.startsWith('http')) {
        window.open(item.uri, item.target || '_self');
      } else {
        // Navigate internally
        this.router.navigate([item.uri]);
      }
    }
  }
  dropdownClass(level: number): string {
    return level === 0 ? 'dropdown-top' : 'dropdown-submenu';
  }
}