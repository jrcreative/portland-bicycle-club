<!-- Saving -->
<template id="template-saving">
	<swal-html>
		<form action="#">
			<div class="holder-popups" style="height: 266px; padding: 50px 30px 10px;">
				<div class="holder-title-text">
					<h2 id="waiting-message"><placeholder name="waiting"></placeholder></h2>
				</div>
				<div class="holder" id="waiting-holder">
					<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/waiting.gif" alt="alt" id="waiting-icon" style="width: 6em; height: 6em; position: relative; top: 20px;" />
				</div>
				<div class="swal2-icon swal2-success swal2-icon-show" id="success-icon" style="display: none; margin-top: 28px;">
					<div class="swal2-success-circular-line-left" style="background-color: rgb(255, 255, 255);"></div>
					<span class="swal2-success-line-tip"></span> <span class="swal2-success-line-long"></span>
					<div class="swal2-success-ring"></div> <div class="swal2-success-fix" style="background-color: rgb(255, 255, 255);"></div>
					<div class="swal2-success-circular-line-right" style="background-color: rgb(255, 255, 255);"></div>
				</div>
				<div class="swal2-icon swal2-error swal2-icon-show" id="error-icon" style="display: none; margin-top: 28px;">
					<span class="swal2-x-mark">
						<span class="swal2-x-mark-line-left"></span>
						<span class="swal2-x-mark-line-right"></span>
					</span>
				</div>
			</div>
		</form>
	</swal-html>
</template>
