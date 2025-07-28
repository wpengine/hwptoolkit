import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterModule, Router } from '@angular/router';
import { fetchGraphQLSSR, gql } from '../../utils/graphql.service';
import { flatListToHierarchical } from '../../utils/utils';
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
  styleUrl: './header.component.scss',
})
export class HeaderComponent implements OnInit {
  // Reactive signals for component state
  siteInfo = signal<{ title: string }>({ title: 'HeadlessWP Toolkit' });
  menuItems = signal<MenuItem[]>([]);
  settingsLoading = signal<boolean>(false);
  navigationLoading = signal<boolean>(false);
  error = signal<string | null>(null);

  constructor(private router: Router) {}

  ngOnInit() {
    this.loadSiteSettings();
    this.loadNavigation();
  }

  private loadSiteSettings() {
    const SETTINGS_QUERY = gql`
      query HeaderSettingsQuery {
        generalSettings {
          title
        }
      }
    `;

    this.settingsLoading.set(true);

    fetchGraphQLSSR<SiteSettings>(SETTINGS_QUERY, {})
      .then((data) => {
        if (data?.generalSettings?.title) {
          this.siteInfo.set({ title: data.generalSettings.title });
        }
        this.settingsLoading.set(false);
      })
      .catch((error) => {
        console.error('Error loading site settings:', error);
        this.error.set('Failed to load site settings');
        this.settingsLoading.set(false);
      });
  }

  private loadNavigation() {
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

    fetchGraphQLSSR<NavigationResponse>(NAVIGATION_QUERY, {})
      .then((data) => {
        if (data?.menu?.menuItems?.nodes) {
          const hierarchicalMenu = this.flatListToHierarchical(
            data.menu.menuItems.nodes
          );
          this.menuItems.set(hierarchicalMenu);
        }
        this.navigationLoading.set(false);
      })
      .catch((error) => {
        console.error('Error loading navigation:', error);
        this.navigationLoading.set(false);
      });
  }
  private flatListToHierarchical(items: MenuItem[]): MenuItem[] {
    return flatListToHierarchical(items, {
      idKey: 'id',
      parentKey: 'parentId',
      childrenKey: 'children',
    });
  }
  isActive(item: MenuItem): boolean {
    if (!item.uri) return false;

    // Format the URI for comparison
    let cleanUri = item.uri.startsWith('/') ? item.uri.substring(1) : item.uri;
    cleanUri = cleanUri.endsWith('/') ? cleanUri.slice(0, -1) : cleanUri;

    // Compare with current route path
    const currentPath = this.router.url;
    const cleanCurrentPath = currentPath.startsWith('/')
      ? currentPath.substring(1)
      : currentPath;

    return `/${cleanUri}` === currentPath || cleanUri === cleanCurrentPath;
  }

  onMenuItemClick(item: MenuItem) {
    if (item.uri) {
      if (item.target === '_blank' || item.uri.startsWith('http')) {
        window.open(item.uri, item.target || '_self');
      } else {
        this.router.navigate([item.uri]);
      }
    }
  }
  dropdownClass(level: number): string {
    return level === 0 ? 'dropdown-top' : 'dropdown-submenu';
  }
}
