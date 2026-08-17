<?php
/**
 * Template for radio ticket field.
 *
 * @package woocommerce-box-office
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
echo $before_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<fieldset class="<?php echo esc_attr( $fieldset_class ); ?>">
	<legend class="<?php echo esc_attr( trim( $label_class . ( $required ? ' ticket-field-option-required' : '' ) ) ); ?>" data-name="<?php echo esc_attr( $name ); ?>">
		<?php echo esc_html( $label ); ?>:
		<?php if ( $required ) : ?>
		<span class="required">*</span>
		<?php endif;?>
	</legend>
	<?php foreach ( $options as $index => $option ) : ?>
		<label for="<?php echo esc_attr( sprintf( '%s_%d', $id, $index + 1 ) ); ?>" class="ticket-field-option-label">
			<input
				type="radio"
				name="<?php echo esc_attr( $name ); ?>"
				class="<?php echo esc_attr( $input_class ); ?>"
				value="<?php echo esc_attr( $option ); ?>"
				id="<?php echo esc_attr( sprintf( '%s_%d', $id, $index + 1 ) ); ?>"
				<?php checked( $option, $value ); ?>
				<?php disabled( $disabled ); ?>>
			<?php echo esc_html( $option ); ?>
		</label>
	<?php endforeach; ?>
	<?php echo $required_el; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</fieldset>
<?php echo $after_field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
