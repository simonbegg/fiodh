<?php

declare(strict_types=1);

use Timber\Timber;

$context = Timber::context();
$post = Timber::get_post();
$context['post'] = $post;

Timber::render([
    'pages/page-' . $post->post_name . '.twig',
    'pages/page.twig',
], $context);