<?php


//add last line *******************custom ajax*****************/

function my_enqueue() {

    wp_enqueue_script( 'ajax-script', get_template_directory_uri() . '/pre_registration.js', array('jquery') );

    wp_localize_script( 'ajax-script', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

}


add_action( 'wp_enqueue_scripts', 'my_enqueue' );
/*
