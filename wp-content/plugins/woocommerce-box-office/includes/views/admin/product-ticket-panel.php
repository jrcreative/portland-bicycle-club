<?php
/**
 * Admin view for ticket field data panel.
 *
 * @package woocommerce-box-office
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<div id="ticket_field_data" class="panel woocommerce_options_panel">
	<div class="options_group show_if_ticket">

		<p class="form-field">
				<?php
				$use_customer_details = get_post_meta( $post->ID, '_ticket_use_customer_details', true );
				$use_customer_details = empty( $use_customer_details ) ? 'no' : $use_customer_details;

				woocommerce_wp_checkbox(
					array(
						'id'            => '_ticket_use_customer_details',
						'wrapper_class' => 'show_if_ticket',
						'label'         => esc_html__( 'Use customer details for tickets', 'woocommerce-box-office' ),
						'value'         => $use_customer_details,
						'description'   => esc_html__(
							'Ticket fields will not be shown on the product page. Ticket data will be auto-populated from the customer\'s billing details. Ticket metadata will be hidden from the thank you page and order emails.',
							'woocommerce-box-office'
						),
					)
				);
				?>
		</p>

		<?php
		$ticket_fields   = get_post_meta( $post->ID, '_ticket_fields', true );
		$ticket_fields   = is_array( $ticket_fields ) ? $ticket_fields : array();
		$cd_fields       = get_post_meta( $post->ID, '_ticket_customer_detail_fields', true );
		$cd_fields       = is_array( $cd_fields ) ? $cd_fields : array();
		$mappings        = get_post_meta( $post->ID, '_ticket_customer_detail_mappings', true );
		$mappings        = is_array( $mappings ) ? $mappings : array();
		$mapping_options = wc_box_office_autofill_options();
		?>

		<!-- Standard ticket fields table (hidden when "Use customer details" is on) -->
		<div id="ticket_standard_fields_wrapper" class="form-field ticket_fields" style="<?php echo 'yes' === $use_customer_details ? 'display:none;' : ''; ?>">
			<table class="widefat">
				<thead>
					<tr>
						<th>
							<?php esc_html_e( 'Label', 'woocommerce-box-office' ); ?>
							<?php if ( function_exists( 'wc_help_tip' ) ) : ?>
								<?php echo wc_help_tip( esc_html__( 'The field label as it is shown to the user.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>
							<?php else : ?>
								<span class="tips" data-tip="<?php echo wc_sanitize_tooltip( esc_html__( 'The field label as it is shown to the user.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>">[?]</span>
							<?php endif; ?>
						</th>
						<th><?php esc_html_e( 'Type', 'woocommerce-box-office' ); ?></th>
						<th>
							<?php esc_html_e( 'Auto-fill', 'woocommerce-box-office' ); ?>
							<?php if ( function_exists( 'wc_help_tip' ) ) : ?>
								<?php echo wc_help_tip( esc_html__( 'Choose the customer\'s billing field from which data is auto-filled as well as what options are available for applicable field types.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>
							<?php else : ?>
								<span class="tips" data-tip="<?php echo wc_sanitize_tooltip( esc_html__( 'Choose the customer\'s billing field from which data is auto-filled as well as what options are available for applicable field types.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>">[?]</span>
							<?php endif; ?>
						</th>
						<th><?php esc_html_e( 'Required', 'woocommerce-box-office' ); ?></th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="5">
							<?php
							$field            = array(
								'label'          => '',
								'type'           => '',
								'options'        => '',
								'autofill'       => '',
								'email_contact'  => 'yes',
								'email_gravatar' => 'yes',
								'required'       => 'yes',
							);
							$field_types      = wc_box_office_ticket_field_types();
							$autofill_options = wc_box_office_autofill_options();
							ob_start();
							require WCBO()->dir . 'includes/views/admin/ticket-field.php';
							$field_row_template = esc_attr( ob_get_clean() );
							?>
							<a href="#" class="button insert" data-row="<?php echo $field_row_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above. ?>"><?php esc_html_e( 'Add Field', 'woocommerce-box-office' ); ?></a>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					$row = 'alternate';
					if ( $ticket_fields ) {
						foreach ( $ticket_fields as $key => $field ) {
							include WCBO()->dir . 'includes/views/admin/ticket-field.php';
							if ( 'alternate' === $row ) {
								$row = '';
							} else {
								$row = 'alternate';
							}
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<!-- Customer details mapping table (shown when "Use customer details" is on) -->
		<div id="ticket_customer_detail_mappings_wrapper" class="form-field ticket_mapping_fields" style="<?php echo 'yes' !== $use_customer_details ? 'display:none;' : ''; ?>">
			<?php
			ob_start();
			?>
			<td class="field_label"><input type="hidden" name="_ticket_mapping_keys[]" value="" /><input type="text" class="input_text" placeholder="<?php esc_attr_e( 'Field Label', 'woocommerce-box-office' ); ?>" name="_ticket_mapping_labels[]" value="" required="required" /></td>
			<td class="field_options">
				<select name="_ticket_mapping_fields[]" style="width: 254px;">
					<option value="none"><?php esc_html_e( '-- None --', 'woocommerce-box-office' ); ?></option>
					<option value="" disabled="disabled"><?php esc_html_e( '--------', 'woocommerce-box-office' ); ?></option>
					<?php foreach ( $mapping_options as $opt_key => $opt_label ) : ?>
						<option value="<?php echo esc_attr( $opt_key ); ?>"><?php echo esc_html( $opt_label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
			<td width="1%"><a href="#" class="delete"><?php esc_html_e( 'Delete', 'woocommerce-box-office' ); ?></a></td>
			<?php
			$mapping_row_template = esc_attr( ob_get_clean() );
			?>
			<table class="widefat" id="ticket_customer_detail_mappings_table" data-row-template="<?php echo $mapping_row_template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Already escaped above. ?>">
				<thead>
					<tr>
						<th>
							<?php esc_html_e( 'Label', 'woocommerce-box-office' ); ?>
							<?php if ( function_exists( 'wc_help_tip' ) ) : ?>
								<?php echo wc_help_tip( esc_html__( 'The ticket field label.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>
							<?php endif; ?>
						</th>
						<th>
							<?php esc_html_e( 'Checkout Field', 'woocommerce-box-office' ); ?>
							<?php if ( function_exists( 'wc_help_tip' ) ) : ?>
								<?php echo wc_help_tip( esc_html__( 'Choose the checkout field to populate this ticket field from.', 'woocommerce-box-office' ) ); // phpcs:ignore ?>
							<?php endif; ?>
						</th>
						<th>&nbsp;</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th colspan="3">
							<a href="#" class="button" id="ticket_customer_detail_add_field"><?php esc_html_e( 'Add Field', 'woocommerce-box-office' ); ?></a>
						</th>
					</tr>
				</tfoot>
				<tbody>
					<?php
					if ( ! empty( $cd_fields ) ) :
						$cd_row = 'alternate';
						foreach ( $cd_fields as $field_hash => $field ) :
							$mapped_value = isset( $mappings[ $field_hash ] ) ? $mappings[ $field_hash ] : 'none';
							?>
						<tr class="<?php echo esc_attr( $cd_row ); ?>">
							<td class="field_label">
								<input type="hidden" name="_ticket_mapping_keys[]" value="<?php echo esc_attr( $field_hash ); ?>" />
								<input type="text" class="input_text" placeholder="<?php esc_attr_e( 'Field Label', 'woocommerce-box-office' ); ?>" name="_ticket_mapping_labels[]" value="<?php echo esc_attr( $field['label'] ); ?>" required="required" />
							</td>
							<td class="field_options">
								<select name="_ticket_mapping_fields[]" style="width: 254px;">
									<option value="none"><?php esc_html_e( '-- None --', 'woocommerce-box-office' ); ?></option>
									<option value="" disabled="disabled"><?php esc_html_e( '--------', 'woocommerce-box-office' ); ?></option>
									<?php foreach ( $mapping_options as $opt_key => $opt_label ) : ?>
										<option value="<?php echo esc_attr( $opt_key ); ?>" <?php selected( $mapped_value, $opt_key ); ?>>
											<?php echo esc_html( $opt_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td width="1%"><a href="#" class="delete"><?php esc_html_e( 'Delete', 'woocommerce-box-office' ); ?></a></td>
						</tr>
							<?php
							$cd_row = ( 'alternate' === $cd_row ) ? '' : 'alternate';
						endforeach;
					endif;
					?>
				</tbody>
			</table>
		</div>

		<p class="form-field">
			<?php
				$pii_setting = get_post_meta( $post->ID, '_user_pii_setting', true );
				$pii_setting = empty( $pii_setting ) ? 'yes' : $pii_setting;

				woocommerce_wp_checkbox(
					array(
						'id'            => '_user_pii_setting',
						'wrapper_class' => 'show_if_ticket',
						'label'         => esc_html__( 'User privacy preference', 'woocommerce-box-office' ),
						'value'         => $pii_setting,
						'description'   => sprintf(
							esc_html__(
								'Allow customers to opt-out from being displayed in the public list of attendees when %s shortcode is used.',
								'woocommerce-box-office'
							),
							'<code>[tickets]</code>'
						),
					)
				);
				?>
		</p>

	</div>
</div>

	<div id="ticket_content_data" class="panel woocommerce_options_panel">
	<div class="options_group show_if_ticket">
			<?php
			$print_tooltip = esc_html__( 'Note that even if disabled here, if this setting is enabled at the global level, the Print ticket button will be shown.', 'woocommerce-box-office' );
			$print_label = esc_html__( 'Enable ticket printing', 'woocommerce-box-office' );
			if ( function_exists( 'wc_help_tip' ) ) {
				$print_label .= ' ' . wc_help_tip( $print_tooltip );
			}
			woocommerce_wp_checkbox( array( 'id' => '_print_tickets', 'wrapper_class' => 'show_if_ticket', 'label' => $print_label, 'description' => esc_html__( 'This will enable the \'Print ticket\' button on the ticket edit page.', 'woocommerce-box-office' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
			?>

			<?php
			$edit_tooltip = esc_html__( 'Note that even if disabled here, if this setting is enabled at the global level, the customer won\'t be allowed to edit the ticket.', 'woocommerce-box-office' );
			$edit_label = esc_html__( 'Disable ticket editing', 'woocommerce-box-office' );
			if ( function_exists( 'wc_help_tip' ) ) {
				$edit_label .= ' ' . wc_help_tip( $edit_tooltip );
			}
			woocommerce_wp_checkbox( array( 'id' => '_disable_edit_tickets', 'wrapper_class' => 'show_if_ticket', 'label' => $edit_label, 'description' => esc_html__( 'This will disable customers to edit their purchased tickets.', 'woocommerce-box-office' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
			?>

			<?php
			if ( function_exists( 'WC_Order_Barcodes' ) ) {
				woocommerce_wp_checkbox( array( 'id' => '_print_barcode', 'wrapper_class' => 'show_if_ticket', 'label' => esc_html__( 'Include barcode', 'woocommerce-box-office' ), 'description' => esc_html__( 'This will add the unique ticket barcode to the bottom of the ticket.', 'woocommerce-box-office' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
			}
			?>
		</div>
		<div class="options_group show_if_ticket">
			<p><?php esc_html_e( 'This is the content that will be shown on each printed ticket.', 'woocommerce-box-office' ); ?></p>
			<p class="ticket-label-variables-info">
				<?php esc_html_e( 'Add ticket fields to the content by using following labels: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-label-variables"></span>
			</p>
			<p>
				<?php esc_html_e( 'To insert ticket ID use: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-id-var">
					<a href="#"><code>{ticket_id}</code></a>
				</span>
			</p>
			<p>
				<?php esc_html_e( 'You can also use this ticket product variables: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-post-vars">
					<a href="#"><code>{post_title}</code></a>
					<a href="#"><code>{post_content}</code></a>
					<a href="#"><code>{product_price}</code></a>
					<a href="#"><code>{product_sku}</code></a>
				</span>
			</p>
			<?php
			$ticket_content = get_post_meta( $post->ID, '_ticket_content', true );

			$settings = array(
				'wpautop'       => true,
				'media_buttons' => true,
				'textarea_name' => 'ticket-content',
				'textarea_rows' => 30,
				'editor_class'  => 'ticket_content_editor',
				'teeny'         => false,
				'dfw'           => false,
				'tinymce'       => true,
				'quicktags'     => true,
				'editor_css'    => '<style>.woocommerce_options_panel textarea{height:175px;}</style>',
			);

			wp_editor( $ticket_content, 'ticket-content-editor', $settings );
			?>
		</div>
	</div>

	<div id="ticket_email_data" class="panel woocommerce_options_panel">
		<?php $wcbo_email = WC()->mailer()->emails['WC_Box_Office_Email'] ?? false; ?>

		<?php if ( $wcbo_email instanceof WC_Email && $wcbo_email->is_enabled() ) : ?>

		<div class="options_group show_if_ticket">
			<?php
			$email_tooltip = esc_html__( 'Note that even if disabled here, if this setting is enabled at the global level, an email will be sent any time the ticket is changed.', 'woocommerce-box-office' );
			$email_label = esc_html__( 'Enable ticket emails', 'woocommerce-box-office' );
			if ( function_exists( 'wc_help_tip' ) ) {
				$email_label .= ' ' . wc_help_tip( $email_tooltip );
			}
			woocommerce_wp_checkbox( array( 'id' => '_email_tickets', 'wrapper_class' => 'show_if_ticket', 'label' => $email_label, 'description' => esc_html__( 'This will send an email to the contact address for each ticket whenever it is purchased or updated.', 'woocommerce-box-office' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
			?>

			<?php
				$ticket_mail_subject = get_post_meta( $post->ID, '_email_ticket_subject', true );
				if ( empty( $ticket_mail_subject ) ) {
					$ticket_mail_subject = esc_html__( 'Your ticket has been purchased successfully!', 'woocommerce-box-office' );
				}

				woocommerce_wp_text_input( array( 'id' => '_email_ticket_subject', 'value' => $ticket_mail_subject, 'class' => 'full', 'label' => esc_html__( 'Email subject', 'woocommerce-box-office' ), 'description' => sprintf( esc_html__( 'Add ticket fields to the subject by inserting the field label like this: %1$s<br>e.g. %2$s', 'woocommerce-box-office' ), '<code>{Label}</code>', '<code>{First Name}</code>' ) ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
			?>
		</div>
		<div class="options_group show_if_ticket">
			<p class="ticket_email"><?php esc_html_e( 'This is the content that will make up each email.', 'woocommerce-box-office' ); ?>
			</p>
			<p class="ticket-label-variables-info">
				<?php esc_html_e( 'Add ticket fields to the content by using following labels: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-label-variables"></span>
			</p>
			<p>
				<?php esc_html_e( 'To insert ticket link use: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-link-var">
					<a href="#"><code>{ticket_link}</code></a>
				</span>
				<?php
				printf(
					/* translators: 1: Placeholder for ticket link. 2: Example of how to wrap the placeholder in a clickable link. */
					esc_html__( 'Wrap the %1$s placeholder in a link to make it clickable, e.g. %2$s', 'woocommerce-box-office' ),
					'<code>{ticket_link}</code>',
					'<code>' . esc_html( '<a href="{ticket_link}">View your ticket</a>' ) . '</code>'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'To insert ticket ID use: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-id-var">
					<a href="#"><code>{ticket_id}</code></a>
				</span>
			</p>
			<?php
			$barcode_obj = new WC_Box_Office_Ticket_Barcode();
			if ( $barcode_obj->is_available() ) {
			?>
			<p>
				<?php esc_html_e( 'To insert barcode use: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-barcode-var">
					<a href="#"><code>{barcode}</code></a>
				</span>
			</p>
			<?php
			}
			?>
			<p>
				<?php esc_html_e( 'To insert ticket token use: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-token-var">
					<a href="#"><code>{token}</code></a>
				</span>
				<?php echo wp_kses_post( __( 'Ticket token can be used to build private content link, e.g. <code>http://example.com/private?token={token}</code>', 'woocommerce-box-office' ) ); ?>
			</p>
			<p>
				<?php esc_html_e( 'You can also use this ticket product variables: ', 'woocommerce-box-office' ); ?>
				<span class="ticket-post-vars">
					<a href="#"><code>{post_title}</code></a>
					<a href="#"><code>{post_content}</code></a>
					<a href="#"><code>{product_price}</code></a>
					<a href="#"><code>{product_sku}</code></a>
				</span>
			</p>

			<?php
			$ticket_email_html = get_post_meta( $post->ID, '_ticket_email_html', true );

			$settings = array(
				'wpautop' => true,
				'media_buttons' => true,
				'textarea_name' => 'ticket-email',
				'textarea_rows' => 30,
				'editor_class' => 'ticket_email_editor',
				'teeny' => false,
				'dfw' => false,
				'tinymce' => true,
				'quicktags' => true,
			);

			wp_editor( $ticket_email_html, 'ticket-email-editor', $settings );
			?>
		</div>

		<?php else : ?>

		<div class="options_group show_if_ticket">
			<div class="wcbo-toolbar">
			<?php
			WC_Box_Office_Settings::display_warning( 'inline' );
			?>
			</div>
		</div>

		<?php endif; ?>
	</div>
