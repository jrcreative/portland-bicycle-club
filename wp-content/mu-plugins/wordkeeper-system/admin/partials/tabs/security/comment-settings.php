<div class="postbox-widget">
	<div class="holder-title-widget holder-with-tooltip">
		<h2>Comment Settings</h2>
        <span class="tooltips" data-template="comments-settings" role="button" tabindex="0" aria-expanded="false">?</span>
        <div class="tooltips-block tippy-popper" id="comments-settings">
            <a class="close" href="#">close</a>
            <div class="tippy-arrow"></div>
            <p>By default, WordPress allows comment submissions on blog posts you create.  If your site doesn't have a blog or doesn't need comments, you can reduce unwanted comment spam and submissions on your site by disabling or limiting comments, pingbacks, and trackbacks.</p>
            <a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-comments'>Watch Video</a>
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
			<h2 class="title">Allow comments from:</h2>
			<div class="list-radio openclose">
				<div class="list-form-item">
					<label class="form-control">
						<input type="radio" value="0" name="comment/restrict" id="comment-unrestricted" class="checkbox-openclose" data-type="toggle"  data-show="block-comment-from" data-showtype="close"<?php if(!isset($settings['comment/restrict']) || empty($settings['comment/restrict'])): ?> checked<?php endif; ?> />
						Anywhere
					</label>
					<span class="tooltips" data-template="item-comment-unrestricted" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-comment-unrestricted">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Allow comments from any country</p>
					</div>
				</div>
				<div class="list-form-item">
					<label class="form-control">
						<input type="radio" value="1" name="comment/restrict" id="comment-restrict" class="checkbox-openclose" data-type="toggle"  data-show="block-comment-from" data-showtype="open"<?php if(isset($settings['comment/restrict']) && !empty($settings['comment/restrict'])): ?> checked<?php endif; ?> />
						Specific Countries
					</label>
					<span class="tooltips" data-template="item-comment-restrict" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-comment-restrict">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Only allow comments from the specified countries</p>
					</div>
				</div>
			</div>
			<div class="content-open-close"<?php if($settings['comment/restrict'] == false): ?> style="display: none;"<?php endif; ?> id="block-comment-from">
				<div class="row">
					<div class="col holder-block">
						<h3 style="display: inline;"><label class="form-control">Allowed Countries</label></h3>
						<div class="holder-multiple-select">
							<select class="multiple-select" name="comment/whitelist" id="comment-whitelist" multiple>
								<?php foreach($countries as $country_code => $country): ?>
									<option value="<?php echo esc_attr($country_code); ?>" <?php echo esc_attr(in_array($country_code, $settings['comment/whitelist']) ? 'selected' : ''); ?>><?php echo esc_html($country); ?></option>
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

<template id='template-video-comments'>
    <swal-html>
        <div class="holder-backup">
            <div style="padding-top: 56.25%; position: relative">
                <iframe src="https://player.vimeo.com/video/1026992248?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Comments"></iframe>
            </div>
        </div>
    </swal-html>
</template>