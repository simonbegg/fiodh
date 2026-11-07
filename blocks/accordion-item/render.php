<?php

declare(strict_types=1);

use Timber\Timber;

$inlineEditingAvailable = $is_preview
    && function_exists('acf_inline_text_editing_attrs');

$context = Timber::context();

$context['item'] = [
    'question' => (string) get_field('question'),
    'answer' => (string) get_field('answer'),
];

$context['editing'] = [
    'question' => $inlineEditingAvailable
        ? acf_inline_text_editing_attrs('question', [
            'placeholder' => 'Add a question',
        ])
        : '',
    'answer' => $inlineEditingAvailable
        ? acf_inline_text_editing_attrs('answer', [
            'placeholder' => 'Add an answer',
        ])
        : '',
];

$context['is_preview'] = (bool) $is_preview;

$context['wrapper_attributes'] = $is_preview
    ? ''
    : get_block_wrapper_attributes(['class' => 'accordion-item']);

Timber::render('blocks/accordion-item.twig', $context);