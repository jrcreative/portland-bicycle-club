<template id="template-warning">
	<swal-html>
		<div class="holder-warning holder-tips">
			<div class="holder-img">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/warning.svg" alt="alt">
			</div>
			<div class="holder-text" id="notice-message"></div>
		</div>
	</swal-html>
</template>