<template id="template-sync-staging">
	<swal-html>
		<form action="#">
			<div class="holder-popups holder-are-you-sure">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/are-you-sure.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Are You Sure?</h2>
					<p>Launch changes you've made in your staging site by overwriting your live site with the the files and data of your staging site.</p>
				</div>
				<?php if(isset($syncdomain) && $syncdomain === true): ?>
				<div class="holder-input">
					<input type="text" placeholder="Domain for live site..." required="" name="domain" id="domain" autocomplete="off" data-validate="host" data-filter="host">
					<div class="text-error">Error Message</div>
				</div>
				<?php endif; ?>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
						<button class="btn button-primary" data-submit data-waiting="Starting Sync..." data-success="Sync Started" data-path="sync/live">Confirm</button>
					</div>
				</div>
				<div class="holder-email-info">
					<div class="email-info">
						Syncs can take a long time.  We'll email <?php printf(__('%s', 'textdomain'), esc_html($user->user_email)); ?> when the sync completes. <a href="#" class="open-btn">Add more emails</a>
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