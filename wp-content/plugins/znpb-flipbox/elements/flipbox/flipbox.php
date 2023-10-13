<?php if(! defined('ABSPATH')){ return; }
/*
	Name: FlipBox Element
	Description: This element will generate an animated flipping box
	Class: ZnFlipbox
	Category: content
	Keywords: 3d, realistic, card, image, hover
	Level: 3
	Style: true
	Multiple: true
*/


class ZnFlipbox extends ZnElements {

	public static function getName(){
		return __( "FlipBox", 'znpb-flipbox-element' );
	}
	function options() {

		$uid = $this->data['uid'];

		$options = array(
			'has_tabs'  => true,

			'general' => array(
				'title' => 'General',
				'options' => array(

					array(
						'id'          => 'height',
						'name'        => __( 'Element Height', 'znpb-flipbox-element'),
						'description' => __( 'Choose the desired height for this element.', 'znpb-flipbox-element' ),
						'type'        => 'smart_slider',
						'std'        => '400',
						'helpers'     => array(
							'min' => '0',
							'max' => '1400'
						),
						'supports' => array('breakpoints'),
						'units' => array('px'),
						// 'properties' => array('min-height','height'),
						'live' => array(
							'type'      => 'css',
							'css_class' => '.'.$uid,
							'css_rule'  => 'height',
							'unit'      => 'px'
						),
					),

					array (
						"name"        => __( "Flip Effect Direction", 'znpb-flipbox-element' ),
						"description" => __( "Select the flipping direction.", 'znpb-flipbox-element' ),
						"id"          => "rotate",
						"std"         => "X",
						'type'        => 'select',
						'options'        => array(
							'X' => __( "Horizontal.", 'znpb-flipbox-element' ),
							'Y' => __( "Vertical.", 'znpb-flipbox-element' ),
						),
					),

					array (
						"name"        => __( "Horizontal Alignment", 'znpb-flipbox-element' ),
						"description" => __( "Choose the horizontal alignment of the content/text.", 'znpb-flipbox-element' ),
						"id"          => "halign",
						"std"         => "Center",
						'type'        => 'select',
						'options'        => array(
							'Left' => __( "Left", 'znpb-flipbox-element' ),
							'Center' => __( "Center", 'znpb-flipbox-element' ),
							'Right' => __( "Right", 'znpb-flipbox-element' ),
						),
						'live'        => array(
							'type'      => 'class',
							'css_class' => '.'.$uid,
							'val_prepend'  => 'znFlipbox--hAlign',
						),
					),

					array (
						"name"        => __( "Vertical Alignment", 'znpb-flipbox-element' ),
						"description" => __( "Choose the vertical alignment of the content/text.", 'znpb-flipbox-element' ),
						"id"          => "valign",
						"std"         => "Middle",
						'type'        => 'select',
						'options'        => array(
							'Top' => __( "Top", 'znpb-flipbox-element' ),
							'Middle' => __( "Middle", 'znpb-flipbox-element' ),
							'Bottom' => __( "Bottom", 'znpb-flipbox-element' ),
						),
						'live'        => array(
							'type'      => 'class',
							'css_class' => '.'.$uid,
							'val_prepend'  => 'znFlipbox--vAlign',
						),
					),

					array (
						"name"        => __( "Effect Speed", 'znpb-flipbox-element' ),
						"description" => __( "Choose the effect's speed.", 'znpb-flipbox-element' ),
						"id"          => "speed",
						"std"         => "Normal",
						'type'        => 'select',
						'options'        => array(
							'Fast' => __( "Fast", 'znpb-flipbox-element' ),
							'Normal' => __( "Normal", 'znpb-flipbox-element' ),
							'Slow' => __( "Slow", 'znpb-flipbox-element' ),
						),
					),

					array (
						"name"        => __( "Enable Shadows", 'znpb-flipbox-element' ),
						"description" => __( "Enable shadows for the element?", 'znpb-flipbox-element' ),
						"id"          => "shadows",
						"std"         => "Hover",
						'type'        => 'select',
						'options'        => array(
							'no' => __( "No", 'znpb-flipbox-element' ),
							'Always' => __( "Yes - Always", 'znpb-flipbox-element' ),
							'Hover' => __( "Yes - Only on hover", 'znpb-flipbox-element' ),
						),
					),

					array (
						"name"        => __( "Custom Perspective", 'znpb-flipbox-element' ),
						"description" => __( "Customise the perspective?", 'znpb-flipbox-element' ),
						"id"          => "perspective",
						"std"         => "1000",
						'type'        => 'slider',
						"helpers"     => array (
							"step" => "100",
							"min" => "500",
							"max" => "3000"
						),
					),

					array (
						"name"        => __( "Edit settings for each device breakpoint. ", 'znpb-flipbox-element' ),
						"description" => __( "This will enable you to have more control over the font typography, margins or padding of the element on each device. .", 'znpb-flipbox-element' ),
						"id"          => "breakpoints",
						"std"         => "lg",
						"tabs"        => true,
						"type"        => "zn_radio",
						"options"     => array (
							"lg"        => __( "LARGE", 'znpb-flipbox-element' ),
							"md"        => __( "MEDIUM", 'znpb-flipbox-element' ),
							"sm"        => __( "SMALL", 'znpb-flipbox-element' ),
							"xs"        => __( "EXTRA SMALL", 'znpb-flipbox-element' ),
						),
						"class"       => "zn_full zn_breakpoints"
					),

					/**
					 * Margins and padding
					 */

					// MARGINS
					array(
						'id'          => 'margin_lg',
						'name'        => 'Margin (Large Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container. Accepts negative margin.',
						'type'        => 'boxmodel',
						'std'			=> '',
						'placeholder' => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('lg') ),
						'live' => array(
							'type'		=> 'boxmodel',
							'css_class' => '.'.$uid,
							'css_rule'	=> 'margin',
						),
					),
					array(
						'id'          => 'margin_md',
						'name'        => 'Margin (Medium Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('md') ),
					),
					array(
						'id'          => 'margin_sm',
						'name'        => 'Margin (Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('sm') ),
					),
					array(
						'id'          => 'margin_xs',
						'name'        => 'Margin (Extra Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('xs') ),
					),
					// PADDINGS
					array(
						'id'          => 'padding_lg',
						'name'        => 'Padding (Large Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => '',
						'placeholder' => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('lg') ),
						'live' => array(
							'type'		=> 'boxmodel',
							'css_class' => '.'.$uid.' .znFlipbox',
							'css_rule'	=> 'padding',
						),
					),
					array(
						'id'          => 'padding_md',
						'name'        => 'Padding (Medium Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('md') ),
					),
					array(
						'id'          => 'padding_sm',
						'name'        => 'Padding (Small Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('sm') ),
					),
					array(
						'id'          => 'padding_xs',
						'name'        => 'Padding (Extra Small Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'breakpoints' , 'value'=> array('xs') ),
					),

				),
			),

			'active' => array(
				'title' => 'Front options',
				'options' => array(

					array (
						"name"        => __( "Background Image", 'znpb-flipbox-element' ),
						"description" => __( "Select a background image.", 'znpb-flipbox-element' ),
						"id"          => "front_img",
						"std"         => "",
						"type"        => "media",
					),

					array (
						"name"        => __( "Background Overlay", 'znpb-flipbox-element' ),
						"description" => __( "Add some description.", 'znpb-flipbox-element' ),
						"id"          => "front_overlay",
						"std"         => "rgba(0,0,0,0.5)",
						"type"        => "colorpicker",
						"alpha"       => true,
					),

				),
			),

			'hover' => array(
				'title' => 'Back options',
				'options' => array(

					array (
						"name"        => __( "Background Image", 'znpb-flipbox-element' ),
						"description" => __( "Select a background image. If none is added, front will be used.", 'znpb-flipbox-element' ),
						"id"          => "back_img",
						"std"         => "",
						"type"        => "media",
					),

					array (
						"name"        => __( "Background Overlay", 'znpb-flipbox-element' ),
						"description" => __( "Add some description.", 'znpb-flipbox-element' ),
						"id"          => "back_overlay",
						"std"         => "rgba(0,0,0,0.5)",
						"type"        => "colorpicker",
						"alpha"       => true,
					),

				),
			),


		);

		return $options;
	}

	function element() {

		$options = $this->data['options'];

		//Class
		$classes = array();
		$classes[] = $uid = $this->data['uid'];
		$classes[] = zn_get_element_classes($options);

		$attributes = zn_get_element_attributes($options);

		// Options
		$classes[] = 'znFlipbox--shadows'.$this->opt('shadows', 'hover');
		$classes[] = 'znFlipbox--rotate' . $this->opt('rotate', 'X');
		$classes[] = 'znFlipbox--hAlign'. $this->opt('halign', 'Center');
		$classes[] = 'znFlipbox--vAlign' . $this->opt('valign', 'Middle');
		$classes[] = 'znFlipbox--speed' . $this->opt('speed', 'Normal');

		$classes[] = ZN()->pagebuilder->is_active_editor ? 'znFlipboxElm-pbOn' : 'znFlipboxElm-pbOff';
	?>

	<?php if(ZN()->pagebuilder->is_active_editor): ?>

		<input type="checkbox" id="<?php echo $uid ?>_show_back" class="znFlipboxElm-pbControl">
		<label for="<?php echo $uid ?>_show_back"></label>

	<?php endif; ?>

	<div class="znFlipboxElm <?php echo implode(' ', $classes); ?>" <?php echo $attributes; ?>>

	<?php

		$faces = array(
			array(
				'main' => 'znFlipbox-front',
				'content' => 'znFlipbox-contentFront',
				'overlay' => 'znFlipbox-overlayFront',
			),
			array(
				'main' => 'znFlipbox-back',
				'content' => 'znFlipbox-contentBack',
				'overlay' => 'znFlipbox-overlayBack',
			),
		);

		foreach ($faces as $i => $face) {

			echo '<div class="znFlipbox '. $face['main'] .'">';
				echo '<div class="znFlipbox-content '. $face['content'] .'">';

					echo '<div class="row zn_columns_container zn_content zn_col_container-flipbox" data-droplevel="1">';

					if ( empty( $this->data['content'][$i] ) ) {
	                    $column = ZNPB()->add_module_to_layout( 'ZnColumn', array() , array(), 'col-sm-12' );
	                    $this->data['content'][$i] = array ( $column );
	                }

					if ( !empty( $this->data['content'][$i] ) ) {
						ZNPB()->zn_render_content( $this->data['content'][$i] );
					}

					echo '</div>';

				echo '</div>';
				echo '<div class="'. $face['overlay'] .'"></div>';
			echo '</div>';

		}

	?>

	</div>

	<?php

	}

	function css(){

		$uid = $this->data['uid'];
		$css = '';

		// Margins
		$margins = array();
		if($this->opt('margin_lg', '' )) $margins['lg'] = $this->opt('margin_lg');
		if($this->opt('margin_md', '' )) $margins['md'] = $this->opt('margin_md');
		if($this->opt('margin_sm', '' )) $margins['sm'] = $this->opt('margin_sm');
		if($this->opt('margin_xs', '' )) $margins['xs'] = $this->opt('margin_xs');
		if( !empty($margins) ){
			$margins['selector'] = '.'.$uid;
			$margins['type'] = 'margin';
			$css .= zn_push_boxmodel_styles( $margins );
		}

		// Paddings
		$paddings = array();
		if($this->opt('padding_lg', '' )) $paddings['lg'] = $this->opt('padding_lg');
		if($this->opt('padding_md', '' )) $paddings['md'] = $this->opt('padding_md');
		if($this->opt('padding_sm', '' )) $paddings['sm'] = $this->opt('padding_sm');
		if($this->opt('padding_xs', '' )) $paddings['xs'] = $this->opt('padding_xs');
		if( !empty($paddings) ){
			$paddings['selector'] = '.' . $uid . ' .znFlipbox';
			$paddings['type'] = 'padding';
			$css .= zn_push_boxmodel_styles( $paddings );
		}

		// Front Image
		$back_fallback = '';
		if( $front_img = $this->opt('front_img') ){
			$css .= '.' . $uid . ' .znFlipbox-front{background-image:url("'. $front_img .'")}';
			$back_fallback = $front_img;
		}
		// Overlay
		$front_overlay = $this->opt('front_overlay', 'rgba(0,0,0,0.5)');
		if( $front_overlay != 'rgba(0,0,0,0.5)' ){
			$css .= '.' . $uid . ' .znFlipbox-overlayFront {background:'. $front_overlay .'}';
		}

		// Back Image
		$back_img = $this->opt('back_img') ? $this->opt('back_img') : $back_fallback;
		if(!empty($back_img)){
			$css .= '.' . $uid . ' .znFlipbox-back{background-image:url("'. $back_img .'")}';
		}
		// Overlay
		$back_overlay = $this->opt('back_overlay', 'rgba(0,0,0,0.5)');
		if( $back_overlay != 'rgba(0,0,0,0.5)' ){
			$css .= '.' . $uid . ' .znFlipbox-overlayBack {background:'. $back_overlay .'}';
		}

		// Perspective
		$perspective = $this->opt('perspective', '1000');
		if( $perspective != '1000' ){
			$css .= '.' . $uid . '{-webkit-perspective: '. $perspective .';perspective: '. $perspective .';}';
		}

		// Height
		$css .= zn_smart_slider_css( $this->opt( 'height' ), '.'.$uid );

		return $css;

	}

}