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

namespace SkyVerge\WooCommerce\Memberships\Rules\Adapters;

defined('ABSPATH') or exit;

/**
 * Adapts access schedule data between formats. Pure data transforms — no side effects on rule objects.
 *
 * The canonical in-memory shape is:
 *  - `['type' => 'immediate']`
 *  - `['type' => 'delayed', 'amount' => int, 'period' => 'days'|'weeks'|'months'|'years']`
 *
 * @since 1.29.0
 */
class AccessScheduleNormalizer
{
	/**
	 * Converts a canonical access schedule shape into the string representation
	 * expected by {@see \WC_Memberships_Membership_Plan_Rule::set_access_schedule()}.
	 *
	 * Branches on `type` only — extra keys (`amount`, `period`) are ignored when
	 * type is "immediate", regardless of whether they were present in the input.
	 *
	 * @since 1.29.0
	 *
	 * @param array{type: string, amount?: int, period?: string} $schedule
	 * @return string e.g. `"immediate"` or `"30 days"`
	 */
	public static function toAccessScheduleString(array $schedule): string
	{
		if ('immediate' === ($schedule['type'] ?? 'immediate')) {
			return 'immediate';
		}

		return sprintf('%d %s', (int) ($schedule['amount'] ?? 0), $schedule['period'] ?? '');
	}
}
