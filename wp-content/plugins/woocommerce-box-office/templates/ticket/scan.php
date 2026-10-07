<?php
/**
 * Template for ticket scan form.
 *
 * @package woocommerce-box-office
 * @version 1.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div class="woocommerce">
	<div id="ticket-scan-form">
		<form name="ticket-scan" action="" method="post">
			<p class="form-row form-row-wide">
				<label for="scan-action" class="woocommerce-form__label"><?php esc_html_e( 'Scan action', 'woocommerce-box-office' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<select name="scan-action" id="scan-action" class="scan_action" aria-required="true">
					<option value=""<?php selected( $action, '' ); ?>><?php esc_html_e( 'Select action', 'woocommerce-box-office' ); ?></option>
					<option value="lookup"<?php selected( $action, 'lookup' ); ?>><?php esc_html_e( 'Look up', 'woocommerce-box-office' ); ?></option>
					<option value="attended"<?php selected( $action, 'attended' ); ?>><?php esc_html_e( 'Mark as attended', 'woocommerce-box-office' ); ?></option>
				</select>
			</p>

			<p class="form-row form-row-wide">
				<label for="scan-code" class="woocommerce-form__label"><?php esc_html_e( 'Ticket barcode', 'woocommerce-box-office' ); ?> <span class="required" aria-hidden="true">*</span></label>
				<input type="text" name="scan-code" id="scan-code" class="input-text" value="" placeholder="<?php esc_attr_e( 'Scan or enter ticket barcode', 'woocommerce-box-office' ); ?>" aria-required="true" />
			</p>

			<input type="submit" value="<?php esc_attr_e( 'Go', 'woocommerce-box-office' ); ?>" />
		</form>
	  </div>

	<div id="ticket-scan-loader"><?php esc_html_e( 'Processing ticket...', 'woocommerce-box-office' ); ?></div>
	<div id="ticket-scan-result"></div>
</div>
