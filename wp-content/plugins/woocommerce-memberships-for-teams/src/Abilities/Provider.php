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

namespace SkyVerge\WooCommerce\Memberships\Teams\Abilities;

defined( 'ABSPATH' ) or exit;

use SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities\CreateTeam;
use SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities\DeleteTeam;
use SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities\GetTeam;
use SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities\InviteToTeam;
use SkyVerge\WooCommerce\Memberships\Teams\Teams\Abilities\ListTeams;
use SkyVerge\WooCommerce\Memberships\Teams\Invitations\Abilities\CancelInvitation;
use SkyVerge\WooCommerce\Memberships\Teams\Invitations\Abilities\GetInvitation;
use SkyVerge\WooCommerce\Memberships\Teams\Invitations\Abilities\ListInvitations;
use SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities\AddTeamMember;
use SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities\GetTeamMember;
use SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities\ListTeamMembers;
use SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities\RemoveTeamMember;
use SkyVerge\WooCommerce\Memberships\Teams\TeamMembers\Abilities\UpdateTeamMemberRole;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\AbstractAbilitiesProvider;
use SkyVerge\WooCommerce\PluginFramework\v6_2_0\Abilities\DataObjects\AbilityCategory;

/**
 * Abilities provider for Teams for WooCommerce Memberships.
 *
 * Registers all abilities and categories for the Teams for WooCommerce Memberships plugin within the WordPress
 * Abilities API.
 *
 * @since 1.8.0
 */
class Provider extends AbstractAbilitiesProvider
{
    const TEAMS_CATEGORY_SLUG = 'woocommerce-memberships-teams';
    const TEAM_MEMBERS_CATEGORY_SLUG = 'woocommerce-memberships-team-members';
    const TEAM_INVITATIONS_CATEGORY_SLUG = 'woocommerce-memberships-team-invitations';

    /** @inheritdoc */
    protected array $abilities = [
        // teams
        CreateTeam::class,
        DeleteTeam::class,
        GetTeam::class,
        InviteToTeam::class,
        ListTeams::class,

        // team members
        AddTeamMember::class,
        GetTeamMember::class,
        ListTeamMembers::class,
        RemoveTeamMember::class,
        UpdateTeamMemberRole::class,

        // team invitations
        CancelInvitation::class,
        GetInvitation::class,
        ListInvitations::class,
    ];

    /** @inheritDoc */
    public function getCategories(): array
    {
        return [
            new AbilityCategory(
                static::TEAMS_CATEGORY_SLUG,
                __('WooCommerce Memberships Teams', 'woocommerce-memberships-for-teams'),
                __('Abilities related to WooCommerce team memberships.', 'woocommerce-memberships-for-teams')
            ),
            new AbilityCategory(
                static::TEAM_MEMBERS_CATEGORY_SLUG,
                __('WooCommerce Memberships Team Members', 'woocommerce-memberships-for-teams'),
                __('Abilities related to WooCommerce team members.', 'woocommerce-memberships-for-teams')
            ),
            new AbilityCategory(
                static::TEAM_INVITATIONS_CATEGORY_SLUG,
                __('WooCommerce Memberships Team Invitations', 'woocommerce-memberships-for-teams'),
                __('Abilities related to WooCommerce team invitations.', 'woocommerce-memberships-for-teams')
            ),
        ];
    }
}