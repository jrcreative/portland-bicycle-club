<template id="template-create-backup">
	<swal-html>
		<form>
			<div class="holder-popups holder-backupinfo">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/backup.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Backup Information</h2>
					<p>Label your backup so you can easily identify it later</p>
				</div>
				<!-- add class error on holder-input -->
				<div class="holder-input">
					<input type="text" placeholder="Backup name* (required)" required name="name" id="backup-name" autocomplete="off" data-validate="basic" data-filter="basic" data-1p-ignore />
					<div class="text-error">Error Message</div>
				</div>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
						<button class="btn button-primary" data-submit data-waiting="Starting Backup..." data-success="Backup Started" data-path="backup/create">Confirm</button>
					</div>
				</div>
				<div class="holder-email-info">
					<div class="email-info">
						Backups can take a long time.  We'll email <?php printf(__('%s', 'textdomain'), esc_html($user->user_email)); ?> when the backup completes. <a href="#" class="open-btn">Add more emails</a>
					</div>
					<div class="hidden-email-info holder-input" style="padding-bottom: 0px;">
						<input type="text" placeholder="Comma-separated list of emails" name="notify" id="notify" autocomplete="off" data-validate="emails">
						<div class="text-error">Error Message</div>
					</div>
				</div>
			</div>
		</form>
	</swal-html>
</template>