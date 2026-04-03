<form id="form-login-protect" action="#">
	<div class="postbox-widget">
		<div class="holder-title-widget">
			<h2>Login & Registration</h2>
		</div>
		<div class="inside">
			<div class="list-form-item">
				<label class="form-control disabled" for="login-bots">
					<input class="checkbox-openclose" type="checkbox" id="login-bots" checked disabled />
					Block Bot Logins and Password Resets
				</label>
				<span class="tooltips" data-template="item-login-bots" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-login-bots">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Obvious bots are automatically blocked from attempting to log into your site or request a password reset</p>
				</div>
			</div>
			<hr />
		</div>
		<div class="inside radio-holder allow-from" <?php if(isset($settings['login/protect']) && !empty($settings['login/protect'])): ?>style="padding-bottom: 0px;"<?php endif; ?>>
			<h3>Allow From:</h3>
			<div class="list-radio openclose">
				<div class="list-form-item" style="margin-bottom: 18px;">
					<label>
						<input type="radio" value="0" name="login/protect" id="login-open" class="checkbox-openclose" data-type="toggle"  data-show="block-login-protect" data-showtype="close"<?php if(!isset($settings['login/protect']) || empty($settings['login/protect'])): ?> checked<?php endif; ?> />
						Anywhere
					</label>
					<span class="tooltips" data-template="item-login-open" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-login-open">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Allow logins, password resets, and new account registrations from anywhere</p>
					</div>
				</div>
				<div class="list-form-item" style="margin-bottom: 18px;">
					<label>
						<input type="radio" value="1" name="login/protect" id="login-protect" class="checkbox-openclose" data-type="toggle"  data-show="block-login-protect" data-showtype="open"<?php if(isset($settings['login/protect']) && !empty($settings['login/protect'])): ?> checked<?php endif; ?> />
						Specific Countries
					</label>
					<span class="tooltips" data-template="item-login-protect" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-login-protect">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Only allow logins, password resets, and/or new account registrations from approved countries</p>
					</div>
				</div>
			</div>
			<div class="content-open-close" style="margin-bottom: 0px; <?php if($settings['login/protect'] == false): ?>display: none;<?php endif; ?>" id="block-login-protect">
				<div class="row">
					<div class="col" style="width: auto;">
						<div class="holder-input">
							<div class="holder-multiple-select w320">
								<select class="multiple-select" name="protect-countries" id="protect-countries" multiple required>
									<?php foreach($countries as $country_code => $country): ?>
										<option value="<?php echo esc_attr($country_code); ?>"<?php echo esc_attr(in_array($country_code, $selected_countries) ? 'selected' : ''); ?>><?php echo esc_html($country); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="text-error">Error Message</div>
								<input type="hidden" id="protect-whitelist" value="<?php echo esc_html(implode(',', $selected_countries)); ?>" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="inside" >
			<div id="login-apply-to-container" <?php if(isset($settings['login/protect']) && $settings['login/protect'] == false): ?> style="display: none;" <?php endif; ?>>
				<h3 style="margin-bottom: 0px;">Apply To:</h3>
				<div class="list-form-item">
					<div class="row">
						<div class="col">
							<div class="holder-input">
								<ul style="margin-left: 30px;">
									<li>
										<label class="tiny-checkbox-label">
											<input class="tiny-checkbox" type="checkbox" name="login/restrict" id="login-restrict" <?php if(isset($settings['login/protect']) && !empty($settings['login/protect'])): ?>data-checkboxgroup<?php endif; ?> <?php if($settings['login/restrict'] === true): ?>checked<?php endif; ?> />
											<strong>Logins</strong>
										</label>
										<div id="login-whitelist-wrapper">
											<?php if($settings['login/restrict'] && $settings['login/whitelist']): ?>
											<input type="hidden" id="login-whitelist" name="login/whitelist" value="<?php echo esc_html(implode(',', $selected_countries));?>" />
											<?php endif;?>
										</div>
									</li>
									<li>
										<label class="tiny-checkbox-label">
											<input class="tiny-checkbox" type="checkbox" name="reset/restrict" id="reset-restrict" <?php if(isset($settings['login/protect']) && !empty($settings['login/protect'])): ?>data-checkboxgroup<?php endif; ?> <?php if($settings['reset/restrict'] === true): ?>checked<?php endif; ?> />
											<strong>Password Resets</strong>
										</label>
										<div id="reset-whitelist-wrapper">
											<?php if( $settings['reset/restrict'] && $settings['reset/whitelist']): ?>
												<input type="hidden" id="reset-whitelist" name="reset/whitelist" value="<?php echo esc_html(implode(',', $selected_countries));?>" />
											<?php endif;?>
										</div>
									</li>
									<li>
										<label class="tiny-checkbox-label">
											<input class="tiny-checkbox" type="checkbox" name="register/restrict" id="register-restrict" <?php if(isset($settings['login/protect']) && !empty($settings['login/protect'])): ?>data-checkboxgroup<?php endif; ?> <?php if($settings['register/restrict'] === true): ?>checked<?php endif; ?> />
											<strong>Account Registrations</strong>
										</label>
										<div id="register-whitelist-wrapper">
											<?php if( $settings['register/restrict'] && $settings['register/whitelist']): ?>
												<input type="hidden" id="register-whitelist" name="register/whitelist" value="<?php echo esc_html(implode(',', $selected_countries));?>" />
											<?php endif;?>
										</div>
									</li>
								</ul>
								<div name="checkboxgroup"></div>
								<div class="text-error">Error Message</div>
								<div class="text-info" style="margin-top: 10px; margin-left: 30px;">
									<p><strong>Selection:</strong>: At least one is required</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</div>
	</div>
</form>