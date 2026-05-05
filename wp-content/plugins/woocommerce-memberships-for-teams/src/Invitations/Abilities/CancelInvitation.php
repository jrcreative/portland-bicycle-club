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

namespace SkyVerge\WooCommerce\Memberships\Teams\Invitations\Abilities;

use SkyVerge\WooCommerce\Memberships\Teams\Abilities\Provider;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0 as Framework;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\AbilityAnnotations;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\RestConfig;
use WP_Error;

defined( 'ABSPATH' ) or exit;

/**
 * Ability: Cancel Team Invitation.
 *
 * Cancels a pending team invitation.
 *
 * @since 1.8.0
 */
class CancelInvitation implements MakesAbilityContract
{
	const NAME = 'woocommerce-memberships-for-teams/invitations-cancel';

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
			__('Cancel Team Invitation', 'woocommerce-memberships-for-teams'),
			__('Cancels a pending team invitation.', 'woocommerce-memberships-for-teams'),
			Provider::TEAM_INVITATIONS_CATEGORY_SLUG,
			function (int $invitationId) {
				$invitation = wc_memberships_for_teams_get_invitation($invitationId);

				if (! $invitation) {
					return new WP_Error('invitation_not_found', __('Invitation not found.', 'woocommerce-memberships-for-teams'), ['status' => 404]);
				}

				try {
					$invitation->cancel();
				} catch (Framework\SV_WC_Plugin_Exception $e) {
					return new WP_Error('cancel_failed', $e->getMessage(), ['status' => 422]);
				}

				return true;
			},
			function () {
				return current_user_can('manage_woocommerce');
			},
			[
				'type'        => 'integer',
				'description' => __('The invitation ID.', 'woocommerce-memberships-for-teams'),
				'required'    => true,
				'minimum'     => 1,
			],
			[
				'type'        => 'boolean',
				'description' => __('Whether the invitation was successfully cancelled.', 'woocommerce-memberships-for-teams'),
			],
			new AbilityAnnotations(false, true, true),
			true,
			new RestConfig('/invitations/(?P<id>\d+)')
		);
	}
}
