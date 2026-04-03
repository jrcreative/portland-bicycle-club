<template id="template-leave">
	<swal-html>
		<form action="#">
			<div class="holder-pupups holder-are-you-sure holder-are-you-sure-speed">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/alert.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Are You Sure You Want to Leave?</h2>
					<p>Your changes have not been saved</p>
				</div>
				<div class="holder-btns">
					<div class="col">
						<a href="#" class="btn button-tirtiary custom-close-button">Stay Here</a>
					</div>
					<div class="col">
						<a href="#" class="btn button-primary">Leave Page</a>
					</div>
				</div>
			</div>
		</form>
	</swal-html>
</template>