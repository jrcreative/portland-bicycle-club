<?php
require_once __DIR__.'/app/bootstrap.php';
use Timber\Timber;

// Timber 2.0: Use Timber::context() instead of get_context()
$context = Timber::context();
$template = 'base.html.twig';

// Handle WooCommerce pages first
if (function_exists('is_woocommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
    include __DIR__.'/resources/woocommerce.php';
    return;
}

if(is_404()) {
    $context['post'] = Timber::get_post();
    $template = 'pages/404.html.twig';
    Timber::render($template, $context);
    return;
}

if(is_search()) {
    include __DIR__ . '/resources/search.php';
    return;
}

if(is_author()) {
    include __DIR__ . '/resources/author.php';
    return;
}

if(is_singular())
{
    // Timber 2.0: Use Timber::get_post() directly
    $context['post'] = Timber::get_post();

    if(is_front_page())
    {
        $template = 'pages/page.html.twig';
        $rows = [];
        while(have_rows('content_rows')) {
            the_row();
            if(get_row_layout() == "news") {
                // Timber 2.0: get_posts() with query array directly
                $context['news'] = Timber::get_posts([
                    'posts_per_page' => 8,
                    'orderby' => array(
                        'date' => 'DESC'
                    )
                ]);
            }
            elseif(get_row_layout() == "rides")
            {
                $timezone = new DateTimeZone(pwtc_get_timezone_string());
                $today = new DateTime('now', $timezone);
                $later = clone $today;
                $later->add(new DateInterval('P2D'));
                $rides_query = new WP_Query([
                    'posts_per_page' => -1,
                    'post_type' => 'scheduled_rides',
                    'meta_query' => [
                        [
                            'key' => 'date',
                            'value' => [$today->format('Y-m-d 00:00:00'), $later->format('Y-m-d 23:59:59')],
                            'compare' => 'BETWEEN',
                            'type' => 'DATETIME'
                        ],
                    ],
                    'orderby' => ['date' => 'ASC'],
                ]);
                $rides_data = [];
                while($rides_query->have_posts()){
                    $rides_query->the_post();
                    $datetime = DateTime::createFromFormat('Y-m-d H:i:s', get_field('date'), $timezone);
                    $date = $datetime->format('Y-m-d');
                    if(!isset($rides_data[$date])){
                        $rides_data[$date] = [];
                    }
                    $rides_data[$date][] = [
                        'title' => get_the_title(),
                        'link' => get_the_permalink(),
                        'date' => get_field('date'),
                        'time' => get_field('date'),
                        'is_canceled' => get_field('is_canceled'),
                    ];
                }
                $context['rides'] = $context['news'] = Timber::get_posts($rides_query);
                $context['today'] = $today->format('n/d/Y g:i A');
                wp_reset_query();
            }
        }
        $context['rows'] = $rows;
    }
    else if(is_single())
    {
        if(is_page())
        {
            $template = 'pages/page.html.twig';
        }
        elseif(get_post_type() == "scheduled_rides")
        {
            include __DIR__ . '/resources/single-scheduled_rides.php';
            return;
        }
        elseif(get_post_type() == "ride_maps")
        {
            include __DIR__ . '/resources/single-ride_maps.php';
            return;
        }
        elseif(get_post_type() == "ride_template")
        {
            include __DIR__ . '/resources/single-ride_template.php';
            return;
        }
        else
        {
            $template = 'two-column.html.twig';
            $context['comments'] = comments_open();
        }
    }
}
elseif(get_post_type() == 'scheduled_rides')
{
    // Use the dedicated scheduled rides archive template (calendar view)
    include __DIR__ . '/resources/archive-scheduled_rides.php';
    return;
}
elseif(get_post_type() == 'newsletter')
{
    $template = 'pages/archive.html.twig';
    $context['title'] = 'Newsletter Articles';
    if($context['title'] == "Archives") { $context['title'] = "Newsletters"; }
    // Timber 2.0: Use Timber::get_posts() instead of new PostQuery()
    $context['posts'] = Timber::get_posts();
    $context['comments'] = comments_open();
}
else
{
    $template = 'pages/archive.html.twig';
    $context['title'] = get_the_archive_title();
    if($context['title'] == "Archives") { $context['title'] = "News"; }
    // Timber 2.0: Use Timber::get_posts() instead of new PostQuery()
    $context['posts'] = Timber::get_posts();
}

Timber::render($template, $context);
