<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Cache Times</h2>
	</div>
	<div class="inside">
		<div class="description">
			WordKeeper automatically clears caches for individual posts whenever you update them and dynamically increases the amount of time that a post can be cached as more time passes without an update to the post.  But if you want to manually set the legnth of a single post cache, you can override the default below.
		</div>
		<form action="#">
			<ul class="list-two-col">
				<li class="row">
					<div class="col-left w220">
						<label class="form-control" for="cache-post">
							Post Cache Length
						</label>
						<span class="tooltips" data-template="item-cache-post" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-cache-post">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Length of time to cache single Posts</p>
						</div>
					</div>
					<div class="col-right">
						<div class="holder-single-select">
							<select name="cache/post" id="cache-post" class="single-select">
								<?php foreach($times as $time => $label): ?>
								<option value="<?php echo esc_html($time); ?>"<?php if($time == $settings['cache/post']): ?> selected<?php endif; ?>><?php echo esc_html($label); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</li>
				<li class="row">
					<div class="col-left w220">
						<label class="form-control" for="cache-page">
							Page Cache Length
						</label>
						<span class="tooltips" data-template="item-cache-page" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-cache-page">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Length of time to cache single Pages</p>
						</div>
					</div>
					<div class="col-right">
					<div class="holder-single-select">
							<select name="cache/page" id="cache-page" class="single-select">
							<?php foreach($times as $time => $label): ?>
								<option value="<?php echo esc_html($time); ?>"<?php if($time == $settings['cache/page']): ?> selected<?php endif; ?>><?php echo esc_html($label); ?></option>
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