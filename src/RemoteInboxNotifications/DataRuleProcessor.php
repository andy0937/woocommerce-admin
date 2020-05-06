<?php
/**
 * Rule processor that performs a comparison operation against a value in the
 * $data object.
 *
 * @package WooCommerce Admin/Classes
 */

namespace Automattic\WooCommerce\Admin\RemoteInboxNotifications;

defined( 'ABSPATH' ) || exit;

/**
 * Rule processor that performs a comparison operation against a value in the
 * $data object.
 */
class DataRuleProcessor {
	/**
	 * Performs a comparison operation against a value in the $data object.
	 *
	 * @param object $rule The rule being processed by this rule processor.
	 * @param object $data Data.
	 *
	 * @return bool The result of the operation.
	 */
	public function process( $rule, $data ) {
		if ( ! isset( $data->{$rule->index} ) ) {
			return false;
		}

		$operand = $data->{$rule->index};

		if ( '=' === $rule->operation ) {
			return $operand === $rule->value;
		} elseif ( '<' === $rule->operation ) {
			return $operand < $rule->value;
		} elseif ( '<=' === $rule->operation ) {
			return $operand <= $rule->value;
		} elseif ( '>' === $rule->operation ) {
			return $operand > $rule->value;
		} elseif ( '>=' === $rule->operation ) {
			return $operand >= $rule->value;
		} elseif ( '!=' === $rule->operation ) {
			return $operand !== $rule->value;
		}

		return false;
	}
}