<?php

namespace WordKeeper\System;

// Common core functions with special handling
// Including them either speeds them up by allowing special OpCode instructions
// Or reduces moderate overhead associated with fallback from the active namespace
// without having to use FQFN's for every reference
use array_pad;
use count;
use function_exists;
use hash_algos;
use in_array;
use is_array;
use is_string;
use preg_match;
use preg_replace;
use str_replace;
use strlen;
use strpos;
use strrpos;
use substr_replace;


/**
 * Sanitize and return query string args
 *
 * @param array $args
 * @return array
 */
function get_sanitized_args($args = array()){
	$ignored = array(
		'utm_source',
		'utm_campaign',
		'utm_medium',
		'utm_term',
		'utm_content',
		'utm_expid',
		'utm_vsrefdom',
		'utm_id',
		'utm_source_platform',
		'utm_creative_format',
		'utm_marketing_tactic',
		'fb_action_ids',
		'fb_action_types',
		'fb_ad_id',
		'fb_source',
		'twclid',
		'hsa_acc',
		'hsa_cam',
		'hsa_grp',
		'hsa_ad',
		'hsa_net',
		'hsa_src',
		'hsa_ver',
		'hsa_la',
		'hsa_ol',
		'hsa_mt',
		'hsa_kw',
		'hsa_tgt',
		'gclid',
		'gclsrc',
		'gPromoCode',
		'gQT',
		'gbraid',
		'dclid',
		'gad_source',
		'gad_campaignid',
		'wbraid',
		'srsltid',
		'_gl',
		'_ga',
		'fbclid',
		'fbaid',
		'ff_campaign',
		'ff_content',
		'ff_medium',
		'ff_source',
		'mc_cid',
		'mc_eid',
		'mkt_tok',
		'_hsenc',
		'_hsmi',
		'__hssc',
		'__hstc',
		'_nlid',
		'_nhids',
		'hsCtaTracking',
		'eventid',
		'externalid',
		'wickedsource',
		'wickedid',
		'_wpnonce',
		'vgo_ee',
		'_ke',
		'_kx',
		'msclkid',
		'hootPostID',
		'elq',
		'elqCampaignId',
		'elqak',
		'elqTrackId',
		'elqaid',
		'elqat',
		'_bhlid',
		'ef_id',
		's_kwcid',
		'epik',
		'yclid',
		'_bta_tid',
		'_bta_c',
		'mtm_campaign',
		'mtm_keyword',
		'mtm_source',
		'mtm_medium',
		'mtm_content',
		'mtm_cid',
		'mtm_group',
		'mtm_placement',
		'matomo_campaign',
		'matomo_keyword',
		'matomo_source',
		'matomo_medium',
		'matomo_content',
		'matomo_cid',
		'matomo_group',
		'matomo_placement',
		'pk_campaign',
		'pk_kwd',
		'pk_keyword',
		'piwik_campaign',
		'piwik_kwd',
		'piwik_keyword',
	);

	// If $args isn't passed to the function, default to a parsed query string
	// Do NOT rely on $_GET since it can be (and often is) manipulated by plugins
	if(empty($args) && !empty($_SERVER['QUERY_STRING'])){
		parse_str($_SERVER['QUERY_STRING'], $args);
	}

	foreach($args as $name => $value){
		if(in_array($name, $ignored)){
			unset($args[$name]);
		}
	}

	return $args;
}


/**
 * Sanitize file names to remove common problem characters
 *
 * @param string $filename
 * @return string
 */
function sanitize_filename($filename){
	// Backup original file name
	$original = $filename;

	// Remove control chars
	$filename = preg_replace('#[\x00-\x1F\x7F]#u', '', $filename);

	// Remove or replace unicode punctuation with normal punctuation
	$filename = strtolower(urlencode($filename));
	$filename = preg_replace('#%e2%80%(?:9c|9d|b3|b6)#', '', $filename);  // curly double quote
	$filename = preg_replace('#%e2%80%(?:98|99|b2|b5)#', '', $filename);  // curly single quote
	$filename = preg_replace('#%e2%80%af#', '', $filename); // narrow, no-break space
	$filename = preg_replace('#%c3%a2%c2%80%c2%af#', '', $filename); // another narrow, no-break space
	$filename = preg_replace('#%e2%80%(?:93|94)#', '-', $filename);  // mdash
	$filename = preg_replace('#%c3%97#', 'x', $filename);  // pseudo x
	$filename = urldecode($filename);
	$filename = preg_replace('#[!@`~$%^&*+=\|\\;<>,?\'"\#\{\}\(\)]#', '', $filename);  // unnecessary punctuation
	$filename = preg_replace('#[\s\t\n\r]#', '-', $filename);  // whitespace

	// Replace quote, unnecessary punctuation, math symbols, and open/close punctuation like parentheses and braces
	$replace = $filename;
	$replace = preg_replace('#\p{Pd}#', '-', $replace);  // dash punctuation
	$replace = preg_replace('#\p{Pe}#', '', $replace);  // close punctuation (like braces, parentheses)
	$replace = preg_replace('#\p{Pf}#', '', $replace);  // final punctuation (like quotes)
	$replace = preg_replace('#\p{Pi}#', '', $replace);  // initial punctuation (like quotes)
	$replace = preg_replace('#\p{Ps}#', '', $replace);  // open punctuation (like braces, parentheses)
	$replace = preg_replace('#\p{Sm}#', '', $replace);  // math symbols

	// More deeply sanitize to remove unicode chars if possible
	$chars = str_split($replace);
	foreach($chars as $char){
		$codepoint = mb_ord($char);

		// Replace chars that are not on the keyboard
		if((is_numeric($codepoint) && $codepoint > 255) || !is_numeric($codepoint)){
			$replace = str_replace($char, '', $replace);
		}
	}

	// If the entire filename has been removed, fallback to more basic sanitation
	if(preg_match('#^(?:[\-_\s\.]*)\.[0-9a-zA-Z]*$#', $replace) == 1){
		// If even the basic sanitation removes the whole file name, fall back to original
		if(preg_match('#^(?:[\-_\s\.]*)\.[0-9a-zA-Z]*$#', $filename) == 1){
			$filename = $original;
		}
	}
	// Otherwise use the sanitized file name
	else{
		$filename = $replace;
	}

	return $filename;
}

/**
 * Determine if REST request is from network admin
 *
 * @param boolean $multisite
 * @return boolean
 */
function from_network_admin(){
	$multisite = (defined('MULTISITE') && MULTISITE === true);

	$referer = wp_get_referer();
	$parts = parse_url($referer);
	$domain = $parts['host'];
	$path = $parts['path'];

	$valid_domain = $_SERVER['HTTP_HOST'];
	$valid_path = '/wp-admin/network/admin.php';

	$network = ($multisite && is_main_site() && strcasecmp($domain, $valid_domain) === 0 && strcasecmp($path, $valid_path) === 0) ? true : false;

	// we could remove the following line and just return the result of previous expression directly,
	// this line exists purely for readability purpose
	return $network;
}
