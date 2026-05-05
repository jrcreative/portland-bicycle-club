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

namespace SkyVerge\WooCommerce\Memberships\Teams;

use SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware;
use SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\LearnDash;
use SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\Sensei;
use SkyVerge\WooCommerce\Memberships\Teams\Integrations\Subscriptions;

defined( 'ABSPATH' ) or exit;

/**
 * Teams integrations class.
 *
 * @since 1.0.0
 */
class Integrations {


	/** @var \SkyVerge\WooCommerce\Memberships\Teams\Integrations\Subscriptions instance */
	private $subscriptions;

	/** @var null|\SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\Sensei instance */
	private $sensei;

	/** @var null|\SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\LearnDash instance */
	private $learndash;


	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( wc_memberships_for_teams()->is_plugin_active( 'woocommerce-subscriptions.php' ) && class_exists( 'WC_Subscriptions' ) ) {
			$this->subscriptions = new Subscriptions();
		}

		if ( wc_memberships_for_teams()->is_plugin_active( 'sensei-lms.php' ) ) {

			require_once( wc_memberships()->get_plugin_path() . '/src/integrations/Courseware.php' );
			require_once( wc_memberships()->get_plugin_path() . '/src/integrations/Courseware/Sensei.php' );

			$this->sensei = new Sensei();
		}

		if ( wc_memberships_for_teams()->is_plugin_active( 'sfwd_lms.php' ) ) {

			require_once( wc_memberships()->get_plugin_path() . '/src/integrations/Courseware.php' );
			require_once( wc_memberships()->get_plugin_path() . '/src/integrations/Courseware/LearnDash.php' );

			$this->sensei = new LearnDash();
		}
	}


	/**
	 * Returns the subscriptions integration class instance.
	 *
	 * @since 1.0.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Integrations\Subscriptions|null subscriptions instance if Subscriptions is active
	 */
	public function get_subscriptions_instance() {

		return $this->subscriptions;
	}


	/**
	 * Returns the sensei integration class instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\Sensei|null sensei instance if Sensei is active
	 */
	public function get_sensei_instance() {

		return $this->sensei;
	}


	/**
	 * Returns the learndash integration class instance.
	 *
	 * @since 1.6.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware\LearnDash|null sensei instance if LearnDash is active
	 */
	public function get_learndash_instance() {

		return $this->learndash;
	}


	/**
	 * Returns the currently active Courseware / LMS integration instance.
	 *
	 * In case multiple LMS plugins are active, only returns the first integration.
	 *
	 * @since 1.6.0
	 *
	 * @return \SkyVerge\WooCommerce\Memberships\Teams\Integrations\Courseware|null first active courseware integration instance, if any
	 */
	public function get_courseware_instance() {

		return $this->sensei ?? $this->learndash;
	}


}
