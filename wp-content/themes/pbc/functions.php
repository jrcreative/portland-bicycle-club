<?php
require_once __DIR__.'/app/bootstrap.php';
require_once __DIR__.'/resources/customizer.php';

// add_action('admin_enqueue_scripts', function () {
//     wp_register_script('custom_wp_admin_css', get_template_directory_uri() . '/assets/scripts/admin.js', ['jquery']);
//     wp_enqueue_script('custom_wp_admin_css');
// });

add_filter('timber/context', function($context) {
    // the plugin doesnt load the avatar correctly with timber so we are defning it globably
    $context['avatar'] = get_avatar(get_current_user_id(), 32);
    $context['options'] = get_fields('option');
    ob_start();
    dynamic_sidebar( 'right_sidebar' );
    $data['right_sidebar'] = ob_get_clean();
    ob_start();
    dynamic_sidebar( 'left_sidebar' );
    $data['left_sidebar'] = ob_get_clean();

    return $context;
});

function pwtc_get_timezone_string() {
    if ($timezone = get_option('timezone_string'))
        return $timezone;

    if (0 === ($utc_offset = get_option('gmt_offset', 0)))
        return 'UTC';

    $utc_offset *= 3600;
    if ($timezone = timezone_name_from_abbr('', $utc_offset, 0)) {
        return $timezone;
    }

    $is_dst = date('I');
    foreach (timezone_abbreviations_list() as $abbr) {
        foreach ($abbr as $city) {
            if ($city['dst'] == $is_dst && $city['offset'] == $utc_offset)
                return $city['timezone_id'];
        }
    }

    return 'UTC';
}

function excerpt() {
    $error_level = error_reporting();
    error_reporting($error_level & ~E_NOTICE);

    $text = get_the_content();
    $raw_excerpt = $text;
    $text = strip_shortcodes( $text );
    $text = apply_filters( 'the_content', $text );
    $text = str_replace(']]>', ']]&gt;', $text);
    $excerpt_length = apply_filters( 'excerpt_length', 55 );
    $excerpt_more = apply_filters( 'excerpt_more', ' ...' );
    $text = wp_trim_words( $text, $excerpt_length, $excerpt_more );

    error_reporting($error_level);

    return apply_filters( 'wp_trim_excerpt', $text, $raw_excerpt );
}

add_action( 'pre_get_posts', function ( $query ) {
    if ( $query->is_home() && $query->is_main_query() ) {
        $query->set( 'cat', '-40' );
    }
});

add_filter('wp_nav_menu_objects', function ($items){
    foreach($items as $item){
        if( $item->title == "Log Out"){
            $item->url = $item->url . "&_wpnonce=" . wp_create_nonce( 'log-out' );
        }
    }
    return $items;

});

// Accept an input string, break it into tokens delemited by whitespace
// and look for strings that start with "http://" or "https://". Convert those
// strings to HTML links using the following translation rules:
// 1) http://foo.bar.com becomes <a href="http://foo.bar.com">http://foo.bar.com</a>
// 2) http://foo.bar.com|foo_bar becomes <a href="http://foo.bar.com">foo bar</a> (underscore is converted to a space)
// 3) http://foo.bar.com|foo_bar|. becomes <a href="http://foo.bar.com">foo bar</a>.
function convert_urls_to_links($input) {
    $output = "";
    $tok = strtok($input, " \n\t\r");
    while ($tok !== false) {
        if (0 === strpos($tok, 'http://') or 0 === strpos($tok, 'https://')) {
            $idx = strpos($tok, '<');
            if ($idx !== false) {
                $link = substr($tok, 0, $idx);
                $rem = substr($tok, $idx);
                $tok = $link;
            }
            else {
                $rem = "";
            }
            $strings = explode("|", $tok, 3);
            $ref = $strings[0];
            $label = $ref;
            $end = "";
            if (count($strings) > 1) {
                if (strlen($strings[1]) > 0) {
                    $label = str_replace("_", " ", $strings[1]);
                }
                if (count($strings) > 2) {
                    if (strlen($strings[2]) > 0) {
                        $end = $strings[2];
                    }
                }
            }
            $output .= '<a href="' . $ref . '" target="_blank">' . $label . '</a>' . $end . $rem;
        }
        else {
            $output .= $tok;
        }
        $output .= " ";
        $tok = strtok(" \n\t\r");
    }
    return $output;
}

function strip_tags_from_post($input) {
    return wp_kses($input, array('br' => array(), 'p' => array(), 'em' => array(), 'strong' => array(), 'a' => array('href' => array())));
}

function strip_tags_from_comment($input) {
    return wp_kses($input, array('br' => array(), 'p' => array()));
}

add_filter('timber/twig/filters', function ($filters) {
    $filters['urls2links'] = [
        'callable' => 'convert_urls_to_links',
    ];
    $filters['strippost'] = [
        'callable' => 'strip_tags_from_post',
    ];
    $filters['stripcomment'] = [
        'callable' => 'strip_tags_from_comment',
    ];

    return $filters;
});
