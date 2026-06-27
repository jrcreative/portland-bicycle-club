<style>
    .indicate-error {
        border-color: #900 !important;
        background-color: #FDD !important;
    }
</style>
<script type="text/javascript">
    jQuery(document).ready(function($) { 

        function show_warning(msg) {
            $('#pwtc-mapdb-edit-bbpost-div .errmsg').html('<div class="callout small warning"><p>' + msg + '</p></div>');
        }

        function show_waiting() {
            $('#pwtc-mapdb-edit-bbpost-div .errmsg').html('<div class="callout small"><i class="fa fa-spinner fa-pulse"></i> please wait...</div>');
        }

        $('#pwtc-mapdb-edit-bbpost-div form').on('keypress', function(evt) {
            var keyPressed = evt.keyCode || evt.which; 
            if (keyPressed === 13) { 
                evt.preventDefault(); 
                return false; 
            } 
        });	

        $('#pwtc-mapdb-edit-bbpost-div form textarea').on('keypress', function(evt) {
            is_dirty = true;
            $(this).removeClass('indicate-error');
            var keyPressed = evt.keyCode || evt.which; 
            if (keyPressed === 13) { 
                evt.stopPropagation(); 
            } 
        });	

        $('#pwtc-mapdb-edit-bbpost-div input[name="title"]').on('input', function() {
            is_dirty = true;
            $(this).removeClass('indicate-error');
        });

        $('#pwtc-mapdb-edit-bbpost-div form').on('submit', function(evt) {
            $('#pwtc-mapdb-edit-bbpost-div input').removeClass('indicate-error');
            $('#pwtc-mapdb-edit-bbpost-div textarea').removeClass('indicate-error');

            if ($('#pwtc-mapdb-edit-bbpost-div input[name="title"]').val().trim().length == 0) {
                show_warning('The <strong>ride title</strong> cannot be blank.');
                $('#pwtc-mapdb-edit-bbpost-div input[name="title"]').addClass('indicate-error');
                evt.preventDefault();
                return;
            }

            if ($('#pwtc-mapdb-edit-bbpost-div textarea[name="content"]').val().trim().length == 0) {
                show_warning('The <strong>content</strong> cannot be blank.');
                $('#pwtc-mapdb-edit-bbpost-div textarea[name="content"]').addClass('indicate-error');
                evt.preventDefault();
                return;
            }

            is_dirty = false;
            show_waiting();
            $('#pwtc-mapdb-edit-bbpost-div button[type="submit"]').prop('disabled',true);
        });

        window.addEventListener('beforeunload', function(e) {
            if (is_dirty) {
                e.preventDefault();
                e.returnValue = 'If you leave this page, any data you have entered will not be saved.';
            }
            else {
                delete e['returnValue'];
            }
        });

        var is_dirty = false;

    <?php if ($postid != 0) { ?>
        $(document).on( 'heartbeat-send', function( e, data ) {
            var send = {};
            send.post_id = '<?php echo $postid; ?>';
            data['pwtc-refresh-post-lock'] = send;
        });

        $(document).on( 'heartbeat-tick', function( e, data ) {
            if ( data['pwtc-refresh-post-lock'] ) {
                var received = data['pwtc-refresh-post-lock'];
                if ( received.lock_error ) {
                    show_warning('You can no longer edit this post. ' + received.lock_error.text);
                    $('#pwtc-mapdb-edit-bbpost-div button[type="submit"]').prop('disabled',true);
                } 
                else if ( received.new_lock ) {
                }
            }
        });

        wp.heartbeat.interval( 15 );
    <?php } ?>		

    });
</script>
<div id='pwtc-mapdb-edit-bbpost-div'>
    <?php echo $return_to_bbpost; ?>
    <?php if (!empty($operation)) { ?>
    <div class="callout small success">
        <p>
        <?php if ($operation == 'update_draft') { ?>
        The draft bulletin board post was updated.
        <?php } else if ($operation == 'submit_review') { ?>
        The draft bulletin board post was submitted for review.
        <?php } else if ($operation == 'update_pending') { ?>
        The pending bulletin board post was updated.
        <?php } else if ($operation == 'published_draft') { ?>
        The draft bulletin board post was published.
        <?php } else if ($operation == 'published') { ?>
        The pending bulletin board post was published
        <?php if ($email_status == 'yes') { ?> and the author notified by email
        <?php } else if ($email_status == 'failed') { ?> but failed to notify author by email<?php } ?>.
        <?php } else if ($operation == 'rejected') { ?>
        The pending bulletin board post was rejected
        <?php if ($email_status == 'yes') { ?> and the author notified by email
        <?php } else if ($email_status == 'failed') { ?> but failed to notify author by email<?php } ?>.
        <?php } else if ($operation == 'update_published') { ?>
        The published bulletin board post was updated.
        <?php } else if ($operation == 'unpublished') { ?>
        The published bulletin board post was unpublished.
        <?php } else if ($operation == 'insert') { ?>
        The first draft of bulletin board post post was saved.
        <?php } else if ($operation == 'revert_draft') { ?>
        The bulletin board post was reverted to draft
        <?php if ($email_status == 'yes') { ?> and the moderator notified by email
        <?php } else if ($email_status == 'failed') { ?> but failed to notify moderator by email<?php } ?>.
        <?php } ?>
        </p>
    </div>
    <?php } ?>
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
        a draft. It can be updated or <?php if (!$is_moderator) { ?>submitted for review<?php } else { ?>published<?php } ?> using the buttons at the bottom of the form.
        <?php } else if ($status == 'pending') { ?>
        pending review by a moderator. It can be updated, published or rejected using the buttons at the bottom of the form.
        <?php } else if ($status == 'publish') { ?>
        published and on the bulletin board. It can be updated or unpublished using the buttons at the bottom of the form.
        <?php } ?>
    <?php } else { ?>
        This is a new bulletin board post, fill out the form below and press the save button at the bottom of the form.
    <?php } ?>
        </p>
    </div>
    <div class="callout">
        <form method="POST" novalidate>
            <?php wp_nonce_field('bbpost-edit-form', 'nonce_field'); ?>
            <div class="row column">
                <label>Post Title
                    <input type="text" name="title" value="<?php echo esc_attr($title); ?>" />
                    <input type="hidden" name="postid" value="<?php echo $postid; ?>"/>
                    <input type="hidden" name="post_status" value="<?php echo $status; ?>"/>
                </label>
                <p class="help-text">TBD</p>
            </div>
            <div class="row column">
                <label>Post Content
                    <textarea name="content" rows="10"><?php echo $content; ?></textarea>
                </label>
		        <p class="help-text">
                    Only the following HTML markup is allowed: &lt;a&gt;, &lt;br&gt;, &lt;em&gt;, &lt;strong&gt; and &lt;p&gt;;
                    all others will be stripped out when this post is viewed.
                </p>
            </div>
            <div class="row column errmsg"></div>
            <div class="row column clearfix">
            <?php if ($postid == 0) { ?>
                <button class="dark button float-left" type="submit">Save Draft</button>
            <?php } else if ($status == 'draft') { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="draft" value="Update" type="submit"/>
                    <?php if (!$is_moderator) { ?>
                    <input class="dark button" name="pending" value="Submit for Review" type="submit"/>
                    <?php } else { ?>
                    <input class="dark button" name="publish" value="Publish" type="submit"/>
                    <?php } ?>
                </div>
            <?php } else if ($status == 'pending') { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="pending" value="Update" type="submit"/>
                    <input class="dark button" name="publish" value="Publish" type="submit"/>
                    <input class="dark button" name="draft" value="Reject" type="submit"/>
                </div>
            <?php } else { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="publish" value="Update" type="submit"/>
                    <input class="dark button" name="draft" value="Unpublish" type="submit"/>
                </div>
            <?php } ?>
            </div>
        </form>
    </div>
</div>
<?php
