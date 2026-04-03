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
			<h1>Hosting</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<?php if($tabs): ?>
		<ul class="tabs">
			<?php foreach($tabs as $tab_slug => $tab): ?>
				<?php
				$active_class = ($tab_slug == $current_tab) ? ' active' : '';
				?>
				<?php if($tab['permission'] === true): ?>
				<li class="item<?php echo esc_attr($active_class); ?>">
					<a href="<?php echo esc_attr($site_path); ?>wp-admin/<?php echo ($network) ? 'network/' : ''; ?>admin.php?page=wordkeeper-system-hosting&tab=<?php echo esc_attr($tab_slug); ?>"><?php echo esc_html($tab['title']); ?></a>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<?php if($current_tab): ?>
		<div class="holder-postbox-widget">
			<?php if(file_exists(dirname(__FILE__) . '/tabs/hosting/' . $current_tab . '.php')): ?>
				<?php require 'tabs/hosting/' . $current_tab . '.php'; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>