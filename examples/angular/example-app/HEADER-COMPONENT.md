# Angular Header Component with WordPress GraphQL Integration

This header component has been fully converted from Vue/Nuxt to Angular and now properly fetches navigation items from a WordPress GraphQL endpoint.

## Features

✅ **WordPress GraphQL Integration** - Fetches site title and navigation menu from WordPress
✅ **Reactive State Management** - Uses Angular Signals for reactive updates
✅ **Hierarchical Menu Support** - Supports nested menu items with dropdowns
✅ **Loading States** - Shows loading indicators while fetching data
✅ **Error Handling** - Graceful error handling with fallback navigation
✅ **Responsive Design** - Mobile-friendly navigation with proper responsive behavior
✅ **Active Link Detection** - Highlights active menu items based on current route
✅ **TypeScript Support** - Full type safety with proper interfaces

## GraphQL Queries

### Site Settings Query
```graphql
query HeaderSettingsQuery {
  generalSettings {
    title
  }
}
```

### Navigation Query
```graphql
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
```

## Component Structure

### TypeScript Component (`header.component.ts`)

```typescript
export class HeaderComponent implements OnInit {
  @Input() title: string = 'HeadlessWP Toolkit';

  // Reactive signals for component state
  siteInfo = signal<{ title: string }>({ title: this.title });
  menuItems = signal<MenuItem[]>([]);
  settingsLoading = signal<boolean>(true);
  navigationLoading = signal<boolean>(true);
  error = signal<string | null>(null);

  // GraphQL queries
  private settingsQuery: any;
  private navigationQuery: any;

  constructor(
    private graphqlState: GraphQLStateService,
    private router: Router
  ) {}

  ngOnInit() {
    this.loadSiteSettings();
    this.loadNavigation();
    this.setupReactiveEffects();
  }
}
```

### Template (`header.component.html`)

The template includes:
- Conditional rendering based on loading states
- Dynamic site title from WordPress or fallback
- Hierarchical navigation menu with dropdown support
- Active link highlighting
- Mobile-responsive navigation
- Loading and error states

### Key Template Features

```html
<!-- Dynamic site title -->
<h1 *ngIf="siteInfo().title; else fallbackTitle">
  {{ siteInfo().title }}
</h1>

<!-- Navigation with dropdowns -->
<ul *ngIf="menuItems().length > 0; else defaultNav">
  <li *ngFor="let item of menuItems()" 
      [class.has-children]="item.children && item.children.length > 0">
    <a [class.active]="isActive(item)">{{ item.label }}</a>
    
    <!-- Dropdown menu -->
    <ul *ngIf="item.children && item.children.length > 0" class="dropdown-menu">
      <li *ngFor="let child of item.children">
        <a [class.active]="isActive(child)">{{ child.label }}</a>
      </li>
    </ul>
  </li>
</ul>
```

## Usage

### Basic Usage

```typescript
// In your app component template
<app-header [title]="appTitle"></app-header>
```

### With Custom Title

```typescript
// In your component
export class AppComponent {
  appTitle = 'My Custom Site Title';
}
```

```html
<app-header [title]="appTitle"></app-header>
```

## Styling Features

### Responsive Navigation
- Desktop: Horizontal navigation with hover dropdowns
- Mobile: Vertical navigation with collapsible dropdowns

### Loading States
- Spinner animations for loading states
- Skeleton loading for header content
- Smooth transitions between states

### Dropdown Menus
- Smooth hover animations
- Proper z-index stacking
- Touch-friendly mobile interactions

### Active States
- Automatic active link detection
- Visual highlighting of current page
- Support for exact and partial route matching

## Configuration

### WordPress Setup

Ensure your WordPress site has:
1. **WPGraphQL plugin** installed and activated
2. **Primary menu** configured in WordPress admin
3. **Menu items** assigned to the primary menu location

### Environment Configuration

```typescript
// src/environments/environment.ts
export const environment = {
  production: false,
  wordpressUrl: 'http://localhost:8080' // Your WordPress GraphQL endpoint
};
```

### App Module Setup

Make sure to import required modules:

```typescript
// app.config.ts
import { ApplicationConfig } from '@angular/core';
import { provideHttpClient } from '@angular/common/http';
import { provideRouter } from '@angular/router';

export const appConfig: ApplicationConfig = {
  providers: [
    provideHttpClient(),
    provideRouter(routes),
    // other providers
  ]
};
```

## Error Handling

The component includes comprehensive error handling:

1. **Network Errors** - Displays error message if GraphQL endpoint is unreachable
2. **Query Errors** - Shows specific GraphQL error messages
3. **Fallback Navigation** - Provides default menu items if WordPress menu fails to load
4. **Graceful Degradation** - Component still functions with fallback title if site settings fail

## Performance Features

1. **Signal-based Reactivity** - Efficient updates using Angular Signals
2. **Query Caching** - GraphQL responses are cached for better performance
3. **Lazy Loading** - Components load data only when needed
4. **Debounced Updates** - Prevents excessive re-renders during loading

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Android Chrome)
- Progressive enhancement for older browsers

## Development Notes

### Converting from Vue to Angular

Key changes made during conversion:
- `v-if` → `*ngIf`
- `v-for` → `*ngFor`
- `NuxtLink` → `routerLink`
- Vue composables → Angular services
- Vue refs → Angular signals
- Template syntax adjustments

### Future Enhancements

Potential improvements:
- Add keyboard navigation support
- Implement search functionality
- Add breadcrumb navigation
- Support for mega menus
- Add animation transitions
- Implement caching strategies
