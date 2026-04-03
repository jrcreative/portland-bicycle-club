<div class="postbox-widget">
	<div class="holder-title-widget holder-with-tooltip">
		<h2>PHP Settings</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-two-col">
				<li class="row">
					<div class="col-left w200">
						<label class="form-control" for="php-version">
							PHP Version
						</label>
						<span class="tooltips" data-template="item-php-version" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-php-version">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Set your site's preferred PHP version.  If the new PHP version creates a fatal error, it will automatically switch back to the current version to ensure site stability.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-single-select">
						<a class="link-dialog sweetalert2-popups" href="#" data-confirm data-waiting="Changing PHP..." data-dialog-template="#template-change-php">Change</a>
							<select name="php/version" id="php-version" class="single-select" disabled>
							<?php foreach($versions as $version): ?>
								<option value="<?php echo esc_html($version); ?>"<?php if(strpos(phpversion(), esc_html($version)) === 0): ?> selected<?php endif; ?>><?php echo esc_html($version); ?></option>
							<?php endforeach; ?>
							</select>
						</div>
					</div>
				</li>
				<li class="row">
					<div class="col-left w200">
						<label class="form-control" for="php-email">
							Outbound Email
						</label>
						<span class="tooltips" data-template="item-php-email" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-php-email">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>If you want to prevent PHP from sending any outbound email, you can block all email from the site entirely.  For staging and dev sites, outbound email is disabled by default to prevent things like newsletter plugins from sending live emails to subscribers.  But if you need to enable outbound mail or block it on a live site, just toggle it accordingly.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-single-select">
							<select name="php/email" id="php-email" class="single-select">
								<option value="true"<?php if($email === true): ?> selected<?php endif; ?>>Enable</option>
								<option value="false"<?php if($email === false): ?> selected<?php endif; ?>>Disable</option>
							</select>
						</div>
					</div>
				</li>
			</ul>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="php/email">Save</button>
		</form>
	</div>
</div>
<!--
<div class="postbox-widget">
	<div class="holder-title-widget holder-with-tooltip">
		<h2>Temporary PHP Settings</h2>
	</div>
	<div class="inside">
		<div class="description">
			Some themes and plugins may temporarily require PHP settings that aren't safe to use on a permanent basis.  This is common with theme demo content imports, for example.  Using the settings below, you can temporarily increase these settings to appease that process.  If you believe that you need a permanent change, please contact support.
		</div>
		<form action="#">
			<ul class="list-two-col" data-inputlist="temporary-php">
				<li class="row">
					<div class="col-left w220">
						<label class="form-control" for="php-max-execution">
							Max Execution Time
						</label>
						<span class="tooltips" data-template="item-php-max-execution" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-php-max-execution">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The maximum amount of time (in seconds) that a PHP request is allowed to process before being closed as a non-responsive request.  For security and stability reasons, less is more.  30s is industry standard for best security and stability.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-input">
							<input type="text" name="php/max-execution" id="php-max-execution" placeholder="Time in seconds (Currently set to: <?php echo esc_html(ini_get('max_execution_time')); ?>s)" data-validate="regex" data-regex="^[0-9]{1,3}[sS]?$" data-no-validate="Field must be a number of seconds (less than 1000)" data-filter="[^0-9sS]" />
							<div class="text-error">Error Message</div>
						</div>
					</div>
				</li>
				<li class="row">
					<div class="col-left w220">
						<label class="form-control" for="php-max-upload">
							Max File Upload Size
						</label>
						<span class="tooltips" data-template="item-php-max-upload" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-php-max-upload">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The maximum file size (in MB) that can be uploaded to a PHP request.  For security and stablity reasons, less is more.  40MB is enough for the vast majority of sites.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-input">
							<input type="text" name="php/max-upload" id="php-max-upload" placeholder="Size in MB (Currently set to: <?php echo esc_html(ini_get('upload_max_filesize')); ?>)" data-validate="regex" data-regex="^[0-9]{1,3}[mM]?$" data-no-validate="Field must be a number of MB (less than 1000)" data-filter="[^0-9mM]" />
							<div class="text-error">Error Message</div>
						</div>
					</div>
				</li>
				<li class="row">
					<div class="col-left w220">
						<label class="form-control" for="php-max-post">
							Max Post Size
						</label>
						<span class="tooltips" data-template="item-php-max-post" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-php-max-post">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The maximum total POST size (in MB) that can be submitted to a PHP request.  Generally this needs to match the max file upload size.  For security and stablity reasons, less is more.  40MB is enough for the vast majority of sites.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-input">
							<input type="text" name="php/max-post" id="php-max-post" placeholder="Size in MB (Currently set to: <?php echo esc_html(ini_get('post_max_size')); ?>)" data-validate="regex" data-regex="^[0-9]{1,3}[mM]?$" data-filter="[^0-9mM]" data-no-validate="Field must be a number of MB (less than 1000)">
							<div class="text-error">Error Message</div>
						</div>
					</div>
				</li>
			</ul>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="php">Save</button>
		</form>
	</div>
</div>
-->

<!-- Change PHP Template -->
<?php include plugin_dir_path(__FILE__) . '../../dialogs/change-php.php'; ?>

<!-- Error Notification -->
<?php include plugin_dir_path(__FILE__) . '../../notifications/error.php'; ?>