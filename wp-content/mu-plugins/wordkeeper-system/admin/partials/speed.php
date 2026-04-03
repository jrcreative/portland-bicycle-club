<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link		https://wordkeeper.com
 * @since		2.0.0
 *
 * @package		WordKeeper\System
 * @subpackage	WordKeeper\System/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap-page-admin-panel">
	<div class="top-line">
		<div class="col">
			<h1>Speed</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="../wp-content/plugins/wordkeeper-system/admin/images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="holder-postbox-widget">
		<?php if(!file_exists(WP_CONTENT_DIR . '/plugins/wordkeeper-speed') && current_user_can('install_plugins')): ?>
		<div class="install-postbox-widget">
			<div class="row">
				<div class="col">
					<div class="holder-img">
						<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/speed-plg.svg" alt="alt">
						<a class="btn btn-link-install" href="javascript:return false;" data-submit data-waiting="Installing..." data-success="Installed" data-path="speed/install">Install Now</a>
					</div>
				</div>
				<div class="col">
					<div class="holder-text">
						<h3>Install Speed by WordKeeper</h3>
						<p>Site speed is more than just hosting and backend code speed.  It's also about keeping your site fast for slower devices with frontend optimizations for slower devices.  Install Speed by WordKeeper to access frontend optimization features like image, video, CSS, and JavaScript lazyloading, code minification/combination, and more.  Click <strong>Install Now</strong> to start using it today. </p>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
		<div class="postbox-widget">
			<div class="holder-title-widget">
				<h2>Heartbeat</h2>
			</div>
			<div class="inside">
				<form action="#">
					<div class="list-form-item">
						<div class="row">
							<div class="col-left w320">
								<label class="form-control" for="wp-heartbeat-frequency">
									Heartbeat Frequency (Seconds)
								</label>
								<span class="tooltips" data-template="item-wp-heartbeat-frequency" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-wp-heartbeat-frequency">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>WordPress heartbeat routinely phones home to the server to check on the site's status and relevant changes.<br /><br />  This is often unnecessary and can create slowness or instability in some situations (like having multiple tabs open).  Reducing its frequency can help alleviate those problems. </p>
								</div>
							</div>
							<div class="col-right">
								<div class="holder-single-select">
									<select id="wp-heartbeat-frequency" name="wp/heartbeat/frequency" class="single-select">
									<?php foreach($options['wp/heartbeat/frequency'] as $value => $name): ?>
									<option value="<?php echo esc_html($value); ?>"<?php if($value == $settings['wp/heartbeat/frequency']): ?> selected<?php endif; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div class="list-form-item">
						<div class="row">
							<div class="col-left w320">
								<label class="form-control" for="wp-heartbeat-limits">
									Heartbeat Limitations
								</label>
								<span class="tooltips" data-template="item-wp-heartbeat-limits" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-wp-heartbeat-limits">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>WordPress heartbeat routinely phones home to the server to check on the site's status and relevant changes.<br /><br />  This is often unnecessary and can create slowness or instability in some situations (like having multiple tabs open).  Limiting the areas where you use heartbeat to the extent possible can help alleviate those problems. </p>
								</div>
							</div>
							<div class="col-right">
								<div class="holder-single-select">
									<select id="wp-heartbeat-limits" name="wp/heartbeat/limits" class="single-select">
									<?php foreach($options['wp/heartbeat/limits'] as $value => $name): ?>
									<option value="<?php echo esc_html($value); ?>"<?php if($value == $settings['wp/heartbeat/limits']): ?> selected<?php endif; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<button type="button" class="btn button-primary sweetalert-btn" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
		<div class="postbox-widget">
			<div class="holder-title-widget">
				<h2>WordPress Image Editor</h2>
			</div>
			<div class="inside">
				<form action="#">
					<div class="list-form-item">
						<div class="row">
							<div class="col-left w320">
								<label class="form-control" for="wp-image-editor">
									Preferred Image Editor
								</label>
								<span class="tooltips" data-template="item-wp-image-editor" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-wp-image-editor">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>WordPress natively supports two image editors:<br /><br /></p>
									<p><strong>Imagick</strong>: WP's default editor.  Slightly higher quality editor at the cost of much slower image uploads and editing.  Can drastically slow down big image uploads (like PNGs).<br /><br /></p>
									<p><strong>GD</strong>: Much faster image uploads and editing (particularly for large images) at the cost of slightly lower image quality.<br /></p>
								</div>
							</div>
							<div class="col-right">
								<div class="holder-single-select">
									<select id="wp-image-editor" name="wp/image/editor" class="single-select">
									<?php foreach($options['wp/image/editor'] as $value => $name): ?>
									<option value="<?php echo esc_html($value); ?>"<?php if($value == $settings['wp/image/editor']): ?> selected<?php endif; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<button type="button" class="btn button-primary sweetalert-btn" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
		<div class="postbox-widget">
			<div class="holder-title-widget">
				<h2>Single Language Mode (US English Only)</h2>
			</div>
			<div class="inside">
				<form action="#">
					<div class="list-form-item">
						<div class="row">
							<div class="col-left w320">
								<label class="form-control" for="wp-translation">
									Single Language Mode:
								</label>
								<span class="tooltips" data-template="item-wp-translation" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-wp-translation">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>Single Language Mode is for "US English Only" websites. <br /><br />
									"Disabled" option disables WordPress translations in WordPress which is the default WordPress behavior. <br /><br />
									"Enabled" option enables WordPress translations in WordPress.<br /></p>
								</div>
							</div>
							<div class="col-right">
								<div class="holder-single-select">
									<select id="wp-translation" name="wp/translation" class="single-select">
									<?php
									foreach($options['wp/translation'] as $value => $name): ?>
									<option value="<?php echo esc_html($value); ?>"<?php if(isset($settings['wp/translation']) && $value == $settings['wp/translation']): ?> selected<?php endif; ?>><?php echo esc_html($name); ?></option>
									<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
					</div>
					<button type="button" class="btn button-primary sweetalert-btn" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
	</div>
</div>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>