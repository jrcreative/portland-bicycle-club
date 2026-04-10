<?php
require_once __DIR__.'/../app/bootstrap.php';
use Timber\Timber;

$context = Timber::context();

// Add shop sidebar
ob_start();
dynamic_sidebar('shop-sidebar');
$context['sidebar'] = ob_get_clean();

// Single Product Page
if (is_singular('product')) {
    $post = Timber::get_post();
    $post->setup(); // Important for WooCommerce compatibility
    $context['post'] = $post;
    $context['product'] = wc_get_product($post->ID);

    Timber::render('pages/single-product.html.twig', $context);
}
// Cart, Checkout, or My Account Pages - use default WooCommerce templates
elseif (is_cart() || is_checkout() || is_account_page()) {
    // These pages use WooCommerce's built-in templates via shortcodes
    // We just need to provide a simple wrapper
    $context['post'] = Timber::get_post();
    $context['title'] = get_the_title();
    
    Timber::render('pages/woocommerce-page.html.twig', $context);
}
// Shop Archive / Category Pages
else {
    $posts = Timber::get_posts();
    $context['products'] = $posts;

    // Product Category
    if (is_product_category()) {
        $queried_object = get_queried_object();
        $term_id = $queried_object->term_id;
        $context['category'] = get_term($term_id, 'product_cat');
        $context['title'] = single_term_title('', false);
    }
    // Product Tag
    elseif (is_product_tag()) {
        $context['title'] = single_term_title('', false);
    }
    // Main Shop Page
    elseif (is_shop()) {
        $context['title'] = woocommerce_page_title(false);
    }

    Timber::render('pages/archive-products.html.twig', $context);
}