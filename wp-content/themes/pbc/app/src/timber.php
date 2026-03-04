<?php
use Timber\Timber;

/** @var $container \Symfony\Component\DependencyInjection\Container */
global $wp;

// Timber 2.0: Global context filter (renamed from 'timber_context')
add_filter('timber/context', function($data) use($wp) {
    $data['current_url'] = home_url(add_query_arg([], $wp->request));

    $data['menu_logo'] = get_theme_mod('menu_logo');
    $data['facebook'] = get_theme_mod('social_facebook');
    $data['google_plus'] = get_theme_mod('social_google_plus');
    $data['instagram'] = get_theme_mod('social_instagram');
    $data['linkedin'] = get_theme_mod('social_linkedin');
    $data['pinterest'] = get_theme_mod('social_pinterest');
    $data['snapchat'] = get_theme_mod('social_snapchat');
    $data['twitter'] = get_theme_mod('social_twitter');
    $data['myspace'] = get_theme_mod('social_myspace');
    $data['yelp'] = get_theme_mod('social_yelp');
    $data['youtube'] = get_theme_mod('social_youtube');
    $data['info'] = get_theme_mod('social_info');
    $data['copyright_type'] = get_theme_mod('copyright_type', 'stellar');
    $data['copyright'] = get_theme_mod('copyright_content');
    $data['scripts_head'] = get_theme_mod('header_scripts');
    $data['scripts_body'] = get_theme_mod('footer_scripts');

    return $data;
});

// Timber 2.0: Template locations via filter (replaces Timber::$locations)
add_filter('timber/locations', function($locations) use ($container) {
    $paths = $container->getParameterBag()->resolveValue($container->getParameter('twig.paths'));
    return array_merge($locations, array_map(fn($p) => [$p], $paths));
});

