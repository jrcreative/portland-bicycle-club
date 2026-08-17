<?php
/**
 * WooCommerce Memberships
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2026, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Blocks;

use SkyVerge\WooCommerce\Memberships\Restrictions;
use WC_Memberships_Admin_Membership_Plan_Rules;
use WC_Memberships_User_Messages;

defined('ABSPATH') or exit;

/**
 * Replacement for the classic "Memberships" meta box on restrictable post types
 * when the block editor is in use on WordPress 6.9+.
 *
 * @since 1.29.0
 */
class BlockEditorSidebar
{

	/** @var string minimum WordPress version required to swap the meta box for the sidebar */
	const MIN_WP_VERSION = '6.9';


	/**
	 * Hooks into WordPress to register the REST-exposed meta and load the sidebar bundle.
	 *
	 * @since 1.29.0
	 */
	public function __construct()
	{
	}

	/**
	 * Registers hooks.
	 */
	public function addHooks() : void
	{
		if (! static::isSupported()) {
			return;
		}

		add_action('init', [$this, 'registerPostMeta'], 20);
		add_action('init', [$this, 'registerCacheBustHooks'], 20);
		add_action('enqueue_block_editor_assets', [$this, 'enqueueSidebarAssets']);
		add_filter('block_editor_rest_api_preload_paths', [$this, 'preloadPostRestrictionRules'], 10, 2);
	}


	/**
	 * Checks whether the current WordPress version supports the sidebar replacement.
	 *
	 * @since 1.29.0
	 *
	 * @return bool
	 */
	public static function isSupported(): bool
	{
		return version_compare(get_bloginfo('version'), self::MIN_WP_VERSION, '>=');
	}


	/**
	 * Returns the post types that should receive the sidebar (content restriction-eligible, excluding products).
	 *
	 * @since 1.29.0
	 *
	 * @return string[]
	 */
	private function getSupportedPostTypes(): array
	{
		return array_keys(
			WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules()
		);
	}


	/**
	 * Returns the restriction-related post meta keys for a given post type.
	 *
	 * The `use_custom` and `message` keys vary by post type because they are derived
	 * from {@see \WC_Memberships_User_Messages::get_message_code_shorthand_by_post_type()}.
	 * Centralizing the derivation keeps the naming convention in one place — both for
	 * REST registration and for handing the right keys to the block-editor sidebar JS.
	 *
	 * @since 1.29.0
	 *
	 * @param string $post_type
	 * @return array{force_public:string, use_custom:string, message:string}
	 */
	protected static function getRestrictionMetaKeys(string $post_type): array
	{
		$code = WC_Memberships_User_Messages::get_message_code_shorthand_by_post_type($post_type);

		return [
			'force_public' => '_wc_memberships_force_public',
			'use_custom'   => "_wc_memberships_use_custom_{$code}_message",
			'message'      => "_wc_memberships_{$code}_message",
		];
	}


	/**
	 * Returns whether the current user can edit the restriction fields the sidebar exposes.
	 *
	 * @since 1.29.1
	 *
	 * @return bool
	 */
	protected static function currentUserCanEditRestrictions(): bool
	{
		return current_user_can('manage_woocommerce_membership_plans');
	}


	/**
	 * Registers the restriction-related post meta against each supported post type
	 * so they can be read/written via REST.
	 *
	 * Meta is only registered for users who can edit the restriction fields. Without this
	 * guard, Gutenberg would load the meta into the post entity on GET and PUT it back on
	 * every save; the `auth_callback` would then reject the write for editors/authors and
	 * surface an "Updating failed" banner even though the post body itself saved fine.
	 *
	 * @internal
	 *
	 * @since 1.29.0
	 */
	public function registerPostMeta()
	{
		if (! static::currentUserCanEditRestrictions()) {
			return;
		}

		$auth_callback = static function () {
			return static::currentUserCanEditRestrictions();
		};

		foreach ($this->getSupportedPostTypes() as $postType) {

			$keys = self::getRestrictionMetaKeys($postType);

			// map meta key to their corresponding sanitize callback
			$registrations = [
				$keys['force_public'] => [$this, 'sanitizeToYesNoString'],
				$keys['use_custom']   => [$this, 'sanitizeToYesNoString'],
				$keys['message']      => [WC_Memberships_User_Messages::class, 'sanitize_message'],
			];

			foreach ($registrations as $metaKey => $sanitizeCallback) {

				register_post_meta($postType, $metaKey, [
					'type'              => 'string',
					'single'            => true,
					'show_in_rest'      => [
						'schema' => [
							'context' => ['edit'],
						],
					],
					'sanitize_callback' => $sanitizeCallback,
					'auth_callback'     => $auth_callback,
				]);
			}
		}
	}

	/**
	 * Cache bust when the `_wc_memberships_force_public` meta changes via REST (or anywhere else)
	 * This is necessary since updates via REST don't get routed through {@see Restrictions::set_content_forced_public()}
	 * which handles its own cache busting via {@see Restrictions::update_public_content_cache()}
	 *
	 * @internal
	 *
	 * @since 1.29.1
	 */
	public function registerCacheBustHooks()
	{
		add_action('updated_post_meta', [$this, 'maybeRefreshPublicContentCache'], 10, 3);
		add_action('added_post_meta', [$this, 'maybeRefreshPublicContentCache'], 10, 3);
		add_action('deleted_post_meta', [$this, 'maybeRefreshPublicContentCache'], 10, 3);
	}


	/**
	 * Coerces the incoming value to the 'yes' / 'no' strings the rest of the plugin expects.
	 *
	 * @internal
	 *
	 * @since 1.29.0
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function sanitizeToYesNoString($value): string
	{
		if (true === $value || 'yes' === $value || '1' === $value || 1 === $value) {
			return 'yes';
		}

		return 'no';
	}


	/**
	 * Invalidates the force-public posts cache when the meta key changes.
	 *
	 * @internal
	 *
	 * @since 1.29.0
	 *
	 * @param int|array $meta_id
	 * @param int $object_id
	 * @param string $meta_key
	 */
	public function maybeRefreshPublicContentCache($meta_id, $object_id, $meta_key)
	{
		if ('_wc_memberships_force_public' !== $meta_key) {
			return;
		}

		wc_memberships()->get_restrictions_instance()->update_public_content_cache();
	}


	/**
	 * Adds the post restriction rules REST endpoint to the block editor preload list
	 * so the sidebar entity has data on first paint instead of triggering a request after mount.
	 *
	 * @internal
	 *
	 * @since 1.29.0
	 *
	 * @param string[] $paths
	 * @param \WP_Block_Editor_Context|null $context
	 * @return string[]
	 */
	public function preloadPostRestrictionRules(array $paths, $context = null): array
	{
		if (! static::currentUserCanEditRestrictions()) {
			return $paths;
		}

		$post = isset($context->post) ? $context->post : null;

		if (! $post instanceof \WP_Post) {
			return $paths;
		}

		if (! in_array($post->post_type, $this->getSupportedPostTypes(), true)) {
			return $paths;
		}

		$paths[] = sprintf('/wc-memberships/v1/post-restriction-rules/%d', $post->ID);

		return $paths;
	}


	/**
	 * Ensures the sidebar bundle is loaded in the block editor for supported post types.
	 *
	 * The sidebar JS ships inside the existing `wc-memberships-blocks` bundle; the blocks
	 * script is already registered by {@see \SkyVerge\WooCommerce\Memberships\Blocks} but
	 * only enqueued as a block `editor_script`. On post types that don't define one of our
	 * blocks we still need the sidebar to load, so enqueue it explicitly here.
	 *
	 * @internal
	 *
	 * @since 1.29.0
	 */
	public function enqueueSidebarAssets()
	{
		if (! static::currentUserCanEditRestrictions()) {
			return;
		}

		$screen = function_exists('get_current_screen') ? get_current_screen() : null;

		if (! $screen || ! in_array($screen->post_type, $this->getSupportedPostTypes(), true)) {
			return;
		}

		if (! wp_script_is('wc-memberships-blocks', 'registered')) {
			return;
		}

		wp_enqueue_script('wc-memberships-blocks');

		// Hand the resolved meta keys to JS so it doesn't need to mirror the
		// post-type → message-code mapping (or miss the related PHP filter).
		wp_localize_script('wc-memberships-blocks', 'wc_memberships_blocks_sidebar', [
			'showSidebar' => true,
			'metaKeys'    => static::getRestrictionMetaKeys($screen->post_type),
		]);

		if (wp_style_is('wc-memberships-blocks-editor', 'registered')) {
			wp_enqueue_style('wc-memberships-blocks-editor');
		}
	}


}
