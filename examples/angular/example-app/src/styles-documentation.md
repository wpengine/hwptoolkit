# Global SCSS Styles Documentation

This document describes the global SCSS utilities available in the Angular HeadlessWP Toolkit application.

## CSS Variables (Custom Properties)

The global styles use CSS custom properties for consistent theming and easy customization:

### Colors
- `--primary-color: #667eea` - Primary brand color
- `--primary-dark: #764ba2` - Darker variant of primary
- `--gray-*` - Grayscale palette from 50 (lightest) to 900 (darkest)
- `--success-color`, `--warning-color`, `--error-color`, `--info-color` - Status colors

### Typography
- `--font-family-sans` - Default sans-serif font stack
- `--text-*` - Font sizes from xs (12px) to 5xl (48px)
- `--font-*` - Font weights from light (300) to extrabold (800)

### Spacing
- `--space-*` - Consistent spacing scale from 1 (4px) to 24 (96px)

### Other Variables
- `--radius-*` - Border radius values
- `--shadow-*` - Box shadow definitions
- `--transition-*` - Transition timing functions
- `--z-*` - Z-index layers for proper stacking

## Utility Classes

### Layout
```html
<!-- Containers -->
<div class="container">       <!-- Max-width 1200px, centered -->
<div class="container-sm">    <!-- Max-width 640px -->
<div class="container-lg">    <!-- Max-width 1024px -->

<!-- Flexbox -->
<div class="flex items-center justify-between">
<div class="flex-col">        <!-- Flex direction column -->

<!-- Grid -->
<div class="grid grid-cols-3 gap-4">     <!-- 3-column grid -->
<div class="grid grid-cols-1 md:grid-cols-3">  <!-- Responsive grid -->
```

### Spacing
```html
<!-- Margin -->
<div class="m-4">     <!-- Margin all sides -->
<div class="mt-6">    <!-- Margin top -->
<div class="mb-8">    <!-- Margin bottom -->

<!-- Padding -->
<div class="p-4">     <!-- Padding all sides -->
<div class="px-6">    <!-- Padding horizontal -->
<div class="py-8">    <!-- Padding vertical -->
```

### Typography
```html
<!-- Font sizes -->
<h1 class="text-4xl">         <!-- Large heading -->
<p class="text-lg">           <!-- Large paragraph -->
<span class="text-sm">        <!-- Small text -->

<!-- Font weights -->
<h2 class="font-bold">        <!-- Bold text -->
<p class="font-medium">       <!-- Medium weight -->

<!-- Text colors -->
<p class="text-primary">      <!-- Primary color text -->
<p class="text-gray-700">     <!-- Gray text -->
<p class="text-success">      <!-- Success color -->

<!-- Text alignment -->
<div class="text-center">     <!-- Center aligned -->
<div class="text-left">       <!-- Left aligned -->
```

### Buttons
```html
<!-- Button variants -->
<button class="btn btn-primary">Primary Button</button>
<button class="btn btn-secondary">Secondary Button</button>
<button class="btn btn-outline">Outline Button</button>

<!-- Button sizes -->
<button class="btn btn-primary btn-sm">Small Button</button>
<button class="btn btn-primary btn-lg">Large Button</button>
```

### Backgrounds & Borders
```html
<!-- Background colors -->
<div class="bg-white">        <!-- White background -->
<div class="bg-gray-100">     <!-- Light gray background -->
<div class="bg-primary">      <!-- Primary color background -->

<!-- Borders -->
<div class="border rounded-lg">        <!-- Border with rounded corners -->
<div class="border-primary">           <!-- Primary color border -->

<!-- Shadows -->
<div class="shadow-md">       <!-- Medium shadow -->
<div class="shadow-lg">       <!-- Large shadow -->
```

### States & Animations
```html
<!-- Loading states -->
<div class="loading">         <!-- Loading state with opacity -->
<div class="spinner">         <!-- Spinning loader -->

<!-- Animations -->
<div class="fade-in">         <!-- Fade in animation -->
```

### WordPress-specific Classes
```html
<!-- WordPress block styling -->
<div class="wp-block">        <!-- Standard WordPress block -->
<div class="wp-block-group">  <!-- WordPress block group -->
<blockquote class="wp-block-quote">  <!-- Styled quote block -->
```

## Responsive Design

The utility classes include responsive variants using breakpoint prefixes:

- `sm:` - 640px and up
- `md:` - 768px and up  
- `lg:` - 1024px and up

```html
<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3">

<!-- Responsive text alignment -->
<div class="text-center md:text-left">

<!-- Responsive spacing -->
<div class="p-4 md:p-8">
```

## Usage Examples

### Card Component
```html
<div class="bg-white p-6 rounded-lg shadow-md border">
  <h3 class="text-xl font-semibold mb-3 text-primary">Card Title</h3>
  <p class="text-gray-600 mb-4">Card description text here.</p>
  <button class="btn btn-primary btn-sm">Action</button>
</div>
```

### Hero Section
```html
<section class="bg-gray-50 p-8">
  <div class="container text-center">
    <h1 class="text-4xl font-bold mb-4 text-gray-900">Hero Title</h1>
    <p class="text-lg text-gray-700 mb-6">Hero description</p>
    <button class="btn btn-primary btn-lg">Call to Action</button>
  </div>
</section>
```

### Feature Grid
```html
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
  <div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-semibold mb-3">Feature</h3>
    <p class="text-gray-600">Feature description</p>
  </div>
  <!-- More feature cards... -->
</div>
```

## Customization

To customize the design system, modify the CSS custom properties in `styles.scss`:

```scss
:root {
  --primary-color: #your-brand-color;
  --font-family-sans: 'Your-Font', sans-serif;
  --space-4: 1.5rem; /* Adjust spacing scale */
}
```

## Performance Notes

- All styles are compiled into a single CSS file
- Utility classes are optimized for reusability
- CSS custom properties allow for dynamic theming
- Print styles are included for better document printing
