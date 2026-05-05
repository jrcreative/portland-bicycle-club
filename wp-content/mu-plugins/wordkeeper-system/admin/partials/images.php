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
			<h1>Images</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="speed-optimization">
		<form>
			<div class="automatic-box holder-with-tooltip">
				<h2 class="title">Automatic Image Optimization Settings</h2>
				<span class="tooltips" data-template="image-optimization" role="button" tabindex="0" aria-expanded="false">?</span>
				<div class="tooltips-block tippy-popper" id="image-optimization">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Optimizing images ensures faster load times for your site and better use of your available storage space.  Consequently, it's best to enable some form of image optimization whether you want to limit those to industry best practices or customize them to your exact needs.</p>
					<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-image-optimization'>Watch Video</a>
				</div>
				<p>Optimizing images ensures faster load times for your site and better use of your available storage space.  Consequently, it's best to enable some form of image optimization.  Recommended settings use lossless optimization techniques, a JPG quality of 80%, and automatic conversion of opaque PNG images to JPGS, but you can use Advanced settings to customize your preferred image optimization.  For supported servers, Recommended settings apply immediately as images are uploaded.  Otherwise, they apply during daily checks for newly uploaded images.  Only Daily image optimization is available for shared hosting customers.</p>
				<div class="radio-holder">
					<ul class="list-radio openclose">
						<li>
							<input type="radio" name="images/optimize" value="recommended" id="recommended" data-type="toggle" data-show="advanced-option" data-showtype="close"<?php if($settings['images/optimize'] == 'recommended'): ?> checked<?php endif; ?>>
							<label for="recommended">Recommended</label>
						</li>
						<li>
							<input type="radio" name="images/optimize" value="advanced" id="advanced" data-type="toggle" data-show="advanced-option" data-showtype="open"<?php if($settings['images/optimize'] == 'advanced'): ?> checked<?php endif; ?>>
							<label for="advanced">Advanced</label>
						</li>
						<li>
							<input type="radio" name="images/optimize" value="off" id="off" data-type="toggle" data-show="advanced-option" data-showtype="close"<?php if($settings['images/optimize'] == 'off'): ?> checked<?php endif; ?>>
							<label for="off">Off</label>
						</li>
					</ul>
				</div>
				<div class="advanced-option" id="advanced-option" <?php if($settings['images/optimize'] == 'advanced'): ?> style="display: block;"<?php endif; ?>>
					<div class="list-form-item">
						<label class="form-control">
							Frequency
						</label>
						<span class="tooltips" data-template="item-images-frequency" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-images-frequency">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p><strong>Daily</strong>: Finds and optimizes images newly uploaded images on a daily schedule.<br /><br /></p>
							<p><strong>Immediately</strong>: Immediately optimizes newly uploaded images as they are uploaded (best for sites that use CDN's).<br /></p>
						</div>
						<ul class="list-radio openclose">
							<li>
								<input name="images/frequency" type="radio" id="daily" value="daily" data-type="toggle" data-show="block-images-frequency" data-showtype="close"<?php if($settings['images/frequency'] == 'daily'): ?> checked<?php endif; ?>>
								<label for="daily">Daily</label>
							</li>
							<li>
								<input name="images/frequency" type="radio" id="immediately" value="immediately" data-type="toggle" data-show="block-images-frequency" data-showtype="open"<?php if($settings['images/frequency'] == 'immediately' && $limits['images'] == 'ondemand'): ?> checked<?php endif; ?><?php if($limits['images'] != 'ondemand'): ?> disabled data-unavailable<?php endif; ?>>
								<label for="immediately">Immediately</label>
							</li>
						</ul>
					</div>
					<div class="list-form-item">
						<label class="form-control">
							Image Quality Settings
						</label>
						<span class="tooltips" data-template="item-images-quality-type" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-images-quality-type">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p><strong>Lossless</strong>: Minimal optimization w/ minimal file size reduction.  Mainly strips image metadata.<br /><br /></p>
							<p><strong>Lossy</strong>: Custom quality settings.  Higher quality means larger file size.  Lower quality means smaller file sizes but too low can mean blurrier images.  80% quality is recommended.<br /></p>
						</div>
					</div>
					<div class="content-open-close">
						<ul class="list-radio openclose">
							<li>
								<input name="images/quality/type" type="radio" id="lossless" value="lossless" data-type="toggle" data-show="block-images-quality" data-showtype="close" <?php if($settings['images/quality/type'] == 'lossless'): ?> checked<?php endif; ?>>
								<label for="lossless">Lossless</label>
							</li>
							<li>
								<input name="images/quality/type" type="radio" id="lossy" value="lossy" data-type="toggle" data-show="block-images-quality" data-showtype="open"<?php if($settings['images/quality/type'] == 'lossy'): ?> checked<?php endif; ?>>
								<label for="lossy">Lossy</label>
							</li>
						</ul>
						<div class="content-open-close"<?php if($settings['images/quality/type'] != 'lossy'): ?> style="display: none;"<?php endif; ?> id="block-images-quality">
							<label class="range-slider" for="images-quality-setting">Select Image Quality (Default Is 80%)</label>
							<input type="range" value="<?php echo esc_html($settings['images/quality/setting']); ?>" min="0" max="100" name="images/quality/setting" id="images-quality-setting" oninput="rangevalue.value=value+'%'"<?php if($settings['images/quality/type'] != 'lossy'): ?> disabled<?php endif; ?> />
							<output id="rangevalue"><?php echo esc_html($settings['images/quality/setting']); ?>%</output>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="images-opaque">
							<input type="checkbox" name="images/opaque" id="images-opaque"<?php if($settings['images/optimize'] != 'advanced'): ?> disabled<?php endif; ?><?php if($settings['images/opaque'] == true): ?> checked<?php endif; ?> />
							Convert Opaque PNGs to JPGs
						</label>
						<span class="tooltips" data-template="item-images-opaque" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-images-opaque">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>JPGs are smaller than PNGs but do not support transparent backgrounds.  PNG's without background transparency can be converted to JPG's to significantly reduce file size without losing quality.</p>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control">
							<input type="checkbox" name="images/resize" id="images-resize" class="checkbox-openclose" data-show="block-resize-settings"<?php if($settings['images/optimize'] != 'advanced'): ?> disabled<?php endif; ?><?php if($settings['images/resize'] == true): ?> checked<?php endif; ?> />
							Image Resize Settings
						</label>
						<span class="tooltips" data-template="item-images-resize" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-images-resize">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Sites rarely use images larger than 2500px x 2500px but it's common for site editors to upload images that are much larger.  Reducing images that are larger than your site's maximum image size can reduce the size of images without affecting quality.</p>
						</div>
					</div>
					<div class="content-open-close"<?php if($settings['images/resize'] == false): ?> style="display: none;"<?php endif; ?> id="block-resize-settings">
						<div class="row">
							<div class="col holder-input">
								<label class="form-control">
									Resize Images Wider Than
									<input type="text" name="images/width/threshold" id="images-width-threshold" value="<?php echo esc_html($settings['images/width/threshold']); ?>" placeholder="0000px" data-validate="minimum" data-minimum="800" data-no-validate="Image width must be at least 800px" data-filter="[^0-9pPxX]" <?php if($settings['images/resize'] == false): ?> disabled<?php endif; ?> />
									<div class="text-error">Error Message</div>
								</label>
							</div>
							<div class="col holder-input">
								<label class="form-control">
									to
									<span id="images-width-max" style="display: inline-block"><?php echo !empty($settings['images/width/threshold']) ? esc_html($settings['images/width/threshold']) : '...'; ?></span>
									<div class="text-error">Error Message</div>
								</label>
							</div>
						</div>
						<div class="row">
							<div class="col holder-input">
								<label class="form-control">
									Resize Images Taller Than&nbsp;
									<input type="text" name="images/height/threshold" id="images-height-threshold" value="<?php echo esc_html($settings['images/height/threshold']); ?>" placeholder="0000px" data-validate="minimum" data-minimum="800" data-no-validate="Image height must be at least 800px" data-filter="[^0-9pPxX]" <?php if($settings['images/resize'] == false): ?> disabled<?php endif; ?> />
									<div class="text-error">Error Message</div>
								</label>
							</div>
							<div class="col holder-input">
								<label class="form-control">
									to
									<span id="images-height-max" style="display: inline-block"><?php echo !empty($settings['images/height/threshold']) ? esc_html($settings['images/height/threshold']) : '...'; ?></span>
									<div class="text-error">Error Message</div>
								</label>
							</div>
						</div>
					</div>
				</div>
				<button type="button" class="btn button-primary sweetalert-btn" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
			</div>
		</form>
	</div>
</div>

<template id='template-video-image-optimization'>
	<swal-html>
		<div class="holder-backup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026978343?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Image Optimization"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>