<?php
/**
 * WooCommerce Memberships
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
 * Do not edit or add to this file if you wish to upgrade WooCommerce Memberships to newer
 * versions in the future. If you wish to customize WooCommerce Memberships for your
 * needs please refer to https://docs.woocommerce.com/document/woocommerce-memberships/ for more information.
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2014-2026, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Memberships\Posts\Abilities;

use SkyVerge\WooCommerce\Memberships\Abilities\Provider;
use SkyVerge\WooCommerce\Memberships\Contracts\ConvertToWpErrorContract;
use SkyVerge\WooCommerce\Memberships\Posts\Adapters\JsonSerializers\PostRestrictionRulesSerializer;
use SkyVerge\WooCommerce\Memberships\Posts\Traits\CanCheckRestrictablePostPermissionTrait;
use SkyVerge\WooCommerce\Memberships\Posts\Traits\CanResolveRestrictablePostTrait;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\AbilityAnnotations;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\RestConfig;

defined('ABSPATH') or exit;

/**
 * Ability: Get Post Restriction Rules.
 *
 * Returns the content restriction rules that apply to a restrictable post —
 * both post-specific rules and inherited rules from post-type/taxonomy-level
 * rules on parent plans. Each rule carries an `editable` flag so the client
 * can render inherited rows as read-only.
 *
 * Registers a REST GET route at `/wc-memberships/v1/post-restriction-rules/{id}`
 * via the framework's auto-registration of {@see RestConfig}. The path is
 * shaped to match the `${baseURL}/${recordId}` contract that core-data entities
 * expect for single-record fetches.
 *
 * @since 1.29.0
 */
class GetPostRestrictionRules implements MakesAbilityContract
{
	use CanResolveRestrictablePostTrait;
	use CanCheckRestrictablePostPermissionTrait;

	const NAME = 'woocommerce-memberships/post-restriction-rules-get';


	/**
	 * Creates and returns the Ability data object for registration.
	 *
	 * @since 1.29.0
	 *
	 * @return Ability
	 */
	public function makeAbility(): Ability
	{
		return new Ability(
			static::NAME,
			__('Get Post Restriction Rules', 'woocommerce-memberships'),
			__('Retrieves content restriction rules — both post-specific and inherited — for a restrictable post.', 'woocommerce-memberships'),
			Provider::POSTS_CATEGORY_SLUG,
			function (int $id) {
				try {
					$post = $this->getRestrictablePostFromId($id);
				} catch (ConvertToWpErrorContract $e) {
					return $e->toWpError();
				}

				return PostRestrictionRulesSerializer::convert($post);
			},
			[$this, 'checkRestrictablePostPermission'],
			$this->getInputSchema(),
			PostRestrictionRulesSerializer::getJsonSchema(),
			new AbilityAnnotations(true, false, true),
			true,
			new RestConfig('/post-restriction-rules/(?P<id>\d+)')
		);
	}


	/**
	 * Returns the input schema for the ability.
	 *
	 * @since 1.29.0
	 *
	 * @return array<string, mixed>
	 */
	protected function getInputSchema(): array
	{
		return [
			'type'        => 'integer',
			'description' => __('The post ID.', 'woocommerce-memberships'),
			'required'    => true,
			'minimum'     => 1,
		];
	}
}
