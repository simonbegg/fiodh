<?php

declare(strict_types=1);

use Timber\Timber;

$link = get_field('link');

$context = Timber::context();

$context['hero'] = [
    'eyebrow' => (string) get_field('eyebrow'),
    'heading' => (string) get_field('heading'),
    'body' => (string) get_field('body'),
    'link' => [
        'url' => is_array($link) ? (string) ($link['url'] ?? '') : '',
        'label' => is_array($link) ? (string) ($link['title'] ?? '') : '',
        'target' => is_array($link) ? (string) ($link['target'] ?? '') : '',
    ],
];

$context['is_preview'] = (bool) $is_preview;

$context['wrapper_attributes'] = $is_preview
    ? ''
    : get_block_wrapper_attributes(['class' => 'acf-hero']);

    $inlineEditingAvailable = $is_preview
    && function_exists('acf_inline_text_editing_attrs')
    && function_exists('acf_inline_toolbar_editing_attrs');

$context['editing'] = [
    'eyebrow' => $inlineEditingAvailable
        ? acf_inline_text_editing_attrs('eyebrow', [
            'placeholder' => 'Add an eyebrow',
        ])
        : '',
    'heading' => $inlineEditingAvailable
        ? acf_inline_text_editing_attrs('heading', [
            'placeholder' => 'Add a heading',
        ])
        : '',
    'body' => $inlineEditingAvailable
        ? acf_inline_text_editing_attrs('body', [
            'placeholder' => 'Add supporting text',
        ])
        : '',
    'link' => $inlineEditingAvailable
        ? acf_inline_toolbar_editing_attrs(['link'])
        : '',
];
Timber::render('blocks/hero.twig', $context);