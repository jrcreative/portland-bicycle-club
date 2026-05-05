<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>General</h2>
	</div>
	<div class="inside">
		<p>
			Spam comes in many forms. Comment spam, user registration spam, and general form submission spam. Usually, it’s best to block all of those types of spam but you can disable spam blocking for each here if needed. Since spam can vary wildly, you can also block suspicious submissions that aren’t 100% identifiable as spam but that have most of the hallmarks of spam with suspicious submission blocking.
		</p>
		<hr />
	</div>
	<div class="inside" style="padding-top:0px">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="bot-restrict">
					<input type="checkbox" name="bot/restrict" id="bot-restrict" <?php if(isset($settings['bot/restrict']) && $settings['bot/restrict'] === true): ?>checked<?php endif; ?> />
					Block Bot Submissions
				</label>
				<span class="tooltips" data-template="item-bot-restrict" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-bot-restrict">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>
						By default WordPress doesn't attempt to prevent bots from registering new accounts or from submitting forms or comments.  To block provably detected bots from doing this, check the boxes for the types of bot submissions you want to block below.<br /><br />
						Account registrations affects all new accounts registered in WordPress.  <br /><br />
						Comments affects WordPress's core comment system. <br /><br />
						Form Submissions affects Contact Form 7, Gravity Forms, Elementor, WPForms, Forminator Forms, Formidable Forms, Ninja Forms, Fluent Forms, MC4WP and bbPress Topics/Replies
					</p>
				</div>
				<div class="list-form-item">
					<ul style="margin-left: 30px;">
						<li>
							<label class="tiny-checkbox-label" for="bot-register">
								<input class="tiny-checkbox" type="checkbox" name="bot/register" id="bot-register" <?php if(isset($settings['bot/register']) && $settings['bot/register'] === true): ?>checked<?php endif; ?> />
								<strong>Account Registrations</strong>
							</label>
						</li>
						<li>
							<label class="tiny-checkbox-label" for="bot-forms">
								<input class="tiny-checkbox" type="checkbox" name="bot/forms" id="bot-forms" <?php if(isset($settings['bot/forms']) && $settings['bot/forms'] === true): ?>checked<?php endif; ?> />
								<strong>Form Submissions</strong>
							</label>
						</li>
						<li>
							<label class="tiny-checkbox-label" for="bot-comments">
								<input class="tiny-checkbox" type="checkbox" name="bot/comments" id="bot-comments" <?php if(isset($settings['bot/comments']) && $settings['bot/comments'] === true): ?>checked<?php endif; ?> />
								<strong>Comments</strong>
							</label>
						</li>
					</ul>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>

<div class="postbox-widget">
	<div class="holder-title-widget holder-with-tooltip">
		<h2>Comment Settings</h2>
		<span class="tooltips" data-template="comments-settings" role="button" tabindex="0" aria-expanded="false">?</span>
		<div class="tooltips-block tippy-popper" id="comments-settings">
			<a class="close" href="#">close</a>
			<div class="tippy-arrow"></div>
			<p>By default, WordPress allows comment submissions on blog posts you create.  If your site doesn't have a blog or doesn't need comments, you can reduce unwanted comment spam and submissions on your site by disabling or limiting comments, pingbacks, and trackbacks.</p>
			<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-comments'>Watch Video</a>
		</div>
	</div>
	<div class="inside radio-holder">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="comments-block">
					<input type="checkbox" name="comments/block" id="comments-block" <?php if($settings['comments/block'] === true): ?>checked<?php endif; ?> />
					Disable All Comments
				</label>
				<span class="tooltips" data-template="item-comments-block" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-comments-block">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>If you don't need comments on your site, there's no better way to block spam than to block all comments. </p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="trackbacks-block">
					<input type="checkbox" name="trackbacks/block" id="trackbacks-block" <?php if($settings['trackbacks/block'] === true): ?>checked<?php endif; ?> />
					Block Trackbacks
				</label>
				<span class="tooltips" data-template="item-trackbacks-block" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-trackbacks-block">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Trackbacks allow commenters to link their comments on your site to their blog.  When approved, they appear as a comment on your blog.  <br /><br />Trackbacks aren't inherently spam but have become a favorite for comment spammers.  If you're receiving a lot of comment spam, you should strongly consider disabling trackbacks. </p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="pingbacks-block">
					<input type="checkbox" name="pingbacks/block" id="pingbacks-block" <?php if($settings['pingbacks/block'] === true): ?>checked<?php endif; ?> />
					Block Pingbacks
				</label>
				<span class="tooltips" data-template="item-pingbacks-block" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-pingbacks-block">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Pingbacks allow other bloggers to notify your blog when they write a post mentioning your article.  When approved, they appear as a comment on your blog.  <br /><br />Pingbacks aren't inherently spam but have become a favorite for comment spammers.  If you're receiving a lot of comment spam, you should strongly consider disabling pingbacks. </p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="comments-url">
					<input type="checkbox" name="comments/url" id="comments-url" <?php if($settings['comments/url'] === true): ?>checked<?php endif; ?> />
					Remove URL Field
				</label>
				<span class="tooltips" data-template="item-comments-url" role="button" tabindex="0">?</span>
				<div class="tooltips-block tippy-popper" id="item-comments-url">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Many spammers submit comments to advertise their URLs.  By removing the URL field from your comment forms and blocking submissions that include it can reduce total comment spam on the site.</p>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>

<div class="postbox-widget">
	<div class="holder-title-widget holder-with-tooltip">
		<h2>Geo Restrictions</h2>
		<span class="tooltips" data-template="geo-restriction-settings" role="button" tabindex="0" aria-expanded="false">?</span>
		<div class="tooltips-block tippy-popper" id="geo-restriction-settings">
			<a class="close" href="#">close</a>
			<div class="tippy-arrow"></div>
			<p>
				By default, WordPress allows comments and form submissions from any country.  Limiting submissions to just a list of approved countries blocks an entire category of out-of-country spam by rejecting from submissions outside of your approved countries.  <br /><br />
				Comments applies to WordPress core comments<br /><br />
				Form affects Contact Form 7, Gravity Forms, Elementor, WPForms, Forminator Forms, Formidable Forms, Ninja Forms, Fluent Forms, MC4WP and bbPress Topics/Replies
		</p>
		</div>
	</div>
	<form id="form-geo-restrictions" action="#">
		<div class="inside radio-holder allow-from" <?php if(isset($settings['geo/restrict']) && !empty($settings['geo/restrict'])): ?>style="padding-bottom: 0px;"<?php endif; ?>>
			<h2 class="title">Allow Form/Comment Submissions from:</h2>
			<div class="list-radio openclose">
				<div class="list-form-item" style="margin-bottom: 18px;">
					<label class="form-control">
						<input type="radio" value="0" name="geo/restrict" id="geo-unrestricted" class="checkbox-openclose" data-type="toggle"  data-show="block-geo-from" data-showtype="close" <?php if(!isset($settings['geo/restrict']) || empty($settings['geo/restrict'])): ?> checked<?php endif; ?> />
						Anywhere
					</label>
					<span class="tooltips" data-template="item-geo-unrestricted" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-geo-unrestricted">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Allow submissions from any country</p>
					</div>
				</div>
				<div class="list-form-item" style="margin-bottom: 18px;">
					<label class="form-control">
						<input type="radio" value="1" name="geo/restrict" id="geo-restrict" class="checkbox-openclose" data-type="toggle"  data-show="block-geo-from" data-showtype="open" <?php if(isset($settings['geo/restrict']) && !empty($settings['geo/restrict'])): ?> checked<?php endif; ?> />
						Specific Countries
					</label>
					<span class="tooltips" data-template="item-geo-restrict" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-geo-restrict">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Only allow submissions from the approved countries</p>
					</div>
				</div>
			</div>
			<div class="content-open-close" style="margin-bottom: 0px; <?php if(isset($settings['geo/restrict']) && $settings['geo/restrict'] == false): ?> display: none; <?php endif; ?>" id="block-geo-from">
				<div class="row">
					<div class="col" style="width: auto;">
						<div class="holder-input">
							<div class="holder-multiple-select w320">
								<select class="multiple-select" name="geo-restrict-whitelist-select" id="geo-restrict-whitelist-select" multiple required>
									<?php foreach($countries as $country_code => $country): ?>
										<option value="<?php echo esc_attr($country_code); ?>" <?php echo esc_attr(in_array($country_code, $selected_countries) ? 'selected' : ''); ?>> <?php echo esc_html($country); ?></option>
									<?php endforeach; ?>
								</select>
								<div class="text-error">Error Message</div>
								<input type="hidden" id="geo-restrict-whitelist-input" value="<?php echo esc_html(implode(',', $selected_countries)); ?>" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="inside" >
			<div class="list-form-item" id="geo-apply-to-container" <?php if(isset($settings['geo/restrict']) && $settings['geo/restrict'] == false): ?> style="display: none;" <?php endif; ?>>
				<h3 style="margin-bottom: 0px;">Apply To:</h3>
				<div class="list-form-item">
					<div class="row">
						<div class="col">
							<div class="holder-input">
								<ul style="margin-left: 30px;">
									<li>
										<label class="tiny-checkbox-label">
											<input class="tiny-checkbox" type="checkbox" name="comment/restrict" id="comment-restrict" <?php if(isset($settings['geo/restrict']) && !empty($settings['geo/restrict'])): ?>data-checkboxgroup<?php endif; ?> <?php if($settings['comment/restrict'] === true): ?>checked<?php endif; ?> />
											<strong>Comments</strong>
										</label>
										<div id="comment-whitelist-wrapper">
											<?php if($settings['comment/restrict'] && $settings['comment/whitelist']):?>
												<input type="hidden" name="comment/whitelist" id="comment-whitelist" value="<?php echo esc_html(implode(',', $selected_countries));?>" />
											<?php endif;?>
										</div>
									</li>
									<li>
										<label class="tiny-checkbox-label">
											<input class="tiny-checkbox" type="checkbox" name="forms/restrict" id="forms-restrict" <?php if(isset($settings['geo/restrict']) && !empty($settings['geo/restrict'])): ?>data-checkboxgroup<?php endif; ?> <?php if($settings['forms/restrict'] === true): ?>checked<?php endif; ?> />
											<strong>Forms</strong>
										</label>
										<div id="forms-whitelist-wrapper">
											<?php if($settings['forms/restrict'] && $settings['forms/whitelist']):?>
												<input type="hidden" name="forms/whitelist" id="forms-whitelist" value="<?php echo esc_html(implode(',', $selected_countries));?>" />
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
	</form>
</div>

<template id='template-comments'>
	<swal-html>
		<div class="holder-backup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026992248?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Comments"></iframe>
			</div>
		</div>
	</swal-html>
</template>
