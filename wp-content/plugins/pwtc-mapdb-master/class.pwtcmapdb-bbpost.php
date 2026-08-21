<?php

class PwtcMapdb_BBPost {

    private static $initiated = false;

    public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

    private static function init_hooks() {
        self::$initiated = true;
		add_action('wp_enqueue_scripts', array('PwtcMapdb_BBPost', 'load_javascripts'));

		if ('yes' === get_option('pwtc_mapdb_modified_time_update', 'no')) {
			add_action('comment_post', array('PwtcMapdb_BBPost', 'comment_update_post_modified_time_callback1'), 10, 2);
			add_action('transition_comment_status', array('PwtcMapdb_BBPost', 'comment_update_post_modified_time_callback2'), 10, 3);
		}
		if ('yes' === get_option('pwtc_mapdb_send_post_submit_email', 'no')) {
			add_action('comment_post', array('PwtcMapdb_BBPost', 'comment_send_submission_email_callback'), 10, 2);
		}
		if ('yes' === get_option('pwtc_mapdb_restrict_feed_output', 'no')) {
			add_action('pre_get_posts', array('PwtcMapdb_BBPost', 'pre_get_feed_posts_callback'));
			add_action('do_feed_rdf', array('PwtcMapdb_BBPost', 'protect_comments_feed_callback'), 0);
			add_action('do_feed_rss', array('PwtcMapdb_BBPost', 'protect_comments_feed_callback'), 0);
			add_action('do_feed_rss2', array('PwtcMapdb_BBPost', 'protect_comments_feed_callback'), 0);
			add_action('do_feed_atom', array('PwtcMapdb_BBPost', 'protect_comments_feed_callback'), 0);
		}
		add_action('template_redirect', array('PwtcMapdb_BBPost', 'disallow_loading_posts_callback'));

		add_filter('heartbeat_received', array('PwtcMapdb_BBPost', 'refresh_post_lock'), 10, 3);
		add_filter('pwtc_header_indicator_icons', array('PwtcMapdb_BBPost', 'header_indicator_icons_callback'));
		add_filter('pwtc_category_exclude_list', array('PwtcMapdb_BBPost', 'category_exclude_list_callback'));
		add_filter('pwtc_recent_posts_after', array('PwtcMapdb_BBPost', 'recent_posts_after_callback'));
		add_filter('pwtc_category_button_links', array('PwtcMapdb_BBPost', 'category_button_links_callback'));
		add_filter('pwtc_allow_post_comments', array('PwtcMapdb_BBPost', 'allow_post_comments_callback'));

		if ('yes' === get_option('pwtc_mapdb_force_comment_moderation', 'no')) {
			add_filter('pre_comment_approved', array('PwtcMapdb_BBPost', 'force_comment_moderation_callback'), 9999);
		}

        add_shortcode('pwtc_mapdb_edit_bbpost', array('PwtcMapdb_BBPost', 'shortcode_edit_bbpost'));
		add_shortcode('pwtc_mapdb_delete_bbpost', array( 'PwtcMapdb_BBPost', 'shortcode_delete_bbpost'));
		add_shortcode('pwtc_mapdb_new_bbpost_link', array('PwtcMapdb_BBPost', 'shortcode_new_bbpost_link'));
		add_shortcode('pwtc_mapdb_manage_bbposts', array('PwtcMapdb_BBPost', 'shortcode_manage_bbposts'));
    }

	public static function load_javascripts() {
		$edit_bbpost_path = get_option('pwtc_mapdb_edit_forum_post_path', '/');
		$submit_bbpost_path = get_option('pwtc_mapdb_submit_forum_post_path', '/');
		$delete_bbpost_path = get_option('pwtc_mapdb_delete_forum_post_path', '/');
		$link = get_the_permalink();
		if ($link and (strpos($link, $delete_bbpost_path)!==false or strpos($link, $edit_bbpost_path)!==false or strpos($link, $submit_bbpost_path)!==false)) {
			wp_enqueue_script('heartbeat');
		}
	}

	public static function refresh_post_lock($response, $data, $screen_id) {
		if ( array_key_exists( 'pwtc-refresh-post-lock', $data ) ) {
			$received = $data['pwtc-refresh-post-lock'];
			$send     = array();
	
			$post_id = absint( $received['post_id'] );
			if ( ! $post_id ) {
				return $response;
			}
		
			$user_id = self::check_post_lock( $post_id );
			$user    = get_userdata( $user_id );
			if ( $user ) {
				$name = $user->first_name . ' ' . $user->last_name;
				$error = array(
					'text' => sprintf('%s has taken over and is currently editing.', $name ),
				);
				$send['lock_error'] = $error;
			} 
			else {
				$new_lock = self::set_post_lock( $post_id );
				if ( $new_lock ) {
					$send['new_lock'] = implode( ':', $new_lock );
				}
			}
	
			$response['pwtc-refresh-post-lock'] = $send;
		}	

		return $response;
	}

	public static function pre_get_feed_posts_callback($query) {
    	if ($query->is_feed() && !is_admin()) {
        	$exclude_categories = apply_filters('pwtc_category_exclude_list', []);
        	if (!empty($exclude_categories)) {
				$cat_query = $query->get('category__not_in');
				if (!is_array($cat_query) || empty($cat_query) ) {
            		$query->set('category__not_in', $exclude_categories);
				}
				else {
					$query->set('category__not_in', array_merge($cat_query, $exclude_categories));
				}
        	}
		}
	}

	public static function protect_comments_feed_callback($is_comment_feed) {
    	if (!is_user_logged_in() and $is_comment_feed) {
       		wp_die('You must be logged in to access this comment feed.', 'Feed Protected', array('response' => 403));
    	}
	}

	public static function disallow_loading_posts_callback() {
		if (is_single() and get_post_type() == "post") { 
			$exclude_categories = apply_filters('pwtc_category_exclude_list', []);
        	if (!empty($exclude_categories)) {
				$categories = get_the_category();
				if (!empty($categories)) {
					foreach( $categories as $category ) {
						if (in_array($category->term_id, $exclude_categories)) {
							wp_die('You are not allowed access to this post.', 403);
						}
					}

				}
			}
		}
	}

	public static function header_indicator_icons_callback($output) {
		$cat_ids = self::get_topic_category_ids(get_option('pwtc_mapdb_topics_parent_category_name', ''));
		$cat_ids = array_merge($cat_ids, self::get_topic_category_ids(get_option('pwtc_mapdb_admin_topics_parent_category_name', '')));
		$after = get_option('pwtc_mapdb_recent_post_time_cutoff', '1 day ago');
		$style = 'color: white;';
		$query_args = [
			'nopaging' => true,
			'cache_results' => false,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'fields' => 'ids',
			'posts_per_page' => -1,
			'category__in' => $cat_ids,
			'date_query' => [
				'relation' => 'OR',
				[
                    'column' => 'post_date_gmt',
                    'after' => $after,
				],
				[
                    'column' => 'post_modified_gmt',
                    'after' => $after,
				],
			],
		];
		$results = new WP_Query($query_args);
		if ($results->found_posts > 0) {
			$title = '' . $results->found_posts . ' member posts added or modified since ' . $after . '.';
			$output .= '<span style="' . $style . '" class="label primary" title="' . $title . '"><i class="fa fa-sticky-note"></i> ' . $results->found_posts . '</span>';	
		}
		return $output;
	}

	public static function category_exclude_list_callback($exclude_list) {
		$current_user = wp_get_current_user();
		if (0 === $current_user->ID) {
			$exclude_list = array_merge($exclude_list, 
				self::get_topic_category_ids(get_option('pwtc_mapdb_topics_parent_category_name', '')));
			$exclude_list = array_merge($exclude_list, 
				self::get_topic_category_ids(get_option('pwtc_mapdb_admin_topics_parent_category_name', '')));
		}
		return $exclude_list;
	}

	public static function recent_posts_after_callback($after) {
		return get_option('pwtc_mapdb_recent_post_time_cutoff', '1 day ago');
	}

	public static function category_button_links_callback($output) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user->ID ) {
			$admin = self::get_topic_button_links(get_option('pwtc_mapdb_admin_topics_parent_category_name', ''));
			if (!empty($admin)) {
				$output .= $admin;
				$output .= '<span style="padding: 0px 10px;"></span>';
			}
			$member = self::get_topic_button_links(get_option('pwtc_mapdb_topics_parent_category_name', ''));
			if (!empty($member)) {
				$output .= $member;
				$output .= '<span style="padding: 0px 10px;"></span>';
			}
			$public = self::get_topic_button_links(get_option('pwtc_mapdb_public_topics_parent_category_name', ''));
			$output .= $public;
		}					
		return $output;
	}

	public static function allow_post_comments_callback($allowed) {
		$current_user = wp_get_current_user();
		if ( 0 !== $current_user->ID ) {	
			$user_info = get_userdata($current_user->ID);
			if ($user_info) {
				$allowed = in_array('current_member', $user_info->roles);
			}
		}	
		return $allowed;
	}

	public static function comment_update_post_modified_time_callback1($comment_id, $comment_approved) {
		if ( !$comment_approved ) {
			return;
		}
		$comment = get_comment( $comment_id );
		$post_id = $comment->comment_post_ID;
		if ($post_id === 0) {
			return;
		}
		wp_update_post([ 'ID' => $post_id ]);
	}

	public static function comment_update_post_modified_time_callback2($new_status, $old_status, $comment) {
		if ( $new_status !== 'approved' ) {
			return;
		}
		$post_id = $comment->comment_post_ID;
		if ($post_id === 0) {
			return;
		}
		wp_update_post([ 'ID' => $post_id ]);
	}

	public static function comment_send_submission_email_callback($comment_id, $comment_approved) {
		if ( $comment_approved ) {
			return;
		}
		$comment = get_comment( $comment_id );
		$post_id = $comment->comment_post_ID;
		$author_id = $comment->user_id;
		if ($post_id === 0) {
			return;
		}
		/*
		$posts = get_posts([
			'ID' => $post_id,
			'category__in' => self::get_topic_category_ids(),
		]);
		if (empty($posts)) {
			return;
		}
		*/
		$moderator_email = get_option('pwtc_mapdb_post_moderator_email', 'webmaster@portlandbicyclingclub.com');
		self::comment_submitted_email($post_id, $author_id, $moderator_email);
	}

	public static function force_comment_moderation_callback($approved) {
		if ($approved === 1) {
			$approved = 0;
		}
		return $approved;
	}

    // Generates the [pwtc_mapdb_edit_bbpost] shortcode.
	public static function shortcode_edit_bbpost($atts, $content) {
		$a = shortcode_atts(array('use_return' => 'no'), $atts);
		$use_return = $a['use_return'] == 'yes';

		$allow_email = ('yes' === get_option('pwtc_mapdb_send_post_submit_email', 'no'));
		$moderator_email = get_option('pwtc_mapdb_post_moderator_email', 'webmaster@portlandbicyclingclub.com');
		$max_title_len = get_option('pwtc_mapdb_post_title_max_len', 0);
		$title_disallowed_chars = get_option('pwtc_mapdb_post_title_disallowed_chars', '');
		if (!empty($title_disallowed_chars)) {
			$ascii = explode(' ', $title_disallowed_chars);
			$title_disallowed_chars = '[';
			foreach ( $ascii as $code ) {
				$title_disallowed_chars .= '\\x' . $code;
			}
			$title_disallowed_chars .= ']';
		}

        $is_moderator = false;
		$is_active_member = false;
    	$current_user = wp_get_current_user();
		$user_info = get_userdata($current_user->ID);
		if ($user_info) {
			$is_active_member = in_array('current_member', $user_info->roles);
		}

        $return = '';
		if (isset($_GET['return'])) {
			$return = $_GET['return'];
		}

        if (isset($_POST['postid']) and isset($_POST['revert']) and $current_user->ID != 0) {
			if (!isset($_POST['nonce_field']) or !wp_verify_nonce($_POST['nonce_field'], 'bbpost-edit-form')) {
				wp_nonce_ays('');
			}

			$postid = intval($_POST['postid']);
			
			$post_status = '';
			if (isset($_POST['post_status'])) {
				$post_status = $_POST['post_status'];
			}

			$my_post = array(
				'ID' => $postid,
				'post_status' => 'draft'
			);
			$status = wp_update_post($my_post);
			if ($status != $postid) {
				wp_die('Failed to update this post.', 403);
			}
			
			$email = 'no';
			if ($allow_email) {
				if ($post_status == 'pending' and !$is_moderator) {
					$email = self::bbpost_unsubmitted_email($postid, $moderator_email) ? 'yes': 'failed';
				}
			}

			wp_redirect(add_query_arg(array(
				'post' => $postid,
				'return' => urlencode($return),
				'op' => 'revert_draft',
				'email' => $email
			), get_permalink()), 303);
            
			exit;
		}
		else if (isset($_POST['postid']) and isset($_POST['preview']) and $current_user->ID != 0) {
			if (!isset($_POST['nonce_field']) or !wp_verify_nonce($_POST['nonce_field'], 'bbpost-edit-form')) {
				wp_nonce_ays('');
			}
						
			$postid = intval($_POST['postid']);

			wp_redirect(add_query_arg(array(
				'post' => $postid,
				'return' => urlencode($return),
				'preview' => 'yes'
			), get_permalink()), 303);
            
			exit;
		}
		else if (isset($_POST['postid']) and isset($_POST['edit']) and $current_user->ID != 0) {
			if (!isset($_POST['nonce_field']) or !wp_verify_nonce($_POST['nonce_field'], 'bbpost-edit-form')) {
				wp_nonce_ays('');
			}
						
			$postid = intval($_POST['postid']);

			wp_redirect(add_query_arg(array(
				'post' => $postid,
				'return' => urlencode($return)
			), get_permalink()), 303);
            
			exit;
		}
        else if (isset($_POST['postid']) and isset($_POST['title']) and $current_user->ID != 0) {
			if (!isset($_POST['nonce_field']) or !wp_verify_nonce($_POST['nonce_field'], 'bbpost-edit-form')) {
				wp_nonce_ays('');
			}

            $operation = '';
			$postid = intval($_POST['postid']);
			$title = trim($_POST['title']);
			$post_status = '';
			if (isset($_POST['post_status'])) {
				$post_status = $_POST['post_status'];
			}

			if ($postid != 0) {
				$my_post = array(
					'ID' => $postid,
					'post_title' => $title
				);
				if (isset($_POST['content'])) {
					$my_post['post_content'] = $_POST['content'];
				}
				if (isset($_POST['draft'])) {
					$my_post['post_status'] = 'draft';
					if ($post_status == 'pending') {
						$operation = 'rejected';
					}
					else if ($post_status == 'publish') {
						$operation = 'unpublished';
					}
					else {
						$operation = 'update_draft';
					}
				}
				else if (isset($_POST['pending'])) {
					$my_post['post_status'] = 'pending';
					if ($post_status == 'draft') {
						$operation = 'submit_review';
					}
					else {
						$operation = 'update_pending';
					}
				}
				else if (isset($_POST['publish'])) {
					$my_post['post_status'] = 'publish';
					if ($post_status == 'draft') {
						$operation = 'published_draft';
					}
					else if ($post_status == 'pending') {
						$operation = 'published';
					}
					else {
						$operation = 'update_published';
					}
				}
				//error_log(print_r($my_post, true));
				$status = wp_update_post( $my_post );	
				if ($status != $postid) {
					wp_die('Failed to update this post.', 403);
				}
				update_post_meta($postid, '_edit_last', $current_user->ID);
			}
			else {
				$my_post = array(
					'post_title'    => $title,
					'post_type'     => 'post',
                    'post_status'   => 'draft',
                    'post_author'   => $current_user->ID
				);
				if (isset($_POST['content'])) {
					$my_post['post_content'] = $_POST['content'];
				}
				$operation = 'insert';
				$postid = wp_insert_post( $my_post );
				if ($postid == 0) {
					wp_die('Failed to create a new post.', 403);
				}
			}

			if (isset($_POST['categories'])) {
				wp_set_post_categories($postid, $_POST['categories']);
			}

			update_field('field_display_post_author', true, $postid);
			update_field('field_filter_post_content', true, $postid);

            $email = 'no';
			if ($allow_email) {
				if ($operation == 'submit_review' and !$is_moderator) {
					$email = self::bbpost_submitted_email($postid, $moderator_email) ? 'yes': 'failed';
				}
			}
			
			wp_redirect(add_query_arg(array(
				'post' => $postid,
				'return' => urlencode($return),
				'op' => $operation,
				'email' => $email
			), get_permalink()), 303);

			exit;
		}

		$email_status = 'no';
		if (isset($_GET['email'])) {
			$email_status = $_GET['email'];
		}

		$bbpost_link = '';
		$return_to_bbpost = '';
		if (!empty($return) and $use_return) {
			$bbpost_link = esc_url($return);
			$return_to_bbpost = self::create_return_link($bbpost_link);
		}

		if (isset($_GET['post'])) {
			$error = self::check_post_id();
			if (!empty($error)) {
				return $return_to_bbpost . $error;
			}
			$postid = intval($_GET['post']);
		}
		else {
			$postid = 0;
		}

        if (0 == $current_user->ID) {
			return $return_to_bbpost . '<div class="callout small alert"><p>You must be logged in to submit forum posts.</p></div>';
		}

		if (!$is_active_member) {
			return $return_to_bbpost . '<div class="callout small warning"><p>You must have an active membership to submit forum posts.</p></div>';
		}

		if ($postid != 0) {
			$post = get_post($postid);
			if ($max_title_len > 0) {
				$title = substr($post->post_title, 0, $max_title_len);
			} 
			else {
            	$title = $post->post_title;
			}
            $author = $post->post_author;
            $status = $post->post_status;
			$content = $post->post_content;
		}
		else {
            $title = '';
            $author = $current_user->ID;
            $status = 'draft';
			$content = '';
		}

		$author_name = '';
		$author_email = '';
		if ($author != 0) {
			$info = get_userdata($author);
			if ($info) {
				$author_name = $info->first_name . ' ' . $info->last_name;
				$author_email = $info->user_email;
			}
			else {
				$author_name = 'Unknown';
				$author_email = '';
			}
		}

		$bbpost_title = '';
		if ($postid != 0) {
			$bbpost_title = esc_html(get_the_title($postid));
		}

		if ($postid != 0) {
			$filter_post_content = get_field('filter_post_content', $postid);
			if (!$filter_post_content) {
				return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" must use limited HTML markup in order to edit.</p></div>';
			}
			if ($author != $current_user->ID) {
				return $return_to_bbpost . '<div class="callout small warning"><p>You must be the author of forum post "' . $bbpost_title . '" to edit it.</p></div>';
			}
			$lock_user = self::check_post_lock($postid);
		    if ($lock_user) {
				$info = get_userdata($lock_user);
				$name = $info->first_name . ' ' . $info->last_name;	
				return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" is currently being edited by ' . $name . '. </p></div>';
			}
		}

		if ($postid != 0) {
			$post_cats = wp_get_post_categories($postid);
		}
		else {
			$post_cats = [];
		}

		if ($is_moderator) {
			$categories = self::get_category_select_tree($post_cats);
		}
		else {
			$categories = self::get_topic_choices(get_option('pwtc_mapdb_topics_parent_category_name', ''));
		}

		if ($postid != 0) {
			if (isset($_GET['preview'])) {
				$allowed_html_tags = [
                	'a' => [
                	    'href' => array(),
					],
                	'br' => [],
                	'em' => [],
                	'strong' => [],
                	'p' => [],
				];
				ob_start();
				include('bbpost-preview-form.php');
				return ob_get_clean();
			}
			if ($status == 'publish') {
				return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" is published so you cannot edit it.</p></div>';
			}
			else if ($status == 'pending') {
				ob_start();
				include('bbpost-pending-form.php');
				return ob_get_clean();
			}
		}

		$operation = '';
		if (isset($_GET['op'])) {
			$operation = $_GET['op'];
		}

		if ($postid != 0) {
			self::set_post_lock($postid);
		}
		
		$edit_link = '';
		$view_link = '';
		if ($postid != 0) {
			$edit_link = add_query_arg(array(
				'post' => $postid
			), get_permalink());
			$view_link = get_permalink($postid);
		}

        ob_start();
        include('bbpost-edit-form.php');
        return ob_get_clean();
	}

	// Generates the [pwtc_mapdb_delete_bbpost] shortcode.
	public static function shortcode_delete_bbpost($atts) {
		$a = shortcode_atts(array('use_return' => 'no'), $atts);
		$use_return = $a['use_return'] == 'yes';

		$is_moderator = false;
		$current_user = wp_get_current_user();
		$user_info = get_userdata($current_user->ID);
		if ($user_info) {
			$is_active_member = in_array('current_member', $user_info->roles);
		}
		
		$return = '';
		if (isset($_GET['return'])) {
			$return = $_GET['return'];
		}

		if (isset($_POST['postid'])) {
			if (!isset($_POST['nonce_field']) or !wp_verify_nonce($_POST['nonce_field'], 'bbpost-delete-form')) {
				wp_nonce_ays('');
			}
			
			if (!$is_active_member) {
				wp_die('Authorization failed.', 403);
			}
			
			$postid = intval($_POST['postid']);
			if (isset($_POST['delete_bbpost'])) {
				if (wp_trash_post($postid)) {
					wp_redirect(add_query_arg(array(
						'post' => $postid,
						'return' => urlencode($return)
					), get_permalink()), 303);
				}
				else {
					wp_die('Failed to delete this post.', 403);
				}
			}
			else if (isset($_POST['undo_delete'])) {
				if (wp_untrash_post($postid)) {
					wp_redirect(add_query_arg(array(
						'post' => $postid,
						'return' => urlencode($return)
					), get_permalink()), 303);
				}
				else {
					wp_die('Failed to undo the delete of this post.', 403);
				}
			}
			exit;
		}

		$bbpost_link = '';
		$return_to_bbpost = '';
		if (!empty($return) and $use_return) {
			$bbpost_link = esc_url($return);
			$return_to_bbpost = self::create_return_link($bbpost_link);
		}
		
		$error = self::check_post_id(true);
		if (!empty($error)) {
			return $return_to_bbpost . $error;
		}
		$postid = intval($_GET['post']);
		
		if (0 == $current_user->ID) {
			return $return_to_bbpost . '<div class="callout small alert"><p>You must be logged in to delete forum posts.</p></div>';
		}

		$bbpost_title = esc_html(get_the_title($postid));

		$post = get_post($postid);
		$author = $post->post_author;
		$status = $post->post_status;

		if (!$is_active_member) {
			return $return_to_bbpost . '<div class="callout small warning"><p>You must have an active membership to delete forum posts.</p></div>';
		}

		if ($status == 'publish') {
			return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" is published so you cannot delete it.</p></div>';
		}
		else if ($status == 'pending') {
			return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" is pending review so you cannot delete it.</p></div>';
		}

		if ($author != $current_user->ID) {
			return $return_to_bbpost . '<div class="callout small warning"><p>You must be the author of forum post "' . $bbpost_title . '" to delete it.</p></div>';
		}

		$deleted = false;
		if ($status != 'trash') {
			$lock_user = self::check_post_lock($postid);
			if ($lock_user) {
				$info = get_userdata($lock_user);
				$name = $info->first_name . ' ' . $info->last_name;	
				return $return_to_bbpost . '<div class="callout small warning"><p>Forum post "' . $bbpost_title . '" is currently being edited by ' . $name . '.</p></div>';
			}
			self::set_post_lock($postid);
		}
		else {
			$deleted = true;
		}

        ob_start();
        include('bbpost-delete-form.php');
        return ob_get_clean();
	}

	// Generates the [pwtc_mapdb_new_bbpost_link] shortcode.
	public static function shortcode_new_bbpost_link($atts, $content) {
		$a = shortcode_atts(array('class' => ''), $atts);
		
		$return_uri = $_SERVER['REQUEST_URI'];

		if (empty($content)) {
				$content = 'new forum post';
		}
		$new_link = self::new_bbpost_link($return_uri);
		
		return '<a class="' . $a['class'] . '" href="' . $new_link . '">' . $content . '</a>';
	}

	// Generates the [pwtc_mapdb_manage_bbposts] shortcode.
	public static function shortcode_manage_bbposts($atts) {
		$current_user = wp_get_current_user();

		if ( 0 == $current_user->ID ) {
			return '<div class="callout small warning"><p>Please <a href="/wp-login.php">log in</a> to view the forum posts that you have created.</p></div>';
		}

		$user_info = get_userdata($current_user->ID);
		
		$is_active_member = in_array('current_member', $user_info->roles);

		if (!$is_active_member) {
			return '<div class="callout small warning"><p>You must have an active membership to view the forum posts that you have created.</p></div>';
		}

		$author_name = $user_info->first_name . ' ' . $user_info->last_name;

		$status = array('draft', 'pending');
		$query_args = [
			'posts_per_page' => -1,
			'post_status' => $status,
			'author' => $current_user->ID,
			'post_type' => 'post',
		];
		$query = new WP_Query($query_args);

		$return_uri = $_SERVER['REQUEST_URI'];

		ob_start();
		include('manage-bbposts-form.php');
		return ob_get_clean();
	}	

	public static function get_topic_choices($parent_category_name) {
		$categories = [];
		$topics_id = get_cat_ID($parent_category_name);
		if ($topics_id > 0) {
			$categories = get_categories([ 
				'hide_empty' => false, 
				'orderby' => 'name',
				'order' => 'ASC',
				'parent' => $topics_id,
			]);
		}
		return $categories;
	}

	public static function get_category_select_tree($post_cats, $category=null, $output='') {
		$categories = get_categories([ 
			'hide_empty' => false, 
			'orderby' => 'name',
			'order' => 'ASC',
			'parent' => ($category !== null ? $category->term_id : 0),
		]);
		if (empty($categories)) {
			if ($category !== null) {
				$tooltip = '';
				if (!empty($category->description)) {
					$tooltip = ' data-tooltip title="' . esc_attr($category->description) . '"';
				}
				$output .= '<li><input type="radio" name="categories[]" value="' . $category->term_id . '" id="' . 
					$category->slug . '" ' . (in_array($category->term_id, $post_cats) ? 'checked': '') . 
					'><label' . $tooltip . ' for="' . $category->slug . '">' . $category->name . '</label></li>';
			}
		}
		else {
			if ($category !== null) {
				$output .= '<li>' . $category->name . '<ul style="list-style: none;">';
			}
			foreach ($categories as $cat) {
				$output = self::get_category_select_tree($post_cats, $cat, $output);
			}	
			if ($category !== null) {
				$output .= '</ul></li>';
			}
		}
		return $output;
	}

	public static function get_topic_button_links($parent_category_name) {
		$output = '';
		$topics_id = get_cat_ID($parent_category_name);
		if ($topics_id > 0) {
			$categories = get_categories([ 
				'hide_empty' => true, 
				'orderby' => 'name',
				'order' => 'ASC',
				'parent' => $topics_id,
			]);
			if (!empty($categories)) {
				foreach($categories as $category) {
					$category_link = sprintf('<a class="button" href="%1$s" title="%2$s">%3$s</a>',
						esc_url( get_category_link( $category->term_id ) ),
						esc_attr( sprintf('View all posts under topic %s', $category->name ) ),
						esc_html( $category->name )
					);
					$output .= $category_link;
				}
			}
		}
		return $output;
	}

	public static function get_topic_category_ids($parent_category_name) {
		$output = [];
		$topics_id = get_cat_ID($parent_category_name);
		if ($topics_id > 0) {
			$categories = get_categories([ 
				'hide_empty' => false, 
				'orderby' => 'name',
				'order' => 'ASC',
				'parent' => $topics_id,
			]);
			if (!empty($categories)) {
				foreach($categories as $category) {
					$output[] = $category->term_id;
				}
			}
		}
		return $output;
	}

	public static function bbpost_submitted_email($postid, $moderator_email) {
		$post_title = esc_html(get_the_title($postid));
		$post_url = get_permalink($postid);
		$author_id = get_post_field('post_author', $postid);
		$author_email = get_the_author_meta('user_email', $author_id);
		$post_author = get_the_author_meta('first_name', $author_id) . ' ' . get_the_author_meta('last_name', $author_id);
		$author_link = '<a href="' . esc_url('mailto:'.$author_email) . '">' . $post_author . '</a>';
		$post_link = '<a href="' . esc_url($post_url) . '">' . $post_title . '</a>';
		$subject = 'PBC Post Submitted for Review';
		$message = <<<EOT
The Portland Bicycling Club post $post_link has been submitted by $author_link for moderator review. 
To review this post, use a browser to log in to your club account and open the post by clicking its link. 
Once the post opens, you can edit it (you must have moderator rights) by clicking the <em>Edit Post</em> link at the top of the page. 
After the editor opens, you may make any changes that you see fit and publish the post or reject (return it to draft). 
Do not reply to this email!
EOT;
		$headers = ['Content-type: text/html'];
		return wp_mail($moderator_email, $subject , $message, $headers);
	}

	public static function comment_submitted_email($postid, $author_id, $moderator_email) {
		$post_title = esc_html(get_the_title($postid));
		$author_email = get_the_author_meta('user_email', $author_id);
		$author_name = get_the_author_meta('first_name', $author_id) . ' ' . get_the_author_meta('last_name', $author_id);
		$author_link = '<a href="' . esc_url('mailto:'.$author_email) . '">' . $author_name . '</a>';
		$post_url = get_permalink($postid);
		$post_link = '<a href="' . esc_url($post_url) . '">' . $post_title . '</a>';
		$subject = 'Comment to PBC Post Submitted for Review';
		$message = <<<EOT
A comment to the Portland Bicycling Club post $post_link has been submitted by $author_link for moderator review.
To review this comment, use a browser to log in to your club account and open the post by clicking its link. 
Once the post opens, you can edit it (you must have moderator rights) by clicking the <em>Edit Post</em> link at the top of the page.
After the editor opens, scroll down to the <em>Comments</em> section; here you can review and approve the comment.
Do not reply to this email!
EOT;
		$headers = ['Content-type: text/html'];
		return wp_mail($moderator_email, $subject , $message, $headers);
	}
	
	public static function bbpost_unsubmitted_email($postid, $moderator_email) {
		$post_title = esc_html(get_the_title($postid));
		$post_url = get_permalink($postid);
		$author_id = get_post_field('post_author', $postid);
		$author_email = get_the_author_meta('user_email', $author_id);
		$post_author = get_the_author_meta('first_name', $author_id) . ' ' . get_the_author_meta('last_name', $author_id);
		$author_link = '<a href="' . esc_url('mailto:'.$author_email) . '">' . $post_author . '</a>';
		$post_link = '<a href="' . esc_url($post_url) . '">' . $post_title . '</a>';
		$subject = 'PBC Post Unsubmitted';
		$message = <<<EOT
The author $author_link has reverted the Portland Bicycling Club post $post_link back to draft. 
Ignore the previous review request email and do not review this post. 
Do not reply to this email!
EOT;
		$headers = ['Content-type: text/html'];
		return wp_mail($moderator_email, $subject , $message, $headers);
	}

	public static function delete_bbpost_link($postid, $return=false) {
		$uri = get_option('pwtc_mapdb_delete_forum_post_path', '/');
		$uri .= '?post=' . $postid;
		if ($return) {
			$uri .= '&return=' . urlencode($return);
		}
		return esc_url($uri);
	}

	public static function new_bbpost_link($return=false) {
		$uri = get_option('pwtc_mapdb_submit_forum_post_path', '/');
		if ($return) {
			$uri .= '?return=' . urlencode($return);
		}
		return esc_url($uri);
	}

	public static function edit_bbpost_link($postid, $return=false) {
		$uri = get_option('pwtc_mapdb_edit_forum_post_path', '/');
		$uri .= '?post=' . $postid;
		if ($return) {
			$uri .= '&return=' . urlencode($return);
		}
		return esc_url($uri);
	}

	public static function create_return_link($bbpost_url) {
		return '<ul class="breadcrumbs"><li><a href="' . $bbpost_url . '">Back to Previous Page</a></li></ul>';
	}

	public static function check_post_id($ignore_trash = false) {
		if (!isset($_GET['post'])) {
			return '<div class="callout small alert"><p>Post ID parameter is missing.</p></div>';
		}

		$postid = intval($_GET['post']);
		if ($postid == 0) {
			return '<div class="callout small alert"><p>Post ID parameter is invalid, it must be an integer number.</p></div>';
		}

		$post = get_post($postid);
		if (!$post) {
			return '<div class="callout small alert"><p>Post ' . $postid . ' does not exist, it may have been deleted.</p></div>';
		}

		if (get_post_type($post) != 'post') {
			return '<div class="callout small alert"><p>Post ' . $postid . ' is not a forum post.</p></div>';
		}

		$post_status = get_post_status($post);

		if ($post_status != 'publish' and $post_status != 'draft' and $post_status != 'pending') {
			if ($post_status == 'trash') {
				if (!$ignore_trash) {
					return '<div class="callout small alert"><p>Forum post ' . $postid . ' has been deleted.</p></div>';
				}
			}
			else {
				return '<div class="callout small alert"><p>Forum post ' . $postid . ' is not draft, pending or published. Its current status is "' . $post_status . '"</p></div>';
			}
		}

		return '';
	}

	public static function check_post_lock( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
	 
		$lock = get_post_meta( $post->ID, '_edit_lock', true );
		if ( ! $lock ) {
			return false;
		}
	 
		$lock = explode( ':', $lock );
		$time = $lock[0];
		$user = isset( $lock[1] ) ? $lock[1] : get_post_meta( $post->ID, '_edit_last', true );
	 
		if ( ! get_userdata( $user ) ) {
			return false;
		}
	 
		$time_window = 150;
		if ( $time && $time > time() - $time_window && get_current_user_id() != $user ) {
			return $user;
		}
	 
		return false;
	}

	public static function set_post_lock( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
	 
		$user_id = get_current_user_id();
		if ( 0 == $user_id ) {
			return false;
		}
	 
		$now  = time();
		$lock = "$now:$user_id";
	 
		update_post_meta( $post->ID, '_edit_lock', $lock );
	 
		return array( $now, $user_id );
	}

}
