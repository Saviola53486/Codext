<?php if(! defined('ABSPATH')){ return; }

/**
 * Theme options > Side Header Options
 */

if( class_exists( 'znNavOverlay' ) ){
	$warning = sprintf(
		__( 'This plugin cannot work with <strong>Kallyas Addon - Overlay Plugin</strong>. To solve this issue please access <a href="%s">Plugins page</a> and disable Kallyas Addon - Overlay Plugin.', 'kallyas-addon-side-header' ),
		admin_url('plugins.php')
	);
	$admin_options[] = array (
		'slug'        => 'side_header_options',
		'parent'      => 'general_options',
		"name"        => __( '<strong style="font-size:120%">Warning!</strong>', 'kallyas-addon-side-header' ),
		"description" => $warning,
		'type'  => 'zn_message',
		'id'    => 'zn_error_notice',
		'show_blank'  => 'true',
		'supports'  => 'warning'
	);
}

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( 'SIDE HEADER', 'kallyas-addon-side-header' ),
	"description" => sprintf(
			__( 'These options will customize the Side header panel.<br><br><span class="dashicons dashicons-editor-help"></span> If you want to enable different types of headers, you will need to disable <strong>Kallyas Addon - Side Header</strong> from <a href="%s">Plugins Page</a>', 'kallyas-addon-side-header' ),
			admin_url('plugins.php')
		) ,
	"id"          => "sh_t2",
	"type"        => "zn_title",
	"class"       => "zn_full zn-custom-title-large zn-toptabs-margin"
);

// $admin_options[] = array (
// 	'slug'        => 'side_header_options',
// 	'parent'      => 'general_options',
// 	"name"        => __( "Disable the Side Header", 'kallyas-addon-side-header' ),
// 	"description" => __( "Select whether to enable or disable the plugin.", 'kallyas-addon-side-header' ),
// 	"id"          => "enable_side_header",
// 	"std"         => "no",
// 	"type"        => "zn_radio",
// 	'options'     => array(
// 		'yes' => __( 'Yes', 'kallyas-addon-side-header' ),
// 		'no' => __( 'No', 'kallyas-addon-side-header' ),
// 	),
// );


/* ==========================================================================
   DISPLAY Options
   ========================================================================== */
$admin_options[] = array (
				'slug'        => 'side_header_options',
				'parent'      => 'general_options',
				"name"        => __( 'DISPLAY OPTIONS', 'kallyas-addon-side-header' ),
				"description" => __( 'These options are dedicated dedicated to customize the looks of the side header.', 'kallyas-addon-side-header' ),
				"id"          => "hd_title1",
				"type"        => "zn_title",
				"class"       => "zn_full zn-custom-title-large zn-top-separator"
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Panel Width", 'kallyas-addon-side-header' ),
	"description" => __( "Select the panel's width.", 'kallyas-addon-side-header' ),
	"id"          => "sha_panel_width",
	"std"         => "300",
	"type"        => "slider",
	'helpers'     => array(
		'min' => '150',
		'max' => '450',
		'step' => '5'
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Panel Color", 'kallyas-addon-side-header' ),
	"description" => __( "Select the panel's background color.", 'kallyas-addon-side-header' ),
	"id"          => "sha_panel_color",
	"std"         => "#ffffff",
	"type"        => "colorpicker",
);


$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Panel Content Theme", 'kallyas-addon-side-header' ),
	"description" => __( "Select the panel's contents theme color.", 'kallyas-addon-side-header' ),
	"id"          => "sha_panel_theme",
	"std"         => "dark",
	'type'        => 'select',
	'options'        => array(
		'dark' => __( "Dark (for lighter background)", 'kallyas-addon-side-header' ),
		'light' => __( "Light (for darker backgrounds)", 'kallyas-addon-side-header' ),
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Panel Screen-Edge Alignment", 'kallyas-addon-side-header' ),
	"description" => __( "Select the panel alignment in the page.", 'kallyas-addon-side-header' ),
	"id"          => "panel_alg",
	"std"         => "left",
	'type'        => 'select',
	'options'        => array(
		'left' => __( "Left Screen-Edge", 'kallyas-addon-side-header' ),
		'right' => __( "Right Screen-Edge", 'kallyas-addon-side-header' ),
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Panel Content Alignment", 'kallyas-addon-side-header' ),
	"description" => __( "Select the panel's content alignment.", 'kallyas-addon-side-header' ),
	"id"          => "panel_cont_alg",
	"std"         => "left",
	'type'        => 'select',
	'options'        => array(
		'left' => __( "Left", 'kallyas-addon-side-header' ),
		'center' => __( "Center", 'kallyas-addon-side-header' ),
		'right' => __( "Right", 'kallyas-addon-side-header' ),
	),
);


/* ==========================================================================
   LOGO OPTIONS
   ========================================================================== */

$admin_options[] = array (
				'slug'        => 'side_header_options',
				'parent'      => 'general_options',
				"name"        => __( 'Logo Option', 'kallyas-addon-side-header' ),
				"id"          => "hd_title1",
				"type"        => "zn_title",
				"class"       => "zn_full zn-custom-title-large zn-top-separator"
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Logo Type", 'kallyas-addon-side-header' ),
	"description" => __( "Select the type of logo.", 'kallyas-addon-side-header' ),
	"id"          => "sha_logo_type",
	"std"         => "disabled",
	'type'        => 'zn_radio',
	'options'        => array(
		'disabled' => __( "No Logo", 'kallyas-addon-side-header' ),
		'img' => __( "Image Logo", 'kallyas-addon-side-header' ),
		'text' => __( "Text Logo", 'kallyas-addon-side-header' ),
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Custom Logo Image", 'kallyas-addon-side-header' ),
	"description" => __( "Add a custom logo image.", 'kallyas-addon-side-header' ),
	"id"          => "sha_logo",
	"std"         => "",
	"type"        => "media",
	"dependency"  => array( 'element' => 'sha_logo_type' , 'value'=> array('img') ),
);

$admin_options[] = array (
    'slug'        => 'side_header_options',
    'parent'      => 'general_options',
    "name"        => __( "Logo TEXT", 'kallyas-addon-side-header' ),
    "description" => __( "Add a custom logo text. If empty, the SITE NAME will be used.", 'kallyas-addon-side-header' ),
    "id"          => "sha_text_logo",
    "std"         => '',
    "type"        => "text",
	"dependency"  => array( 'element' => 'sha_logo_type' , 'value'=> array('text') ),
);

$admin_options[] = array (
    'slug'        => 'side_header_options',
    'parent'      => 'general_options',
    "name"        => __( "Logo TEXT Typography pptions", 'kallyas-addon-side-header' ),
    "description" => __( "Specify the logo typography properties. Will only work if you don't upload a logo image.", 'kallyas-addon-side-header' ),
    "id"          => "sha_text_logo_typo",
    "std"         => '',
    'supports'   => array( 'size', 'font', 'style', 'color', 'line', 'weight', 'case', 'spacing' ),
    "type"        => "font",
	"dependency"  => array( 'element' => 'sha_logo_type' , 'value'=> array('text') ),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Logo alignment", 'kallyas-addon-side-header' ),
	"description" => __( "Select the logo alignment.", 'kallyas-addon-side-header' ),
	"id"          => "sha_logo_align",
	"std"         => "inherit",
	'type'        => 'select',
	'options'        => array(
		'inherit' => __( "Inherit", 'kallyas-addon-side-header' ),
		'left' => __( "Left", 'kallyas-addon-side-header' ),
		'center' => __( "Center", 'kallyas-addon-side-header' ),
		'right' => __( "Right", 'kallyas-addon-side-header' ),
	),
	"dependency"  => array( 'element' => 'sha_logo_type' , 'value'=> array('img', 'text') ),
);


/* ==========================================================================
   MENU OPTIONS
   ========================================================================== */

$admin_options[] = array (
				'slug'        => 'side_header_options',
				'parent'      => 'general_options',
				"name"        => __( 'Menu Options', 'kallyas-addon-side-header' ),
				"id"          => "hd_title1",
				"type"        => "zn_title",
				"class"       => "zn_full zn-custom-title-large zn-top-separator"
);

// Get menus
$menus = get_terms( 'nav_menu', array ( 'hide_empty' => false ) );
$menusList = array(''=>'None');
foreach ( $menus as $menu ) {
	$menusList[$menu->term_id] = $menu->name;
}

if ( ! $menus ) {
	$admin_options[] = array (
		'slug'        => 'side_header_options',
		'parent'      => 'general_options',
		"name"        => __( "Please create Menus!", 'kallyas-addon-side-header' ),
		"description" => sprintf( __( 'No menus have been created yet. <a href="%s">Create some</a>.', 'kallyas-addon-side-header' ), admin_url( 'nav-menus.php' ) ),
		"id"          => "sha_nomenus",
		"std"         => "",
		"type"        => "zn_title",
	);
}
else {
	$admin_options[] = array (
		'slug'        => 'side_header_options',
		'parent'      => 'general_options',
		"name"        => __( "Choose a menu", 'kallyas-addon-side-header' ),
		"description" => __( "Choose a menu to display.", 'kallyas-addon-side-header' ),
		"id"          => "sha_menu",
		"std"         => "",
		"type"        => "select",
		"options"     => $menusList,
	);
}

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Menu Depth", 'kallyas-addon-side-header' ),
	"description" => __( "Choose the maximum depth of the menu.", 'kallyas-addon-side-header' ),
	"id"          => "sha_depth",
	"std"         => "1",
	"type"        => "select",
	"options"     => array(
		"1" => "1 Level",
		"2" => "2 Levels",
		"3" => "3 Levels",
		"4" => "4 Levels",
		"5" => "5 Levels (Not recommended, better restructure it)"
	),
);
$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Menu Typography", 'kallyas-addon-side-header' ),
	"description" => __( "Specify the typography properties for the menu.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_typo",
	"std"         => '',
	'supports'   => array( 'size', 'font', 'style', 'line', 'weight', 'spacing', 'case', 'color' ),
	"type"        => "font",
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Menu Links HOVER Color", 'kallyas-addon-side-header' ),
	"description" => __( "Select a hover text color for the menu items.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_hover",
	"std"         => "",
	"type"        => "colorpicker",
	"alpha" 	=> true,
);


$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Menu Links ACTIVE Color", 'kallyas-addon-side-header' ),
	"description" => __( "Select a text color for the menu items that will be applied on active menu items.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_active",
	"std"         => "",
	"type"        => "colorpicker",
	"alpha" 	=> true,
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Enable Breadcrumb", 'kallyas-addon-side-header' ),
	"description" => __( "Enable the breadcrumb above the menu?.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_brdc",
	"std"         => "yes",
	'type'        => 'zn_radio',
	'options'        => array(
		'yes' => __( "Yes", 'kallyas-addon-side-header' ),
		'no' => __( "No", 'kallyas-addon-side-header' ),
	),
	'class'        => 'zn_radio--yesno',
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Breadcrumb Initial Text", 'kallyas-addon-side-header' ),
	"description" => __( "Change the initial breadcrumb text.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_brdc_all",
	"std"         => "all",
	'type'        => 'text',
	"dependency"  => array( 'element' => 'sha_menu_brdc' , 'value'=> array('yes') ),
);
$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Breadcrumbs Color", 'kallyas-addon-side-header' ),
	"description" => __( "Select a text color for the breadcrumb items.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_brdc_color",
	"std"         => "#5c5edc",
	"type"        => "colorpicker",
	"alpha" 	=> true,
	"dependency"  => array( 'element' => 'sha_menu_brdc' , 'value'=> array('yes') ),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Vertical Align", 'kallyas-addon-side-header' ),
	"description" => __( "Select the vertical alignment.", 'kallyas-addon-side-header' ),
	"id"          => "sha_menu_vert",
	"std"         => "top",
	'type'        => 'select',
	'options'        => array(
		'top' => __( "Top", 'kallyas-addon-side-header' ),
		'mid' => __( "Middle", 'kallyas-addon-side-header' ),
		'bottom' => __( "Bottom", 'kallyas-addon-side-header' ),
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Menu Top Margin", 'kallyas-addon-side-header' ),
	"description" => __( "Select the top margin for the menu.", 'kallyas-addon-side-header' ),
	"id"          => "sha_main_topmargin",
	"std"         => "40",
	// "class"       => "zn_input_xs",
	"type"       => "smart_slider",
	'supports' => array('breakpoints'),
	'units' => array('px'),
	'helpers'     => array(
		'min' => '10',
		'max' => '140',
		'step' => '1'
	),
	"dependency"  => array( 'element' => 'sha_menu_vert' , 'value'=> array('top') ),
);

/* ==========================================================================
   SOCIAL ICONS
   ========================================================================== */
$admin_options[] = array (
				'slug'        => 'side_header_options',
				'parent'      => 'general_options',
				"name"        => __( 'Social Icons Options', 'kallyas-addon-side-header' ),
				"id"          => "hd_title1",
				"type"        => "zn_title",
				"class"       => "zn_full zn-custom-title-large zn-top-separator"
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Icons Layout", 'kallyas-addon-side-header' ),
	"description" => __( "Select the layout of the icons.", 'kallyas-addon-side-header' ),
	"id"          => "sha_shape",
	"std"         => "def",
	"options"     => array (
		'def'  => __( 'Default', 'kallyas-addon-side-header' ),
		'special1' => __( 'Special (Needs Background Color)', 'kallyas-addon-side-header' )
	),
	"type"        => "select",
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Social icons Size", 'kallyas-addon-side-header' ),
	"description" => __( "Select the size (font-size) of the social icons.", 'kallyas-addon-side-header' ),
	"id"          => "sha_size",
	"std"         => "14",
	"type"        => "text",
	"class"       => "zn_input_xs",
	"numeric" => true,
	'helpers'     => array(
		'min' => '10',
		'max' => '100',
		'step' => '1'
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Padding Inside", 'kallyas-addon-side-header' ),
	"description" => __( "Select the size of the social icons.", 'kallyas-addon-side-header' ),
	"id"          => "sha_padding",
	"std"         => "10",
	"type"        => "text",
	"class"       => "zn_input_xs",
	"numeric" => true,
	'helpers'     => array(
		'min' => '0',
		'step' => '1'
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Corner Radius", 'kallyas-addon-side-header' ),
	"description" => __( "Adjust the corners radius of the icons.", 'kallyas-addon-side-header' ),
	"id"          => "sha_corners",
	"std"         => "0",
	"type"        => "text",
	"class"       => "zn_input_xs",
	"numeric" => true,
	'helpers'     => array(
		'min' => '0',
		'step' => '1'
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Icons Distance", 'kallyas-addon-side-header' ),
	"description" => __( "Select the distance between the social icons.", 'kallyas-addon-side-header' ),
	"id"          => "sha_icon_distance",
	"std"         => "3",
	"type"        => "text",
	"class"       => "zn_input_xs",
	"numeric" => true,
	'helpers'     => array(
		'min' => '0',
		'max' => '300',
		'step' => '1'
	),
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"           => __( "Social Icons", 'kallyas-addon-side-header' ),
	"description"    => __( "Add Social Icons.", 'kallyas-addon-side-header' ),
	"id"             => "sha_single_sc",
	"std"            => "",
	"type"           => "group",
	"add_text"       => __( "Social Icon", 'kallyas-addon-side-header' ),
	"remove_text"    => __( "Social Icon", 'kallyas-addon-side-header' ),
	"group_sortable" => true,
	"element_title" => "sha_icon_title",
	"subelements"    => array (
		array (
			"name"        => __( "Icon title", 'kallyas-addon-side-header' ),
			"description" => __( "Here you can enter a title for this social icon.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_title",
			"std"         => "",
			"type"        => "text"
		),
		array (
			"name"        => __( "Social icon link", 'kallyas-addon-side-header' ),
			"description" => __( "Please enter your desired link for the social icon. If this field is left
				blank, the icon will not be linked.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_link",
			"std"         => "",
			"type"        => "link",
			"options"     => zn_get_link_targets(),
		),
		array (
			"name"        => __( "Social icon", 'kallyas-addon-side-header' ),
			"description" => __( "Select your desired social icon.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_icon",
			"std"         => "",
			"type"        => "icon_list",
			'class'       => 'zn_icon_list',
			'compact'       => true,
		),

		array(
			"name"        => __( 'Colors options', 'kallyas-addon-side-header' ),
			"type"        => "zn_title",
			"id"        => "hd_title1",
			"class"       => "zn_full zn-custom-title-large zn-top-separator"
		),

		array (
			"name"        => __( "Icon Background color", 'kallyas-addon-side-header' ),
			"description" => __( "Select a background color for the icon.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_color",
			"std"         => "",
			"type"        => "colorpicker",
			"alpha" => true,
		),
		array (
			"name"        => __( "Icon color", 'kallyas-addon-side-header' ),
			"description" => __( "Select a color for the icon.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_textcolor",
			"std"         => "",
			"type"        => "colorpicker",
			"alpha" => true,
		),
		array (
			"name"        => __( "Icon Background color on Hover", 'kallyas-addon-side-header' ),
			"description" => __( "Select a background color for the icon when hovering over it.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_color_hov",
			"std"         => "",
			"type"        => "colorpicker",
			"alpha" => true,
		),
		array (
			"name"        => __( "Icon color on Hover", 'kallyas-addon-side-header' ),
			"description" => __( "Select a color for the icon.", 'kallyas-addon-side-header' ),
			"id"          => "sha_icon_textcolor_hov",
			"std"         => "",
			"type"        => "colorpicker",
			"alpha" => true,
		),

	),
);


/* ==========================================================================
   Custom Text
   ========================================================================== */
$admin_options[] = array (
				'slug'        => 'side_header_options',
				'parent'      => 'general_options',
				"name"        => __( 'Custom Text Options', 'kallyas-addon-side-header' ),
				"id"          => "hd_title1",
				"type"        => "zn_title",
				"class"       => "zn_full zn-custom-title-large zn-top-separator"
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Custom Text", 'kallyas-addon-side-header' ),
	"description" => __( "Add some custom text. Supports HTML or Shortcodes. Leave blank to hide it.", 'kallyas-addon-side-header' ),
	"id"          => "sha_ctext",
	"std"         => "",
	"type"        => "textarea",
);

$admin_options[] = array (
	'slug'        => 'side_header_options',
	'parent'      => 'general_options',
	"name"        => __( "Custom Text Typography", 'kallyas-addon-side-header' ),
	"description" => __( "Specify the typography properties for the custom text.", 'kallyas-addon-side-header' ),
	"id"          => "sha_ctext_typo",
	"std"         => array(
		'font-size' => '10px',
		'letter-spacing' => '2px',
	),
	'supports'   => array( 'size', 'font', 'style', 'line', 'weight', 'spacing', 'case', 'color' ),
	"type"        => "font",
);

/* ==========================================================================
   Search Options
   ========================================================================== */
// $admin_options[] = array (
// 				'slug'        => 'side_header_options',
// 				'parent'      => 'general_options',
// 				"name"        => __( 'Search Form Options', 'kallyas-addon-side-header' ),
// 				"id"          => "hd_title1",
// 				"type"        => "zn_title",
// 				"class"       => "zn_full zn-custom-title-large zn-top-separator"
// );

// $admin_options[] = array (
// 	'slug'        => 'side_header_options',
// 	'parent'      => 'general_options',
// 	"name"        => __( "Display Search Form?", 'kallyas-addon-side-header' ),
// 	"description" => __( "Choose if you want to show the search form.", 'kallyas-addon-side-header' ),
// 	"id"          => "sha_searchform",
// 	"std"         => "yes",
// 	'type'        => 'zn_radio',
// 	'options'        => array(
// 		'yes' => __( "Yes", 'kallyas-addon-side-header' ),
// 		'no' => __( "No", 'kallyas-addon-side-header' ),
// 	),
// 	'class'        => 'zn_radio--yesno',
// );


/**
 *************** HELP FIELDS FROM HERE
 */

// $admin_options[] = array (
// 	'slug'        => 'side_header_options',
// 	'parent'      => 'general_options',
// 	"name"        => __( '<span class="dashicons dashicons-editor-help"></span> HELP:', 'kallyas-addon-side-header' ),
// 	"description" => __( 'Below you can find quick access to documentation, video documentation or our support forum.', 'kallyas-addon-side-header' ),
// 	"id"          => "ho_title",
// 	"type"        => "zn_title",
// 	"class"       => "zn_full zn-custom-title-md zn-top-separator zn-sep-dark"
// );

// $admin_options[] = zn_options_video_link_option( 'http://support.hogash.com/kallyas-videos/#TuXcJu9jl7c', __( "Click here to access the video tutorial for this section's options.", 'kallyas-addon-side-header' ), array(
// 	'slug'        => 'side_header_options',
// 	'parent'      => 'general_options'
// ));

// $admin_options[] = wp_parse_args( znpb_general_help_option( 'zn-admin-helplink' ), array(
// 	'slug'        => 'side_header_options',
// 	'parent'      => 'general_options',
// ));
