<template id="template-restore-backup">
	<swal-html>
		<form action="#">
			<div class="holder-popups holder-backuprestore">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/backuprestore.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Restore to <placeholder name="date"></placeholder> <placeholder name="time"></placeholder></h2>
					<p>What do you want to restore:</p>
				</div>
				<div class="holder-single-select" style="margin-bottom: 30px;">
					<div style="width: 160px; margin: 0 auto; text-align: left; line-height: 25px;">
					<select name="restore" id="backup-restore" class="single-select">
						<option value="everything">Everything</option>
						<option value="files">Files Only</option>
						<option value="database">Database Only</option>
						<option value="plugins">Plugin Files</option>
						<option value="themes">Theme Files</option>
					</select>
					</div>
				</div>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
						<button class="btn button-primary" data-submit data-waiting="Starting Restore..." data-success="Restore Started" data-path="backup/restore">Confirm</button>
					</div>
				</div>
				<div class="holder-title-text">
					<p>As the backup restores, the site might behave in unpredictable ways.  Wait for the restore to complete.</p>
				</div>
				<div class="holder-email-info">
					<div class="email-info">
						Restores can take a long time.  We'll email <?php printf(__('%s', 'textdomain'), esc_html($user->user_email)); ?> when the restore completes. <a href="#" class="open-btn">Add more emails</a>
					</div>
					<div class="hidden-email-info holder-input" style="padding-bottom: 0px;">
						<input type="text" placeholder="comma separated email addresses for notifications" name="notify" id="notify"autocomplete="off" data-validate="emails">
						<div class="text-error">Error Message</div>
					</div>
				</div>
			</div>
			<input name="name" id="backup-name" value="" type="hidden" data-filter="basic" />
			<input name="offset" id="backup-offset" value="" type="hidden" data-filter="regex" data-regex="[^0-9\-\+]" />
		</form>
	</swal-html>
</template>