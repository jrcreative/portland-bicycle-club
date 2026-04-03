<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>WP Cron</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-two-col">
				<li class="row">
					<div class="col-left w200">
						<label class="form-control" for="wp-cron">
						CLI Cron Frequency
						</label>
						<span class="tooltips" data-template="item-wp-cron" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-wp-cron">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Determines how often the server manually runs WP's scheduled tasks process as a CLI process.  <br /><br />Does NOT affect WP's native cron timing.  <br /><br />CLI cron frequency options vary based on your service tier.</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-single-select">
							<select name="wp/cron" id="wp-cron" class="single-select">
								<?php foreach($options['wp/cron'] as $cron => $label): ?>
								<option value="<?php echo esc_html($cron); ?>" <?php if($cron == $settings['wp/cron']): ?> selected<?php endif; ?> <?php if((((int) $cron) / 60) < $limits['cron']): ?>disabled <?php endif; ?>><?php echo esc_html($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</li>
				<li class="row">
					<div class="col-left w200">
						<label class="form-control" for="wp-cron-web">
						Web Cron
						</label>
						<span class="tooltips" data-template="item-wp-cron-web" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-wp-cron-web">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Enable or disable WP's native web-based cron process to prevent them it affecting frontend site speed/stability.  CLI crons will still run on schedule.<br /><br />Ideally it's best to disable it for busier/heavier sites.  </p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-single-select">
							<select name="wp/cron/web" id="wp-cron-web" class="single-select">
								<?php foreach($options['wp/cron/web'] as $cron => $label): ?>
								<option value="<?php echo esc_html($cron); ?>" <?php if($cron == $settings['wp/cron/web']): ?> selected<?php endif; ?>><?php echo esc_html($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</li>
			</ul>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>