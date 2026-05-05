<div class="automatic-box">
	<form>
	<h2 class="title">Automatic Cleaning Settings</h2>
	<p>Scheduling automatic cleanings helps reduce gradual increases in slowness as your site grows by preventing junk buildup over time.</p>
	<div class="radio-holder">
		<ul class="list-radio openclose">
			<li>
				<input type="radio" name="database/optimize" value="recommended" id="recommended" data-type="toggle" data-show="advanced-option" data-showtype="close"<?php if($settings['database/optimize'] == 'recommended'): ?> checked<?php endif; ?> />
				<label for="recommended">Recommended</label>
			</li>
			<li>
				<input type="radio" name="database/optimize" value="advanced" id="advanced" data-type="toggle" data-show="advanced-option" data-showtype="open"<?php if($settings['database/optimize'] == 'advanced'): ?> checked<?php endif; ?> />
				<label for="advanced">Advanced</label>
			</li>
			<li>
				<input type="radio" name="database/optimize" value="off" id="off" data-type="toggle" data-show="advanced-option" data-showtype="close"<?php if($settings['database/optimize'] == 'off'): ?> checked<?php endif; ?> />
				<label for="off">Off</label>
			</li>
		</ul>
	</div>
	<div class="advanced-option" id="advanced-option"<?php if($settings['database/optimize'] == 'advanced'): ?> style="display: block;"<?php endif; ?>>
		<div class="over-radio" style="padding-top: 0px">
			<p>Select schedule frequency (default is monthly)</p>
			<ul class="list-radio">
				<li>
					<input name="database/frequency" type="radio" id="monthly" value="monthly"<?php if($settings['database/frequency'] == 'monthly'): ?> checked<?php endif; ?> />
					<label for="monthly">Monthly</label>
				</li>
				<li>
					<input name="database/frequency" type="radio" id="weekly" value="weekly"<?php if($settings['database/frequency'] == 'weekly'): ?> checked<?php endif; ?> />
					<label for="weekly">Weekly</label>
				</li>
				<li>
					<input name="database/frequency" type="radio" id="daily" value="daily"<?php if($settings['database/frequency'] == 'daily'): ?> checked<?php endif; ?> />
					<label for="daily">Daily</label>
				</li>
			</ul>
		</div>
		<div class="over-options" data-checkboxlist="database/auto-clean">
			<h2>Additional Options</h2>
			<ul class="list-ch">
				<li>
					<input type="checkbox" name="database/posts/revisions" id="database-posts-revisions"<?php if($settings['database/posts/revisions'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-posts-revisions">Remove All Post Revisions</label>
					<span class="tooltips" data-template="item-database-posts-revisions" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-posts-revisions">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Deletes all past post revisions. Safe as long as you don't need to revert to an older version of a post.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/posts/autodrafts" id="database-posts-autodrafts"<?php if($settings['database/posts/autodrafts'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-posts-autodrafts">Remove All Auto-Drafts</label>
					<span class="tooltips" data-template="item-database-posts-autodrafts" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-posts-autodrafts">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Removes automatic drafts created while editing posts.  Safe as long as you remember to save your post changes.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/posts/trashed" id="database-posts-trashed"<?php if($settings['database/posts/trashed'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-posts-trashed">Remove All Trashed Posts</label>
					<span class="tooltips" data-template="item-database-posts-trashed" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-posts-trashed">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Removes all posts currently in the trash.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/comments/spam" id="database-comments-spam"<?php if($settings['database/comments/spam'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-comments-spam">Remove Spam and Trashed Comments</label>
					<span class="tooltips" data-template="item-database-comments-spam" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-comments-spam">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Removes all comments that have been trashed or marked as spam.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/comments/unapproved" id="database-comments-unapproved"<?php if($settings['database/comments/unapproved'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-comments-unapproved">Remove Unapproved Comments</label>
					<span class="tooltips" data-template="item-database-comments-unapproved" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-comments-unapproved">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Removes all pending comments that have not been approved or published.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/transients" id="database-transients"<?php if($settings['database/transients'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-transients">Remove Expired Transients</label>
					<span class="tooltips" data-template="item-database-transients" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-transients">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Removes any temporary "transient" caches that have passed their expiration date.  Typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/pingbacks" id="database-pingbacks"<?php if($settings['database/pingbacks'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-pingbacks">Remove Pingbacks</label>
					<span class="tooltips" data-template="item-database-pingbacks" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-pingbacks">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove ALL pingbacks.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/trackbacks" id="database-trackbacks"<?php if($settings['database/trackbacks'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-trackbacks">Remove Trackbacks</label>
					<span class="tooltips" data-template="item-database-trackbacks" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-trackbacks">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove ALL trackbacks.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/posts/orphans" id="database-posts-orphans"<?php if($settings['database/posts/orphans'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-posts-orphans">Remove Orphaned Post Data</label>
					<span class="tooltips" data-template="item-database-posts-orphans" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-posts-orphans">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove leftover data in postmeta if the post that it belongs to has already been deleted.  Typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/comments/orphans" id="database-comments-orphans"<?php if($settings['database/comments/orphans'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-comments-orphans">Remove Orphaned Comment Data</label>
					<span class="tooltips" data-template="item-database-comments-orphans" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-comments-orphans">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove leftover data in commentmeta if the comment that it belongs to has already been deleted.  Typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/taxonomy/orphans" id="database-taxonomy-orphans"<?php if($settings['database/taxonomy/orphans'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-taxonomy-orphans">Remove Orphaned Taxonomy Data</label>
					<span class="tooltips" data-template="item-database-taxonomy-orphans" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-taxonomy-orphans">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove leftover data in termmeta if the term that it belongs to has already been deleted.  Typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/users/orphans" id="database-users-orphans"<?php if($settings['database/users/orphans'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-users-orphans">Remove Orphaned User Data</label>
					<span class="tooltips" data-template="item-database-users-orphans" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-users-orphans">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Remove leftover data in usermeta if the user that it belongs to has already been deleted.  Typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/oembed" id="database-oembed"<?php if($settings['database/oembed'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-oembed">Remove Failed oEmbed Caches</label>
					<span class="tooltips" data-template="item-database-oembed" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-oembed">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>WordPress caches oEmbed lookups when you paste in content from third parties (e.g. YouTube, Twitter, etc) even if these looks fail.  Failed lookups can clog your site.  Removing them is typically safe in all circumstances.</p>
					</div>
				</li>
				<li>
					<input type="checkbox" name="database/logs" id="database-logs"<?php if($settings['database/logs'] == true): ?> checked<?php endif; ?><?php if($settings['database/optimize'] != 'advanced'): ?> disabled<?php endif; ?> />
					<label for="database-logs">Remove Old Plugin Logs</label>
					<span class="tooltips" data-template="item-database-logs" role="button" tabindex="0">?</span>
					<div class="tooltips-block tippy-popper" id="item-database-logs">
						<a class="close" href="#">close</a>
						<div class="tippy-arrow"></div>
						<p>Many plugins retain logs about user or site behavior.  This could be API logs, security logs, or user activity logs.  Retaining these logs for more than 30 days can clog your site. Removing them is typically safe in all circumstances.</p>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
	</form>
</div>

<!-- Sync Error Template -->
<?php include plugin_dir_path(__FILE__) . '../../notifications/error.php'; ?>