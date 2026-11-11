<?php

declare(strict_types=1);

use App\Theme;
use Timber\Timber;

if (! is_readable(__DIR__ . '/vendor/autoload.php')) {
    wp_die('Theme dependencies are missing. Run composer install in the theme directory.');
}
require_once __DIR__ . '/vendor/autoload.php';

Timber::init();
Timber::$dirname = [
    'resources/views',
    'resources/views/pages',
];

Theme::boot();

add_action('admin_notices', static function (): void {
    if (current_user_can('activate_plugins') && ! function_exists('acf_add_local_field_group')) {
        echo '<div class="notice notice-warning"><p>' . esc_html__('This theme requires ACF Pro for its custom blocks and homepage fields.', 'timber-prototype') . '</p></div>';
    }
});

add_filter('timber/context', static function (array $context): array {
    $context['primary_menu'] = has_nav_menu('primary') ? Timber::get_menu('primary') : null;
    $context['footer_menu'] = has_nav_menu('footer') ? Timber::get_menu('footer') : null;
    $context['current_year'] = (int) current_time('Y');
    return $context;
});
