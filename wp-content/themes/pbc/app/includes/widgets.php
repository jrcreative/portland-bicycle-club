<?php
add_action( 'widgets_init', function(){
    register_widget( 'PBC\Sharethis' );
    register_widget( 'PBC\UpcomingRides' );
});