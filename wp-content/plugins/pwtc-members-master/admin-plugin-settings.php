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
            if (isset($_POST['size_member_avatar'])) {
                update_option('pwtc_members_size_member_avatar', absint($_POST['size_member_avatar']));
            }
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
            if (isset($_POST['delete_membership_path'])) {
                update_option('pwtc_members_delete_membership_path', trim($_POST['delete_membership_path']));
            }
            if (isset($_POST['complete_virtual_orders'])) {
                update_option('pwtc_members_complete_virtual_orders', 'yes');
            }
            else {
                update_option('pwtc_members_complete_virtual_orders', 'no');
            }
            if (isset($_POST['remove_edit_profile'])) {
                update_option('pwtc_members_remove_edit_profile', 'yes');
            }
            else {
                update_option('pwtc_members_remove_edit_profile', 'no');
            }
            if (isset($_POST['prevent_invalid_purchase'])) {
                update_option('pwtc_members_prevent_invalid_purchase', 'yes');
            }
            else {
                update_option('pwtc_members_prevent_invalid_purchase', 'no');
            }
            if (isset($_POST['log_mileage_update'])) {
                update_option('pwtc_members_log_mileage_update', 'yes');
            }
            else {
                update_option('pwtc_members_log_mileage_update', 'no');
            }
        }
    }
    $size_member_avatar = get_option('pwtc_members_size_member_avatar', 100);
    $sync_start_times = 'yes' === get_option('pwtc_members_sync_start_times', 'no');
    $sync_end_times = 'yes' === get_option('pwtc_members_sync_end_times', 'no');
    $allow_member_deletion = 'yes' === get_option('pwtc_members_allow_member_deletion', 'no');
    $delete_membership_path = get_option('pwtc_members_delete_membership_path', '/');
    $complete_virtual_orders = 'yes' === get_option('pwtc_members_complete_virtual_orders', 'no');
    $remove_edit_profile = 'yes' === get_option('pwtc_members_remove_edit_profile', 'no');
    $prevent_invalid_purchase = 'yes' === get_option('pwtc_members_prevent_invalid_purchase', 'no');
    $log_mileage_update = 'yes' === get_option('pwtc_members_log_mileage_update', 'no');
?>
<script type="text/javascript">
jQuery(document).ready(function($) { 
});
</script>
    <div id="members-settings-section">
        <form method="POST">
	        <?php wp_nonce_field('pwtc_members_submit_settings'); ?>
            <p>
                <label for="size_member_avatar">Size of the member avatar shown in the membership directory.</label>
                <input type="number" step="10" min="100" max="500" id="size_member_avatar" name="size_member_avatar" value="<?php echo $size_member_avatar; ?>"/>
            </p>
            <p>
                <input type="checkbox" id="sync_start_times" name="sync_start_times" <?php echo $sync_start_times ? 'checked' : ''; ?>/>
                <label for="sync_start_times">Synchronize member start dates to their rider ID issue year</label>
            </p>
            <p>
                <input type="checkbox" id="sync_end_times" name="sync_end_times" <?php echo $sync_end_times ? 'checked' : ''; ?>/>
                <label for="sync_end_times">Synchronize individual member expiration dates to their parent team expiration date</label>
            </p>
            <p>
                <input type="checkbox" id="allow_member_deletion" name="allow_member_deletion" <?php echo $allow_member_deletion ? 'checked' : ''; ?>/>
                <label for="allow_member_deletion">Allow users to delete their expired membership</label>
            </p>
            <p>
                <label for="delete_membership_path">URL pathname of membership delete page</label>
                <input type="text" id="delete_membership_path" name="delete_membership_path" value="<?php echo $delete_membership_path; ?>"/>
            </p>
            <p>
                <input type="checkbox" id="complete_virtual_orders" name="complete_virtual_orders" <?php echo $complete_virtual_orders ? 'checked' : ''; ?>/>
                <label for="complete_virtual_orders">Allow products that are virtual but not downloadable to complete orders automatically</label>
            </p>
            <p>
                <input type="checkbox" id="remove_edit_profile" name="remove_edit_profile" <?php echo $remove_edit_profile ? 'checked' : ''; ?>/>
                <label for="remove_edit_profile">Remove edit profile option from admin desktop for non-administrators</label>
            </p>
            <p>
                <input type="checkbox" id="prevent_invalid_purchase" name="prevent_invalid_purchase" <?php echo $prevent_invalid_purchase ? 'checked' : ''; ?>/>
                <label for="prevent_invalid_purchase">Prevent invalid purchase scenarios when buying club memberships</label>
            </p>
            <p>
                <input type="checkbox" id="log_mileage_update" name="log_mileage_update" <?php echo $log_mileage_update ? 'checked' : ''; ?>/>
                <label for="log_mileage_update">Log when rider info updates in the mileage datebase occur</label>
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