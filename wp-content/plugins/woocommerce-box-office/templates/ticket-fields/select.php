<?php
/**
 * Template for select ticket field.
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
		<?php endif;?>
	</label>
	<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>" <?php disabled( $disabled ); ?><?php echo $required ? ' aria-required="true"' : ''; ?>>
	<?php foreach ( $options as $option ) : ?>
		<option <?php selected( $option, $value ) ?> value="<?php echo esc_attr( $option ); ?>"><?php echo esc_html( $option ); ?></option>
	<?php endforeach; ?>
	</select>
	<?php echo $required_el; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php echo $after_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
