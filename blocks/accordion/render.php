<?php

declare(strict_types=1);

use Timber\Timber;

$allowedBlocks = [
    'acf/accordion-item',
];

$template = [
    ['acf/accordion-item', []],
    ['acf/accordion-item', []],
];

$context = Timber::context();

$context['is_preview'] = (bool) $is_preview;

$context['wrapper_attributes'] = $is_preview
    ? ''
    : get_block_wrapper_attributes(['class' => 'acf-accordion']);

$context['inner_blocks'] = [
    'allowed' => wp_json_encode($allowedBlocks),
    'template' => wp_json_encode($template),
];

Timber::render('blocks/accordion.twig', $context);