<script type="text/javascript">
    jQuery(document).ready(function($) { 

        function show_waiting() {
            $('#pwtc-mapdb-preview-bbpost-div .errmsg').html('<div class="callout small"><i class="fa fa-spinner fa-pulse"></i> please wait...</div>');
        }

        $('#pwtc-mapdb-preview-bbpost-div form').on('submit', function(evt) {
            show_waiting();
        });

    });
</script>
<div id='pwtc-mapdb-preview-bbpost-div'>
    <?php echo $return_to_bbpost; ?>
    <div>
        <p>
    <?php if ($postid != 0) { ?>
        This bulletin board post was authored by 
        <?php if ($author != $current_user->ID) { 
            echo '<a href="' . esc_url('mailto:' . $author_email) . '">' . $author_name . '</a>';
        } else { 
            echo $author_name;
        } ?> and is 
        <?php if ($status == 'draft') { ?>
        a draft.
        <?php } else if ($status == 'pending') { ?>
        pending review by a moderator.
        <?php } else if ($status == 'publish') { ?>
        published and on the bulletin board.
        <?php } ?>
    <?php } ?>
        </p>
    </div>
        <form method="POST" novalidate>
            <?php wp_nonce_field('bbpost-edit-form', 'nonce_field'); ?>
            <input type="hidden" name="postid" value="<?php echo $postid; ?>"/>
            <input type="hidden" name="post_status" value="<?php echo $status; ?>"/>
            <div class="row column">
                <h3><?php echo $title; ?></h3>
            </div>
            <div class="row column">
                <div class="callout">
                    <?php echo wp_kses(wpautop($content), $allowed_html_tags); ?>
                </div>
            </div>
            <div class="row column errmsg"></div>
            <div class="row column clearfix">
                <input class="dark button float-left" name="edit" value="Back to Edit" type="submit"/>
            </div>
        </form>
</div>
<?php