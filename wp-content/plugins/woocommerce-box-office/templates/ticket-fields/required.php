<?php
/**
 * Ticket form required field template.
 *
 * @package woocommerce-box-office
 * @version 1.5.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<span
	id="wcbo_for_<?php echo esc_attr( $field_key ); ?>_0"
	data-fieldkey="<?php echo esc_attr( $field_key ); ?>"
	data-fieldtype="<?php echo esc_attr( $field_type ); ?>"
	data-errormessage="<?php echo esc_attr( $error_message ); ?>"
	data-errormessagechoice="<?php echo esc_attr( $error_message_choice ); ?>"
	aria-hidden="true"
	class="wc-box-office-ticket-field-error-message"
	>
	<svg xmlns="http://www.w3.org/2000/svg" viewBox="-2 -2 24 24" width="24" height="24" aria-hidden="true" focusable="false">
		<path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm1.13 9.38l.35-6.46H8.52l.35 6.46h2.26zm-.09 3.36c.24-.23.37-.55.37-.96 0-.42-.12-.74-.36-.97s-.59-.35-1.06-.35-.82.12-1.07.35-.37.55-.37.97c0 .41.13.73.38.96.26.23.61.34 1.06.34s.8-.11 1.05-.34z"></path>
	</svg>
	<span class="error-message-text"></span>
</span>
