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
use SkyVerge\WooCommerce\Memberships\Teams\Invitation;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\AbilityAnnotations;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\RestConfig;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Invite to Team.
 *
 * Invites a user to a team by email.
 *
 * @since 1.8.0
 */
class InviteToTeam implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/teams-invite';

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
			__('Invite to Team', 'woocommerce-memberships-for-teams'),
			__('Invites a user to a team by email.', 'woocommerce-memberships-for-teams'),
			Provider::TEAMS_CATEGORY_SLUG,
			function (array $params) {
				$team = wc_memberships_for_teams_get_team($params['team_id']);

				if (! $team) {
					return new WP_Error('team_not_found', __('Team not found.', 'woocommerce-memberships-for-teams'), ['status' => 404]);
				}

				try {
					return $team->invite($params['email'], $params['role'] ?? 'member');
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('invite_failed', $e->getMessage(), ['status' => 422]);
				}
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			[
				'type'       => 'object',
				'properties' => [
					'team_id' => [
						'type'        => 'integer',
						'required'    => true,
						'description' => __('The team ID.', 'woocommerce-memberships-for-teams'),
						'minimum'     => 1,
					],
					'email' => [
						'type'        => 'string',
						'required'    => true,
						'format'      => 'email',
						'description' => __('The email address to send the invitation to.', 'woocommerce-memberships-for-teams'),
					],
					'role' => [
						'type'        => 'string',
						'description' => __('The role to assign the invited user (e.g. "member", "manager").', 'woocommerce-memberships-for-teams'),
						'default'     => 'member',
					],
				],
			],
			Invitation::getJsonSchema(),
			new AbilityAnnotations(false, false, false),
			true,
			new RestConfig('/invitations')
		);
	}
}
