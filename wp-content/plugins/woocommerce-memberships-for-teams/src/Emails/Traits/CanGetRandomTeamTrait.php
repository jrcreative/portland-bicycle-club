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

namespace SkyVerge\WooCommerce\Memberships\Teams\Emails\Traits;

use ReflectionClass;
use SkyVerge\WooCommerce\Memberships\Teams\Team;

trait CanGetRandomTeamTrait
{
	protected function getRandomTeam() : Team
	{
		$realTeam = $this->getRandomRealTeam();
		if ($realTeam) {
			return $realTeam;
		}

		$fakeTeam = $this->makeFakeTeam();
		if ($fakeTeam->get_plan_id() === 0) {
			$class = new ReflectionClass($fakeTeam);
			$planProperty = $class->getProperty('plan');
			$planProperty->setAccessible(true);
			$planProperty->setValue($fakeTeam, $this->makeFakePlan());
		}

		return $fakeTeam;
	}

	protected function getRandomRealTeam() : ?Team
	{
		$teams = array_values(get_posts([
			'posts_per_page' => 1,
			'post_type'      => 'wc_memberships_team',
			'post_status'    => 'any',
			'orderby'        => 'rand',
		]));

		if (($teams[0] ?? null) instanceof \WP_Post) {
			return new Team($teams[0]);
		}

		return null;
	}

	protected function makeFakeTeam() : Team
	{
		$dummyPostObject = (object) [
			'post_title'   => 'Sample Team',
			'post_excerpt' => '',
			'post_status'  => 'publish',
			'post_type'    => 'wc_memberships_team',
			'post_author'  => get_current_user_id(),
			'post_parent'  => $this->getRandomPlanId(),
		];

		return new Team(new \WP_Post($dummyPostObject));
	}

	protected function getRandomPlanId() : int
	{
		$plans = array_values(wc_memberships()->get_plans_instance()->get_membership_plans([
			'posts_per_page' => 1,
			'orderby'        => 'rand',
		]));

		if (($plans[0] ?? null) instanceof \WC_Memberships_Membership_Plan) {
			return $plans[0]->get_id();
		}

		// if we can't get a real plan then we'll make a fake one later
		return 0;
	}

	protected function makeFakePlan() : \WC_Memberships_Membership_Plan
	{
		$dummyPostObject = (object) [
			'post_title' => 'Sample Membership',
			'post_excerpt' => '',
			'post_status'  => 'publish',
			'post_type'    => 'wc_membership_plan',
			'post_author'  => get_current_user_id(),
		];

		return new \WC_Memberships_Membership_Plan(new \WP_Post($dummyPostObject));
	}
}
