<?php
add_action('init', function() use($container) {
    // load languages directory
    if ($container->hasParameter('wordpress.translations')) {
        load_theme_textdomain('supertheme', $container->getParameter('wordpress.translations'));
    }
});