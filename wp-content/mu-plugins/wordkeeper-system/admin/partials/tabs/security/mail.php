<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Mail Settings</h2>
	</div>
	<div class="inside">
		<div class="description">
			Set your From Name and From Email address from this panel.
		</div>
		<form action="#">
            <ul class="list-settings">
                <li class="item">
                    <div class="list-two-col">
                        <div class="row">
                            <div class="col-left">
                                <label class="form-control" for="item-mail-name">
                                    From Name:
                                </label>
                                <span class="tooltips" data-template="item-mail-name" role="button" tabindex="0">?</span>
                                <div class="tooltips-block tippy-popper" id="item-mail-name">
                                    <a class="close" href="#">close</a>
                                    <div class="tippy-arrow"></div>
                                    <p>Set the FROM Name that your site will use to send emails.</p>
                                </div>
                            </div>
                            <div class="col-right">
                                <div class="form-item holder-input">
                                    <input type="text" placeholder="Name" name="mail/name" id="mail-name" value="<?php echo esc_attr($this->settings['mail/name']); ?>" required data-validate="regex" data-regex="^[\w\-]+$" data-no-validate="Valid names may only contain letters, numbers, dashes, and underscores">
                                    <div class="text-error">Error Message</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="item">
                    <div class="list-two-col">
                        <div class="row">
                            <div class="col-left">
                                <label class="form-control" for="item-mail-email">
                                    From Email:
                                </label>
                                <span class="tooltips" data-template="item-mail-email" role="button" tabindex="0">?</span>
                                <div class="tooltips-block tippy-popper" id="item-mail-email">
                                    <a class="close" href="#">close</a>
                                    <div class="tippy-arrow"></div>
                                    <p>Set the FROM Email that your site will use to send emails.</p>
                                </div>
                            </div>
                            <div class="col-right">
                                <div class="form-item holder-input">
                                    <input type="text" placeholder="Email" name="mail/email" id="mail-email" value="<?php echo esc_attr($this->settings['mail/email']); ?>" required data-validate="email" data-no-validate="Must be a valid email address">
                                    <div class="text-error">Error Message</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            </ul>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>