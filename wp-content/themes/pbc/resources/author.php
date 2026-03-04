<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$data = Timber::context();

$curauth = (isset($_GET['author_name'])) ? get_user_by('slug', $author_name) : get_userdata(intval($author));

$data['id'] = $curauth->ID;
$data['nickname'] = $curauth->nickname;
$data['first_name'] = $curauth->first_name;
$data['last_name'] = $curauth->last_name;

// render
Timber::render('pages/author.html.twig', $data);
