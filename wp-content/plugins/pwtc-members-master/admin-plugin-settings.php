<div class="wrap">
	<h1><?= esc_html(get_admin_page_title()); ?></h1>
<?php
if (!current_user_can($capability)) {
?> 
	<p><strong>Access Denied</strong> - you do not have the rights to view this page.</p>
<?php   
}
else {
    if (isset($_POST['_wpnonce'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'pwtc_members_submit_settings')) {
            if (isset($_POST['sync_start_times'])) {
                update_option('pwtc_members_sync_start_times', 'yes');
            }
            else {
                update_option('pwtc_members_sync_start_times', 'no');
            }
            if (isset($_POST['sync_end_times'])) {
                update_option('pwtc_members_sync_end_times', 'yes');
            }
            else {
                update_option('pwtc_members_sync_end_times', 'no');
            }
            if (isset($_POST['allow_member_deletion'])) {
                update_option('pwtc_members_allow_member_deletion', 'yes');
            }
            else {
                update_option('pwtc_members_allow_member_deletion', 'no');
            }
        }
    }
    $sync_start_times = 'yes' === get_option('pwtc_members_sync_start_times', 'no');
    $sync_end_times = 'yes' === get_option('pwtc_members_sync_end_times', 'no');
    $allow_member_deletion = 'yes' === get_option('pwtc_members_allow_member_deletion', 'no');
?>
<script type="text/javascript">
jQuery(document).ready(function($) { 
});
</script>
    <div id="members-settings-section">
        <p><strong>Use this page to adjust the settings for the PWTC Members plugin.</strong></p>
        <form method="POST">
	        <?php wp_nonce_field('pwtc_members_submit_settings'); ?>
            <p>
                <input type="checkbox" id="sync_start_times" name="sync_start_times" <?php echo $sync_start_times ? 'checked' : ''; ?>/>
                <label for="sync_start_times">Sync member start times to rider ID year</label>
            </p>
            <p>
                <input type="checkbox" id="sync_end_times" name="sync_end_times" <?php echo $sync_end_times ? 'checked' : ''; ?>/>
                <label for="sync_end_times">Sync member end times to parent team end time</label>
            </p>
            <p>
                <input type="checkbox" id="allow_member_deletion" name="allow_member_deletion" <?php echo $allow_member_deletion ? 'checked' : ''; ?>/>
                <label for="allow_member_deletion">Allow users to delete their expired membership</label>
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