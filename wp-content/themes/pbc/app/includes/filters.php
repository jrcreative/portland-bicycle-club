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
        if ($query->is_search && !is_user_logged_in() && get_field('logged_in_to_search', 'option')) {
            wp_die('You must be logged in to search this website.', 'Search Protected', array('response' => 403));
        }
        if ($query->is_search && get_field('exclude_page_search', 'option')) {
            // Create a meta-query filtering out excluded pages
            $hidden_meta_query = [
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
            ];

            // Get the current metadata query so we can alter it instead of overwriting it
            $meta_query = $query->get( 'meta_query' );

            // If there's not already a meta-query, supply the one for excluded pages
            if ( !is_array( $meta_query ) || empty( $meta_query ) ) {
                $query->set( 'meta_query', $hidden_meta_query );
            }
            else {
                // If there is an existing meta-query, modify it to support our new excluded pages
                // meta-query as a top-level 'AND' condition, if need be
                if ( isset( $meta_query[ 'relation' ] ) && 'OR' === $meta_query[ 'relation' ] ) {
                    $meta_query = [
                        'relation' => 'AND',
                        $meta_query
                    ];
                }

                // Add the hidden posts meta-query and overwrite query's old meta-query
                $meta_query[] = $hidden_meta_query;
                $query->set( 'meta_query', $meta_query );
            }
        }
    }
});
