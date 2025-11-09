<?php
/*
Plugin Name: Bystra Scripts
Plugin URI: 
Description: Script injections for the Header, after Opening Body and Footer. e.g. Facebook Pixel Code, Google Tag Manager, Linkedin insight tag and similar.
Version: 1.4
Author: 
Author URI: 
License: GPL2
*/

/* Prevent direct access to the file */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

function bystra_header_scripts() {
/* Your header scripts go here */
?>



<?php
}
add_action( 'wp_head', 'bystra_header_scripts', 10 );


function bystra_body_open_scripts() {
/* To add theme support to trigger any actions hooked by wp_body_open, call the function <?php wp_body_open(); ?> just after the opening body tag. */
/* Your code to be inserted just after the opening of the body tag goes here */
?>



<?php
}
add_action( 'wp_body_open', 'bystra_body_open_scripts' );


function bystra_footer_scripts() {
/* Your footer scripts go here */
?>



<?php
}
add_action( 'wp_footer', 'bystra_footer_scripts', 10 );

?>