<?php
/**
 * Template for input ticket field.
 *
 * @package woocommerce-box-office
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
echo $before_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<label class="<?php echo esc_attr( $label_class ); ?>" for="<?php echo esc_attr( $id ); ?>">
		<?php echo esc_html( $label ); ?>:
		<?php if ( $required ) : ?>
		<span class="required">*</span>
		<?php endif; ?>
	</label>
	<input
		type="<?php echo esc_attr( $type ); ?>"
		class="<?php echo esc_attr( trim( $input_class . ( $required ? ' ticket-field-input-required' : '' ) ) ); ?>"
		value="<?php echo esc_attr( $value ); ?>" name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo esc_attr( $id ); ?>"<?php disabled( $disabled ); ?>
		<?php echo ( $required ? 'required' : '' ); ?>
		<?php echo ( $autocomplete ? 'autocomplete="' . esc_attr( $autocomplete ) . '"' : '' ); ?>
		/>
	<?php echo $required_el; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $after_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
