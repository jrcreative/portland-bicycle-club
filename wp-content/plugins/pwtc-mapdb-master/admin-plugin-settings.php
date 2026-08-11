<div class="wrap">
	<h1><?= esc_html(get_admin_page_title()); ?></h1>
<?php
if (!current_user_can($capability)) {
?> 
	<p><strong>Access Denied</strong> - you do not have the rights to view this page.</p>
<?php   
}
else {
    $after_error_msg = '';
    $email_error_msg = '';
    if (isset($_POST['_wpnonce'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'pwtc_mapdb_submit_settings')) {
            if (isset($_POST['max_post_title_len'])) {
                update_option('pwtc_mapdb_post_title_max_len', absint($_POST['max_post_title_len']));
            }
            if (isset($_POST['post_title_disallowed_chars'])) {
                update_option('pwtc_mapdb_post_title_disallowed_chars', trim($_POST['post_title_disallowed_chars']));
            }
            if (isset($_POST['after'])) {
                if (strtotime(trim($_POST['after']))) {
                    update_option('pwtc_mapdb_recent_post_time_cutoff', trim($_POST['after']));
                }
                else {
                    $after_error_msg = '<span style="color: red;">entry <code>' . trim($_POST['after']) . '</code> was not valid</span>';
                }
            }
            if (isset($_POST['public_topics'])) {
                update_option('pwtc_mapdb_public_topics_parent_category_name', trim($_POST['public_topics']));
            }
            if (isset($_POST['admin_topics'])) {
                update_option('pwtc_mapdb_admin_topics_parent_category_name', trim($_POST['admin_topics']));
            }
            if (isset($_POST['topics'])) {
                update_option('pwtc_mapdb_topics_parent_category_name', trim($_POST['topics']));
            }
            if (isset($_POST['force_comment_moderation'])) {
                update_option('pwtc_mapdb_force_comment_moderation', 'yes');
            }
            else {
                update_option('pwtc_mapdb_force_comment_moderation', 'no');
            }
            if (isset($_POST['radiobtn_category_select'])) {
                update_option('pwtc_mapdb_radiobtn_category_select', 'yes');
            }
            else {
                update_option('pwtc_mapdb_radiobtn_category_select', 'no');
            }
            if (isset($_POST['send_post_submit_email'])) {
                update_option('pwtc_mapdb_send_post_submit_email', 'yes');
            }
            else {
                update_option('pwtc_mapdb_send_post_submit_email', 'no');
            }
            if (isset($_POST['post_moderator_email'])) {
                if (!empty(trim($_POST['post_moderator_email']))) {
                    update_option('pwtc_mapdb_post_moderator_email', trim($_POST['post_moderator_email']));
                }
                else {
                    $email_error_msg = '<span style="color: red;">blank email address was not valid</span>';
                }
            }
            if (isset($_POST['modified_time_update'])) {
                update_option('pwtc_mapdb_modified_time_update', 'yes');
            }
            else {
                update_option('pwtc_mapdb_modified_time_update', 'no');
            }
            if (isset($_POST['restrict_feed_output'])) {
                update_option('pwtc_mapdb_restrict_feed_output', 'yes');
            }
            else {
                update_option('pwtc_mapdb_restrict_feed_output', 'no');
            }
            if (isset($_POST['enable_post_copy'])) {
                update_option('pwtc_mapdb_enable_post_copy', 'yes');
            }
            else {
                update_option('pwtc_mapdb_enable_post_copy', 'no');
            }
            if (isset($_POST['edit_forum_post_path'])) {
                update_option('pwtc_mapdb_edit_forum_post_path', trim($_POST['edit_forum_post_path']));
            }
            if (isset($_POST['submit_forum_post_path'])) {
                update_option('pwtc_mapdb_submit_forum_post_path', trim($_POST['submit_forum_post_path']));
            }
            if (isset($_POST['delete_forum_post_path'])) {
                update_option('pwtc_mapdb_delete_forum_post_path', trim($_POST['delete_forum_post_path']));
            }
        }
    }
    $max_post_title_len = get_option('pwtc_mapdb_post_title_max_len', 0);
    $post_title_disallowed_chars = get_option('pwtc_mapdb_post_title_disallowed_chars', '');
    $after = get_option('pwtc_mapdb_recent_post_time_cutoff', '1 day ago');
    $public_topics = get_option('pwtc_mapdb_public_topics_parent_category_name', '');
    $admin_topics = get_option('pwtc_mapdb_admin_topics_parent_category_name', '');
    $topics = get_option('pwtc_mapdb_topics_parent_category_name', '');
    $post_moderator_email = get_option('pwtc_mapdb_post_moderator_email', 'webmaster@portlandbicyclingclub.com');
    $radiobtn_category_select = 'yes' === get_option('pwtc_mapdb_radiobtn_category_select', 'no');
    $send_post_submit_email = 'yes' === get_option('pwtc_mapdb_send_post_submit_email', 'no');
    $force_comment_moderation = 'yes' === get_option('pwtc_mapdb_force_comment_moderation', 'no');
    $enable_post_copy = 'yes' === get_option('pwtc_mapdb_enable_post_copy', 'no');
    $modified_time_update = 'yes' === get_option('pwtc_mapdb_modified_time_update', 'no');
    $restrict_feed_output = 'yes' === get_option('pwtc_mapdb_restrict_feed_output', 'no');
    $edit_forum_post_path = get_option('pwtc_mapdb_edit_forum_post_path', '/');
    $submit_forum_post_path = get_option('pwtc_mapdb_submit_forum_post_path', '/');
    $delete_forum_post_path = get_option('pwtc_mapdb_delete_forum_post_path', '/');
?>
    <div id="mapdb-settings-section">
        <form method="POST">
	        <?php wp_nonce_field('pwtc_mapdb_submit_settings'); ?>
            <h3>Forum Posts</h3>
            <p>
                <label for="max_post_title_len">Maximum character length for post title</label>
                <input type="number" id="max_post_title_len" name="max_post_title_len" value="<?php echo $max_post_title_len; ?>"/>
            </p>
            <p>
                <label for="post_title_disallowed_chars">Hex ascii code for disallowed characters in post title (delimit by a space)</label>
                <input type="text" id="post_title_disallowed_chars" name="post_title_disallowed_chars" value="<?php echo $post_title_disallowed_chars; ?>"/>
            </p>
            <p>
                <label for="after">Time cutoff for recent posts</label>
                <input type="text" id="after" name="after" value="<?php echo $after; ?>"/>
                <?php echo $after_error_msg; ?>
            </p>
            <p>
                <label for="public_topics">Name of post category that contains public topics</label>
                <input type="text" id="public_topics" name="public_topics" value="<?php echo $public_topics; ?>"/>
            </p>
            <p>
                <label for="admin_topics">Name of post category that contains administrator topics</label>
                <input type="text" id="admin_topics" name="admin_topics" value="<?php echo $admin_topics; ?>"/>
            </p>
            <p>
                <label for="topics">Name of post category that contains member topics</label>
                <input type="text" id="topics" name="topics" value="<?php echo $topics; ?>"/>
            </p>
            <p>
                <input type="checkbox" id="radiobtn_category_select" name="radiobtn_category_select" <?php echo $radiobtn_category_select ? 'checked' : ''; ?>/>
                <label for="radiobtn_category_select">Use radio button category selection in post editor</label>
            </p>
            <p>
                <input type="checkbox" id="send_post_submit_email" name="send_post_submit_email" <?php echo $send_post_submit_email ? 'checked' : ''; ?>/>
                <label for="send_post_submit_email">Send notification email to moderator when a post or comment is submitted</label>
            </p>
            <p>
                <label for="post_moderator_email">Email address of the post moderator</label>
                <input type="text" id="post_moderator_email" name="post_moderator_email" value="<?php echo $post_moderator_email; ?>"/>
                <?php echo $email_error_msg; ?>
            </p>
            <p>
                <input type="checkbox" id="force_comment_moderation" name="force_comment_moderation" <?php echo $force_comment_moderation ? 'checked' : ''; ?>/>
                <label for="force_comment_moderation">Force all post comments to moderation</label>
            </p>
            <p>
                <input type="checkbox" id="modified_time_update" name="modified_time_update" <?php echo $modified_time_update ? 'checked' : ''; ?>/>
                <label for="modified_time_update">Update post modified time when a new comment is approved</label>
            </p>
            <p>
                <input type="checkbox" id="restrict_feed_output" name="restrict_feed_output" <?php echo $restrict_feed_output ? 'checked' : ''; ?>/>
                <label for="restrict_feed_output">Restrict RSS feed output when user not logged in</label>
            </p>
            <p>
                <label for="edit_forum_post_path">URL pathname of forum post edit page</label>
                <input type="text" id="edit_forum_post_path" name="edit_forum_post_path" value="<?php echo $edit_forum_post_path; ?>"/>
            </p>
            <p>
                <label for="submit_forum_post_path">URL pathname of forum post submit page</label>
                <input type="text" id="submit_forum_post_path" name="submit_forum_post_path" value="<?php echo $submit_forum_post_path; ?>"/>
            </p>
            <p>
                <label for="delete_forum_post_path">URL pathname of forum post delete page</label>
                <input type="text" id="delete_forum_post_path" name="delete_forum_post_path" value="<?php echo $delete_forum_post_path; ?>"/>
            </p>
            <h3>General</h3>
            <p>
                <input type="checkbox" id="enable_post_copy" name="enable_post_copy" <?php echo $enable_post_copy ? 'checked' : ''; ?>/>
                <label for="enable_post_copy">Enable post/page copy functionality</label>
            </p>
            <p>
                <input type="submit" value="Save" class="button button-primary" />
            </p>
        </form>
    </div>
<?php
}
?>
</div>
<?php
