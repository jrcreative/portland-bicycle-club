<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$data = Timber::context();
$data['post'] = Timber::get_post();

$data['is_published'] = get_post_status() == 'publish';
$data['is_pending'] = get_post_status() == 'pending';

$data['terrain'] = get_actual_ride_terrain();
$data['length'] = get_actual_ride_length();
$data['max_length'] = get_actual_ride_maxlength();
if ($data['length'] == $data['max_length']) {
    $data['max_length'] = null;
}
$data['maps'] = get_actual_ride_maps();

$data['description'] = convert_ride_desc_addr_to_link();

$data['current_url'] = get_permalink();

// render
Timber::render('pages/single-ride_template.html.twig', $data);
