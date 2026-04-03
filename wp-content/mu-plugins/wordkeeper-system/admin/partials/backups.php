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
			<h2>Backups</h2>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="backups">
		<div class="top-holder">
			<div class="col">
				<button class="btn button-primary sweetalert2-popups" name="create-backup" id="create-backup" data-confirm data-waiting="Creating Backup..." data-dialog-template="#template-create-backup">Create Backup</button>
			</div>
			<div class="col">
				<button class="btn button-primary more-info tooltip-moreinfo" name="more-info" id="more-info" data-template="item-more-info" role="button" tabindex="0" aria-expanded="false">More Info
					<span class="tooltips" data-template="item-more-info" role="button" tabindex="0" aria-expanded="false">?</span>
					<div class="tooltips-block tippy-popper" id="item-more-info">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>WordKeeper's backup service includes 30 total backups of your site and automatically creates a new backup each day.  By default, that's 30 days worth of backups. But if you need to, you can create a manual backup of your site before major changes as well.<br /><br />If you need to restore all or part of any backup to your site, you can do so by finding the desired backup below and clicking <strong>Restore</strong>.</p>
						<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-backup'>Watch Video</a>
					</div>
				</button>
			</div>
		</div>
		<div class="backup-list-holder">
			<div class="list-backups">
				<div class="row top-title">
					<div class="col">Date</div>
					<div class="col">Time</div>
					<div class="col">Backup Name</div>
					<div class="col">Action</div>
				</div>
				<?php foreach($backups as $backup): ?>
					<div class="row">
						<div class="col"><span class="title">Date</span><?php echo esc_html($backup['date']); ?></div>
						<div class="col"><span class="title">Time</span><?php echo esc_html($backup['time']); ?></div>
						<div class="col"><span class="title">Backup Name</span><?php echo esc_html($backup['name']); ?></div>
						<div class="col">
							<span class="title">Action</span>
							<?php if($admin): ?>
								<!--<button class="btn button-primary sweetalert2-popups" data-confirm data-dialog-template="#template-download-backup">Download</button>-->
								<button class="btn button-primary sweetalert2-popups" data-confirm data-dialog-template="#template-restore-backup" data-date="<?php echo esc_html($backup['date']); ?>" data-time="<?php echo esc_html($backup['time']); ?>" data-offset="<?php echo esc_html($backup['offset']); ?>" data-name="<?php echo esc_html($backup['name']); ?>">Restore</button>
							<?php endif; ?>

						</div>
					</div>
				<?php endforeach; ?>
				<div>
					<?php
					if($pages > 1){
						?>
						<ul class="navigation">
							<li><a href="/wp-admin/admin.php?page=wordkeeper-system-backups&page_num=1">First</a></li>
							<?php for($i=1; $i <= $pages; $i++){ ?>
								<li><a href="/wp-admin/admin.php?page=wordkeeper-system-backups&page_num=<?php echo (int) $i; ?>"><?php echo (int) $i; ?></a></li>
							<?php } ?>
							<li><a href="/wp-admin/admin.php?page=wordkeeper-system-backups&page_num=<?php echo (int) $pages; ?>">Last</a></li>
						</ul>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</div>

<template id='template-video-backup'>
	<swal-html>
		<div class="holder-backup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026997343?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Backup"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- Create Backup Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/create-backup.php'; ?>

<!-- Restore Backup Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/restore-backup.php'; ?>

<!-- Download Backup Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/download-backup.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>