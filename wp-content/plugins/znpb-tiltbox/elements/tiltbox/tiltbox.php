<?php if(! defined('ABSPATH')){ return; }
/*
	Name: TiltBox Element
	Description: This element will generate an animated flipping box
	Class: ZnTiltbox
	Category: content
	Keywords: 3d, realistic, card, image, hover, effect
	Level: 3
	Style: true
*/


class ZnTiltbox extends ZnElements {

	public static function getName(){
		return __( "TiltBox", 'znpb-tiltbox-element' );
	}
	function options() {

		$uid = $this->data['uid'];

		$options = array(
			'has_tabs'  => true,

			'general' => array(
				'title' => 'Content',
				'options' => array(

					array (
						"name"        => __( "Select Image", 'znpb-tiltbox-element' ),
						"description" => __( "Please select a background image.", 'znpb-tiltbox-element' ),
						"id"          => "img",
						"std"         => "",
						"type"        => "media",
					),

					array (
						"name"        => __( "Title", 'znpb-tiltbox-element' ),
						"description" => __( "Please add a title.", 'znpb-tiltbox-element' ),
						"id"          => "title",
						"std"         => "",
						"type"        => "text",
						"class"       => "zn_input_xl",
					),

					array (
						"name"        => __( "Description", 'znpb-tiltbox-element' ),
						"description" => __( "Add some description.", 'znpb-tiltbox-element' ),
						"id"          => "desc",
						"std"         => "",
						"type"        => "text",
						"class"       => "zn_input_xl",
					),

					array (
						"name"        => __( "Add a link", 'znpb-tiltbox-element' ),
						"description" => __( "Add a link to this box.", 'znpb-tiltbox-element' ),
						"id"          => "link",
						"std"         => "",
						"type"        => "link",
						"options"     => zn_get_link_targets(),
					),


				),
			),

			'styles' => array(
				'title' => 'Styles',
				'options' => array(

					array (
						"name"        => __( "Box Style", 'znpb-tiltbox-element' ),
						"description" => __( "Please select the box style.", 'znpb-tiltbox-element' ),
						"id"          => "box_style",
						"std"         => "1",
						'type'        => 'select',
						'options'        => array(
							'1' => __( "Style #1.", 'znpb-tiltbox-element' ),
							'2' => __( "Style #2.", 'znpb-tiltbox-element' ),
							'3' => __( "Style #3.", 'znpb-tiltbox-element' ),
							'4' => __( "Style #4.", 'znpb-tiltbox-element' ),
							'5' => __( "Style #5.", 'znpb-tiltbox-element' ),
							'6' => __( "Style #6.", 'znpb-tiltbox-element' ),
							'7' => __( "Style #7.", 'znpb-tiltbox-element' ),
							'8' => __( "Style #8.", 'znpb-tiltbox-element' ),
						),
					),

					array (
						"name"        => __( "Tilt Movement", 'znpb-tiltbox-element' ),
						"description" => __( "Please select a tilting style.", 'znpb-tiltbox-element' ),
						"id"          => "movement",
						"std"         => "1",
						'type'        => 'select',
						'options'        => array(
							'1' => __( "Movement #1", 'znpb-tiltbox-element' ),
							'2' => __( "Movement #2", 'znpb-tiltbox-element' ),
							'3' => __( "Movement #3", 'znpb-tiltbox-element' ),
							'4' => __( "Movement #4", 'znpb-tiltbox-element' ),
							'5' => __( "Movement #5", 'znpb-tiltbox-element' ),
							'6' => __( "Movement #6", 'znpb-tiltbox-element' ),
							'7' => __( "Movement #7", 'znpb-tiltbox-element' ),
						),
					),

					array(
						"name"        => __( "Box Height", 'znpb-tiltbox-element' ),
						"description" => __( "Please choose a height.", 'znpb-tiltbox-element' ),
						"id"          => "height",
						'type'        => 'smart_slider',
						'std'        => '415',
						'helpers'     => array(
							'min' => '0',
							'max' => '1000'
						),
						'supports' => array('breakpoints'),
						'units' => array('px', 'vh'),
						'live' => array(
							'type'      => 'css',
							'css_class' => '.'.$uid. ' > .znTiltBox-tilter',
							'css_rule'  => 'height',
							'unit'      => 'px'
						),
					),

					array (
						"name"        => __( "Title Typography", 'znpb-tiltbox-element' ),
						"description" => __( "Specify the typography properties for the title.", 'znpb-tiltbox-element' ),
						"id"          => "typo",
						"std"         => '',
						'supports'   => array( 'size', 'font', 'style', 'line', 'color', 'weight', 'spacing', 'case' ),
						"type"        => "font",
						'live' => array(
							'type'      => 'font',
							'css_class' => '.'.$uid. ' .znTiltBox-tilter__title',
						),
					),

					array (
						"name"        => __( "Description Typography", 'znpb-tiltbox-element' ),
						"description" => __( "Specify the typography properties for the title.", 'znpb-tiltbox-element' ),
						"id"          => "desc_typo",
						"std"         => '',
						'supports'   => array( 'size', 'font', 'style', 'line', 'color', 'weight', 'spacing', 'case' ),
						"type"        => "font",
						'live' => array(
							'type'      => 'font',
							'css_class' => '.'.$uid. ' .znTiltBox-tilter__description',
						),
					),

					/**
					 * Margins and padding
					 */
					array (
						"name"        => __( "Edit element margins for each device breakpoint. ", 'znpb-tiltbox-element' ),
						"description" => __( "This will enable you to have more control over the padding of the container on each device. Click to see <a href='http://hogash.d.pr/1f0nW' target='_blank'>how box-model works</a>.", 'znpb-tiltbox-element' ),
						"id"          => "spacing_breakpoints",
						"std"         => "lg",
						"tabs"        => true,
						"type"        => "zn_radio",
						"options"     => array (
							"lg"        => __( "LARGE", 'znpb-tiltbox-element' ),
							"md"        => __( "MEDIUM", 'znpb-tiltbox-element' ),
							"sm"        => __( "SMALL", 'znpb-tiltbox-element' ),
							"xs"        => __( "EXTRA SMALL", 'znpb-tiltbox-element' ),
						),
						"class"       => "zn_full zn_breakpoints"
					),
					// MARGINS
					array(
						'id'          => 'margin_lg',
						'name'        => 'Margin (Large Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container. Accepts negative margin.',
						'type'        => 'boxmodel',
						'std'	  => '',
						'placeholder' => '0px',
						"dependency"  => array( 'element' => 'spacing_breakpoints' , 'value'=> array('lg') ),
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
						"dependency"  => array( 'element' => 'spacing_breakpoints' , 'value'=> array('md') ),
					),
					array(
						'id'          => 'margin_sm',
						'name'        => 'Margin (Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'spacing_breakpoints' , 'value'=> array('sm') ),
					),
					array(
						'id'          => 'margin_xs',
						'name'        => 'Margin (Extra Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'spacing_breakpoints' , 'value'=> array('xs') ),
					),

				),
			),

			'deco' => array(
				'title' => 'Decoration Options',
				'options' => array(


					array (
						"name"        => __( "Display Lines?", 'znpb-tiltbox-element' ),
						"description" => __( "Force the lines to display or not.", 'znpb-tiltbox-element' ),
						"id"          => "lines",
						"std"         => "",
						'type'        => 'zn_radio',
						'options'        => array(
							'' => __( "Inherit from style", 'znpb-tiltbox-element' ),
							'yes' => __( "Yes.", 'znpb-tiltbox-element' ),
							'no' => __( "No.", 'znpb-tiltbox-element' ),
						),
					),

					array (
						"name"        => __( "Line Color", 'znpb-tiltbox-element' ),
						"description" => __( "You can choose a color for the lines.", 'znpb-tiltbox-element' ),
						"id"          => "line_color",
						"std"         => "",
						"type"        => "colorpicker",
						"alpha"       => "true",
						"dependency"  => array( 'element' => 'lines' , 'value'=> array('yes', '') ),
					),

					array (
						"name"        => __( "Display Shine?", 'znpb-tiltbox-element' ),
						"description" => __( "Force the shine effect to display or not.", 'znpb-tiltbox-element' ),
						"id"          => "shine",
						"std"         => "",
						'type'        => 'zn_radio',
						'options'        => array(
							'' => __( "Inherit from style", 'znpb-tiltbox-element' ),
							'yes' => __( "Yes.", 'znpb-tiltbox-element' ),
							'no' => __( "No.", 'znpb-tiltbox-element' ),
						),
					),


					array (
						"name"        => __( "Display Overlay?", 'znpb-tiltbox-element' ),
						"description" => __( "Force the overlay to display or not.", 'znpb-tiltbox-element' ),
						"id"          => "overlay",
						"std"         => "",
						'type'        => 'zn_radio',
						'options'        => array(
							'' => __( "Inherit from Style", 'znpb-tiltbox-element' ),
							'yes' => __( "Yes.", 'znpb-tiltbox-element' ),
							'no' => __( "No.", 'znpb-tiltbox-element' ),
						),
					),

					array(
						'id'          => 'overlay_color',
						'name'        => 'Overlay Color Type',
						'description' => 'Override the overlay colors?',
						'type'        => 'select',
						'std'         => 'no',
						"options"     => array (
							"normal" => __( "Normal Background Color", 'zn_framework' ),
							"gradient" => __( "Gradient Background Color", 'zn_framework' ),
							"no"  => __( "No", 'zn_framework' )
						)
					),

					array(
						'id'          => 'overlay_color_main',
						'name'        => 'Overlay Background Color/Gradient Top',
						'description' => 'Pick a color.',
						'type'        => 'colorpicker',
						'std'         => '',
						'alpha'		=> true,
						"dependency"  => array( 'element' => 'overlay_color' , 'value'=> array('normal', 'gradient') ),
					),

					array(
						'id'          => 'overlay_color_color_gradient',
						'name'        => 'Overlay Gradient Bottom Color',
						'description' => 'Pick a color',
						'type'        => 'colorpicker',
						'std'         => '',
						'alpha'		=> true,
						"dependency"  => array( 'element' => 'overlay_color' , 'value'=> array('gradient') ),
					),


				),
			),


		);

		return $options;
	}

	function element() {

		$options = $this->data['options'];

		//Class
		$classes[] 	= $uid = $this->data['uid'];
		$classes[] 	= zn_get_element_classes($options);

		$attributes = zn_get_element_attributes($options);

		// Style
		$box_style = $this->opt('box_style', 1);

		// Link
		$link['start'] = $link['end'] = '';
		$link_class = 'znTiltBox-tilter znTiltBox-tilter--'.$box_style;
		$link_attr = 'data-movement="'.  esc_attr( $this->opt('movement', 1) ) .'"';
		$link = zn_extract_link(
			$this->opt('link',''),
			$link_class,
			$link_attr,
			'<div class="'.$link_class.'" '.$link_attr.'>',
			'</div>'
		);

		// Deco Defaults
		$shine = true;
		$overlay = true;
		$lines = true;

		switch($box_style):
			case"1":
				$overlay = false;
			break;
			case"3":
				$lines = false;
			break;
		endswitch;

		if( $force_shine = $this->opt('shine','') ){
			$shine = $this->is_true($force_shine);
		}
		if( $force_overlay = $this->opt('overlay','') ){
			$overlay = $this->is_true($force_overlay);
		}
		if( $force_lines = $this->opt('lines','') ){
			$lines = $this->is_true($force_lines);
		}
	?>

	<div class="znTiltboxElm <?php echo implode(' ', $classes); ?>" <?php echo $attributes; ?>>

		<?php echo $link['start']; ?>


		<figure class="znTiltBox-tilter__figure">

			<?php if( $img = $this->opt('img','') ){
				$alt = ZngetImageAltFromUrl($img, true);
				$title = ZngetImageTitleFromUrl($img, true);
				echo '<img class="znTiltBox-tilter__image cover-fit-img" src="'.$img.'" '.$alt.' '.$title.' />';
			} ?>

			<?php if( $shine ){ ?>
			<div class="znTiltBox-tilter__deco znTiltBox-tilter__deco--shine"><div></div></div>
			<?php } ?>

			<?php if( $overlay ){ ?>
			<div class="znTiltBox-tilter__deco znTiltBox-tilter__deco--overlay"></div>
			<?php } ?>

			<figcaption class="znTiltBox-tilter__caption">
				<?php

				if( $title = $this->opt('title','') )
					echo '<h3 class="znTiltBox-tilter__title">'. $title .'</h3>';

				if( $desc = $this->opt('desc','') )
					echo '<p class="znTiltBox-tilter__description">'. $desc .'</p>';

				?>
			</figcaption>

			<?php if( $lines ){ ?>
			<svg class="znTiltBox-tilter__deco znTiltBox-tilter__deco--lines"><rect></rect></svg>
			<?php } ?>

		</figure>

		<?php echo $link['end']; ?>

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

		// Height
		$selector = '.'.$uid.' .znTiltBox-tilter';
		$css .= zn_smart_slider_css( $this->opt( 'height' ), $selector );

		// Title Typo
		if( $this->opt('typo', '' ) ){
			$css .= zn_typography_css(array(
					'selector' => '.'.$uid.' .znTiltBox-tilter__title',
					'lg' =>  $this->opt('typo', '' ),
				)
			);
		}
		// Description Typo
		if( $this->opt('desc_typo', '' ) ){
			$css .= zn_typography_css(array(
					'selector' => '.'.$uid.' .znTiltBox-tilter__description',
					'lg' =>  $this->opt('desc_typo', '' ),
				)
			);
		}

		// Line Color
		if( $line_color = $this->opt('line_color','') ){
			$css .= '.'.$uid.' .znTiltBox-tilter__deco--lines rect {stroke:'.$line_color.'}';
		}

		// Overlay Color
		$overlay_color = $this->opt('overlay_color', 'no');
		if( $overlay_color != 'no' ){

			$overlay_color_main = $this->opt('overlay_color_main','');

			if( $overlay_color == 'normal' && $overlay_color_main ){
				$css .= '.'.$uid.' .znTiltBox-tilter__deco--overlay {background:'.$overlay_color_main.'}';
			}
			elseif ( $overlay_color == 'gradient' ){
				$top_gr = $this->opt('overlay_color_color_gradient','') ? $this->opt('overlay_color_color_gradient','') : 'rgba(0,0,0,0)';
				$btn_gr = $overlay_color_main ? $overlay_color_main : 'rgba(0,0,0,0)';
				$css .= '.'.$uid.' .znTiltBox-tilter__deco--overlay {background: linear-gradient(45deg, '.$top_gr.', '.$overlay_color_main.') }';
			}
		}

		return $css;

	}


	/**
	 * Load dependant resources
	 */
	function scripts(){
		wp_enqueue_script( 'tiltbox', plugin_dir_url( __FILE__ ) . 'js/app.min.js', array ( 'jquery' ), ZN_FW_VERSION, true );
	}

	function is_true($val, $return_null=false){
	    $boolval = ( is_string($val) ? filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : (bool) $val );
	    return ( $boolval===null && !$return_null ? false : $boolval );
	}

}