<template id="template-redirect-add">
	<swal-html>
		<form action="#">
			<div class="holder-popups holder-redirect">
				<img src="<?php echo esc_html(plugin_dir_url(dirname(dirname(__FILE__)))); ?>images/redirect.svg" alt="alt">
				<div class="holder-title-text">
					<h2>Add Redirect</h2>
				</div>
				<div class="holder-inputs-popups">
					<!-- add class error on holder-input -->
					<div class="item holder-input">
						<div class="left-col">
							<label for="source-url">Source URL</label>
						</div>
						<div class="right-col">
							<input type="text" name="source-url" id="source-url" placeholder="The relative URL you want to redirect from" required autocomplete="off" data-validate="url" />
							<div class="text-error">Error Message</div>
						</div>
					</div>
					<!-- add class error on holder-input -->
					<div class="item holder-input">
						<div class="left-col">
							<label for="destination-url">Destination URL</label>
						</div>
						<div class="right-col">
							<input type="text" name="destination-url" id="destination-url" placeholder="The relative URL you want to redirect to" required autocomplete="off" data-validate="url" />
							<div class="text-error">Error Message</div>
						</div>
					</div>
					<div class="item">
						<div class="left-col">
							<label for="redirect-type">Redirect Type</label>
						</div>
						<div class="right-col">
							<div class="holder-radio">
								<input checked type="radio" name="redirect-type" id="permanent-redirect" />
								<label for="permanent-redirect">301 - Redirect Permanently</label>
							</div>
							<div class="holder-radio">
								<input type="radio" name="redirect-type" id="temporary-redirect" />
								<label for="temporary-redirect">302 - Redirect Temporarily</label>
							</div>
						</div>
					</div>
					<!-- add class error on holder-input -->
					<div class="item holder-input">
						<div class="left-col">
							<label for="priority">Priority</label>
						</div>
						<div class="right-col">
							<input type="number" name="priority" id="priority" placeholder="2" data-validate="number" />
							<div class="text-error">Error Message</div>
						</div>
					</div>
					<div class="item">
						<div class="left-col">
							<label class="form-control" for="enabled">Enable</label>
						</div>
						<div class="right-col">
							<input type="checkbox" id="enabled" name="enabled" checked />
						</div>
					</div>
				</div>
				<div class="holder-btns">
					<div class="col">
						<button type="button" class="btn button-tertiary close-button" data-close>Cancel</button>
					</div>
					<div class="col">
					<button type="button" class="btn button-primary sweetalert-btn" data-confirm data-waiting="Saving" data-success="Saved" data-path="redirect/create">Save</button>
					</div>
				</div>
			</div>
		</form>
	</swal-html>
</template>