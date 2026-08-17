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

namespace SkyVerge\WooCommerce\Memberships\Posts\Adapters\JsonSerializers;

use SkyVerge\WooCommerce\Memberships\Plans\Adapters\JsonSerializers\MembershipPlanRuleSerializer;
use WC_Memberships_Membership_Plan_Rule;
use WP_Post;

defined('ABSPATH') or exit;

/**
 * Serializes the per-post content restriction rules entity into an array suitable
 * for JSON output.
 *
 * The entity bundles the post ID with both the post-specific rules (overrides
 * targeting this post directly) and the inherited rules (post-type or taxonomy
 * level rules that cascade down to this post). Each rule carries an `editable`
 * flag so the client can render inherited rows as read-only.
 *
 * @since 1.29.0
 */
class PostRestrictionRulesSerializer
{
	/**
	 * Builds the serialized rules entity for a given post.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @return array<string, mixed>
	 */
	public static function convert(WP_Post $post): array
	{
		return [
			'id'    => (int) $post->ID,
			'rules' => static::getRulesData($post),
		];
	}


	/**
	 * Returns the JSON schema for the entity.
	 *
	 * @since 1.29.0
	 *
	 * @return array<string, mixed>
	 */
	public static function getJsonSchema(): array
	{
		return [
			'type'       => 'object',
			'properties' => [
				'id' => [
					'type'        => 'integer',
					'description' => __('The post ID.', 'woocommerce-memberships'),
					'required'    => true,
				],
				'rules' => [
					'type'        => 'array',
					'description' => __('Content restriction rules that apply to this post, including inherited rules.', 'woocommerce-memberships'),
					'items'       => static::getRuleSchema(),
					'required'    => true,
				],
			],
		];
	}


	/**
	 * Returns the JSON schema for a single rule row in the response.
	 *
	 * Extends the standard `content_restriction` rule schema with two properties:
	 *  - `membership_plan_id` — required at the post level since rules aren't grouped under a plan post here.
	 *  - `editable` — `true` for post-specific rules; `false` for inherited rules.
	 *
	 * @since 1.29.0
	 *
	 * @return array<string, mixed>
	 */
	public static function getRuleSchema(): array
	{
		$schema = MembershipPlanRuleSerializer::getJsonSchema('content_restriction');

		$schema['properties']['membership_plan_id'] = [
			'type'        => 'integer',
			'description' => __('The membership plan this rule belongs to.', 'woocommerce-memberships'),
			'required'    => true,
			'minimum'     => 1,
		];

		$schema['properties']['editable'] = [
			'type'        => 'boolean',
			'description' => __('Whether this rule can be edited from this post. Inherited rules are read-only here and must be edited on the parent plan or content type.', 'woocommerce-memberships'),
			'required'    => true,
		];

		return $schema;
	}


	/**
	 * Returns the rules that apply to the given post — both post-specific and inherited —
	 * as serialized arrays.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @return array<int, array<string, mixed>>
	 */
	protected static function getRulesData(WP_Post $post): array
	{
		$rules = wc_memberships()->get_rules_instance()->get_rules([
			'rule_type'         => 'content_restriction',
			'object_id'         => $post->ID,
			'content_type'      => 'post_type',
			'content_type_name' => $post->post_type,
			'exclude_inherited' => false,
			'plan_status'       => 'any',
		]);

		$serialized = [];

		foreach ($rules as $rule) {
			$serialized[] = array_merge(
				MembershipPlanRuleSerializer::convert($rule),
				[
					'membership_plan_id' => $rule->get_membership_plan_id(),
					'editable'           => static::isRuleEditableForPost($rule, $post),
				]
			);
		}

		return $serialized;
	}


	/**
	 * Whether the given rule was attached directly to this post (vs. inherited from
	 * a post-type or taxonomy-level rule on the plan).
	 *
	 * @since 1.29.0
	 *
	 * @param WC_Memberships_Membership_Plan_Rule $rule
	 * @param WP_Post $post
	 * @return bool
	 */
	protected static function isRuleEditableForPost(WC_Memberships_Membership_Plan_Rule $rule, WP_Post $post): bool
	{
		if (! $rule->is_content_type('post_type')) {
			return false;
		}

		if (! $rule->is_content_type_name($post->post_type)) {
			return false;
		}

		return in_array((int) $post->ID, array_map('intval', $rule->get_object_ids()), true);
	}
}
