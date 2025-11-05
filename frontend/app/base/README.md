# Base Components Layer

This is the base UI components layer for the Certchain platform, containing all core design system components and utilities.

## Features

- **Logo Components**: CertchainLogo and SimpleLogo with all variants
- **Typography**: Headings, paragraphs, and text styles  
- **Buttons**: Various button styles and sizes
- **Cards**: Basic cards and course cards
- **Interactive Elements**: Navigation and form components

## Development

### Running the Base Layer

To develop and test the base components:

```bash
cd frontend/app/base
pnpm dev
```

The base layer playground will be available at `http://localhost:3002`

### Building

To build the playground:

```bash
cd app/base/.playground
npm run build
# or
pnpm build
```

## Structure

- `app.vue` - Main playground showcasing all base components
- `nuxt.config.ts` - Playground-specific configuration
- `package.json` - Playground dependencies and scripts

The playground extends the parent base configuration and inherits all components and styles from the base layer.

## Components Tested

### Logo Components
- ✅ CertchainLogo (horizontal, vertical, icon-only variants)
- ✅ SimpleLogo (all variants with different colors)

### UI Components  
- ✅ Button styles (.btn, .btn-secondary)
- ✅ Card components (.card, .course-card)
- ✅ Typography (hero sections, headings)
- ✅ Navigation elements
- ✅ Form components

This playground serves as a living style guide and component library for the Certchain platform.
