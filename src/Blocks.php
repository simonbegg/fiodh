<?php

declare(strict_types=1);

namespace App;

final class Blocks
{
    public static function boot(): void
    {
        add_action('init', [self::class, 'registerBlocks']);
    }

    public static function registerBlocks(): void
    {
        $metadataFiles = glob(
            get_theme_file_path('blocks/*/block.json')
        ) ?: [];

        foreach ($metadataFiles as $metadataFile) {
            register_block_type(dirname($metadataFile));
        }
    }

}
