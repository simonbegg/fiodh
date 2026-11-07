<?php

declare(strict_types=1);

use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();
$context['is_singular'] = is_singular();
$context['is_404'] = is_404();
$context['is_search'] = is_search();
$context['search_query'] = get_search_query();
$context['archive_title'] = is_archive() ? get_the_archive_title() : '';

Timber::render('pages/index.twig', $context);
