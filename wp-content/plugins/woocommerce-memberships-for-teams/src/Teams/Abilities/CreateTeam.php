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
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\AbilityAnnotations;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\RestConfig;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Create Team.
 *
 * Creates a new team.
 *
 * @since 1.8.0
 */
class CreateTeam implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/teams-create';

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
			__('Create Team', 'woocommerce-memberships-for-teams'),
			__('Creates a new team.', 'woocommerce-memberships-for-teams'),
			Provider::TEAMS_CATEGORY_SLUG,
			function (array $data) {
				try {
					return wc_memberships_for_teams_create_team($data);
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('create_failed', $e->getMessage(), ['status' => 422]);
				}
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			$this->getInputSchema(),
			Team::getJsonSchema(),
			new AbilityAnnotations(false, false, false),
			true,
			new RestConfig('/teams')
		);
	}

	/**
	 * Returns the input schema for the create team ability.
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
				'name' => [
					'type'        => 'string',
					'description' => __('The team name. Defaults to "Team".', 'woocommerce-memberships-for-teams'),
				],
				'owner_id' => [
					'type'        => 'integer',
					'required'    => true,
					'description' => __('The owner user ID.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'plan_id' => [
					'type'        => 'integer',
					'required'    => true,
					'description' => __('The membership plan ID.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'product_id' => [
					'type'        => 'integer',
					'description' => __('The product ID that grants access.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'order_id' => [
					'type'        => 'integer',
					'description' => __('The order ID associated with the team.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
				'seats' => [
					'type'        => 'integer',
					'description' => __('The number of seats. If not provided, uses the max member count from the product.', 'woocommerce-memberships-for-teams'),
					'minimum'     => 1,
				],
			],
		];
	}
}
