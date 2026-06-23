<div class="wrap">
	<h1><?= esc_html(get_admin_page_title()); ?></h1>
<?php
if (!current_user_can($capability)) {
?> 
	<p><strong>Access Denied</strong> - you do not have the rights to view this page.</p>
<?php   
}
else {
    $offset_days = 10;
    if (isset($_POST['_wpnonce'])) {
        if (wp_verify_nonce($_POST['_wpnonce'], 'pwtc_members_set_expiring_members')) {
            if (isset($_POST['offset_days'])) {
                $offset_days = absint($_POST['offset_days']);
            }
        }
    }
    $upcoming = PwtcMembers::fetch_expiring_memberships($offset_days);
    $recent = PwtcMembers::fetch_expiring_memberships(-$offset_days);
?>
<script type="text/javascript">
jQuery(document).ready(function($) { 
});
</script>
    <div id="expiring-members-section">
        <p><strong>Detect all members that have expired or will expire within <?php echo $offset_days; ?> days of the current date.</strong></p>
        <?php if (empty($recent['members']) and empty($upcoming['members'])) { ?>
        <p>No recent expired or expiring members found.</p>
        <?php } else { ?>
        <table class="pwtc-members-rwd-table">
            <tr><th>Name</th><th>Email</th><th>Family</th><th>Status</th><th>End Date</th></tr>
            <?php
            foreach ($recent['members'] as $item) {
                $member_url = admin_url('post.php?post=' . $item['member_id'] . '&action=edit');
                $memname = $item['name'] . ' (#' . $item['member_id'] . ') <a title="Edit membership record." href="' . $member_url . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                $teamname = '-';
                if ($item['team_id'] > 0) {
                    $team_url = admin_url('post.php?post=' . $item['team_id'] . '&action=edit');
                    $teamname = $item['team_name'] . ' (#' . $item['team_id'] . ') <a title="Edit team record." href="' . $team_url . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                }
            ?>
            <tr>
                <td data-th="Name"><?php echo $memname; ?></td>
                <td data-th="Email"><?php echo $item['email']; ?></td>
                <td data-th="Family"><?php echo $teamname; ?></td>
                <td data-th="Status"><?php echo $item['status']; ?></td>
                <td data-th="End Date"><?php echo $item['end_date']; ?></td>
            </tr>
            <?php 
            } 
            ?>
            <tr><th colspan="4" style="text-align: right">Current Date (UTC)</th><th><?php echo $upcoming['now']; ?></th></tr>
            <?php
            foreach ($upcoming['members'] as $item) {
                $member_url = admin_url('post.php?post=' . $item['member_id'] . '&action=edit');
                $memname = $item['name'] . ' (#' . $item['member_id'] . ') <a title="Edit membership record." href="' . $member_url . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                $teamname = '-';
                if ($item['team_id'] > 0) {
                    $team_url = admin_url('post.php?post=' . $item['team_id'] . '&action=edit');
                    $teamname = $item['team_name'] . ' (#' . $item['team_id'] . ') <a title="Edit team record." href="' . $team_url . '" target="_blank"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></a>';
                }
            ?>
            <tr>
                <td data-th="Name"><?php echo $memname; ?></td>
                <td data-th="Email"><?php echo $item['email']; ?></td>
                <td data-th="Family"><?php echo $teamname; ?></td>
                <td data-th="Status"><?php echo $item['status']; ?></td>
                <td data-th="End Date"><?php echo $item['end_date']; ?></td>
            </tr>
            <?php 
            } 
            ?>
        </table>
        <p>
        <form method="POST">
	        <?php wp_nonce_field('pwtc_members_set_expiring_members'); ?>
            <label for="offset_days">Number of days:</label>
            <input type="number" id="offset_days" name="offset_days" value="<?php echo $offset_days; ?>" min="1"/>
            <input type="submit" name="set_expiring_members" value="Submit" class="button button-primary"/>
        </form>
        </p>
        <?php } ?>
    </div>
<?php
}
?>
</div>
<?php