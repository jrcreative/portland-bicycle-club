<?php
add_action('init', function() {
    add_filter('get_the_excerpt', function ($text) {
        return rtrim($text, '[&hellip;]') . '&hellip;';
    });

    add_filter('excerpt_length', function(){
        return 50;
    });
});

/* After login, redirect all users that are not administrators to the home page. */
add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if (is_wp_error($user) || !is_a($user, 'WP_User')) {
        return $redirect_to;
    }
    $home_page = trim(get_field('home_page', 'user_'.$user->ID));
    if (!empty($home_page)) {
        return $home_page;
    }
    if ( isset( $user->roles ) && is_array( $user->roles ) ) {
        if ( in_array( 'administrator', $user->roles ) ) {
            return $redirect_to;
        } else {
            return home_url();
        }
    } else {
        return $redirect_to;
    }
}, 999999, 3);
