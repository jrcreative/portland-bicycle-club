<?php
add_action('wp_footer', 'clear_wordkeeper_cache');

function clear_wordkeeper_cache(){
	if(isset($_GET['cache-clear']) && $_GET['cache-clear'] == 1){
		WordKeeper\System\Purge::purge_all();
	}
}

add_action('wp_footer', 'add_clear_cache_chron_job');

add_action('pwtc_chron_clear_cache', function () {
	WordKeeper\System\Purge::purge_all();
});

function add_clear_cache_chron_job() {
	if (!wp_next_scheduled('pwtc_chron_clear_cache')) {
		$datetime = new DateTime(null, new DateTimeZone(pwtc_get_timezone_string()));
		$datetime->setTime(1, 0);
		$datetime->add(new DateInterval('P1D'));
    		wp_schedule_event($datetime->getTimestamp(), 'daily', 'pwtc_chron_clear_cache');
	}
}
