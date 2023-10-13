<?php if(! defined('ABSPATH')){ return; }
/*
	Name: Animated text
	Description: This element will generate an empty element with an unique ID that can be used as an achor point
	Class: AnimatedText
	Category: content
	Level: 3
	Keywords: animate,text,animated

*/

	class AnimatedText extends ZnElements {
	/**
	 * Output the inline css to head or after the element in case it is loaded via ajax
	 */
	function css(){

		$uid = $this->data['uid'];
		$css = '';

		// backwards compatibility for top and bottom padding
		$tt_padding_std = array('top' => '0', 'bottom'=> '35px');
		if(isset($this->data['options']['top_padding']) && $this->data['options']['top_padding'] != '' ){
			$tt_padding_std['top'] = $this->data['options']['top_padding'].'px';
		}
		if(isset($this->data['options']['bottom_padding']) && $this->data['options']['bottom_padding'] != '' ){
			$tt_padding_std['bottom'] = $this->data['options']['bottom_padding'].'px';
		}

		// Margin
		if( $this->opt('cc_margin_lg', '' ) || $this->opt('cc_margin_md', '' ) || $this->opt('cc_margin_sm', '' ) || $this->opt('cc_margin_xs', '' ) ){
			$css .= zn_push_boxmodel_styles(array(
					'selector' => '.'.$uid,
					'type' => 'margin',
					'lg' =>  $this->opt('cc_margin_lg', '' ),
					'md' =>  $this->opt('cc_margin_md', '' ),
					'sm' =>  $this->opt('cc_margin_sm', '' ),
					'xs' =>  $this->opt('cc_margin_xs', '' ),
				)
			);
		}
		// Padding
		if( $this->opt('cc_padding_lg', $tt_padding_std ) || $this->opt('cc_padding_md', '' ) || $this->opt('cc_padding_sm', '' ) || $this->opt('cc_padding_xs', '' ) ){
			$css .= zn_push_boxmodel_styles(array(
					'selector' => '.'.$uid,
					'type' => 'padding',
					'lg' =>  $this->opt('cc_padding_lg', $tt_padding_std ),
					'md' =>  $this->opt('cc_padding_md', '' ),
					'sm' =>  $this->opt('cc_padding_sm', '' ),
					'xs' =>  $this->opt('cc_padding_xs', '' ),
				)
			);
		}

		$ttl_bmargin = array(
			'lg' =>  $this->opt('title_bmargin', 10),
			'unit_lg' => 'px',
		);
		if($ttl_bmargin['lg'] != '10'){
			$css .= zn_smart_slider_css( $this->opt( 'title_bmargin', $ttl_bmargin ), '.'.$uid , 'margin-bottom' );
		}

		// Title Styles
		if( $this->opt('title_typo', '' ) || $this->opt('title_typo_md', '' ) || $this->opt('title_typo_sm', '' ) || $this->opt('title_typo_xs', '' ) ){
			$css .= zn_typography_css(array(
					'selector' => '.'.$uid.' .zn-dynamic-animated-typed, .'.$uid.' .typed-cursor',
					'lg' =>  $this->opt('title_typo', '' ),
					'md' =>  $this->opt('title_typo_md', '' ),
					'sm' =>  $this->opt('title_typo_sm', '' ),
					'xs' =>  $this->opt('title_typo_xs', '' ),
				)
			);
		}

	// cursor color
			$custom_color = $this->opt('cursor_custom_color', '#000000');
				$css .= '.'.$uid.' .typed-cursor {color:'.$custom_color.';}';

	// blink cursor
		if( $this->opt('blink_cursor', true) === 'false'){
			$css .= '.'. $uid .' .typed-cursor {animation:none; -webkit-animation: none; -moz-animation: none;}';
		}


		return $css;
	}
	function options() {

		$uid = $this->data['uid'];

		$options = array(
			'has_tabs'  => true,
			'general' => array(
				'title' => 'General options',
				'options' => array(
					array (
						'id'          => 'dynamic_text',
						'name'        => 'Dynamic Text',
						'description' => 'The text that will appear typed. Enter each phrase on a new line.',
						'std'         => "My text\nVery nice\nExample text",
						'type'        => 'textarea'
					),
					array (
						'id'          => 'shuffle',
						'name'        => 'Shuffle Text',
						'description' => 'This will shuffle randomnly the texts.',
    					'std'         => 'false',
    					'options'     => array ( 'true' => __( "Yes", 'zn_framework' ), 'false' => __( "No", 'zn_framework' ) ),
    					'type'        => "zn_radio",
    					'class'       => "zn_radio--yesno",
					),
					array (
						'id'          => 'type_speed',
						'name'        => 'Type Speed',
						'description' => 'The speed of typing in miliseconds',
						'type'        => 'slider',
						'std'         => '30',
						'helpers'     => array(
							'min' => '0',
							'max' => '5000',
							'step' => '10'
						),
					),
					array (
						'id'          => 'start_delay',
						'name'        => 'Start Delay',
						'description' => 'The start delay of typing in miliseconds',
						'type'        => 'slider',
						'std'         => '0',
						'helpers'     => array(
							'min' => '0',
							'max' => '5000',
							'step' => '10'
						),
					),
					array (
						'id'          => 'back_speed',
						'name'        => 'Back Speed',
						'description' => 'The back speed of backspacing in miliseconds',
						'type'        => 'slider',
						'std'         => '0',
						'helpers'     => array(
							'min' => '0',
							'max' => '5000',
							'step' => '10'
						),
					),
					array (
						'id'          => 'back_delay',
						'name'        => 'Back Delay',
						'description' => 'The back delay of backspacing in miliseconds.',
						'type'        => 'slider',
						'std'         => '500',
						'helpers'     => array(
							'min' => '0',
							'max' => '5000',
							'step' => '10'
						),
					),
					array (
						'id'          => 'loop',
						'name'        => 'Loop',
						'description' => 'The loop...',
    					'std'         => 'true',
    					'options'     => array ( 'true' => __( "Yes", 'zn_framework' ), 'false' => __( "No", 'zn_framework' ) ),
    					'type'        => "zn_radio",
    					'class'       => "zn_radio--yesno",
					),
					array (
						'id'          => 'loop_count',
						'name'        => 'Loop Count',
						'description' => 'The number of times this loop will play. The value is in miliseconds',
						"type"        => "slider",
						'std'         => '500',
						'helpers'     => array(
							'min' => '0',
							'max' => '1000',
							'step' => '1'
						),
						"dependency"  => array( 'element' => 'loop' , 'value'=> array('true') ),
					),
					array (
						'id'          => 'show_cursor',
						'name'        => 'Show Cursor',
						'description' => 'If you want to show cursor or not',
    					'std'         => 'true',
    					'options'     => array ( 'true' => __( "Yes", 'zn_framework' ), 'false' => __( "No", 'zn_framework' ) ),
    					'type'        => "zn_radio",
    					'class'       => "zn_radio--yesno",
					),
					array (
						'id'          => 'hide_cursor_time',
						'name'        => 'Hide Cursor Time',
						'description' => 'This option is for hiding cursor after a certain time. 0 is for not hiding. The value is in miliseconds',
						"type"        => "slider",
						'std'         => '0',
						'helpers'     => array(
							'min' => '0',
							'max' => '10000',
							'step' => '100'
						),
    					"dependency"  => array( 'element' => 'show_cursor' , 'value'=> array('true') ),
					),
					array (
						'id'          => 'blink_cursor',
						'name'        => 'Blink Cursor',
						'description' => 'This option is for blinking animation cursor.',
    					'std'         => 'true',
    					'options'     => array ( 'true' => __( "Yes", 'zn_framework' ), 'false' => __( "No", 'zn_framework' ) ),
    					'type'        => "zn_radio",
    					'class'       => "zn_radio--yesno",
    					"dependency"  => array( 'element' => 'show_cursor' , 'value'=> array('true') ),
					),
					array (
						'id'          => 'cursor_char',
						'name'        => 'Cursor Character',
						'description' => 'The cursor Character',
						"type"        => "text",
						'std'         => '|',
						"dependency"  => array( 'element' => 'show_cursor' , 'value'=> array('true') ),
					),
					array (
						"name"        => __( "Custom Cursor Color", 'zn_framework' ),
						"description" => __( "Select Cursor Color.", 'zn_framework' ),
						"id"          => "cursor_custom_color",
						"std"         => "#000000",
						"type"        => "colorpicker",
						"dependency"  => array( 'element' => 'show_cursor' , 'value'=> array('true') ),
						'live' => array(
							'type'      => 'css',
							'css_class' => '.'.$uid.' .typed-cursor',
							'css_rule'  => 'color',
							'unit'      => ''
						)
					),
				),
			),



			'font' => array(
				'title' => 'Font settings',
				'options' => array(


					array (
						"id"          => "cc_font_breakpoints",
						"std"         => "lg",
						"tabs"        => true,
						"type"        => "zn_radio",
						"options"     => array (
							"lg"        => __( "LARGE", 'zn_framework' ),
							"md"        => __( "MEDIUM", 'zn_framework' ),
							"sm"        => __( "SMALL", 'zn_framework' ),
							"xs"        => __( "EXTRA SMALL", 'zn_framework' ),
						),
						"class"       => "zn_full zn_breakpoints"
					),

					array (
						"name"        => __( "Dynamic Text settings", 'zn_framework' ),
						"description" => __( "Specify the typography properties for the dynamic text.", 'zn_framework' ),
						"id"          => "title_typo",
						"std"         => '',
						'supports'   => array( 'size', 'font', 'style', 'line', 'color', 'weight', 'spacing', 'case', 'shadow' ),
						"type"        => "font",
						'live' => array(
							'multiple' => array(
								array(
									'type'      => 'font',
									'css_class' => '.'.$uid. ' .zn-dynamic-animated-typed',
								),

								array(
									'type'      => 'font',
									'css_class' => '.'.$uid. ' .typed-cursor',
								)
							),

						),
						"dependency"  => array( 'element' => 'cc_font_breakpoints' , 'value'=> array('lg') ),
					),

					array (
						"name"        => __( "Dynamic Text settings", 'zn_framework' ),
						"description" => __( "Specify the typography properties for the dynamic text.", 'zn_framework' ),
						"id"          => "title_typo_md",
						"std"         => '',
						'supports'   => array( 'size', 'line', 'spacing' ),
						"type"        => "font",
						"dependency"  => array( 'element' => 'cc_font_breakpoints' , 'value'=> array('md') ),
					),

					array (
						"name"        => __( "Dynamic Text settings", 'zn_framework' ),
						"description" => __( "Specify the typography properties for the dynamic text.", 'zn_framework' ),
						"id"          => "title_typo_sm",
						"std"         => '',
						'supports'   => array( 'size', 'line', 'spacing' ),
						"type"        => "font",
						"dependency"  => array( 'element' => 'cc_font_breakpoints' , 'value'=> array('sm') ),
					),

					array (
						"name"        => __( "Dynamic Text settings", 'zn_framework' ),
						"description" => __( "Specify the typography properties for the dynamic text.", 'zn_framework' ),
						"id"          => "title_typo_xs",
						"std"         => '',
						'supports'   => array( 'size', 'line', 'spacing' ),
						"type"        => "font",
						"dependency"  => array( 'element' => 'cc_font_breakpoints' , 'value'=> array('xs') ),
					)

				)
			),


			'padding' => array(
				'title' => 'Spacing options',
				'options' => array(
					/**
					 * Margins and padding
					 */
					array (
						"name"        => __( "Edit padding & margins for each device breakpoint", 'zn_framework' ),
						"description" => __( "This will enable you to have more control over the padding of the container on each device. Click to see <a href='http://hogash.d.pr/1f0nW' target='_blank'>how box-model works</a>.", 'zn_framework' ),
						"id"          => "cc_spacing_breakpoints",
						"std"         => "lg",
						"tabs"        => true,
						"type"        => "zn_radio",
						"options"     => array (
							"lg"        => __( "LARGE", 'zn_framework' ),
							"md"        => __( "MEDIUM", 'zn_framework' ),
							"sm"        => __( "SMALL", 'zn_framework' ),
							"xs"        => __( "EXTRA SMALL", 'zn_framework' ),
						),
						"class"       => "zn_full zn_breakpoints"
					),
					// MARGINS
					array(
						'id'          => 'cc_margin_lg',
						'name'        => 'Margin (Large Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container. Accepts negative margin.',
						'type'        => 'boxmodel',
						'std'	  => '',
						'placeholder' => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('lg') ),
						'live' => array(
							'type'		=> 'boxmodel',
							'css_class' => '.'.$uid,
							'css_rule'	=> 'margin',
						),
					),
					array(
						'id'          => 'cc_margin_md',
						'name'        => 'Margin (Medium Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('md') ),
					),
					array(
						'id'          => 'cc_margin_sm',
						'name'        => 'Margin (Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('sm') ),
					),
					array(
						'id'          => 'cc_margin_xs',
						'name'        => 'Margin (Extra Small Breakpoints)',
						'description' => 'Select the margin (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('xs') ),
					),
					// PADDINGS
					array(
						'id'          => 'cc_padding_lg',
						'name'        => 'Padding (Large Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => array('top' => '0', 'bottom'=> '0'),
						'placeholder' => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('lg') ),
						'live' => array(
							'type'		=> 'boxmodel',
							'css_class' => '.'.$uid,
							'css_rule'	=> 'padding',
						),
					),
					array(
						'id'          => 'cc_padding_md',
						'name'        => 'Padding (Medium Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('md') ),
					),
					array(
						'id'          => 'cc_padding_sm',
						'name'        => 'Padding (Small Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('sm') ),
					),
					array(
						'id'          => 'cc_padding_xs',
						'name'        => 'Padding (Extra Small Breakpoints)',
						'description' => 'Select the padding (in percent % or px) for this container.',
						'type'        => 'boxmodel',
						"allow-negative" => false,
						'std'	  => 	'',
						'placeholder'        => '0px',
						"dependency"  => array( 'element' => 'cc_spacing_breakpoints' , 'value'=> array('xs') ),
					)
				)
			),

			'help' => znpb_get_helptab( array(
				//'video'   => 'http://support.hogash.com/kallyas-videos/#GAiAelvoOg4',
				//'docs'    => 'http://support.hogash.com/documentation/anchor-point-element/',
				'copy'    => $uid,
				'general' => true,
			)),

		);

		return $options;

	}

	function element(){

		$uid = $this->data['uid'];
		$dynamic_text = $this->opt('dynamic_text', "My text\nVery nice\nExample text");
		$hide_cursor_time = $this->opt('hide_cursor_time', 500);
		if( empty( $dynamic_text ) ) {
			return;
		}

		$config = array(
			'typeSpeed' 	=> (int)$this->opt('type_speed', 	30),
			'startDelay'	=> (int)$this->opt('start_delay', 	0),
			'backSpeed' 	=> (int)$this->opt('back_speed', 	0),
			'backDelay' 	=> (int)$this->opt('back_delay', 	500),
			'loop' 			=> ($this->opt('loop', 'false') === 'true') 	  ? true : false,
			'loopCount' 	=> (int)$this->opt('loop_count', 	500),
			'showCursor'	=> ($this->opt('show_cursor', 'true') === 'true') ? true : false,
			'cursorChar' 	=>  $this->opt('cursor_char', '|'),
			'shuffle' 		=> ($this->opt('shuffle', 	'false') === 'true')  ? true : false,

		);

		$pluginInstance = Zn_Animated_Text_Element::get_instance();
		include($pluginInstance->path . '/templates/element-output.php');

	}
}