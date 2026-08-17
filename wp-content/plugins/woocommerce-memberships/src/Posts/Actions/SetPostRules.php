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

namespace SkyVerge\WooCommerce\Memberships\Posts\Actions;

use InvalidArgumentException;
use SkyVerge\WooCommerce\Memberships\Posts\DataObjects\RulePartition;
use SkyVerge\WooCommerce\Memberships\Rules\Adapters\AccessScheduleNormalizer;
use SkyVerge\WooCommerce\Memberships\Rules\Validators\AccessScheduleValidator;
use WC_Memberships_Membership_Plan_Rule;
use WP_Post;

defined('ABSPATH') or exit;

/**
 * Replaces the per-post content restriction rules for a given post.
 *
 * The provided rules array is treated as the desired full state of post-specific rules:
 * existing rules whose IDs are not in the input get deleted; rules in the input with a
 * known ID get updated; rules without an ID get added. Inherited rules (post-type or
 * taxonomy-level) are never touched.
 *
 * @since 1.29.0
 */
class SetPostRules
{

	/**
	 * Replaces post-specific content restriction rules.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @param array<int, array<string, mixed>> $rulesData each item is a rule shaped per {@see PostRestrictionRulesSerializer::getRuleSchema()}
	 * @throws InvalidArgumentException
	 */
	public function execute(WP_Post $post, array $rulesData): void
	{
		$existingRules = $this->getExistingRules($post);
		$partition     = $this->partitionIncomingRules($post, $rulesData, $existingRules);
		$delete        = $this->findOrphanedRules($existingRules, $partition->incomingIds);

		$this->dispatchRuleChanges($partition->add, $partition->update, $delete);
	}


	/**
	 * Walks the incoming rule payload, configures each as a rule object,
	 * and partitions into add vs. update arrays based on whether the rule's
	 * ID matches an existing rule for this post.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @param array<int, array<string, mixed>> $rulesData
	 * @param array<string, WC_Memberships_Membership_Plan_Rule> $existingRules keyed by rule ID
	 * @return RulePartition
	 * @throws InvalidArgumentException when an entry is not an array
	 */
	protected function partitionIncomingRules(WP_Post $post, array $rulesData, array $existingRules): RulePartition
	{
		$add         = [];
		$update      = [];
		$incomingIds = [];

		foreach ($rulesData as $index => $ruleData) {

			if (! is_array($ruleData)) {
				throw new InvalidArgumentException(sprintf('Rule at index %d must be an object.', $index));
			}

			$rule = $this->configureRule($post, $ruleData);

			if ($rule->has_id()) {
				if (! isset($existingRules[$rule->get_id()])) {
					throw new InvalidArgumentException(sprintf(
						'Rule id "%s" does not belong to this post.',
						$rule->get_id()
					));
				}

				$update[]                     = $rule;
				$incomingIds[$rule->get_id()] = true;
			} else {
				$rule->set_id();

				$add[] = $rule;
			}
		}

		return new RulePartition($add, $update, $incomingIds);
	}


	/**
	 * Returns existing rules that are not present in the incoming payload —
	 * i.e. rules to delete to honor the "incoming = full desired state" contract.
	 *
	 * @since 1.29.0
	 *
	 * @param array<string, WC_Memberships_Membership_Plan_Rule> $existingRules
	 * @param array<string, true> $incomingIds lookup of rule IDs present in the incoming payload
	 * @return WC_Memberships_Membership_Plan_Rule[]
	 */
	protected function findOrphanedRules(array $existingRules, array $incomingIds): array
	{
		$delete = [];

		foreach ($existingRules as $existingId => $existingRule) {
			if (! isset($incomingIds[$existingId])) {
				$delete[] = $existingRule;
			}
		}

		return $delete;
	}


	/**
	 * Dispatches add/update/delete operations to the rules store.
	 *
	 * @since 1.29.0
	 *
	 * @param WC_Memberships_Membership_Plan_Rule[] $add
	 * @param WC_Memberships_Membership_Plan_Rule[] $update
	 * @param WC_Memberships_Membership_Plan_Rule[] $delete
	 */
	protected function dispatchRuleChanges(array $add, array $update, array $delete): void
	{
		wc_memberships()->get_rules_instance()->set_rules([
			'add'    => $add,
			'update' => $update,
			'delete' => $delete,
		]);
	}

	/**
	 * Gets the existing rules already applied to the given post.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @return WC_Memberships_Membership_Plan_Rule[] associative array of rule IDs and rule objects
	 */
	protected function getExistingRules(WP_Post $post): array
	{
		return wc_memberships()->get_rules_instance()->get_rules([
			'rule_type'         => 'content_restriction',
			'object_id'         => $post->ID,
			'content_type'      => 'post_type',
			'content_type_name' => $post->post_type,
			'exclude_inherited' => true,
			'plan_status'       => 'any',
		]);
	}


	/**
	 * Configures a rule object from the incoming rule data.
	 *
	 * @since 1.29.0
	 *
	 * @param WP_Post $post
	 * @param array<string, mixed> $ruleData
	 * @return WC_Memberships_Membership_Plan_Rule
	 * @throws InvalidArgumentException
	 */
	protected function configureRule(WP_Post $post, array $ruleData): WC_Memberships_Membership_Plan_Rule
	{
		$membershipPlanId = filter_var(
			$ruleData['membership_plan_id'] ?? null,
			FILTER_VALIDATE_INT,
			['options' => ['min_range' => 1]]
		);

		if (false === $membershipPlanId) {
			throw new InvalidArgumentException('Each rule must include a positive integer "membership_plan_id".');
		}

		$rule = $this->instantiateRule();

		if (! empty($ruleData['id'])) {
			$rule->set_id($ruleData['id']);
		}

		$rule->set_rule_type('content_restriction');
		$rule->set_membership_plan_id($membershipPlanId);
		$rule->set_content_type('post_type');
		$rule->set_content_type_name($post->post_type);
		$rule->set_object_ids([$post->ID]);

		$this->setAccessSchedule($rule, $ruleData);

		return $rule;
	}


	/**
	 * Sets the access schedule on a rule from the JSON-shape rule data.
	 *
	 * @since 1.29.0
	 *
	 * @param WC_Memberships_Membership_Plan_Rule $rule
	 * @param array<string, mixed> $ruleData
	 * @throws InvalidArgumentException on invalid schedule input
	 */
	protected function setAccessSchedule(WC_Memberships_Membership_Plan_Rule $rule, array $ruleData): void
	{
		$schedule = $ruleData['access_schedule'] ?? ['type' => 'immediate'];

		AccessScheduleValidator::validate($schedule);

		$rule->set_access_schedule(AccessScheduleNormalizer::toAccessScheduleString($schedule));
	}


	/**
	 * Creates a new instance of the rule class. Split off for easier unit testing.
	 *
	 * @since 1.29.0
	 *
	 * @codeCoverageIgnore
	 */
	protected function instantiateRule(): WC_Memberships_Membership_Plan_Rule
	{
		return new WC_Memberships_Membership_Plan_Rule();
	}
}
