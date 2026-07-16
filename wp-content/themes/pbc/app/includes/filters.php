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

add_action( 'pre_get_posts', function ($query) {
    if ( ! is_admin() && $query->is_main_query() ) {
        if ($query->is_search && get_field('exclude_page_search', 'option')) {
            $query->set( 'meta_query', [
                'relation' => 'OR',
                [
                    'key' => 'exclude_page_from_search',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key' => 'exclude_page_from_search',
                    'value' => '1',
                    'compare' => '!=',
                ],
            ] );
        }
    }
});
