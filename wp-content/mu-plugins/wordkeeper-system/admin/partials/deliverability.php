<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link		https://wordkeeper.com
 * @since		2.0.0
 *
 * @package		WordKeeper\System
 * @subpackage	WordKeeper\System/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap-page-admin-panel">
	<div class="top-line">
		<div class="col">
			<h1>Deliverability</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="holder-postbox-widget">
		<div class="postbox-widget">
			<div class="holder-title-widget">
				<h2>Mail Settings</h2>
			</div>
			<div class="inside">
				<div class="description">
					<?php if(!$overridden): ?>
					Set your From Name and From Email address from this panel.
					<?php else: ?>
					Your site's mail settings are managed by a 3rd party email/SMTP plugin and cannot be managed here.
					<?php endif; ?>
				</div>
				<form action="#">
					<ul class="list-settings">
						<li class="item">
							<div class="list-two-col">
								<div class="row">
									<div class="col-left">
										<label class="form-control" for="item-mail-name">
											From Name:
										</label>
										<span class="tooltips" data-template="item-mail-name" role="button" tabindex="0">?</span>
										<div class="tooltips-block tippy-popper" id="item-mail-name">
											<a class="close" href="#">close</a>
											<div class="tippy-arrow"></div>
											<p>Set the FROM Name that your site will use to send emails.</p>
										</div>
									</div>
									<div class="col-right">
										<div class="form-item holder-input">
											<input type="text" placeholder="Name" name="mail/name" id="mail-name" value="<?php echo esc_attr($this->settings['mail/name']); ?>"  data-validate="regex" data-regex="^[\w\-]+$" data-no-validate="Valid names may only contain letters, numbers, dashes, and underscores" <?php if($overridden): ?>disabled<?php endif; ?>>
											<div class="text-error">Error Message</div>
										</div>
									</div>
								</div>
							</div>
						</li>
						<li class="item">
							<div class="list-two-col">
								<div class="row">
									<div class="col-left">
										<label class="form-control" for="item-mail-email">
											From Email:
										</label>
										<span class="tooltips" data-template="item-mail-email" role="button" tabindex="0">?</span>
										<div class="tooltips-block tippy-popper" id="item-mail-email">
											<a class="close" href="#">close</a>
											<div class="tippy-arrow"></div>
											<p>Set the FROM Email that your site will use to send emails.</p>
										</div>
									</div>
									<div class="col-right">
										<div class="form-item holder-input">
											<input type="text" placeholder="Email" name="mail/email" id="mail-email" value="<?php echo esc_attr($this->settings['mail/email']); ?>"  data-validate="email" data-no-validate="Must be a valid email address" <?php if($overridden): ?>disabled<?php endif; ?>>
											<div class="text-error">Error Message</div>
										</div>
									</div>
								</div>
							</div>
						</li>
					</ul>
					<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
	</div>

</div>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>