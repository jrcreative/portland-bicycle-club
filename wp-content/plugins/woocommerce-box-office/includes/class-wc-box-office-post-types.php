<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Box Office post types.
 *
 * @class   WC_Box_Office_Post_Types
 * @version x.x.x
 */
class WC_Box_Office_Post_Types {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Modify custom post type arguments.
		add_filter( 'event_ticket_register_args', array( $this, 'ticket_post_type_args' ), 10, 1 );
		add_filter( 'event_ticket_email_register_args', array( $this, 'ticket_email_post_type_args' ), 10, 1 );
		add_filter( 'register_event_ticket_post_type_args', array( $this, 'commerce_post_type_args' ), PHP_INT_MAX );
		add_filter( 'register_event_ticket_email_post_type_args', array( $this, 'commerce_post_type_args' ), PHP_INT_MAX );

		// Register post types.
		$this->_register_post_types();
	}

	/**
	 * Change settings for ticket post type.
	 *
	 * @param  array $args Default args
	 * @return array       Modified args
	 */
	public function ticket_post_type_args( $args = array() ) {
		$args['public']              = false;
		$args['publicly_queryable']  = false;
		$args['exclude_from_search'] = true;
		$args['rewrite']             = false;
		$args['show_in_nav_menus']   = false;
		$args['menu_position']       = 58;
		$args['menu_icon']           = 'dashicons-tickets-alt';
		$args['supports']            = array( 'title' );

		return $args;
	}

	/**
	 * Change settings for ticket email post type.
	 *
	 * @param  array $args Default args
	 * @return array       Modified args
	 */
	public function ticket_email_post_type_args( $args = array() ) {
		$args['publicly_queryable']  = false;
		$args['exclude_from_search'] = true;
		$args['show_in_menu']        = false;
		$args['show_in_nav_menus']   = false;
		$args['supports']            = array( 'title' );

		return $args;
	}

	/**
	 * Require commerce authority while preserving explicit capability overrides.
	 *
	 * @param array $args Post type arguments.
	 * @return array
	 */
	public function commerce_post_type_args( $args ) {
		$capability_type = $args['capability_type'] ?? 'post';
		if ( 'post' !== $capability_type && array( 'post', 'posts' ) !== $capability_type ) {
			return $args;
		}

		if ( ! isset( $args['map_meta_cap'] ) && empty( $args['capabilities'] ) ) {
			$args['map_meta_cap'] = true;
		}
		$args['capabilities'] = array_merge(
			array_fill_keys(
				array(
					'edit_posts',
					'edit_others_posts',
					'delete_posts',
					'publish_posts',
					'read_private_posts',
					'read',
					'delete_private_posts',
					'delete_published_posts',
					'delete_others_posts',
					'edit_private_posts',
					'edit_published_posts',
				),
				'manage_woocommerce'
			),
			isset( $args['capabilities'] ) && is_array( $args['capabilities'] ) ? $args['capabilities'] : array()
		);

		return $args;
	}

	/**
	 * Register post types.
	 *
	 * @return void
	 */
	private function _register_post_types() {
		$this->_register_post_type( 'event_ticket' );
		$this->_register_post_type( 'event_ticket_email' );
	}

	/**
	 * Wrapper function to register a new post type.
	 *
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	private function _register_post_type( $post_type = '', $plural = '', $single = '', $description = '' ) {
		if ( ! $post_type ) {
			return;
		}

		require_once( WCBO()->dir . 'includes/lib/class-wc-box-office-post-type.php' );
		$post_type = new WC_Box_Office_Post_Type( $post_type, $plural, $single, $description );

		return $post_type;
	}
}
