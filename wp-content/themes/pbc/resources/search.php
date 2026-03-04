<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$context = Timber::context();

$template = 'pages/search.html.twig';
$context['query'] = get_search_query();
$context['title'] = 'Search Results';
$context['posts'] = Timber::get_posts();

Timber::render($template, $context);
