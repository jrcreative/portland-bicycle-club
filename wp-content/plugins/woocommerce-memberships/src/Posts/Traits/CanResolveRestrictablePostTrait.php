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

namespace SkyVerge\WooCommerce\Memberships\Posts\Traits;

use SkyVerge\WooCommerce\Memberships\Posts\Exceptions\PostNotFoundException;
use SkyVerge\WooCommerce\Memberships\Posts\Exceptions\PostNotRestrictableException;
use WC_Memberships_Admin_Membership_Plan_Rules;
use WP_Post;

defined('ABSPATH') or exit;

/**
 * Resolves a post ID into a WP_Post that supports membership content restrictions,
 * throwing typed exceptions for the lookup-fails and not-restrictable cases.
 *
 * Used by abilities that operate on per-post membership configuration.
 *
 * @since 1.29.0
 */
trait CanResolveRestrictablePostTrait
{
	/**
	 * Looks up a post by ID and verifies it is of a restrictable post type.
	 *
	 * @since 1.29.0
	 *
	 * @param int $postId
	 * @return WP_Post
	 * @throws PostNotFoundException when the post does not exist or the ID is invalid
	 * @throws PostNotRestrictableException when the post type does not support content restrictions
	 */
	protected function getRestrictablePostFromId(int $postId): WP_Post
	{
		if ($postId <= 0) {
			throw PostNotFoundException::forPostId($postId);
		}

		$post = get_post($postId);

		if (! $post instanceof WP_Post) {
			throw PostNotFoundException::forPostId($postId);
		}

		if (! $this->isRestrictablePostType($post->post_type)) {
			throw PostNotRestrictableException::forPostType($post->post_type);
		}

		return $post;
	}


	/**
	 * Returns whether the given post type is eligible for membership content restrictions.
	 *
	 * @since 1.29.0
	 *
	 * @param string $postType
	 * @return bool
	 */
	protected function isRestrictablePostType(string $postType): bool
	{
		return array_key_exists(
			$postType,
			WC_Memberships_Admin_Membership_Plan_Rules::get_valid_post_types_for_content_restriction_rules()
		);
	}
}
