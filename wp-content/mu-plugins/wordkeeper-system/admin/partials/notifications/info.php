<template id="template-info">
	<swal-html>
		<div class="holder-info holder-tips">
			<div class="holder-img">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/info.svg" alt="alt">
			</div>
			<div class="holder-text" id="notice-message"></div>
		</div>
	</swal-html>
</template>