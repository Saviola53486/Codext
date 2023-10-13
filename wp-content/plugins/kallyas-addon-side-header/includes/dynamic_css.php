<?php

// Panel Width
$panel_width = zget_option( 'sha_panel_width', 'general_options', false, '300' );
$minimize = self::getMinimizeWidth();

$css .= '
	@media (min-width:768px){
		.znSdHead {width:'. $panel_width .'px}
	}

	/* If UNDER minimize */
	@media (min-width:768px) and (max-width:'. $minimize .'px) {
		/* LEFT */
		.znSdHead-alg--left {
			transform: translate3d(-'. $panel_width .'px, 0px, 0px);
		}
		.znSdHead-alg--left.is-opened ~ #page_wrapper {
			padding-left: '. $panel_width .'px;
		}
		/* RIGHT */
		.znSdHead-alg--right {
			transform: translate3d('. $panel_width .'px, 0px, 0px);
		}
		.znSdHead-alg--right.is-opened ~ #page_wrapper {
			padding-right: '. $panel_width .'px;
		}
	}

	/* If OVER minimize (eg: desktop) */
	@media (min-width:'. ($minimize + 1) .'px){
		/* LEFT */
		.znSdHead-alg--left ~ #page_wrapper {
			padding-left: '. $panel_width .'px;
		}
		/* RIGHT */
		.znSdHead-alg--right ~ #page_wrapper {
			padding-right: '. $panel_width .'px;
		}
	}
';


$bg_color = zget_option( 'sha_panel_color', 'general_options', false, '#ffffff' );
if( $bg_color != '#ffffff' ){
	$css .= '.znSdHead .znSdHead-inner{background-color:'. $bg_color .'}';
}

// Custom Text Typography
$typo = array();
$typo['lg'] = zget_option( 'sha_ctext_typo', 'general_options', false, '' );
if( !empty($typo) ){
	$typo['selector'] = '.znSdHeadText';
	$css .= zn_typography_css( $typo );
}

// Logo Text Typography
$logo_typo = array();
$logo_typo['lg'] = zget_option( 'sha_text_logo_typo', 'general_options', false, '' );
if( !empty($logo_typo) ){
	$logo_typo['selector'] = '.znSdHeadLogo-textHolder';
	$css .= zn_typography_css( $logo_typo );
}

/**
 * Social Icons
 */
$sha_shape = zget_option( 'sha_shape', 'general_options', false, 'def' );

// Icon Size
$sh_ico_size = zget_option( 'sha_size', 'general_options', false, '14' );
$sh_ico_size = (int)$sh_ico_size;
if( $sh_ico_size != '14' ){
	$css .= '.znSdHeadSocial-list .znSdHeadSocial-icon {font-size:'.$sh_ico_size.'px}';
}

// Icon Padding
$sh_padding = zget_option( 'sha_padding', 'general_options', false, '10' );
$sh_padding = (int)$sh_padding;
if( $sh_padding != '10' ){

	$ptop = $pright = $pbot = $pleft = $sh_padding;

	if($sha_shape == 'special1'){
		$ptop = $pleft = ceil($sh_padding * 1.5);
		$pright = $pbot = ceil($sh_padding * 0.5);
	}
	$padding = $ptop.'px '.$pright.'px '.$pbot.'px '.$pleft.'px';

	$css .= '.znSdHeadSocial-list .znSdHeadSocial-icon {padding:'.$padding.'}';
}

// Corner Radius
$sha_corners = zget_option( 'sha_corners', 'general_options', false, '0' );
$sha_corners = (int)$sha_corners;
if( $sha_corners != '0' ){
	$css .= '.znSdHeadSocial-list .znSdHeadSocial-icon {border-radius:'.$sha_corners.'px}';
}

// Distance
$sha_icon_distance = zget_option( 'sha_icon_distance', 'general_options', false, '3' );
$sha_icon_distance = (int)$sha_icon_distance;
if( $sha_icon_distance != '3' ){
	$css .= '.znSdHeadSocial-list li:not(:first-child) {margin-left:'.$sha_icon_distance.'px}';
	$css .= '.znSdHeadSocial-list li:not(:last-child) {margin-right:'.$sha_icon_distance.'px}';
}

// Icons options
$sicons = zget_option( 'sha_single_sc', 'general_options', false, array() );
if( !empty( $sicons ) ){
	foreach ( $sicons as $k => $icon ) {
		// Active
		$sha_icon_color = '';
		if(isset($icon['sha_icon_color']) && !empty($icon['sha_icon_color'])){
			$sha_icon_color = 'background-color:'.$icon['sha_icon_color'];
		}
		$sha_icon_textcolor = '';
		if(isset($icon['sha_icon_textcolor']) && !empty($icon['sha_icon_textcolor'])){
			$sha_icon_textcolor = 'color:'.$icon['sha_icon_textcolor'];
		}
		// Hover
		$sha_icon_color_hov = '';
		if(isset($icon['sha_icon_color_hov']) && !empty($icon['sha_icon_color_hov'])){
			$sha_icon_color_hov = 'background-color:'.$icon['sha_icon_color_hov'];
		}
		$sha_icon_textcolor_hov = '';
		if(isset($icon['sha_icon_textcolor_hov']) && !empty($icon['sha_icon_textcolor_hov'])){
			$sha_icon_textcolor_hov = 'color:'.$icon['sha_icon_textcolor_hov'];
		}
		$css .= '.znSdHeadSocial-list .znSdHeadSocial-item'.$k.' .znSdHeadSocial-icon {'.$sha_icon_color.'; '.$sha_icon_textcolor.';}';
		$css .= '.znSdHeadSocial-list .znSdHeadSocial-item'.$k.':hover .znSdHeadSocial-icon {'.$sha_icon_color_hov.'; '.$sha_icon_textcolor_hov.';}';
	}
}

// Menu Typo
$menu_typo = array();
$menu_typo['lg'] = zget_option( 'sha_menu_typo', 'general_options', false, '' );
if( !empty($menu_typo) ){
	$menu_typo['selector'] = '.znSdHead-menuList .znSdHead-menuList-link';
	$css .= zn_typography_css( $menu_typo );
}
// Menu Hover Color
$menu_hov_color = zget_option( 'sha_menu_hover', 'general_options', false, '' );
if( !empty($menu_hov_color) ){
	$css .= '
		.znSdHead-menuList .znSdHead-menuList-link:hover,
		.znSdHead-menuList .active > .znSdHead-menuList-link {color:'. $menu_hov_color .'}';
}
// Menu ACTIVE Color
$menu_active_color = zget_option( 'sha_menu_active', 'general_options', false, '' );
if( !empty($menu_active_color) ){
	$css .= '.znSdHead-menuList .active > .znSdHead-menuList-link {color:'. $menu_active_color .'}';
}
// Breadcrumbs Color
$breadcrumb_color = zget_option( 'sha_menu_brdc_color', 'general_options', false, '#5c5edc' );
if( $breadcrumb_color != '#5c5edc' ){
	$css .= '.znSdHead-menuBrc a{color:'. $breadcrumb_color .'}';
}

if(zget_option( 'sha_menu_vert', 'general_options', false, 'top' ) == 'top'){
	// Top Margin
	$selector = '.znSdHead-cellValign--top .side-main-menu';
	$menu_top_margin = zget_option( 'sha_main_topmargin', 'general_options', false, '40' );
	$css .= zn_smart_slider_css( $menu_top_margin, $selector, 'margin-top' );
}