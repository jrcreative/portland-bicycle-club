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

namespace SkyVerge\WooCommerce\Memberships\Rules\Validators;

use InvalidArgumentException;

defined('ABSPATH') or exit;

/**
 * Validates a JSON-shape access schedule payload.
 *
 * Strict: throws on invalid input. Used by abilities-driven save paths where
 * a malformed payload should be reported back to the API caller.
 *
 * @since 1.29.0
 */
class AccessScheduleValidator
{
	/** @var string[] valid access schedule periods */
	public const VALID_PERIODS = ['days', 'weeks', 'months', 'years'];

	/** @var string[] valid access schedule types */
	public const VALID_TYPES = ['immediate', 'delayed'];


	/**
	 * Asserts that the given access schedule payload is structurally valid.
	 *
	 * Allowed shapes:
	 *  - `['type' => 'immediate']` (additional keys are ignored)
	 *  - `['type' => 'delayed', 'amount' => positive int, 'period' => 'days'|'weeks'|'months'|'years']`
	 *
	 * @since 1.29.0
	 *
	 * @param array<string, mixed> $schedule
	 * @throws InvalidArgumentException on invalid type, amount, or period
	 */
	public static function validate(array $schedule): void
	{
		$type = $schedule['type'] ?? 'immediate';

		if (! in_array($type, self::VALID_TYPES, true)) {
			throw new InvalidArgumentException(
				sprintf('Invalid access schedule type "%s". Must be one of: %s.', $type, implode(', ', self::VALID_TYPES))
			);
		}

		if ('immediate' === $type) {
			return;
		}

		$amount = $schedule['amount'] ?? null;

		if (! is_int($amount) || $amount < 1) {
			throw new InvalidArgumentException('Access schedule "amount" must be a positive integer when type is "delayed".');
		}

		$period = $schedule['period'] ?? '';

		if (! in_array($period, self::VALID_PERIODS, true)) {
			throw new InvalidArgumentException(
				sprintf('Invalid access schedule period "%s". Must be one of: %s.', $period, implode(', ', self::VALID_PERIODS))
			);
		}
	}
}
