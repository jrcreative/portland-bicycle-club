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

use InvalidArgumentException;
use SkyVerge\WooCommerce\Memberships\Abilities\Provider;
use SkyVerge\WooCommerce\Memberships\Contracts\ConvertToWpErrorContract;
use SkyVerge\WooCommerce\Memberships\Posts\Actions\SetPostRules;
use SkyVerge\WooCommerce\Memberships\Posts\Adapters\JsonSerializers\PostRestrictionRulesSerializer;
use SkyVerge\WooCommerce\Memberships\Posts\Traits\CanCheckRestrictablePostPermissionTrait;
use SkyVerge\WooCommerce\Memberships\Posts\Traits\CanResolveRestrictablePostTrait;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\Contracts\MakesAbilityContract;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\Ability;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\AbilityAnnotations;
use SkyVerge\WooCommerce\PluginFramework\v6_2_1\Abilities\DataObjects\RestConfig;
use WP_Error;

defined('ABSPATH') or exit;

/**
 * Ability: Update Post Restriction Rules.
 *
 * Replaces the post-specific content restriction rules for a restrictable post.
 * The incoming `rules` array is treated as the desired full state of post-specific
 * rules: missing IDs are deleted, present IDs are updated, new entries are added.
 * Inherited rules (post-type or taxonomy-level) are not affected.
 *
 * Registers a REST PUT route at `/wc-memberships/v1/post-restriction-rules/{id}`
 * to mirror the GET route registered by {@see GetPostRestrictionRules}.
 *
 * @since 1.29.0
 */
class UpdatePostRestrictionRules implements MakesAbilityContract
{
	use CanResolveRestrictablePostTrait;
	use CanCheckRestrictablePostPermissionTrait;

	const NAME = 'woocommerce-memberships/post-restriction-rules-update';


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
			__('Update Post Restriction Rules', 'woocommerce-memberships'),
			__('Replaces the post-specific content restriction rules for a restrictable post. Inherited rules are not affected.', 'woocommerce-memberships'),
			Provider::POSTS_CATEGORY_SLUG,
			function (array $params) {
				try {
					$post = $this->getRestrictablePostFromId((int) $params['id']);
				} catch (ConvertToWpErrorContract $e) {
					return $e->toWpError();
				}

				try {
					$this->getSetPostRulesInstance()->execute($post, $params['rules']);
				} catch (InvalidArgumentException $e) {
					return new WP_Error('invalid_input', $e->getMessage(), ['status' => 422]);
				}

				return PostRestrictionRulesSerializer::convert($post);
			},
			[$this, 'checkRestrictablePostPermission'],
			$this->getInputSchema(),
			PostRestrictionRulesSerializer::getJsonSchema(),
			new AbilityAnnotations(false, false, true),
			true,
			new RestConfig('/post-restriction-rules/(?P<id>\d+)', null, 'v1', 'PUT')
		);
	}

	/**
	 * Gets an instance of the SetPostRules class for easier unit tests.
	 *
	 * @codeCoverageIgnore
	 *
	 * @since 1.29.0
	 */
	protected function getSetPostRulesInstance() : SetPostRules
	{
		return new SetPostRules();
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
			'type'       => 'object',
			'properties' => [
				'id' => [
					'type'        => 'integer',
					'description' => __('The post ID.', 'woocommerce-memberships'),
					'required'    => true,
					'minimum'     => 1,
				],
				'rules' => [
					'type'        => 'array',
					'description' => __('Replacement set of post-specific content restriction rules. Empty array clears all post-specific rules.', 'woocommerce-memberships'),
					'required'    => true,
					'items'       => [
						'type'       => 'object',
						'properties' => [
							'id' => [
								'type'        => 'string',
								'description' => __('Rule ID. Omit when adding a new rule; include to update an existing post-specific rule.', 'woocommerce-memberships'),
								'required'    => false,
							],
							'membership_plan_id' => [
								'type'        => 'integer',
								'description' => __('The membership plan this rule belongs to.', 'woocommerce-memberships'),
								'required'    => true,
								'minimum'     => 1,
							],
							'access_schedule' => [
								'type'       => 'object',
								'required'   => false,
								'properties' => [
									'type'   => [
										'type'     => 'string',
										'enum'     => ['immediate', 'delayed'],
										'required' => true,
									],
									'amount' => [
										'type'        => 'integer',
										'required'    => false,
										'description' => __('Only present when type is "delayed".', 'woocommerce-memberships'),
									],
									'period' => [
										'type'        => 'string',
										'enum'        => ['days', 'weeks', 'months', 'years'],
										'required'    => false,
										'description' => __('Only present when type is "delayed".', 'woocommerce-memberships'),
									],
								],
							],
						],
					],
				],
			],
		];
	}
}
