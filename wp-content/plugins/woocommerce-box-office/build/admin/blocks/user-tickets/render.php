<?php
/**
 * Render the User Tickets block.
 *
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 * @package woocommerce-box-office
 */

$format       = $attributes['format'] ?? 'flat';
$block_title  = $attributes['title'] ?? '';
$description  = $attributes['description'] ?? '';
$ticket_count = 'all';

if ( isset( $attributes['numberOfTickets'] ) && -1 !== (int) $attributes['numberOfTickets'] ) {
	$ticket_count = (int) $attributes['numberOfTickets'];
}

$shortcode = new WC_Box_Office_Ticket_Shortcode();
$html      = $shortcode->user_ticket_list(
	array(
		'amount'         => $ticket_count,
		'fields_format'  => $format,
		'title'          => $block_title,
		'description'    => $description,
		'enable_filters' => false,
	)
);

echo wp_kses_post( $html );
