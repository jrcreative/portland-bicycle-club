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
			<h2>Redirects (2)</h2>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="redirects">
		<div class="top-holder">
			<div class="col">
				<div class="holder-btn">
					<button type="button" class="btn button-primary sweetalert2-popups" data-confirm data-dialog-template="#template-redirect-add" data-container="popups-widest-template">Add New</button>
				</div>
				<div class="holder-select-btn">
					<select>
						<option value="-1">Bulk actions</option>
						<option value="enable">Enable</option>
						<option value="disable">Disable</option>
						<option value="delete">Delete</option>
					</select>
					<button class="btn button-secondary apply" type="submit" >Apply</button>
				</div>
			</div>
			<div class="col">
				<form action="#" class="form-search-redirects">
					<input type="search">
					<button class="btn button-secondary" type="submit">Search</button>
				</form>
				<span class="info"><span class="num">2 </span>items</span>
			</div>
		</div>
		<div class="redirects-list-holder">
			<div class="list-redirects">
				<div class="row top-title">
					<div class="col">
						<label for="ch-all">
							<span class="over-ch">
								<input type="checkbox" id="ch-all">
							</span>
							<span class="over-txt">
								Type
							</span>
						</label>
					</div>
					<div class="col">Priority</div>
					<div class="col">Source URL</div>
					<div class="col">Status</div>
					<div class="col">Action</div>
				</div>
				<div class="row">
					<div class="col">
						<span class="title">Type</span>
						<label for="ch01">
							<span class="over-ch">
								<input class="checkbox" type="checkbox" id="ch01">
							</span>
							301
						</label>
					</div>
					<div class="col">
						<span class="title">Priority</span>
						1
					</div>
					<div class="col">
						<span class="title">Source URL</span>
						<a href="#">http://test104.wordkeeper.net/test222/</a>
					</div>
					<div class="col">
						<span class="title">Status</span>
						Enabled
					</div>
					<div class="col">
						<span class="title">Action</span>
						<button class="btn button-primary sweetalert2-popups" data-confirm data-dialog-template="#template-redirect-edit" data-container="popups-widest-template">Edit</button>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<span class="title">Type</span>
						<label for="ch02">
							<span class="over-ch">
								<input class="checkbox" type="checkbox" id="ch02">
							</span>
							301
						</label>
					</div>
					<div class="col">
						<span class="title">Priority</span>
						1
					</div>
					<div class="col">
						<span class="title">Source URL</span>
						<a href="#">http://test104.wordkeeper.net/test222/</a>
					</div>
					<div class="col">
						<span class="title">Status</span>
						Disabled
					</div>
					<div class="col">
						<span class="title">Action</span>
						<button class="btn button-primary sweetalert2-popups" data-confirm data-dialog-template="#template-redirect-edit" data-container="popups-widest-template">Edit</button>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- Redirects Edit -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/edit-redirect.php'; ?>

<!-- Add Redirect -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/add-redirect.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>