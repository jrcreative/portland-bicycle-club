<template id="template-change-php">
	<swal-html>
		<form action="#">
			<div class="holder-popups holder-backuprestore">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/icon-warning.svg" alt="alt" style="height: 62px;">
				<div class="holder-title-text">
					<h2>Warning</h2>
					<p>While changing PHP versions, your site may become unresponsive for up to a minute.</p>
					<div class="holder-single-select" style="margin-bottom: 20px; text-align: center;">
						<div style="width: 148px; margin: 0 auto; line-height: 20px;">
						<select name="php/version" id="php-version" class="single-select">
						<?php foreach($versions as $version): ?>
							<option value="<?php echo esc_html($version); ?>"<?php if(strpos(phpversion(), esc_html($version)) === 0): ?> selected<?php endif; ?>><?php echo esc_html($version); ?></option>
						<?php endforeach; ?>
						</select>
						</div>
					</div>
					<p>&nbsp;</p>
				</div>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
						<button class="btn button-primary" data-submit data-waiting="Changing PHP..." data-success="Changed" data-path="php/version">Change</button>
					</div>
				</div>
				<div class="holder-title-text">
					<p>If obvious PHP fatal errors occur with the updated PHP version, we'll automatically fall back to a working version.</p>
				</div>
			</div>
		</form>
	</swal-html>
</template>