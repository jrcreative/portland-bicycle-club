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

defined('ABSPATH') or exit;

/**
 * Permission callback for abilities that read or modify per-post membership configuration.
 *
 * Always requires `manage_woocommerce_membership_plans`. Additionally requires
 * `edit_post` for the target post when a numeric ID is available — but the framework's
 * REST integration calls permission callbacks with no arguments during preload, so the
 * post-level check is best-effort and skipped when no ID can be resolved.
 *
 * @since 1.29.0
 */
trait CanCheckRestrictablePostPermissionTrait
{
	/**
	 * Returns whether the current user is allowed to read or modify per-post
	 * membership configuration.
	 *
	 * @since 1.29.0
	 *
	 * @param mixed $id post ID, or null/non-numeric when called without context
	 * @return bool
	 */
	public function checkRestrictablePostPermission($id = null): bool
	{
		if (! empty($id) && is_numeric($id) && ! current_user_can('edit_post', $id)) {
			return false;
		}

		return (bool) current_user_can('manage_woocommerce_membership_plans');
	}
}
