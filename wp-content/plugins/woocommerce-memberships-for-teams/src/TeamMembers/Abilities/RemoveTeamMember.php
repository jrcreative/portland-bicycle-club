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
use SkyVerge\WooCommerce\PluginFramework\v6_1_1 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\AbilityAnnotations;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Remove Team Member.
 *
 * Removes a user from a team.
 *
 * @since 1.8.0
 */
class RemoveTeamMember implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/team-members-remove';

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
			__('Remove Team Member', 'woocommerce-memberships-for-teams'),
			__('Removes a user from a team.', 'woocommerce-memberships-for-teams'),
			Provider::TEAM_MEMBERS_CATEGORY_SLUG,
			function (array $params) {
				$team = wc_memberships_for_teams_get_team($params['team_id']);

				if (! $team) {
					return new WP_Error('team_not_found', __('Team not found.', 'woocommerce-memberships-for-teams'), ['status' => 404]);
				}

				try {
					$team->remove_member(
						$params['user_id'],
						$params['keep_user_memberships'] ?? false,
						$params['add_note'] ?? true
					);
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('remove_member_failed', $e->getMessage(), ['status' => 422]);
				}

				return true;
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			$this->getInputSchema(),
			[
				'type'        => 'boolean',
				'description' => __('Whether the member was successfully removed.', 'woocommerce-memberships-for-teams'),
			],
			new AbilityAnnotations(false, true, false),
			true
		);
	}

	/**
	 * Returns the input schema for the remove team member ability.
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
					'description' => __('The user ID to remove from the team.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'keep_user_memberships' => [
					'type'        => 'boolean',
					'description' => __('Whether to keep the user\'s memberships after removal. If false, memberships are deleted.', 'woocommerce-memberships-for-teams'),
					'default'     => false,
				],
				'add_note' => [
					'type'        => 'boolean',
					'description' => __('Whether to add a note to the user\'s membership when keeping memberships.', 'woocommerce-memberships-for-teams'),
					'default'     => true,
				],
			],
		];
	}
}
