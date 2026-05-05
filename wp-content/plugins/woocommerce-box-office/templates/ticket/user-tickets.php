<?php
/**
 * User tickets shortcode template.
 *
 * @package woocommerce-box-office
 */

if ( ! empty( $title ) ) : ?>
	<h2>
		<?php
			echo wp_kses_post(
				/**
				 * Filters the title of the user tickets section.
				 *
				 * @since 1.0.0
				 * @param string $title The title of the user tickets section.
				 */
				$enable_filters ? apply_filters( 'woocommerce_box_office_my_account_tickets_title', $title ) : $title
			);
		?>
	</h2>
<?php endif; ?>

<?php if ( $description ) : ?>
	<p class="ticket-list-description">
		<?php
			echo wp_kses_post(
				/**
				 * Filters the description of the user tickets section.
				 *
				 * @since 1.0.0
				 * @param string $description The description of the user tickets section.
				 */
				$enable_filters ? apply_filters( 'woocommerce_box_office_user_tickets_description', $description ) : $description
			);
		?>
	</p>
<?php endif; ?>

<dl class="purchased-tickets">
<?php foreach ( $tickets as $ticket ) : ?>
	<dt>
		<a href="<?php echo esc_url( wcbo_get_my_ticket_url( $ticket->ID ) ); ?>"><?php echo esc_html( $ticket->post_title ); ?></a>
	</dt>
	<dd class="description"><?php echo wp_kses_post( wc_box_office_get_ticket_description( $ticket->ID, $fields_format ) ); ?></dd>
<?php endforeach; ?>
</dl>
