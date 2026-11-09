<?php

declare(strict_types=1);

namespace App;

use JsonException;

final class Assets
{
    private const FRONTEND_ENTRY = 'resources/js/app.js';
    private const EDITOR_ENTRY = 'resources/js/editor.js';

    // Google-hosted example. Set to false when using local fontFace files in theme.json.
    private const USE_GOOGLE_FONTS = true;
    private const GOOGLE_FONTS_URL = 'https://fonts.googleapis.com/css2?family=Astloch:wght@400;700&family=Basic&family=Inter:wght@400;700&family=Jim+Nightshade&display=swap';

    public static function boot(): void
    {
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontend']);
        add_action('enqueue_block_assets', [self::class, 'enqueueEditor']);
        // enqueue_block_assets supplies both the front end and the editor canvas.
        add_action('enqueue_block_assets', [self::class, 'enqueueFonts'], 5);
        add_filter('timber/twig', [self::class, 'registerTwigFunctions']);
    }

    public static function enqueueFonts(): void
    {
        if (! self::USE_GOOGLE_FONTS) {
            return;
        }

        wp_enqueue_style('timber-prototype-google-fonts', self::GOOGLE_FONTS_URL, [], null);
    }

    public static function registerTwigFunctions(\Twig\Environment $twig): \Twig\Environment
    {
        $twig->addFunction(new \Twig\TwigFunction('theme_image', [self::class, 'imageUrl']));
        return $twig;
    }

    public static function imageUrl(string $name): string
    {
        $manifestPath = get_theme_file_path('dist/images/manifest.json');
        if (! is_readable($manifestPath)) {
            return '';
        }
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        $file = is_array($manifest) ? ($manifest[$name] ?? '') : '';
        return is_string($file) && str_starts_with($file, 'images/') && ! str_contains($file, '..')
            ? get_theme_file_uri('dist/' . $file)
            : '';
    }

    public static function enqueueFrontend(): void
    {
        self::enqueueEntry(
            self::FRONTEND_ENTRY,
            'timber-prototype-app'
        );
    }

    public static function enqueueEditor(): void
    {
        if (! is_admin()) {
            return;
        }

        $server = self::developmentServer();

        if ($server !== null) {

            wp_enqueue_style(
                'timber-prototype-editor',
                $server . '/resources/css/editor.css?direct',
                [],
                null
            );

            return;
        }

        $manifestPath = get_theme_file_path('dist/manifest.json');

        if (! is_readable($manifestPath)) {
            return;
        }

        try {
            $manifest = json_decode(
                (string) file_get_contents($manifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return;
        }

        $entry = $manifest[self::EDITOR_ENTRY] ?? null;

        if (! is_array($entry)) {
            return;
        }

        foreach ($entry['css'] ?? [] as $index => $stylesheet) {
            wp_enqueue_style(
                'timber-prototype-editor-' . $index,
                get_theme_file_uri('dist/' . $stylesheet),
                [],
                null
            );
        }
    }

    private static function enqueueEntry(string $entryName, string $handle): void
    {
        $server = self::developmentServer();

        if ($server !== null) {

            wp_enqueue_script_module(
                $handle . '-vite-client',
                $server . '/@vite/client',
                [],
                null
            );

            wp_enqueue_script_module(
                $handle,
                $server . '/' . $entryName,
                [],
                null
            );

            return;
        }

        $manifestPath = get_theme_file_path('dist/manifest.json');

        if (! is_readable($manifestPath)) {
            return;
        }

        try {
            $manifest = json_decode(
                (string) file_get_contents($manifestPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException) {
            return;
        }

        $entry = $manifest[$entryName] ?? null;

        if (! is_array($entry)) {
            return;
        }

        foreach ($entry['css'] ?? [] as $index => $stylesheet) {
            wp_enqueue_style(
                $handle . '-' . $index,
                get_theme_file_uri('dist/' . $stylesheet),
                [],
                null
            );
        }

        if (isset($entry['file'])) {
            $scriptPath = get_theme_file_path('dist/' . $entry['file']);

            if (! is_readable($scriptPath) || 0 === filesize($scriptPath)) {
                return;
            }

            wp_enqueue_script_module(
                $handle,
                get_theme_file_uri('dist/' . $entry['file']),
                [],
                null
            );
        }
    }

    private static function developmentServer(): ?string
    {
        static $checked = false;
        static $server = null;
        if ($checked) {
            return $server;
        }
        $checked = true;
        if (! in_array(wp_get_environment_type(), ['local', 'development'], true)) {
            return null;
        }
        $hotFile = get_theme_file_path('dist/hot');
        if (! is_readable($hotFile)) {
            return null;
        }
        $candidate = rtrim(trim((string) file_get_contents($hotFile)), '/');
        $parts = wp_parse_url($candidate);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host']) || ! in_array($parts['scheme'], ['http', 'https'], true)) {
            return null;
        }
        $port = $parts['port'] ?? ($parts['scheme'] === 'https' ? 443 : 80);
        $connection = @fsockopen($parts['host'], $port, $errorCode, $errorMessage, 0.2);
        if ($connection === false) {
            return null;
        }
        fclose($connection);
        $server = $candidate;
        return $server;
    }
}
