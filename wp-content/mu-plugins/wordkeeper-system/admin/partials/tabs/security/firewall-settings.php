<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>Firewall Settings</h2>
	</div>
	<div class="inside">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="firewall-headers">
					<input type="checkbox" name="firewall/headers" id="firewall-headers" checked />
					Enable XSS And iFrame Protections
				</label>
				<span class="tooltips" data-template="item-firewall-headers">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-headers">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Add security headers to block certain types of XSS and clickjacking.</p>
					<a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP" target="_blank" class="link-btn">More Info</a>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-ua">
					<input type="checkbox" name="firewall/ua" id="firewall-ua" />
					Block Bad Bots And User Agents
				</label>
				<span class="tooltips" data-template="item-firewall-ua">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-ua">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block known suspicious and malicious browsers and bots.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-referer">
					<input type="checkbox" name="firewall/referer" id="firewall-referer" />
					Block Bad/Spam Referring Sites
				</label>
				<span class="tooltips" data-template="item-firewall-referer">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-referer">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block known bad/spam sites directing traffic to your site.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-type">
					<input type="checkbox" name="firewall/type" id="firewall-type" />
					Block Non-PHP Script Files
				</label>
				<span class="tooltips" data-template="item-firewall-type">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-type">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block access to any server-side script file that isn't PHP or JS.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-method">
					<input type="checkbox" name="firewall/method" id="firewall-method" />
					Block Uncommon HTTP Methods
				</label>
				<span class="tooltips" data-template="item-firewall-method">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-method">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block less frequently used HTTP methods to non-REST URLs.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-system">
					<input type="checkbox" name="firewall/system" id="firewall-system" />
					Block Suspicious Requests
				</label>
				<span class="tooltips" data-template="item-firewall-system">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-system">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block requests that closely mirror hacker behavior.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="firewall-char">
					<input type="checkbox" name="firewall/char" id="firewall-char" />
					Block Non-English Characters
				</label>
				<span class="tooltips" data-template="item-firewall-char">?</span>
				<div class="tooltips-block tippy-popper" id="item-firewall-char">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Block characters in URLs that aren't in typical western/latin alphabets.</p>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>
<div class="postbox-widget">
	<div class="holder-title-widget">
		<h2>API Interactions</h2>
	</div>
	<div class="inside">
		<form action="#">
			<div class="list-form-item">
				<label class="form-control" for="wp-rest">
					<input type="checkbox" name="wp/rest" id="wp-rest" checked />
					Protect WP REST API access
				</label>
				<span class="tooltips" data-template="item-wp-rest">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-rest">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Limit core WP REST access to authorized access only.</p>
				</div>
			</div>
			<div class="list-form-item">
				<label class="form-control" for="wp-xmlrpc">
					<input type="checkbox" name="wp/xmlrpc" id="wp-xmlrpc" />
					Block WP XML-RPC access
				</label>
				<span class="tooltips" data-template="item-wp-xmlrpc">?</span>
				<div class="tooltips-block tippy-popper" id="item-wp-xmlrpc">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Completely block access to WP's xmlrpc.php file.</p>
				</div>
			</div>
			<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
		</form>
	</div>
</div>