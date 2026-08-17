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

namespace SkyVerge\WooCommerce\Memberships\Posts\DataObjects;

use WC_Memberships_Membership_Plan_Rule;

defined('ABSPATH') or exit;

/**
 * Result of partitioning an incoming per-post rules payload against the rules
 * already stored for that post.
 *
 * Used internally by {@see \SkyVerge\WooCommerce\Memberships\Posts\Actions\SetPostRules}
 * to communicate between its partition and orphan-detection steps.
 *
 * @since 1.29.0
 */
class RulePartition
{
	/** @var WC_Memberships_Membership_Plan_Rule[] rules to insert as new */
	public array $add;

	/** @var WC_Memberships_Membership_Plan_Rule[] rules to update in place */
	public array $update;

	/** @var array<string, true> lookup of rule IDs present in the incoming payload, used to compute orphans */
	public array $incomingIds;

	/**
	 * @param WC_Memberships_Membership_Plan_Rule[] $add
	 * @param WC_Memberships_Membership_Plan_Rule[] $update
	 * @param array<string, true> $incomingIds
	 */
	public function __construct(array $add, array $update, array $incomingIds)
	{
		$this->add         = $add;
		$this->update      = $update;
		$this->incomingIds = $incomingIds;
	}
}
