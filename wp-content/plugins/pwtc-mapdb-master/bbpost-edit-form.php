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

        function show_warning2(msg) {
            $('#pwtc-mapdb-edit-bbpost-div .errmsg2').html('<div class="callout small warning">' + msg + '</div>');
        }

        function show_waiting() {
            $('#pwtc-mapdb-edit-bbpost-div .errmsg').html('<div class="callout small"><i class="fa fa-spinner fa-pulse"></i> please wait...</div>');
        }

        function insert_tag(startTag, endTag) {
            $('#pwtc-mapdb-edit-bbpost-div form textarea').each(function() {
                const start = this.selectionStart;
                const end = this.selectionEnd;
                const oldText = this.value;

                const prefix = oldText.substring(0, start);
                const inserted = startTag + oldText.substring(start, end) + endTag;
                const suffix = oldText.substring(end);

                this.value = `${prefix}${inserted}${suffix}`;

                const newStart = start + startTag.length;
                const newEnd = end + startTag.length;

                this.setSelectionRange(newStart, newEnd);
                this.focus();
            });
        }

        function is_text_selected() {
            var selected = false;
            $('#pwtc-mapdb-edit-bbpost-div form textarea').each(function() {
                selected = this.selectionEnd > this.selectionStart;
            });
            return selected;
        }

        $('#pwtc-mapdb-edit-bbpost-div form').on('keypress', function(evt) {
            var keyPressed = evt.keyCode || evt.which; 
            if (keyPressed === 13) { 
                evt.preventDefault(); 
                return false; 
            } 
        });	

        $('#pwtc-mapdb-edit-bbpost-div form textarea').on('keypress', function(evt) {
            var keyPressed = evt.keyCode || evt.which; 
            if (keyPressed === 13) { 
                evt.stopPropagation(); 
            } 
        });	

        $('#pwtc-mapdb-edit-bbpost-div form .bold-btn').on('click', function(evt) { 
            if (is_text_selected()) {
                $('#pwtc-mapdb-edit-bbpost-div .errmsg2').empty();
                is_dirty = true;
                insert_tag("<strong>", "</strong>");
            }
            else {
                show_warning2('First select the text that you want to make bold.');
            }
            evt.preventDefault();
        });

        $('#pwtc-mapdb-edit-bbpost-div form .italic-btn').on('click', function(evt) { 
            if (is_text_selected()) {
                $('#pwtc-mapdb-edit-bbpost-div .errmsg2').empty();
                is_dirty = true;
                insert_tag("<em>", "</em>");
            }
            else {
                show_warning2('First select the text that you want to italicize.');
            }
            evt.preventDefault();
        });

        $('#pwtc-mapdb-edit-bbpost-div form .url-btn').on('click', function(evt) { 
            if (is_text_selected()) {
                $('#pwtc-mapdb-edit-bbpost-div .errmsg2').empty();
                is_dirty = true;
                insert_tag('<a href="">', "</a>");
            }
            else {
                show_warning2('First select the text that you want to turn into a link.');
            }
            evt.preventDefault();
        });

        $('#pwtc-mapdb-edit-bbpost-div input[name="preview"]').on('click', function(evt) { 
            if (is_dirty) {
                show_warning('You have unsaved changes; you must save them before you can preview this post.');
                evt.preventDefault();
            }
        });

        $('#pwtc-mapdb-edit-bbpost-div input[name="title"]').on('change', function() {
            is_dirty = true;
            $(this).removeClass('indicate-error');
        });

        $('#pwtc-mapdb-edit-bbpost-div form textarea').on('change', function() {
            is_dirty = true;
            $(this).removeClass('indicate-error');
        });

        $('#pwtc-mapdb-edit-bbpost-div input[type="radio"]').change(function() {
            is_dirty = true;
	        $('#pwtc-mapdb-edit-bbpost-div .categories-fst').removeClass('indicate-error');
        });

        $('#pwtc-mapdb-edit-bbpost-div form').on('submit', function(evt) {
            $('#pwtc-mapdb-edit-bbpost-div input').removeClass('indicate-error');
            $('#pwtc-mapdb-edit-bbpost-div textarea').removeClass('indicate-error');
            $('#pwtc-mapdb-edit-bbpost-div .categories-fst').removeClass('indicate-error');

            if ($('#pwtc-mapdb-edit-bbpost-div input[name="title"]').val().trim().length == 0) {
                show_warning('The <strong>post title</strong> cannot be blank.');
                $('#pwtc-mapdb-edit-bbpost-div input[name="title"]').addClass('indicate-error');
                evt.preventDefault();
                return;
            }

            if ($('#pwtc-mapdb-edit-bbpost-div textarea[name="content"]').val().trim().length == 0) {
                show_warning('The <strong>post content</strong> cannot be blank.');
                $('#pwtc-mapdb-edit-bbpost-div textarea[name="content"]').addClass('indicate-error');
                evt.preventDefault();
                return;
            }

            <?php if ( !empty($categories) ) { ?>
            var categories_empty = $('#pwtc-mapdb-edit-bbpost-div input[name="categories[]"]:checked').length == 0;
            if (categories_empty) {
                show_warning('You must choose a <strong>forum topic</strong>.');
                $('#pwtc-mapdb-edit-bbpost-div .categories-fst').addClass('indicate-error');
                evt.preventDefault();
                return;						
            }
            <?php } ?>

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
                <p class="help-text">Please keep your post titles short and concise.</p>
            </div>
            <div class="row column">
                <label>Post Content
                    <textarea name="content" rows="10"><?php echo $content; ?></textarea>
                </label>
                <div class="errmsg2"></div>
                <div class="tiny dark button-group">
                    <button class="bold-btn button">Bold</button>
                    <button class="italic-btn button">Italic</button>
                    <button class="url-btn button">URL</button>
                </div>
		        <p class="help-text">
                    Use the above buttons to insert allowed HTML markup around highlighted text. 
                    Only the following HTML markup is allowed: &lt;a&gt;, &lt;em&gt; and &lt;strong&gt;;
                    all others will be stripped out when this post is viewed.
                </p>
            </div>
            <?php if ( !empty($categories) ) { ?>
            <div class="row column">
                <fieldset class="categories-fst">
                    <legend>Forum Topic</legend>
                    <?php foreach($categories as $category) { ?>
                    <span><input type="radio" name="categories[]" value="<?php echo $category->term_id; ?>" id="<?php echo $category->slug; ?>" <?php echo in_array($category->term_id, $post_cats) ? 'checked': ''; ?>><label for="<?php echo $category->slug; ?>"><?php echo $category->name; ?></label></span>
                    <?php } ?>
                </fieldset>
                <p class="help-text">Choose the forum topic under which you want your post to appear.</p>
            </div>
            <?php } ?>
            <div class="row column errmsg"></div>
            <div class="row column clearfix">
            <?php if ($postid == 0) { ?>
                <button class="dark button float-left" type="submit">Save Draft</button>
            <?php } else if ($status == 'draft') { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="preview" value="Preview" type="submit"/>
                    <input class="dark button" name="draft" value="Update" type="submit"/>
                    <?php if (!$is_moderator) { ?>
                    <input class="dark button" name="pending" value="Submit for Review" type="submit"/>
                    <?php } else { ?>
                    <input class="dark button" name="publish" value="Publish" type="submit"/>
                    <?php } ?>
                </div>
            <?php } else if ($status == 'pending') { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="preview" value="Preview" type="submit"/>
                    <input class="dark button" name="pending" value="Update" type="submit"/>
                    <input class="dark button" name="publish" value="Publish" type="submit"/>
                    <input class="dark button" name="draft" value="Reject" type="submit"/>
                </div>
            <?php } else { ?>
                <div class="button-group float-left">
                    <input class="dark button" name="preview" value="Preview" type="submit"/>
                    <input class="dark button" name="publish" value="Update" type="submit"/>
                    <input class="dark button" name="draft" value="Unpublish" type="submit"/>
                </div>
            <?php } ?>
            </div>
        </form>
    </div>
</div>
<?php
