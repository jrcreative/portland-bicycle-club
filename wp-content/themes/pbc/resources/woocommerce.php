<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$context            = Timber::context();
$context['sidebar'] = Timber::get_widgets('shop-sidebar');

if (is_singular('product')) {
    $context['post']    = Timber::get_post();
    $product            = wc_get_product($context['post']->ID);
    $context['product'] = $product;

    Timber::render('pages/single-product.html.twig', $context);
} elseif (is_cart() || is_checkout() || is_account_page()) {
    // Handle WooCommerce shortcode pages (cart, checkout, my-account)
    $context['post'] = Timber::get_post();
    $context['title'] = get_the_title();
    
    Timber::render('pages/woocommerce-page.html.twig', $context);
} else {
    $posts = Timber::get_posts();
    $context['products'] = $posts;

    if (is_product_category()) {
        $queried_object = get_queried_object();
        $term_id = $queried_object->term_id;
        $context['category'] = get_term($term_id, 'product_cat');
        $context['title'] = single_term_title('', false);
    }

    Timber::render('pages/archive-products.html.twig', $context);
}