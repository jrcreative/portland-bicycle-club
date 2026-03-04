<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$data = Timber::context();

// render
Timber::render('pages/404.html.twig', $data);
