<template id="template-download-backup">
	<swal-html>
		<form>
			<div class="holder-popups holder-are-you-sure">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/are-you-sure.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Create Downloadable Backup</h2>
					<p>When the backup is ready, we'll email you.  If needed, you can add additional notification addresses below.</p>
				</div>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
						<button class="btn button-primary" data-confirm data-waiting="Starting Backup..." data-success="Backup Started" data-path="backup/download">Confirm</button>
					</div>
				</div>
				<div class="holder-email-info">
					<div class="email-info">
						We'll email <?php printf(__('%s', 'textdomain'), esc_html($user->user_email)); ?> when the download is ready. <a href="#" class="open-btn">Add more emails</a>
					</div>
					<div class="hidden-email-info holder-input" style="padding-bottom: 0px;">
						<input type="text" placeholder="comma separated email addresses for notifications" name="notify" id="notify" autocomplete="off" data-validate="emails">
						<div class="text-error">Error Message</div>
					</div>
				</div>
			</div>
		</form>
	</swal-html>
</template>