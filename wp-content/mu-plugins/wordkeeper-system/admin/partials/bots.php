<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link		https://wordkeeper.com
 * @since		2.0.0
 *
 * @package		WordKeeper\System
 * @subpackage	WordKeeper\System/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap-page-admin-panel">
	<div class="top-line">
		<div class="col">
			<h1>Bots</h1>
		</div>
		<div class="col">
			<!-- <a href="#"><img src="../wp-content/plugins/wordkeeper-system/admin/images/logo-icon.svg" alt="logo"></a> -->
			<img src="<?php echo esc_html(plugin_dir_url(dirname(__FILE__))); ?>images/logo-icon.svg" alt="logo">
		</div>
	</div>
	<div class="holder-postbox-widget">
		<div class="postbox-widget">
			<div class="holder-title-widget holder-with-tooltip">
				<h2>SEO Analysis Crawlers</h2>
				<span class="tooltips" data-template="seo-analysis-crawlers" role="button" tabindex="0" aria-expanded="false">?</span>
				<div class="tooltips-block tippy-popper" id="seo-analysis-crawlers">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>For most sites, SEO analysis bots are used more by competitors and self-interested parties than by the site owner or the site owner's SEO team. Unless you need the bots below for your own SEO analysis, it's best to leave them blocked and only enable them temporarily when you need them for your own analysis.</p>
					<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-seo-analysis-crawlers'>Watch Video</a>
				</div>
			</div>
			<div class="inside">
				<div class="description">
					For most sites, SEO analysis bots are used more by competitors and self-interested parties than by the site owner or the site owner's SEO team. Unless you need the bots below for your own SEO analysis, it's best to leave them blocked and only enable them temporarily when you need them for your own analysis.
				</div>
				<form action="#">
					<div class="list-form-item">
						<label class="form-control" for="bots-ahrefs">
							<input type="checkbox" name="bots/ahrefs" id="bots-ahrefs" <?php if($settings['bots/ahrefs'] === true): ?>checked<?php endif; ?>/>
							Block Ahrefs
						</label>
						<span class="tooltips" data-template="item-bots-ahrefs" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-ahrefs">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Ahrefs SEO crawler</p>
							<a href="https://ahrefs.com/robot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-moz">
							<input type="checkbox" name="bots/moz" id="bots-moz" <?php if($settings['bots/moz'] === true): ?>checked<?php endif; ?>/>
							Block Moz
						</label>
						<span class="tooltips" data-template="item-bots-moz" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-moz">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Moz Pro crawler (Rogerbot)</p>
							<a href="https://moz.com/help/moz-procedures/crawlers/rogerbot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-semrush">
							<input type="checkbox" name="bots/semrush" id="bots-semrush" <?php if($settings['bots/semrush'] === true): ?>checked<?php endif; ?>/>
							Block SemRush
						</label>
						<span class="tooltips" data-template="item-bots-semrush" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-semrush">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Semrush bot</p>
							<a href="https://www.semrush.com/bot/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-screaming-frog">
							<input type="checkbox" name="bots/screaming-frog" id="bots-screaming-frog" <?php if($settings['bots/screaming-frog'] === true): ?>checked<?php endif; ?>/>
							Block Screaming Frog
						</label>
						<span class="tooltips" data-template="item-bots-screaming-frog" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-screaming-frog">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Screaming Frog Spider bot</p>
							<a href="https://www.screamingfrog.co.uk/seo-spider/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-majestic">
							<input type="checkbox" name="bots/majestic" id="bots-majestic" <?php if($settings['bots/majestic'] === true): ?>checked<?php endif; ?>/>
							Block Majestic
						</label>
						<span class="tooltips" data-template="item-bots-majestic" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-majestic">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Majestic analysis bot</p>
							<a href="https://www.mj12bot.com/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-dataforseo">
							<input type="checkbox" name="bots/dataforseo" id="bots-dataforseo" <?php if($settings['bots/dataforseo'] === true): ?>checked<?php endif; ?>/>
							Block DataForSEO
						</label>
						<span class="tooltips" data-template="item-bots-dataforseo" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-dataforseo">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The DataForSEO backlink checker bot</p>
							<a href="https://dataforseo.com/dataforseo-bot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-raven">
							<input type="checkbox" name="bots/raven" id="bots-raven" <?php if($settings['bots/raven'] === true): ?>checked<?php endif; ?>/>
							Block Raven
						</label>
						<span class="tooltips" data-template="item-bots-raven" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-raven">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Raven SEO analysis crawler</p>
							<a href="https://raventools.com/seo-website-auditor/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
		<div class="postbox-widget">
			<div class="holder-title-widget holder-with-tooltip">
				<h2>Country + Language Specific Crawlers</h2>
				<span class="tooltips" data-template="country-language-crawlers" role="button" tabindex="0" aria-expanded="false">?</span>
				<div class="tooltips-block tippy-popper" id="country-language-crawlers">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>Some search engines caters to specific geographic regions or languages. If your primary demographic is outside of these regions or languages, allowing these bots to index your site only serves to distract your site from serving your primary audience. You should leave unneeded search engines disabled.</p>
					<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-country-language-crawlers'>Watch Video</a>
				</div>
			</div>
			<div class="inside">
				<div class="description">
					Some search engines caters to specific geographic regions or languages. If your primary demographic is outside of these regions or languages, allowing these bots to index your site only serves to distract your site from serving your primary audience. You should leave unneeded search engines disabled.
				</div>
				<form action="#">
					<div class="list-form-item">
						<label class="form-control" for="bots-yandex">
							<input type="checkbox" name="bots/yandex" id="bots-yandex" <?php if($settings['bots/yandex'] === true): ?>checked<?php endif; ?>/>
							Block Yandex
						</label>
						<span class="tooltips" data-template="item-bots-yandex" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-yandex">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Yandex is a search engine that caters to Russia and Russian-speaking nations. </p>
							<a href="https://yandex.com/support/webmaster/robot-workings/check-yandex-robots.html" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-baidu">
							<input type="checkbox" name="bots/baidu" id="bots-baidu" <?php if($settings['bots/baidu'] === true): ?>checked<?php endif; ?>/>
							Block Baidu
						</label>
						<span class="tooltips" data-template="item-bots-baidu" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-baidu">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Baidu is a search engine that caters to China and Chinese speakers. </p>
							<a href="https://baidu.com/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-huawei">
							<input type="checkbox" name="bots/huawei" id="bots-huawei" <?php if($settings['bots/huawei'] === true): ?>checked<?php endif; ?>/>
							Block Huawei
						</label>
						<span class="tooltips" data-template="item-bots-huawei" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-huawei">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Petal search engine is Huawei's mobile search engine. It caters to Huawei mobile devices users. </p>
							<a href="https://aspiegel.com/petalbot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-seznam">
							<input type="checkbox" name="bots/seznam" id="bots-seznam" <?php if($settings['bots/seznam'] === true): ?>checked<?php endif; ?>/>
							Block Seznam
						</label>
						<span class="tooltips" data-template="item-bots-seznam" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-seznam">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Seznam is a search engine that caters to the Czech Republic.</p>
							<a href="https://napoveda.seznam.cz/en/seznambot-crawler/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-mailru">
							<input type="checkbox" name="bots/mailru" id="bots-mailru" <?php if($settings['bots/mailru'] === true): ?>checked<?php endif; ?>/>
							Block Mail.RU
						</label>
						<span class="tooltips" data-template="item-bots-mailru" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-mailru">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Mail.ru is a mail service, internet portal, and social networking company catering to Russia and Russian-speaking nations.</p>
							<a href="https://en.wikipedia.org/wiki/VK_(company)" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-qwant">
							<input type="checkbox" name="bots/qwant" id="bots-qwant" <?php if($settings['bots/qwant'] === true): ?>checked<?php endif; ?>/>
							Block Qwant
						</label>
						<span class="tooltips" data-template="item-bots-qwant" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-qwant">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Qwant is a search engine that caters to France and French-speakers.</p>
							<a href="https://help.qwant.com/bot/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-sogou">
							<input type="checkbox" name="bots/sogou" id="bots-sogou" <?php if($settings['bots/sogou'] === true): ?>checked<?php endif; ?>/>
							Block Sogou
						</label>
						<span class="tooltips" data-template="item-bots-sogou" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-sogou">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Sogou is a search engine that caters to China and Chinese-speakers.</p>
							<a href="https://en.wikipedia.org/wiki/Sogou" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-coccoc">
							<input type="checkbox" name="bots/coccoc" id="bots-coccoc" <?php if($settings['bots/coccoc'] === true): ?>checked<?php endif; ?>/>
							Block Coc Coc
						</label>
						<span class="tooltips" data-template="item-bots-coccoc" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-coccoc">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Coc Coc is a search engine that caters to Vietnam and Vietnamese speakers.</p>
							<a href="https://help.coccoc.com/en/search-engine/coccoc-robots" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
		<div class="postbox-widget">
			<div class="holder-title-widget holder-with-tooltip">
				<h2>AI + Non-Search Crawlers</h2>
				<span class="tooltips" data-template="ai-non-search-crawlers" role="button" tabindex="0" aria-expanded="false">?</span>
				<div class="tooltips-block tippy-popper" id="ai-non-search-crawlers">
					<a class="close" href="#">close</a>
					<div class="tippy-arrow"></div>
					<p>AI and other non-search bots are often aggressive, resource consuming, and unwilling or unable to support robots.txt crawl speed directives. Often, they provide little to no clear value to site owners - in many cases being fully self-interested.  Unless you definitively know that you need them, you should block them to preserve your resources for search engines and visitors.</p>
					<a href="#" class="link-btn holder-poster sweetalert2-popups" data-swal-template-video-popups='#template-video-ai-non-search-crawlers'>Watch Video</a>
				</div>
			</div>
			<div class="inside">
				<div class="description">
					AI and other non-search bots are often aggressive, resource consuming, and unwilling or unable to support robots.txt crawl speed directives. Often, they provide little to no clear value to site owners - in many cases being fully self-interested.  Unless you definitively know that you need them, you should block them to preserve your resources for search engines and visitors.
				</div>
				<form action="#">
					<div class="list-form-item">
						<label class="form-control" for="bots-gptbot">
							<input type="checkbox" name="bots/gptbot" id="bots-gptbot" <?php if($settings['bots/gptbot'] === true): ?>checked<?php endif; ?>/>
							Block GPTBot
						</label>
						<span class="tooltips" data-template="item-bots-gptbot" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-gptbot">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The OpenAI GPT bot. Controls whether OpenAI is allowed to use your website to train and develop its AIs.</p>
							<a href="https://platform.openai.com/docs/gptbot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-google-extended">
							<input type="checkbox" name="bots/google-extended" id="google-extended" <?php if($settings['bots/google-extended'] === true): ?>checked<?php endif; ?>/>
							Block Google AI
						</label>
						<span class="tooltips" data-template="item-bots-google-extended" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-google-extended">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Controls whether Google Bard/Vertex/Gemini is allowed to use your website to train its AIs with your website.  Does NOT have any impact on search rank</p>
							<a href="https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-google-other">
							<input type="checkbox" name="bots/google-other" id="google-other" <?php if($settings['bots/google-other'] === true): ?>checked<?php endif; ?>/>
							Block Google Other
						</label>
						<span class="tooltips" data-template="item-bots-google-other" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-google-other">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Used for internal R&D at Google. Does NOT have any impact on search rank</p>
							<a href="https://developers.google.com/search/docs/crawling-indexing/overview-google-crawlers" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-amazonbot">
							<input type="checkbox" name="bots/amazonbot" id="bots-amazonbot" <?php if($settings['bots/amazonbot'] === true): ?>checked<?php endif; ?>/>
							Block Amazon Alexa/Echo
						</label>
						<span class="tooltips" data-template="item-bots-amazonbot" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-amazonbot">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Controls whether Amazon is allowed to use your website to train its AIs for internal use in apps like Echo/Alexa.  This bot has historically been quite aggressive.  Better to leave blocked unless you absolutely need it.</p>
							<a href="https://developer.amazon.com/amazonbot" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-applebot">
							<input type="checkbox" name="bots/applebot" id="bots-applebot" <?php if($settings['bots/applebot'] === true): ?>checked<?php endif; ?>/>
							Block Apple AI
						</label>
						<span class="tooltips" data-template="item-bots-applebot" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-applebot">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Controls whether Apple AI is allowed to use your website to train its AIs for their purposes (such as with Siri and Spotlight)</p>
							<a href="https://support.apple.com/en-us/HT204683" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-anthropic-ai">
							<input type="checkbox" name="bots/anthropic-ai" id="bots-anthropic-ai" <?php if($settings['bots/anthropic-ai'] === true): ?>checked<?php endif; ?>/>
							Block Anthropic AI
						</label>
						<span class="tooltips" data-template="item-bots-anthropic-ai" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-anthropic-ai">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Controls whether Anthropic AI is allowed to use your website to train its AIs for their purposes.</p>
							<a href="https://www.anthropic.com/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-ccbot">
							<input type="checkbox" name="bots/ccbot" id="bots-ccbot" <?php if($settings['bots/ccbot'] === true): ?>checked<?php endif; ?>/>
							Block Common Crawl
						</label>
						<span class="tooltips" data-template="item-bots-ccbot" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-ccbot">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>Controls whether Common Crawl is allowed to use your website to crawl it and make it available as a public data set.</p>
							<a href="https://commoncrawl.org/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>
					<div class="list-form-item">
						<label class="form-control" for="bots-facebookbot">
							<input type="checkbox" name="bots/facebookbot" id="bots-facebookbot" <?php if($settings['bots/facebookbot'] === true): ?>checked<?php endif; ?>/>
							Block Meta/Facebook AI
						</label>
						<span class="tooltips" data-template="item-bots-facebookbot" role="button" tabindex="0">?</span>
						<div class="tooltips-block tippy-popper" id="item-bots-facebookbot">
							<a class="close" href="#">close</a>
							<div class="tippy-arrow"></div>
							<p>The Meta/Facebook/LLaMa AI. Controls whether Meta/Facebook AI is allowed to use your website to train its AIs for their purposes and language models.</p>
							<a href="https://developers.facebook.com/docs/sharing/bot/" target="_blank" class="link-btn">More Info</a>
						</div>
					</div>

					<button type="button" class="btn button-primary sweetalert-btn" data-type="save" data-submit data-waiting="Saving" data-success="Saved" data-path="settings">Save</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script src="https://player.vimeo.com/api/player.js"></script>

<template id='template-video-seo-analysis-crawlers'>
	<swal-html>
		<div class="holder-video-popup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026978555?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="SEO Analysis Crawlers"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<template id='template-video-country-language-crawlers'>
	<swal-html>
		<div class="holder-video-popup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026978446?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="Country Language Crawlers"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<template id='template-video-ai-non-search-crawlers'>
	<swal-html>
		<div class="holder-video-popup">
			<div style="padding-top: 56.25%; position: relative">
				<iframe src="https://player.vimeo.com/video/1026978320?title=0&amp;byline=0&amp;portrait=0&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479&amp;autoplay=1" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media" style="position:absolute;top:0;left:0;width:100%;height:100%;" title="AI NON Search Crawlers"></iframe>
			</div>
		</div>
	</swal-html>
</template>

<!-- Save Template -->
<?php include plugin_dir_path(__FILE__) . '/dialogs/saving.php'; ?>

<!-- All Notification types -->
<?php include plugin_dir_path(__FILE__) . '/notifications/all.php'; ?>