<?php

declare(strict_types=1);

namespace App;

final class DesignSystem
{
    public static function boot(): void
    {
        add_action('init', [self::class, 'registerBlockStyles'], 20);
    }

    public static function registerBlockStyles(): void
    {
        $surfaceBlocks = [
            'core/group',
            'core/columns',
            'acf/hero',
            'acf/accordion',
        ];

        $surfaceStyles = [
            'surface-canvas' => 'Canvas surface',
            'surface-ink' => 'Ink surface',
            'surface-brand' => 'Brand surface',
            'surface-accent' => 'Accent surface',
        ];

        foreach ($surfaceBlocks as $blockName) {
            foreach ($surfaceStyles as $name => $label) {
                register_block_style($blockName, [
                    'name' => $name,
                    'label' => __($label, 'timber-prototype'),
                ]);
            }
        }

        register_block_style('core/button', [
            'name' => 'design-outline',
            'label' => __('Design outline', 'timber-prototype'),
        ]);

        register_block_style('core/button', [
            'name' => 'design-accent',
            'label' => __('Accent button', 'timber-prototype'),
        ]);

        register_block_style('core/image', [
            'name' => 'design-rounded',
            'label' => __('Rounded image', 'timber-prototype'),
        ]);

        register_block_style('core/image', [
            'name' => 'design-frame',
            'label' => __('Framed image', 'timber-prototype'),
        ]);
    }
}
