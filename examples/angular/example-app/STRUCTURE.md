# Angular Project Structure

This project follows Angular best practices for folder organization and component structure.

## Folder Structure

```
src/
├── app/
│   ├── components/           # Reusable UI components
│   │   ├── header/          # Header component
│   │   ├── footer/          # Footer component
│   │   └── index.ts         # Component exports
│   ├── pages/               # Page components (route components)
│   ├── services/            # Angular services
│   ├── shared/              # Shared utilities and models
│   │   ├── models/          # TypeScript interfaces and types
│   │   └── utils/           # Utility functions
│   ├── app.ts              # Root app component
│   ├── app.html            # Root app template
│   ├── app.css             # Root app styles
│   └── app.routes.ts       # App routing configuration
└── assets/                 # Static assets (images, fonts, etc.)
```

## Component Architecture

### Header Component (`components/header/`)
- **Purpose**: Navigation and branding
- **Features**: 
  - Responsive navigation menu
  - Dynamic title from parent
  - Configurable navigation items
- **Input Properties**: `title` (string)

### Footer Component (`components/footer/`)
- **Purpose**: Site footer with links and contact info
- **Features**:
  - Multi-column layout
  - Quick links navigation
  - Contact information
  - Dynamic copyright year
- **Input Properties**: `title` (string)

## Best Practices Implemented

1. **Component Separation**: Header and footer are isolated, reusable components
2. **Input Properties**: Components receive data through `@Input()` decorators
3. **Standalone Components**: Using Angular's standalone component feature
4. **Barrel Exports**: Index files for cleaner imports
5. **Responsive Design**: Mobile-first CSS approach
6. **TypeScript Interfaces**: For type safety (in shared/models)
7. **Service Layer**: For business logic and API calls
8. **Page Components**: Route-level components in pages folder

## Usage

### Using Components
```typescript
// Import components
import { HeaderComponent, FooterComponent } from './components';

// Use in component
@Component({
  imports: [HeaderComponent, FooterComponent],
  // ...
})

// In template
<app-header [title]="appTitle"></app-header>
<app-footer [title]="appTitle"></app-footer>
```

### Adding New Components
1. Create component folder in appropriate directory
2. Generate component files (.ts, .html, .css)
3. Export component in index.ts file
4. Import where needed

## Future Enhancements

- Add loading states and error handling
- Implement theme switching
- Add accessibility features (ARIA labels, keyboard navigation)
- Create shared UI component library
- Add unit and integration tests
