<script type="text/javascript">
    jQuery(document).ready(function($) { 
        $('#pwtc-mapdb-edit-bbpost-div .revert-action').on('click', function(evt) {
            $('#pwtc-mapdb-edit-bbpost-div form').submit();
        });

        $('#pwtc-mapdb-edit-bbpost-div form').on('submit', function(evt) {
            $('#pwtc-mapdb-edit-bbpost-div .callout').html('<i class="fa fa-spinner fa-pulse"></i> please wait...');
        })
    });
</script>
<div id='pwtc-mapdb-edit-bbpost-div'>
    <?php echo $return_to_bbpost; ?>
    <div class="callout small success">
        <p>The draft forum post "<?php echo $bbpost_title; ?>" was submitted for review<?php if ($email_status == 'yes') { ?> and the moderator notified by email<?php } else if ($email_status == 'failed') { ?> but failed to notify moderator by email<?php } ?>.</p>
    </div>
    <div class="row column">
        <p>Did you submit this forum post by mistake? If so, <a class="revert-action">undo the submission.</a></p>
    </div>
    <form method="POST">
        <?php wp_nonce_field('bbpost-edit-form', 'nonce_field'); ?>
        <input type="hidden" name="postid" value="<?php echo $postid; ?>"/>
        <input type="hidden" name="post_status" value="<?php echo $status; ?>"/>
        <input type="hidden" name="revert" value="draft"/>
    </form> 
</div>
<?php 
