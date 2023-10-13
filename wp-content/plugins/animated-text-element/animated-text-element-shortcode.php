<?php

function ZnHg_animated_text_element_shortcode( $atts, $dynamic_text = null ){
	$uid = zn_uid();
	$defaults = array(
		'typeSpeed' 	=> 30,
		'startDelay'	=> 0,
		'backSpeed' 	=> 0,
		'backDelay' 	=> 500,
		'loop' 			=> false,
		'loopCount' 	=> false,
		'showCursor'	=> true,
		'cursorChar' 	=> '|',
	);

	$config = shortcode_atts( $defaults, $atts );
	$pluginInstance = Zn_Animated_Text_Element::get_instance();
	ob_start();
		include($pluginInstance->path . '/templates/element-output.php');
	return ob_get_clean();
}

add_shortcode( 'zn_animated_text', 'ZnHg_animated_text_element_shortcode' );