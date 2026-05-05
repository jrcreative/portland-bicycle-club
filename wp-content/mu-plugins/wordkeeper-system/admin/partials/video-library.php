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
			<h2>Video Library</h2>
		</div>
		<div class="col">
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="page-holder-with-filters">
		<div class="holder-video-list">
			<?php
			$j = 1;
			foreach($videos as $video){
				?>
				<div class="item">
					<div class="holder-item">
						<a href="#" class="holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-<?php echo (int) $j; ?>' style="background-image: url('<?php echo esc_url_raw($video[3]); ?>')">play</a>
						<div class="holder-text">
							<h3><?php echo preg_replace('#[^0-9a-zA-Z\-\_\.\s,\?;:\'\"&\(\)+%!@\#\/\\\]#', '', $video[0]); ?></h3>
						</div>
					</div>
				</div>
				<?php
				$j++;
			}
			?>
		</div>
		<div>
			<?php
				if($pages > 1){
			?>
			<ul class="navigation">
				<li><a href="/wp-admin/admin.php?page=wordkeeper-system-video-library&page_num=1">First</a></li>
				<?php for($i=1; $i <= $pages; $i++){ ?>
					<li><a href="/wp-admin/admin.php?page=wordkeeper-system-video-library&page_num=<?php echo (int) $i; ?>"><?php echo (int) $i; ?></a></li>
				<?php } ?>
				<li><a href="/wp-admin/admin.php?page=wordkeeper-system-video-library&page_num=<?php echo (int) $pages; ?>">Last</a></li>
			</ul>
			<?php } ?>
		</div>
	</div>
</div>
<script src="https://player.vimeo.com/api/player.js"></script>
<?php
$j = 1;
foreach($videos as $video){
	$url = $video[1];
	?>
	<template id='template-video-<?php echo (int) $j; ?>'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="<?php echo str_replace('&amp;', '&', esc_url_raw($url)); ?>" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="<?php echo esc_attr($video[0]); ?>"></iframe>
				</div>
			</div>
		</swal-html>
	</template>
	<?php
	$j++;
}
?>
<!-- Video popup -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/video.php'; ?>