<?php

echo '<div class="zn-animated-text-element '.$uid.'">';
	if( ! empty( $dynamic_text )) {

		echo '<span class="zn_animated_dynamic_texts" data-hidecursor="'.$hide_cursor_time.'" data-config=\''.json_encode($config).'\'>';
			$exported_dynamic_text = explode("\n", $dynamic_text);
			foreach ($exported_dynamic_text as $value) {
				echo '<span>'.$value.'</span>';
			}
		echo '</span>';
		echo '<span class="zn-dynamic-animated-typed"></span>';
	}

echo '</div>';