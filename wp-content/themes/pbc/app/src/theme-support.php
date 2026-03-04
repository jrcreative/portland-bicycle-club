<?php
add_action('after_setup_theme', function() use($container) {
    // theme supports
    if ($container->hasParameter('wordpress.theme_support')) {
        add_theme_support('admin-bar', array('callback' => '__return_false'));
        foreach ($container->getParameter('wordpress.theme_support') as $support) {
            // html5 requires an array of types in WordPress 6+ / PHP 8+
            if ($support === 'html5') {
                add_theme_support('html5', array(
                    'search-form',
                    'comment-form',
                    'comment-list',
                    'gallery',
                    'caption',
                    'style',
                    'script',
                ));
            } else {
                add_theme_support($support);
            }
        }
    }
});