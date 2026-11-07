# Timber Prototype starter

A classic WordPress theme using Timber 2, Twig, Tailwind CSS, Vite and ACF Pro blocks.

## Requirements

PHP 8.2 or newer, Composer 2 and WordPress 6.5 or newer. Node.js 20.19+ or 22.12+ and npm are required to build assets. ACF Pro is required for the custom blocks; the prototype was tested with ACF Pro 6.8.11. Obtain and license it separately.

## Local setup

From the theme directory run `composer install`, `npm ci` and `npm run build`. Activate ACF Pro and the theme. Set `WP_ENVIRONMENT_TYPE` to `local` or `development` in the local WordPress configuration, run `npm run dev` and visit the WordPress site URL. The development server defaults to localhost:5173; set `VITE_DEV_HOST`, `VITE_DEV_PORT` and, if needed, `VITE_DEV_ORIGIN` in the shell or Vite environment for another local setup. Allow the WordPress origin in `vite.config.js` when it is not a localhost or .local domain. WordPress ignores a stale hot marker if the server cannot be reached, and ignores it entirely in staging and production.

## Production

Run `composer install --no-dev --optimize-autoloader`, `npm ci` and `npm run build`. Deploy the theme including `vendor/` and `dist/`; omit `node_modules/`, local environment files and `dist/hot`. The repository ignores generated dependencies and assets, so deployment must build them or package them in CI. Build in a separate release directory and deploy atomically: the build clears `dist/`.

The build converts JPEG, PNG and WebP files in `resources/images/` to hashed WebP files in `dist/images/` and copies SVGs with hashes. In Twig use `theme_image('filename.png')` to resolve a source-relative image name to its built URL, and escape it for an HTML attribute. Run a build after adding or changing theme images, including during development. Media Library optimization is a separate site-level responsibility.

## Customisation

Assign a static homepage in Settings > Reading to edit its Homepage sections ACF fields. The dedicated `front-page.php` and Twig template compose the ACF hero, intro partial and optional block content. Assign Primary and Footer menus in Appearance > Menus. Alpine is bundled in the frontend entry; `src/Assets.php` handles asset loading.

ACF field groups are stored in `acf-json/`. ACF loads these automatically; use ACF's Field Groups sync screen to import them into the database for editing. Commit JSON changes after updating a field group. Homepage fields are accessed in Twig via `post.meta()`.

Update the theme name and text domain in `style.css`, the `timber-prototype` text domain and asset handles in PHP/Twig, and the npm package name. Blocks use stable `acf/` names and field keys: changing those after content is saved requires a content migration. Demo block design and tokens live in `blocks/`, `resources/views/` and `theme.json`.
