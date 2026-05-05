<form>
<div class="top-holder">
	<div class="col">
		<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Selected</button>
	</div>
	<div class="col">
		<button onclick="return false;" class="btn button-primary more-info tooltip-moreinfo" name="more-info" id="more-info" data-template="item-more-info" role="button" tabindex="0" aria-expanded="false">More Info
			<span class="tooltips" data-template="item-more-info" role="button" tabindex="0" aria-expanded="false">?</span>
			<div class="tooltips-block tippy-popper" id="item-more-info">
				<a class="close" href="#">close</a>
				<div class="tippy-arrow"></div>
				<p>As you manage and edit your site, temporary database records can accumulate over time.  In combination with logs about old events, comment submissions, expired caches, and other trash, your site's database can become bloated.  By periodically (if not regularly) cleaning that bloat from your database, you can keep your site fast and stable as it ages.</p>
				<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-one-time-cleaning'>Watch Video</a>
			</div>
		</button>
	</div>
</div>

<div class="list-databases" data-checkboxlist="database/clean">
	<div class="row">
		<div class="col">
			<label for="ch-all">
				<span class="over-ch" tabindex="0">
					<input type="checkbox" id="ch-all">
				</span>
				<span class="over-txt" role="button" tabindex="0">
					Select All
				</span>
			</label>
		</div>
		<!--<div class="col" style="color:#000;">
			Action
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-posts-revisions">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/posts/revisions" id="database-posts-revisions" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove All Post Revisions</span>
					<span class="txt">(<?php echo esc_html($counts['post/revisions']); ?>) post revisions found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-post-revisions" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-posts-autodrafts">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/posts/autodrafts" id="database-posts-autodrafts" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove All Auto-Drafts</span>
					<span class="txt">(<?php echo esc_html($counts['post/autodrafts']); ?>) auto-draft posts found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-auto_draft-post" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-posts-trashed" for="database-posts-trashed">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/posts/trashed" id="database-posts-trashed" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove All Trashed Posts</span>
					<span class="txt">(<?php echo esc_html($counts['post/trash']); ?>) trashed posts found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-trashed-post" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-comments-spam">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/comments/spam" id="database-comments-spam" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Spam and Trashed Comments</span>
					<span class="txt">(<?php echo esc_html($counts['comment/spam']); ?>) spam comments found | (<?php echo $counts['comment/trash']; ?>) trashed comments found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-spam-trashed-comment" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-comments-unapproved">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/comments/unapproved" id="database-comments-unapproved" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Unapproved Comments</span>
					<span class="txt">(<?php echo esc_html($counts['comment/unapproved']); ?>) unapproved comments found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-unapproved-comment" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-transients">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/transients" id="database-transients" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Expired Transients</span>
					<span class="txt">(<?php echo esc_html($counts['transients']); ?>) expired transients found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-transient" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-pingbacks">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/pingbacks" id="database-pingbacks" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Pingbacks</span>
					<span class="txt">(<?php echo esc_html($counts['pingbacks']); ?>) pingbacks found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-pingback" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-trackbacks">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/trackbacks" id="database-trackbacks" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Trackbacks</span>
					<span class="txt">(<?php echo esc_html($counts['trackbacks']); ?>) trackbacks found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-trackbacks" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-posts-orphans">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/posts/orphans" id="database-posts-orphans" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Orphaned Post Data</span>
					<span class="txt">(<?php echo esc_html($counts['post/orphans']); ?>) orphaned post rows found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-orphan-post" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-comments-orphans">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/comments/orphans" id="database-comments-orphans" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Orphaned Comment Data</span>
					<span class="txt">(<?php echo esc_html($counts['comment/orphans']); ?>) orphaned comment rows found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-orphan-comment" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-taxonomy-orphans">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/taxonomy/orphans" id="database-taxonomy-orphans" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Orphaned Taxonomy Data</span>
					<span class="txt">(<?php echo esc_html($counts['taxonomy/orphans']); ?>) orphaned taxonomy rows found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-orphan-taxonomy" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-users-orphans">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/users/orphans" id="database-users-orphans" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Orphaned User Data</span>
					<span class="txt">(<?php echo esc_html($counts['user/orphans']); ?>) orphaned user rows found</span>
				</span>
			</label>
		</div>
		<!--<div class="col">
			<button type="button" class="btn button-primary sweetalert-btn" name="clear-junk" id="clear-junk-orphan-usermeta" data-submit data-waiting="Optimizing..." data-success="Done" data-path="database/clean">Run Optimization</button>
		</div>-->
	</div>
	<div class="row">
		<div class="col">
			<label for="database-oembed">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/oembed" id="database-oembed" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Failed oEmbed Caches</span>
					<span class="txt">(<?php echo esc_html($counts['oembed']); ?>) failed oEmbed caches found</span>
				</span>
			</label>
		</div>
	</div>
	<div class="row">
		<div class="col">
			<label for="database-logs">
				<span class="over-ch" tabindex="0">
					<input class="checkbox" type="checkbox" name="database/logs" id="database-logs" />
				</span>
				<span class="over-txt" role="button" tabindex="0">
					<span class="title">Remove Old Plugin Logs</span>
					<span class="txt">(<?php echo esc_html($counts['logs']); ?>) plugin log entries older than 30 days found</span>
				</span>
			</label>
		</div>
	</div>
</div>
</form>

<template id='template-video-one-time-cleaning'>
	<swal-html>
		<div class="holder-video-popup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026978386?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="One Time Cleaning"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<!-- Error Notification -->
<?php include plugin_dir_path(__FILE__) . '../../notifications/error.php'; ?>