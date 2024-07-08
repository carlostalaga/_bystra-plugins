<?php
/*
Plugin Name: Bystra Analytics
Plugin URI: 
Description: Script injections for the Header, After Opening Body and Footer. e.g. Facebook Pixel Code, Google Tag Manager, Linkedin insight tag and similar.
Version: 1.2
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

<!-- Google Tag Manager -->
<script>
(function(w, d, s, l, i) {
    w[l] = w[l] || [];
    w[l].push({
        'gtm.start': new Date().getTime(),
        event: 'gtm.js'
    });
    var f = d.getElementsByTagName(s)[0],
        j = d.createElement(s),
        dl = l != 'dataLayer' ? '&l=' + l : '';
    j.async = true;
    j.src =
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
    f.parentNode.insertBefore(j, f);
})(window, document, 'script', 'dataLayer', 'GTM-P3MLDVVW');
</script>
<!-- End Google Tag Manager -->

<?php
}
add_action( 'wp_head', 'bystra_header_scripts', 10 );


function bystra_body_open_scripts() {
/* To add theme support to trigger any actions hooked by wp_body_open, call the function <?php wp_body_open(); ?> just after the opening body tag. */
/* Your code to be inserted just after the opening of the body tag goes here */
?>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P3MLDVVW" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

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