<?php
/**
 * Template for order tickets list.
 *
 * @package woocommerce-box-office
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<h2><?php echo apply_filters( 'woocommerce_box_office_order_tickets_title', esc_html__( 'Order Tickets', 'woocommerce-box-office' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></h2>

<p class="ticket-list-description">
	<?php echo apply_filters( 'woocommerce_box_office_order_tickets_description', esc_html__( 'View or edit each of your purchased tickets using the links below.', 'woocommerce-box-office' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
</p>

<ul class="purchased-tickets <?php echo sanitize_html_class( 'purchased-tickets-fields-format-' . $fields_format ); ?>">
	<?php foreach ( $tickets as $ticket ) : ?>
		<li>
			<a href="<?php echo esc_url( wcbo_get_my_ticket_url( $ticket->ID ) ); ?>">
				<?php echo esc_html( $ticket->post_title ); ?>
			</a>
			<?php if ( 'pending' === $ticket->post_status ) : ?>
				&mdash;
				<span class="pending"><?php esc_html_e( 'Pending', 'woocommerce-box-office' ); ?></span>
			<?php endif; ?>
			<div class="description">
				<?php echo wp_kses_post( wc_box_office_get_ticket_description( $ticket->ID, $fields_format ) ); ?>
			</div>
		</li>
	<?php endforeach; ?>
</ul>
