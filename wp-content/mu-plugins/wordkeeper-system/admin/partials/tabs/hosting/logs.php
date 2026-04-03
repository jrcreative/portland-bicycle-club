<?php if(!empty($logs['access'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Access Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['access'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['access']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>
<?php if(!empty($logs['error'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Error Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['error'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['error']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>
<?php if(!empty($logs['phpslow'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Slow Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['phpslow'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['phpslow']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>
<?php if(!empty($logs['debug'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Debug Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['debug'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['debug']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>

<?php if(!empty($logs['optimize-images'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Image Optimization Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['optimize-images'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['optimize-images']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>

<?php if(!empty($logs['wp-cron'])): ?>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>WP Cron Logs</h2>
	</div>
	<div class="inside">
		<form action="#">
			<ul class="list-downloads">
				<?php foreach($logs['wp-cron'] as $log): ?>
				<li>
					<span class="text" style="width: 104px; display: inline-block;"><?php echo esc_html($log['name']); ?></span>
					<a class="link-downloads" href="javascript:return false;" data-log="<?php echo esc_html($log['file']); ?>" role="button" tabindex="0">Download</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php if(count($logs['wp-cron']) > 5): ?>
				<button type="button" class="show-more">Show More</button>
			<?php endif; ?>
		</form>
	</div>
</div>
<?php endif; ?>