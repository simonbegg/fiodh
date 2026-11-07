<?php

declare(strict_types=1);

namespace App;

final class Theme
{
    public static function boot(): void
    {
        add_action('after_setup_theme', [self::class, 'setup']);
        Assets::boot();
        Blocks::boot();
        DesignSystem::boot();
    }

    public static function setup(): void
    {
        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('editor-styles');
        add_theme_support('wp-block-styles');
        add_theme_support('align-wide');
        register_nav_menus([
            'primary' => __('Primary navigation', 'timber-prototype'),
            'footer' => __('Footer navigation', 'timber-prototype'),
        ]);

        add_theme_support('html5', [
            'comment-list',
            'comment-form',
            'search-form',
            'gallery',
            'caption',
            'style',
            'script',
        ]);
    }
}
