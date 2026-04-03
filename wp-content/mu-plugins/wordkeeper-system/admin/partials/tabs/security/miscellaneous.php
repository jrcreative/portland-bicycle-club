<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>WP Options</h2>
	</div>
	<div class="inside">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="wp-editor">
					<input type="checkbox" name="wp/editor" id="wp-editor" <?php if($settings['wp/editor'] === true){ ?>checked<?php } ?> />
					Enable WP File Editor
				</label>
				<span class="tooltips" data-template="item-wp-editor" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-editor">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>While necessary for some theme features and plugins, the WP File Editor functionality is generally best left disabled for security reasons.  It's much safer to use SFTP access instead.  If you do need it, however, you can enable it here.</p>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>