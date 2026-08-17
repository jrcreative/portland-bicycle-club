<?php
/**
 * My Tickets.
 *
 * Shows list of tickets customer has on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-box-office/myaccount/my-tickets.php.
 *
 * HOWEVER, on occasion WooCommerce Box Office will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package woocommerce-box-office
 * @version 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_page    = get_query_var( 'my-tickets' ) ? (int) get_query_var( 'my-tickets' ) : 1;
$tickets_query   = wc_box_office_query_tickets_by_user( get_current_user_id(), null, $current_page );
$total_pages     = $tickets_query->max_num_pages;
$total_tickets   = $tickets_query->found_posts;
$tickets         = $tickets_query->posts;
$has_tickets     = count( $tickets ) > 0;
$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';
?>

<?php if ( $total_tickets ) : ?>

	<table class="woocommerce-MyAccount-my-tickets shop_table shop_table_responsive">
		<thead>
			<tr>
				<th class="ticket-product" scope="col"><span class="nobr"><?php esc_html_e( 'Product', 'woocommerce-box-office' ); ?></span></th>
				<th class="ticket-order" scope="col"><span class="nobr"><?php esc_html_e( 'Order', 'woocommerce-box-office' ); ?></span></th>
				<th class="ticket-actions" scope="col"><span class="nobr"><?php esc_html_e( 'Actions', 'woocommerce-box-office' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ( $tickets as $ticket ) : ?>
			<?php
			$ticket  = wc_box_office_get_ticket( $ticket );
			$order   = $ticket->order;
			$product = $ticket->product;

			if ( ! is_a( $ticket->product, 'WC_Product' ) ) {
				continue;
			}
			?>
			<tr>
				<td class="ticket-product">
					<a href="<?php echo esc_url( $product->get_permalink() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Product: %s', 'woocommerce-box-office' ), $product->get_title() ) ); ?>"><?php echo esc_html( $product->get_title() ); ?></a>
				</td>
				<th class="ticket-order" scope="row">
					<?php
					/**
					 * In case a ticket created from admin without an order.
					 */
					?>
					<?php if ( is_a( $order, 'WC_Order' ) ) : ?>
						<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Order #: %s', 'woocommerce-box-office' ), $order->get_order_number() ) ); ?>"><?php echo esc_html( $order->get_order_number() ); ?></a>
					<?php endif; ?>
				</th>
				<td class="ticket-actions">
					<a href="<?php echo esc_url( wcbo_get_my_ticket_url( $ticket->id ) ); ?>" class="button woocommerce-Button">
						<?php esc_html_e( 'View or Edit', 'woocommerce-box-office' ); ?>
						<span class="screen-reader-text">
							<?php
							/* translators: Hidden accessibility text. */
							echo esc_html( sprintf( __( 'ticket for order #%s', 'woocommerce-box-office' ), $order->get_order_number() ) );
							?>
						</span>
					</a>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ( $total_pages > 1 ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'my-tickets', 2 === $current_page ? '' : $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce-box-office' ); ?></a>
			<?php endif; ?>

			<?php if ( $total_pages !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'my-tickets', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce-box-office' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<div class="woocommerce-Message woocommerce-Message--info woocommerce-info">
		<a class="woocommerce-Button button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Go Shop', 'woocommerce-box-office' ) ?>
		</a>
		<?php esc_html_e( 'No ticket has been purchased yet.', 'woocommerce-box-office' ); ?>
	</div>
<?php endif; ?>
