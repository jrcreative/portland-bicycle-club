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

namespace SkyVerge\WooCommerce\Memberships\Contracts;

use WP_Error;

defined('ABSPATH') or exit;

/**
 * Implemented by domain exceptions that can be safely converted to a WP_Error
 * for return from REST/ability execute callbacks.
 *
 * Allows ability classes to catch exception types polymorphically and translate
 * to the standard WP_Error response shape without duplicating per-exception
 * mapping logic at every call site.
 *
 * @since 1.29.0
 */
interface ConvertToWpErrorContract
{
	/**
	 * Returns a WP_Error representation of this exception.
	 *
	 * Implementations should populate the WP_Error data array with at least a
	 * `status` key reflecting the HTTP status to return.
	 *
	 * @since 1.29.0
	 *
	 * @return WP_Error
	 */
	public function toWpError(): WP_Error;
}
