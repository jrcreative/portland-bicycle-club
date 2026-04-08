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
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_1_1\Abilities\DataObjects\AbilityAnnotations;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: List Team Members.
 *
 * Retrieves a collection of members for a given team.
 *
 * @since 1.8.0
 */
class ListTeamMembers implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/team-members-list';

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
			__('List Team Members', 'woocommerce-memberships-for-teams'),
			__('Retrieves a collection of members for a given team.', 'woocommerce-memberships-for-teams'),
			Provider::TEAM_MEMBERS_CATEGORY_SLUG,
			function (array $params) {
				$teamId = $params['team_id'];
				unset($params['team_id']);

				$members = wc_memberships_for_teams_get_team_members($teamId, $params);

				return is_array($members) ? array_values($members) : [];
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			$this->getInputSchema(),
			[
				'type'  => 'array',
				'items' => Team_Member::getJsonSchema(),
			],
			new AbilityAnnotations(true, false, true),
			true
		);
	}

	/**
	 * Returns the input schema for the list team members ability.
	 *
	 * @since 1.8.0
	 *
	 * @return array<string, mixed>
	 */
	protected function getInputSchema() : array
	{
		return [
			'type'                 => 'object',
			'additionalProperties' => true,
			'description'          => __('Query parameters. Accepts standard WP_User_Query arguments in addition to the listed properties.', 'woocommerce-memberships-for-teams'),
			'properties'           => [
				'team_id' => [
					'type'        => 'integer',
					'required'    => true,
					'description' => __('The team ID to retrieve members for.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'role' => [
					'type'        => 'string',
					'description' => __('Filter by team member role (e.g. "owner", "manager", "member").', 'woocommerce-memberships-for-teams'),
				],
				'per_page' => [
					'type'        => 'integer',
					'description' => __('Number of members to return per page.', 'woocommerce-memberships-for-teams'),
				],
				'paged' => [
					'type'        => 'integer',
					'description' => __('Page number when paginating results.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
			],
		];
	}
}
