<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>SSL Settings</h2>
	</div>
	<div class="inside">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="https-force">
					<input type="checkbox" name="https/force" id="https-force" <?php if($settings['https/force'] === true): ?>checked<?php endif; ?> />
					Force HTTPS
				</label>
				<span class="tooltips" data-template="item-https-force" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-https-force">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Force HTTPS (secure) connections by redirecting HTTP traffic to HTTPS.  Can cause mixed content errors if your site has historically only used HTTP.</p>
				</div>
			</div>
			<!--<div class="list-form-item">
				<label class="form-control" for="https-fix">
					<input type="checkbox" name="https/fix" id="https-fix" <?php if($settings['https/fix'] === true): ?>checked<?php endif; ?> />
					Fix SSL Mixed Content Problems
				</label>
				<span class="tooltips" data-template="item-https-fix" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-https-fix">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Try to prevent mixed content errors by switching asset protocols from HTTP to HTTPS automatically.</p>
				</div>
			</div>-->
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>