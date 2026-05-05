<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Update Management</h2>
	</div>
	<div class="inside">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="wp-updates-core">
					<input type="checkbox" name="wp/updates/core" id="wp-updates-core"
						   <?php if($settings['wp/updates/core'] === true): ?>checked<?php endif; ?> />
					Automatically Install Core WP Updates
				</label>
				<span class="tooltips" data-template="item-wp-updates-core">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-updates-core">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p><strong>Minor Updates</strong>: Automatically install bug fix and security patch updates to WP
						core (no theme updates).</p>
					<p>&nbsp;</p>
					<p><strong>Major Updates</strong>: Install curated and approved safe major updates to WP core (no
						theme updates).</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="wp-updates-plugins">
					<input type="checkbox" name="wp/updates/plugins" id="wp-updates-plugins"
						   <?php if($settings['wp/updates/plugins'] === true): ?>checked<?php endif; ?> />
					Automatically Install Curated Plugin Updates
				</label>
				<span class="tooltips" data-template="item-wp-updates-plugins">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-updates-plugins">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p><strong>Minor Updates</strong>: Automatically install bug fix and security patch updates to
						plugins (no theme updates).</p>
					<p>&nbsp;</p>
					<p><strong>Major Updates</strong>: Install curated and approved safe major updates to plugins once
						monthly (no theme updates).</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control">
					<input type="checkbox" name="wp/updates/exclusions" id="wp-plugins-exclusions"
						   <?php if($settings['wp/updates/exclusions'] === true): ?>checked<?php endif; ?>
						   class="checkbox-openclose" data-show="block-wp-plugins-exclusions"/>
					Exclude Specific Plugins
				</label>
				<span class="tooltips" data-template="item-wp-plugins-exclusions">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-plugins-exclusions">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Prevent problem plugins from automatically updating when approved in curated updates</p>
				</div>
			</div>
			<div class="content-open-close"
				 style="display: <?php if($settings['wp/updates/exclusions'] === true): ?>block<?php else: ?>none<?php endif; ?>"
				 id="block-wp-plugins-exclusions">
				<p>Choose the plugins to ignore when curated updates are installed</p>
				<div class="custom-select-block">
					<?php
					$exclusions_plugin_list = explode(',', $settings['wp/updates/exclusions/list']); ?>
					<select class="my-select" multiple name="wp/updates/exclusions/list" id="wp-updates-exclusions-list"
							data-dual-listbox="true">
						<?php foreach($plugins as $plugin => $data): ?>
							<option value="<?php echo esc_html($plugin); ?>"
									<?php if(in_array($plugin, $exclusions_plugin_list) === true): ?>selected<?php endif; ?>><?php echo esc_html($data['Name']); ?></option>
						<?php endforeach; ?>
						<option value="fakeplugin/fakeplugin.php">fake plugin - this plugin does not exist</option>
					</select>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit
					data-waiting="Saving" data-success="Saved" data-path="updates/save">Save
			</button>
		</form>
	</div>
</div>
