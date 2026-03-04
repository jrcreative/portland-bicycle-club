<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$data = Timber::context();
$data['post'] = Timber::get_post();

// cancel/reschedule ride?
if(isset($_GET['canceled']) && can_cancel_ride(get_the_ID())) {
    update_field('is_canceled', (bool) $_GET['canceled']);
    if (class_exists('WordKeeper\System\Purge')) {
        WordKeeper\System\Purge::purge_all();
    }
}

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

$data['user_can_cancel'] = can_cancel_ride(get_the_ID());
$data['user_can_view_signups'] = can_view_signups(get_the_ID());
$data['current_url'] = get_permalink();

if (function_exists('pwtc_mapdb_get_signup')) {
    $signup = pwtc_mapdb_get_signup();
    $data['view_signup_url'] = $signup['view_signup_url'];
    $data['edit_ride_url'] = $signup['edit_ride_url'];
    $data['copy_ride_url'] = $signup['copy_ride_url'];
    $data['ride_signup_msg'] = $signup['ride_signup_msg'];
    $data['ride_signup_url'] = $signup['ride_signup_url'];
    $data['ride_signup_btn'] = $signup['ride_signup_btn'];
    $data['allow_cancel'] = $signup['allow_cancel'];

}
else {
    $data['view_signup_url'] = false;
    $data['edit_ride_url'] = false;
    $data['copy_ride_url'] = false;
    $data['ride_signup_msg'] = false;
    $data['ride_signup_url'] = false;
    $data['ride_signup_btn'] = false;
    $data['allow_cancel'] = true;
}

// render
Timber::render('pages/single-ride.html.twig', $data);
