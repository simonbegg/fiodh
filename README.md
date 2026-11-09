# Timber Prototype starter

A classic WordPress theme using Timber 2, Twig, Tailwind CSS, Vite and ACF Pro blocks.

## Requirements

PHP 8.2 or newer, Composer 2 and WordPress 6.5 or newer. Node.js 20.19+ or 22.12+ and npm are required to build assets. ACF Pro is required for the custom blocks; the prototype was tested with ACF Pro 6.8.11. Obtain and license it separately.

## Local setup

From the theme directory run `composer install`, `npm ci` and `npm run build`. Activate ACF Pro and the theme. Set `WP_ENVIRONMENT_TYPE` to `local` or `development` in the local WordPress configuration, run `npm run dev` and visit the WordPress site URL. The development server defaults to localhost:5173; set `VITE_DEV_HOST`, `VITE_DEV_PORT` and, if needed, `VITE_DEV_ORIGIN` in the shell or Vite environment for another local setup. Allow the WordPress origin in `vite.config.js` when it is not a localhost or .local domain. WordPress ignores a stale hot marker if the server cannot be reached, and ignores it entirely in staging and production.

## Production

Run `composer install --no-dev --optimize-autoloader`, `npm ci` and `npm run build`. Deploy the theme including `vendor/` and `dist/`; omit `node_modules/`, local environment files and `dist/hot`. The repository ignores generated dependencies and assets, so deployment must build them or package them in CI. Build in a separate release directory and deploy atomically: the build clears `dist/`.

The build converts JPEG, PNG and WebP files in `resources/images/` to hashed WebP files in `dist/images/` and copies SVGs with hashes. In Twig use `theme_image('filename.png')` to resolve a source-relative image name to its built URL, and escape it for an HTML attribute. During `npm run dev`, images are processed at startup and when added, changed or removed; the browser reloads after processing. Older generated hashes remain until the next production build clears `dist/`. Media Library optimization is a separate site-level responsibility.

## Customisation

### Fonts

The active Google-hosted fonts are Inter (400 and 700), Astloch (400 and 700), Basic (400) and Jim Nightshade (400). `src/Assets.php` enqueues their stylesheet on the frontend and in the block editor. `theme.json` registers the families and makes Inter the default. Twig can use `font-sans` (Inter), `font-serif` (Astloch), `font-basic` (Basic), or `font-display` (Jim Nightshade). Basic and Jim Nightshade are loaded in regular weight only; avoid relying on a real bold variant for them. Update the Google Fonts URL and the matching presets in `theme.json` together when changing families or weights. Google-hosted fonts require visitors' browsers to connect to Google.

To self-host instead, set `USE_GOOGLE_FONTS` to `false` in `src/Assets.php`, add the appropriately licensed WOFF2 files to `assets/fonts/`, and add a `fontFace` array to the corresponding family in `theme.json`. WordPress resolves `file:./` from the theme root and loads the font in the frontend and editor. For example, replace the filename, family and range with your actual font:

```json
{
  "slug": "sans",
  "name": "Your font",
  "fontFamily": "\"Your Font\", sans-serif",
  "fontFace": [
    {
      "fontFamily": "Your Font",
      "fontStyle": "normal",
      "fontWeight": "400 700",
      "fontDisplay": "swap",
      "src": ["file:./assets/fonts/your-font-variable.woff2"]
    }
  ]
}
```

Use a weight range only for a variable font that supports it. Add separate entries for any italic or static-weight files, and deploy the `assets/fonts/` directory with the theme. The theme does not bundle an example font file.

Assign a static homepage in Settings > Reading to edit its Homepage sections ACF fields. The dedicated `front-page.php` and Twig template compose the ACF hero, intro partial and optional block content. Assign Primary and Footer menus in Appearance > Menus. Alpine is bundled in the frontend entry; `src/Assets.php` handles asset loading.

ACF field groups are stored in `acf-json/`. ACF loads these automatically; use ACF's Field Groups sync screen to import them into the database for editing. Commit JSON changes after updating a field group. Homepage fields are accessed in Twig via `post.meta()`.

Update the theme name and text domain in `style.css`, the `timber-prototype` text domain and asset handles in PHP/Twig, and the npm package name. Blocks use stable `acf/` names and field keys: changing those after content is saved requires a content migration. Demo block design and tokens live in `blocks/`, `resources/views/` and `theme.json`.
