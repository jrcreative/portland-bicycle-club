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
 * @subpackage 	WordKeeper\System/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap-page-admin-panel">
	<div class="top-line">
		<div class="col">
			<h2>Announcements</h2>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="page-holder-with-filters">
		<div class="top-line-filters">
			<div class="row">
				<div class="col">
					<form action="#">
						<div class="holder-selects">
							<div class="holder-select-cat holder-single-select">
								<label for="cat-select">Category</label>
								<select id="cat-select" class="single-select">
									<option value="1">Recent Recent</option>
									<option value="2">Recent 1</option>
									<option value="3">Recent 2</option>
								</select>
							</div>
							<div class="holder-select-tag holder-multiple-select">
								<label for="tag-select">Tags</label>
								<select id="tag-select" class="multiple-select" multiple>
									<option value="1">CSS</option>
									<option value="2">CSS 1</option>
									<option value="3">CSS 2</option>
									<option value="1">CSS</option>
									<option value="2">CSS 1</option>
									<option value="3">CSS 2</option>
								</select>
								<div class="holder-btn">
									<button class="btn button-secondary filter" type="submit">Filter</button>
								</div>
							</div>
						</div>
					</form>
				</div>
				<div class="col">
					<form action="#">
						<div class="holder-search-with-icon">
							<input type="search" placeholder="Search">
							<button class="btn-icon" type="submit">Search</button>
						</div>
					</form>
				</div>
			</div>
		</div>
		<div class="holder-announcements-list">
			<div class="item">
				<div class="holder-item">
					<h3>Lorem Ipsum Dolor Sit Amet</h3>
					<h4 class="date">May 26, 2022</h4>
					<div class="text">
						Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum… <a class="read-more sweetalert2-popups" data-dialog data-announcement data-dialog-template="#template-announcements" href="#">Read More</a>
					</div>
				</div>
			</div>
			<div class="item">
				<div class="holder-item">
					<h3>Lorem Ipsum Dolor Sit Amet</h3>
					<h4 class="date">May 26, 2022</h4>
					<div class="text">
						Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum… <a class="read-more sweetalert2-popups" data-dialog data-announcement data-dialog-template="#template-announcements" href="#">Read More</a>
					</div>
				</div>
			</div>
			<div class="item">
				<div class="holder-item">
					<h3>Lorem Ipsum Dolor Sit Amet</h3>
					<h4 class="date">May 26, 2022</h4>
					<div class="text">
						Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum… <a class="read-more sweetalert2-popups" data-dialog data-announcement data-dialog-template="#template-announcements" href="#">Read More</a>
					</div>
				</div>
			</div>
			<div class="item">
				<div class="holder-item">
					<h3>Lorem Ipsum Dolor Sit Amet</h3>
					<h4 class="date">May 26, 2022</h4>
					<div class="text">
						Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum… <a class="read-more sweetalert2-popups" data-dialog data-announcement data-dialog-template="#template-announcements" href="#">Read More</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Announcements -->
<template id="template-announcements">
	<swal-html>
		<div class="holder-popups holder-announcements">
			<h2>Lorem Ipsum Dolor Sit Amet</h2>
			<h4 class="date">May 26, 2022</h4>
			<div class="text">
				<p>
					Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
				</p>
				<p>
					Lorem ipsum dolor sit amet, cons ectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
				</p>
				<p>
					Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi
				</p>
				<p>
					Lorem ipsum dolor sit amet, cons ectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
				</p>
				<p>
					Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
				</p>
				<p>
					Lorem ipsum dolor sit amet, cons ectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.
				</p>
				<p>
				Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.
				</p>
			</div>
		</div>
	</swal-html>
</template>