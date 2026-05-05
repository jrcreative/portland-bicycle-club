<template id="template-success">
	<swal-html>
		<div class="holder-success holder-tips">
			<div class="holder-img">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/success.svg" alt="alt">
			</div>
			<div class="holder-text" id="notice-message"></div>
		</div>
	</swal-html>
</template>