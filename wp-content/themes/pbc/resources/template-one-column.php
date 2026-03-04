<?php
/**
 * Template Name: One Columns
 *
 */
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();

// render
Timber::render('one-column.html.twig', $context);
