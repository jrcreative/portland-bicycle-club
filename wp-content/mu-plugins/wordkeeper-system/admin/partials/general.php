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
			<h1>General</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="holder-content-sidebar">
		<div class="content">
			<div class="general-block">
				<?php if(current_user_can('publish_pages') || current_user_can('publish_posts')): ?>
				<div class="item">
					<div class="holder-item holder-with-tooltip">
						<form>
							<span class="tooltips" data-template="item-caching" role="button" tabindex="0">?</span>
							<div class="tooltips-block tippy-popper" id="item-caching">
								<a class="close" href="#">close</a>
								<div class="tippy-arrow"></div>
								<p>Only clears hosting page cache, object cache, and some mainstream plugin/theme caches. <br /><br />Be sure to clear any CDN cache or browser caches as well.</p>
								<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-caching'>Watch Video</a>
							</div>
							<div class="holder-top-line">
								<div class="holder-icon">
									<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item1.svg" alt="icon">
								</div>
								<h2>Caching</h2>
								<p>WordKeeper caches copies of the pages of your website to keep it fast and stable. Click <strong>Clear</strong> below to flush all page and object caches.</p>
							</div>
							<div class="holder-btn">
								<button type="button" class="btn button-primary sweetalert-btn" name="clear-caches" id="clear-caches" data-submit data-waiting="Clearing Caches..." data-success="Cleared" data-path="cache/clear">Clear</button>
							</div>
							<input type="hidden" name="cache" value="all" />
						</form>
					</div>
				</div>
				<?php endif; ?>
				<?php if(($multisite && current_user_can('manage_network_options')) || (current_user_can('manage_options'))): ?>
					<div class="item">
						<div class="holder-item">
							<form>
								<div class="holder-top-line holder-with-tooltip">
									<div class="holder-icon">
										<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item2.svg" alt="icon">
									</div>
									<h2>Quick Backup</h2>
									<span class="tooltips" data-template="quick-backup" role="button" tabindex="0" aria-expanded="false">?</span>
									<div class="tooltips-block tippy-popper" id="quick-backup">
										<a class="close" href="#">close</a>
										<div class="tippy-arrow"></div>
										<p>WordKeeper automatically creates daily backups of your site, but you can create an extra backup manually by clicking <strong>Create Backup</strong> below.</p>
										<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-quick-backup'>Watch Video</a>
									</div>
									<p>WordKeeper automatically creates daily backups of your site, but you can create an extra backup manually by clicking <strong>Create Backup</strong> below.</p>
								</div>
								<div class="holder-btn">
									<button type="button" class="btn button-primary sweetalert2-popups" name="create-backup" id="create-backup" data-confirm data-waiting="Creating Backup..." data-dialog-template="#template-create-backup">Create Backup</button>
								</div>
							</form>
						</div>
					</div>
					<?php endif; ?>
					<?php if($admin): ?>
					<div class="item<?php if(!$syncstaging): ?> disabled<?php endif; ?>">
						<div class="holder-item holder-with-tooltip">
							<form>
								<span class="tooltips" data-template="item-sync-staging" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-sync-staging">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>Overwrites existing staging site with a fresh copy from live or creates a new staging site if one doesn't exist<br /><br /></p>
									<p><strong>Staging URL</strong>: https://staging-<?php echo esc_html($_SERVER['USER']); ?>.wordkeeper.net</p>
									<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-sync-staging'>Watch Video</a>
								</div>
								<div class="holder-top-line">
									<div class="holder-icon">
										<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item3.svg" alt="icon">
									</div>
									<h2>Copy from Live to Staging</h2>
									<p>Sync to a staging area so you can safely test new development without affecting or breaking your live site.</p>
								</div>
								<div class="holder-btn">
									<button <?php if(!$syncstaging): ?>disabled<?php endif; ?> type="button" class="btn button-primary sweetalert2-popups" name="sync-staging" id="sync-staging" data-confirm data-waiting="Starting Sync..." data-dialog-template="#template-sync-live">Sync</button>
								</div>
							</form>
						</div>
					</div>
					<div class="item<?php if(!$synclive): ?> disabled<?php endif; ?>">
						<div class="holder-item holder-with-tooltip">
							<form>
								<span class="tooltips" data-template="item-sync-live" role="button" tabindex="0">?</span>
								<div class="tooltips-block tippy-popper" id="item-sync-live">
									<a class="close" href="#">close</a>
									<div class="tippy-arrow"></div>
									<p>Overwrites <strong>live</strong> site with a fresh copy from staging.<br /><br /></p>
									<p><strong>Warning</strong>: Fully replaces live site with data from staging. Any data that only exists in the live site will be deleted during the sync.<br /><br /></p>
									<p><strong>Staging URL</strong>: https://staging-<?php echo esc_html($_SERVER['USER']); ?>.wordkeeper.net</p>
									<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-sync-live'>Watch Video</a>
								</div>
								<div class="holder-top-line">
									<div class="holder-icon">
										<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item4.svg" alt="icon">
									</div>
									<h2>Deploy from Staging to Live</h2>
									<p>Launch changes you've made in your staging area to the live site, overwriting your live site with the staging copy.</p>
								</div>
								<div class="holder-btn">
									<button <?php if(!$synclive): ?> disabled<?php endif; ?> type="button" class="btn button-primary sweetalert2-popups" name="sync-live" id="sync-live" data-confirm data-waiting="Starting Sync..." data-dialog-template="#template-sync-staging">Sync</button>
								</div>
							</form>
						</div>
					</div>
					<?php endif; ?>
					<?php if(($multisite && current_user_can('manage_network_options')) || (current_user_can('manage_options'))): ?>
					<div class="item">
						<div class="holder-item holder-with-tooltip">
							<form>
								<div class="holder-top-line">
									<div class="holder-icon">
										<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item5.svg" alt="icon">
									</div>
									<h2>Permissions</h2>
									<p>Running into file permissions problems? Reset the file permissions to WordPress defaults by clicking <strong>Fix</strong> below.</p>
								</div>
								<div class="holder-btn">
									<button type="button" class="btn button-primary sweetalert-btn" name="fix-permissions" id="fix-permissions" data-submit data-waiting="Fixing Permissions..." data-success="Fixed" data-path="permissions/fix">Fix</button>
								</div>
							</form>
						</div>
					</div>
					<?php endif; ?>
					<?php if($admin): ?>
					<div class="item">
						<div class="holder-item holder-with-tooltip">
							<span class="tooltips" data-template="item-phpmyadmin" role="button" tabindex="0">?</span>
							<div class="tooltips-block tippy-popper" id="item-phpmyadmin">
								<a class="close" href="#">close</a>
								<div class="tippy-arrow"></div>
								<p>You can find your database login information in your site's wp-config.php file.  <br /><br />If you don't know how to retrieve it through wp-config.php, contact support for assistance.</p>
								<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-phpmyadmin'>Watch Video</a>
							</div>
							<div class="holder-top-line">
								<div class="holder-icon">
									<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item6.svg" alt="icon">
								</div>
								<h2>phpMyAdmin</h2>
								<p>View and manage your site's database tables and data.</p>
							</div>
							<div class="holder-btn">
								<a href="https://db-<?php echo esc_html(gethostname()); ?>" title="https://db-<?php echo esc_html(gethostname()); ?>" target="_blank" class="btn button-primary">Access</a>
							</div>
						</div>
					</div>
					<div class="item">
						<div class="holder-item holder-with-tooltip">
							<span class="tooltips" data-template="logs-tooltip" role="button" tabindex="0" aria-expanded="false">?</span>
							<div class="tooltips-block tippy-popper" id="logs-tooltip">
								<a class="close" href="#">close</a>
								<div class="tippy-arrow"></div>
								<p>Website logs can help assess the rough traffic to your website, find the cause of problem requests or PHP errors, identify the cause of slowness in your PHP code and more.  You can access those logs in the logs folder at the root of your site's SFTP connection or via the WordPress admin by clicking the <strong>See All</strong> link below.</p>
								<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-logs'>Watch Video</a>
							</div>
							<div class="holder-top-line">
								<div class="holder-icon">
									<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item7.svg" alt="icon">
								</div>
								<h2>Logs</h2>
								<p>View any available site access logs, PHP error logs, and PHP slow logs.</p>
							</div>
							<div class="holder-btn">
								<a href="/wp-admin/admin.php?page=wordkeeper-system-hosting" class="btn button-primary">See All</a>
							</div>
						</div>
					</div>
					<?php if(!is_ssl()): ?>
					<div class="item">
						<div class="holder-item holder-with-tooltip">
							<form>
								<div class="holder-top-line">
									<div class="holder-icon">
										<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/item8.svg" alt="icon">
									</div>
									<h2>Install Free SSL Certificate</h2>
									<p>Get a free SSL security certificate from Let’s Encrypt to encrypt your traffic, enable support for faster HTTP, and appease browsers' SSL preference.</p>
								</div>
								<div class="holder-btn">
									<button class="btn button-primary sweetalert-btn" name="install-ssl" id="install-ssl" data-submit data-waiting="Installing SSL..." data-success="Installed" data-path="ssl/install">Install</button>
								</div>
							</form>
						</div>
					</div>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>
		<div class="sidebar">
			<?php if($admin): ?>
			<div class="postbox-widget">
				<div class="holder-title-widget holder-with-tooltip">
					<h2>SFTP</h2>
					<span class="tooltips" data-template="sftp-tooltip" role="button" tabindex="0" aria-expanded="false">?</span>
					<div class="tooltips-block tippy-popper" id="sftp-tooltip">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Secure File Transfer Protocol or "SFTP" provides a secure way to connect to your website hosting account to view and manage the files that compose your website.  <br /><br />All you need to manage your site files with SFTP is an FTP program (all support SFTP as well) and the proper connection settings (below) to connect to your account.</p>
						<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-sftp'>Watch Video</a>
					</div>
				</div>
				<div class="inside">
					<p>
						Use the settings below along with the hosting <br />provided username/password to connect to SFTP<br /><br />
						<strong style="display: inline-block; width: 100px;">Host:</strong>  <?php echo esc_html(gethostname()); ?><br />
						<strong style="display: inline-block; width: 100px;">IP:</strong>  <?php echo esc_html($_SERVER['SERVER_ADDR']); ?><br />
						<strong style="display: inline-block; width: 100px;">Protocol:</strong>  SFTP (not FTP)<br />
						<strong style="display: inline-block; width: 100px;">Port:</strong>  2222<br /><br />
						If you can't find your SFTP username/password, <br />please contact support
					</p>
				</div>
			</div>
			<?php if(!empty($sizes) && $admin): ?>
				<div class="postbox-widget">
				<div class="holder-title-widget">
					<h2>Disk Usage</h2>
				</div>
				<div class="inside">
					<p>
						<strong style="display: inline-block; width: 100px;">Total:</strong>  <?php echo esc_html($sizes['total']); ?><br />
						<strong style="display: inline-block; width: 100px;">Files:</strong>  <?php echo esc_html($sizes['file']); ?><br />
						<strong style="display: inline-block; width: 100px;">Database:</strong>  <?php echo esc_html($sizes['db']); ?><br />
					</p>
				</div>
			</div>
			<?php endif; ?>
			<?php if(!empty($domain)): ?>
				<div class="postbox-widget">
					<div class="holder-title-widget">
						<h2>Domain Info</h2>
					</div>
					<div class="inside">
						<p>
							<strong style="display: inline-block; width: 100px;">NS Records:</strong> <br /><br /><?php echo nl2br(esc_html($allns)); ?><br />

							<strong style="display: inline-block; width: 100px;">SPF Record:</strong> <br/>
							<?php if(!empty($domain['spf'])): ?>
								<?php if(isset($domain['spf']['multiple']) && $domain['spf']['multiple'] === true): ?>
									<span class="error">Multiple SPF records in use. <a href="#" target="_blank">Click Here</a></span>
									<br/><br/>
									<strong style="display: inline-block; width: 200px;">Suggested SPF Record:</strong> <br/><?php echo esc_html($domain['spf']['suggested']); ?>
								<?php elseif(isset($domain['spf']['valid']) && $domain['spf']['valid'] === false): ?>
									<span class="error">Invalid SPF record. <a href="#" target="_blank">Click Here</a></span>
								<?php elseif(isset($domain['spf']['recommended']) && $domain['spf']['recommended'] === true): ?>
									<span class="warning">SPF record missing suggested settings. See suggested below.<!--<a href="#" target="_blank">Click Here</a>--></span>
									<br/><br/>
									<strong style="display: inline-block; width: 200px;">Suggested SPF Record:</strong> <br/><?php echo esc_html($domain['spf']['suggested']); ?>
								<?php else: ?>
									Good
								<?php endif; ?>
							<?php else: ?>
								<span class="error">No SPF record found. <!--<a href="#" target="_blank">Click Here</a>--></span>
							<?php endif; ?>
							<br/><br/>

							<?php if(!empty($domain['registrar'])): ?>
							<strong style="display: inline-block; width: 100px;">Registrar:</strong><br/><?php if(!empty($domain['registrar_url'])): ?><a href="<?php echo esc_html($domain['registrar_url']); ?>" class="registrar-url" title="<?php echo esc_html($domain['registrar_url']); ?>" target="_blank"><?php endif; ?><?php echo esc_html($domain['registrar']); ?><?php if(!empty($domain['registrar_url'])): ?></a><?php endif; ?><br /><br/>
							<?php endif; ?>
							<?php if(isset($expiry) && !empty($expiry)): ?>
							<strong style="display: inline-block; width: 150px;">Expiration Date:</strong> <br/>
								<?php echo esc_html($expiry); ?><br />
								<?php if(!empty($expiry_diff) && is_numeric($expiry_diff->days) && $expiry_diff->days <= 30 && $expiry_diff->days > 7):?>
										<span class="warning">Expires in <?php echo esc_html($expiry_diff->days); ?> days. <!--<a href="#" target="_blank">Click Here</a>--></span>
									<?php elseif($expiry_diff->days <= 7):?>
										<span class="error">Expires in <?php echo esc_html($expiry_diff->days); ?> days. <!--<a href="#" target="_blank">Click Here</a>--></span>
									<?php endif; ?>
							<?php endif; ?>
						</p>
					</div>
				</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>

	<template id='template-video-caching'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1026978410?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Caching"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-quick-backup'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1026997343?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Quick Backup"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-sync-staging'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1026978579?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Sync Staging"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-sync-live'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1026978579?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Sync Live"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-sync-staging-ms'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1044819475?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Sync Staging Multisite"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-phpmyadmin'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1044819643?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="phpMyAdmin"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-sftp'>
		<swal-html>
			<div class="holder-video-popup">
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1045164279?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="SFTP"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<template id='template-video-logs'>
		<swal-html>
			<div class="holder-video-popup">1026978596
				<div style="padding-top: 56.25%; position: relative">
					<iframe src="https://player.vimeo.com/video/1026978596?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Logs"></iframe>
				</div>
			</div>
		</swal-html>
	</template>

	<!-- Save Template -->
	<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

	<!-- Create Backup Template -->
	<?php include plugin_dir_path(__FILE__) . '/dialogs/create-backup.php'; ?>

	<!-- Sync Staging Template -->
	<?php include plugin_dir_path(__FILE__) . '/dialogs/sync-staging.php'; ?>

	<!-- Sync Live Template -->
	<?php include plugin_dir_path(__FILE__) . '/dialogs/sync-live.php'; ?>

	<!-- All Notification types -->
	<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>

</div>
