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
			<h2>Task Status</h2>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="status" style="margin-top: 40px;">
		<div class="redirects-list-holder">
			<div class="list-status">
				<div class="row top-title">
					<div class="col">Time</div>
					<div class="col">Task</div>
					<div class="col">Status</div>
					<div class="col">Info</div>
				</div>
				<?php
					foreach($tasks as $taskid => $task){
						$initiatior =  (!empty($task['initiator']) ? 'Started by: ' . esc_html($task['initiator']) : '');
						$taskinfo = !empty($task['info']) ? $task['info'] : '';
						if(!empty($initiatior)) {
							$taskinfo .= !empty($task['info']) ? "\n" . $initiatior : $initiatior;
						}
						?>
						<div class="row">
							<div class="col">
								<span class="title"><?php echo esc_html($task['time']); ?></span>
							</div>
							<div class="col">
								<span class="title"><?php echo esc_html($task['title']); ?></span>
							</div>
							<div class="col">
								<span class="title"><?php echo esc_html($task['status']); ?></span>
							</div>
							<div class="col">
								<span class="title"><?php echo nl2br(esc_html($taskinfo)); ?></span>
							</div>
						</div>
						<?php
					}
				?>
			</div>
		</div>
	</div>
</div>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>