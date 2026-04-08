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

namespace SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities;

use SkyVerge\WooCommerce\Memberships\Teams\Abilities\Provider;
use SkyVerge\WooCommerce\Memberships\Teams\Team_Member;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\AbilityAnnotations;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Add Team Member.
 *
 * Adds a user as a member of a team.
 *
 * @since 1.8.0
 */
class AddTeamMember implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/team-members-add';

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
			__('Add Team Member', 'woocommerce-memberships-for-teams'),
			__('Adds a user as a member of a team.', 'woocommerce-memberships-for-teams'),
			Provider::TEAM_MEMBERS_CATEGORY_SLUG,
			function (array $params) {
				$team = wc_memberships_for_teams_get_team($params['team_id']);

				if (! $team) {
					return new WP_Error('team_not_found', __('Team not found.', 'woocommerce-memberships-for-teams'), ['status' => 404]);
				}

				try {
					return $team->add_member($params['user_id'], $params['role'] ?? 'member');
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('add_member_failed', $e->getMessage(), ['status' => 422]);
				}
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			$this->getInputSchema(),
			Team_Member::getJsonSchema(),
			new AbilityAnnotations(false, false, false),
			true
		);
	}

	/**
	 * Returns the input schema for the add team member ability.
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, mixed>
	 */
	protected function getInputSchema() : array
	{
		return [
			'type'       => 'object',
			'properties' => [
				'team_id' => [
					'type'        => 'integer',
					'required'    => true,
					'description' => __('The team ID.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'user_id' => [
					'type'        => 'integer',
					'required'    => true,
					'description' => __('The user ID to add as a member.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'role' => [
					'type'        => 'string',
					'description' => __('The member role (e.g. "member", "manager").', 'woocommerce-memberships-for-teams'),
					'default'     => 'member',
				],
			],
		];
	}
}
