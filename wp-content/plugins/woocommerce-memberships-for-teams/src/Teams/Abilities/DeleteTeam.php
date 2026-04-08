<?php
/**
 * Teams for WooCommerce Memberships
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
 * Do not edit or add to this file if you wish to upgrade Teams for WooCommerce Memberships to newer
 * versions in the future. If you wish to customize Teams for WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/teams-woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2017-2026, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities;

use SkyVerge\WooCommerce\Memberships\Teams\Abilities\Provider;
use SkyVerge\WooCommerce\Memberships\Teams\Team;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\AbilityAnnotations;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Delete Team.
 *
 * Deletes a team by ID.
 *
 * @since 1.8.0
 */
class DeleteTeam implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/teams-delete';

	/**
	 * Creates and returns the Ability data object for registration.
	 *
	 * @since 1.8.0
	 *
	 * @return Ability
	 */
	public function makeAbility() : Ability
	{
		return new Ability(
			static::NAME,
			__('Delete Team', 'woocommerce-memberships-for-teams'),
			__('Deletes a team by ID.', 'woocommerce-memberships-for-teams'),
			Provider::TEAMS_CATEGORY_SLUG,
			function (int $teamId) {
				try {
					wc_memberships_for_teams_delete_team($teamId);
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('delete_failed', $e->getMessage(), ['status' => 500]);
				}

				return true;
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			[
				'type'        => 'integer',
				'description' => __('The team ID.', 'woocommerce-memberships-for-teams'),
				'required'    => true,
				'minimum'     => 1,
			],
			[
				'type'        => 'boolean',
				'description' => __('Whether the team was successfully deleted.', 'woocommerce-memberships-for-teams'),
			],
			new AbilityAnnotations(false, true, false),
			true
		);
	}
}
