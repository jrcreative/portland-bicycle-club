<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Form Settings</h2>
	</div>
	<div class="inside radio-holder">
		<form action="#">
			<p>Allow form submissions from:</p>
			<div class="list-radio openclose">
				<div class="list-form-item">
					<label class="form-control">
						<input type="radio" value="0" name="forms/restrict" id="forms-unrestricted" class="checkbox-openclose" data-type="toggle"  data-show="block-forms-from" data-showtype="close"<?php if(!isset($settings['forms/restrict']) || empty($settings['forms/restrict'])): ?> checked<?php endif; ?> />
						Anywhere
					</label>
					<span class="tooltips" data-template="item-forms-unrestricted" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-forms-unrestricted">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Allow form submissions from any country.  <br /><br />Affects: Contact Form 7, Gravity Forms, Elementor, WPForms, Forminator Forms, Formidable Forms, Ninja Forms, Fluent Forms, MC4WP and bbPress Topics/Replies</p>
					</div>
				</div>
				<div class="list-form-item">
					<label class="form-control">
						<input type="radio" value="1" name="forms/restrict" id="forms-restrict" class="checkbox-openclose" data-type="toggle"  data-show="block-forms-from" data-showtype="open"<?php if(isset($settings['forms/restrict']) && !empty($settings['forms/restrict'])): ?> checked<?php endif; ?> />
						Specific Countries
					</label>
					<span class="tooltips" data-template="item-forms-restrict" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-forms-restrict">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Only allow form submissions from the specified countries.  <br /><br />Affects: Contact Form 7, Gravity Forms, Elementor, WPForms, Forminator Forms, Formidable Forms, Ninja Forms, Fluent Forms, MC4WP and bbPress Topics/Replies</p>
					</div>
				</div>
			</div>
			<div class="content-open-close"<?php if($settings['forms/restrict'] == false): ?> style="display: none;"<?php endif; ?> id="block-forms-from">
				<div class="row">
					<div class="col holder-block">
						<h3 style="display: inline;"><label class="form-control">Allowed Countries</label></h3>
						<div class="holder-multiple-select">
							<select class="multiple-select" name="forms/whitelist" id="forms-whitelist" multiple>
								<?php foreach($countries as $country_code => $country): ?>
									<option value="<?php echo esc_attr($country_code); ?>" <?php echo esc_attr(in_array($country_code, $settings['forms/whitelist']) ? 'selected' : ''); ?>><?php echo esc_html($country); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>