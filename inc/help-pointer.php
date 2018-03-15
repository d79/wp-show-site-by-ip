<?php

// dismissed pointers
$dismissed = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

// if not dismissed, add help pointer
if ( !in_array( 'wssbi_help', $dismissed ) ) {
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wssbi-help-pointer', plugins_url( 'js/help-pointer.js', dirname(__FILE__) ), array( 'wp-pointer' ) );
	$pointer = array(
		'p_id'    => 'wssbi_help',
		'target'  => '#contextual-help-link-wrap',
		'options' => array(
			'content' => sprintf( '<h3> %s </h3> <p> %s </p>',
				__( 'Got any questions?' ,'wp-show-site-by-ip'),
				sprintf(__( 'If you want to better understand how this plugin works, please take advantage of the help inline to find the answers you need.','wp-show-site-by-ip'), '<strong>', '</strong>')
			),
			'position' => array( 'edge' => 'top', 'align' => 'right' )
		)
	);
	wp_localize_script( 'wssbi-help-pointer', 'wssbiHelpPointer', $pointer );
	wp_add_inline_style( 'wp-pointer', '.wp-pointer-bottom .wp-pointer-arrow, .wp-pointer-top .wp-pointer-arrow, .wp-pointer-undefined .wp-pointer-arrow { left: initial; right: 30px }' );
}