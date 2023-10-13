<?php

/**
 * Plugin Name: Kallyas Addon - Side Header
 * Plugin URI: http://hogash.com
 * Description: This plugin will replace the default Kallyas theme header with a side block containing components such as logo image, main navigation, search form, social icons etc.
 * Version: 1.0.3
 * Author: Hogash Studio
 * Author URI: http://hogash.com
 * License: GPL2
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class znSideHeader{

	/**
	 * Holds the plugin current version
	 * @var string
	 */
	var $version = '1.0.3';

	/**
	 * Holds the plugin url
	 * @var string
	 */
	var $url = '';

	/**
	 * Holds the plugin path
	 * @var string
	 */
	var $path = '';


	/**
	 * Holds a refference of the class instance
	 */
	static $instance = null;


	/**
	 * Returns an instance of the classs
	 * @return [type] [description]
	 */
	static public function get_instance(){
		if( null === self::$instance ){
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * Main class constructor
	 */
	function __construct(){

		// Set plugin paths
		$this->url       = plugin_dir_url( __FILE__ );
		$this->path      = plugin_dir_path( __FILE__ );

		register_activation_hook(__FILE__, array( $this, 'activate_plugin' ));

		add_action( 'after_setup_theme', array( $this, 'init' ) );

		// load text domain
		load_plugin_textdomain('kallyas-addon-side-header', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Fire up the plugin or show a notice in case Kallyas theme is not installed
	 */
	function init(){
		if( ! function_exists( 'ZNPB' ) ){
			add_action( 'admin_notices', array( $this, 'showAdminNotice' ) );
		}
		else {
			if( class_exists( 'znNavOverlay' ) ){
				add_action( 'admin_notices', array( $this, 'showAdminNotice_conflict' ) );
			}
			// Add options
			add_filter( 'zn_theme_pages', array( $this, 'addPageOptions' ) );
			add_filter( 'zn_theme_options', array( $this, 'addThemeOptions' ) );

			// Add Assets
			add_action( 'wp_enqueue_scripts', array( $this, 'addAssets' ), 50 );

			// Load Markup
			add_action( 'zn_after_body', array( $this, 'addHeaderMarkup' ), 90 );
			remove_action( 'zn_after_body', 'zn_add_hidden_panel', 10 );

			// Add dynamic CSS
			add_filter( 'zn_dynamic_css', array( $this, 'addDynamicCSS' ), 110 );

			// Disable Header
			add_filter( 'zn_display_header', array( $this, 'hideDefaultHeader' ) );

			// Add Settings button in plugin
			$plugin_basename = plugin_basename( __FILE__ );
			add_filter("plugin_action_links_$plugin_basename", array( $this, 'settingsLink') );

			// Add admin CSS
			add_action('admin_head', array( $this, 'addAdminCSS' ), 130);

		}
	}

	/**
	 * Add settings link on plugin page
	 * @param  array $links existing plugins links
	 * @return array        modified list
	 */
	function settingsLink($links) {
		$settings_link = '<a href="'. admin_url( 'admin.php?page=zn_tp_general_options#side_header_options' ) .'">'.__( 'Settings', 'kallyas-addon-side-header' ).'</a>';
		array_push($links, $settings_link);
		return $links;
	}

	/**
	 * Shows an admin notice telling you that the Kallyas theme is not installed.
	 */
	function showAdminNotice(){

		$class = 'notice notice-error is-dismissible';
		$buy_kallyas_url = 'https://themeforest.net/item/kallyas-responsive-multipurpose-wordpress-theme/4091658';
		$buy_link = sprintf( '<a class="button button button-primary" href="%1$s" target="_blank">%2$s</a>', $buy_kallyas_url, __( 'Get Kallyas Theme!', 'kallyas-addon-side-header' ) );
		$message = __( 'Kallyas theme is not installed! Side Header Plugin only works with Kallyas theme.', 'kallyas-addon-side-header' );

		printf( '<div class="%1$s"><p>%2$s</p><p>%3$s</p></div>', $class, $message, $buy_link );

	}

	function showAdminNotice_conflict(){

		$message = __( '<strong>Side Header Plugin</strong> cannot work with <strong>Kallyas Addon - Overlay Plugin</strong>. To solve this issue please disable Kallyas Addon - Overlay Plugin.', 'kallyas-addon-side-header' );

		printf( '<div class="notice notice-error"><p>%s</p></div>', $message );

	}

	/**
	 * Load the options slugs and hook to Kallyas theme options
	 * @param array $admin_pages Existing slugs
	 */
	function addPageOptions($admin_pages){

		$header_options = array(
			'slug' => 'side_header_options',
			'title' =>  'Header - Side Header'
		);

		if(isset($admin_pages['general_options']['submenus'])){
			$admin_pages['general_options']['submenus'][] = $header_options;
		}

		return $admin_pages;
	}

	/**
	 * Load the options that are displayed in Kallyas options > General options > Header - Side Header
	 * @param array $admin_options Existing options
	 */
	function addThemeOptions($admin_options){

		if ( ! is_admin() )
			return;

		include( $this->path . '/includes/options.php' );

		return $admin_options;
	}

	/**
	 * Load Assets
	 */
	function addAssets() {

		$assets = $this->url . 'assets/';
		$suffix = ( (defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG) || defined( 'ZN_FW_DEBUG' ) && ZN_FW_DEBUG == true ) ? '' : '.min';

		wp_enqueue_style( 'kallyas-addon-side-header-css', $assets . 'styles'.$suffix.'.css', false, $this->version );
		wp_enqueue_script( 'kallyas-addon-side-header-js', $assets . 'app'.$suffix.'.js', array ( 'zn-script' ), $this->version, true );

	}

	/**
	 * Change Main Menu's Class
	 */
	function hideDefaultHeader(){
		return false;
	}

	/**
	 * Load the HTML Markup
	 */
	function addHeaderMarkup(){

		if ( is_admin() ) return;

		include_once $this->path . '/includes/markup.php';
	}


	/**
	 * Cache Custom CSS
	 */
	function addDynamicCSS( $css ){

		include( $this->path . '/includes/dynamic_css.php' );

		return $css;
	}

	function getMinimizeWidth(){
		$ct_width = '1170';
		if( 'custom' == zget_option( 'zn_width' , 'layout_options', false, '1170' ) ){
			$ct_width = zget_option( 'custom_width' , 'layout_options', false, '1170' );
		}
		$ct_width = (int)$ct_width;
		// Panel Width
		$panel_width = zget_option( 'sha_panel_width', 'general_options', false, '300' );
		$panel_width = (int)$panel_width;
		// Minimize Breakpoint
		return $panel_width + $ct_width;
	}

	/*
	 * Admin CSS for highlighting the menu item in theme options
	 */
	function addAdminCSS() {
		echo '<style>
			#side_header_options_menu_item {background-color: #202529;}
			#side_header_options_menu_item.wp-ui-highlight {color: #fff; background-color: #2bbb23;}
			/* Hide Default Header Options */
			#header_options, #header_options_menu_item,
			#logo_options, #logo_options_menu_item,
			#cta_options, #cta_options_menu_item,
			#nav_options, #nav_options_menu_item,
			#head_adv_options, #head_adv_options_menu_item,
			#wp-admin-bar-kallyas-theme-options-submenu-item-header_options,
			#wp-admin-bar-kallyas-theme-options-submenu-item-logo_options,
			#wp-admin-bar-kallyas-theme-options-submenu-item-cta_options,
			#wp-admin-bar-kallyas-theme-options-submenu-item-nav_options,
			#wp-admin-bar-kallyas-theme-options-submenu-item-head_adv_options
			{display:none !important;}
		</style>';
	}


	public function activate_plugin(){
		add_filter( 'zn_dynamic_css', array( $this, 'addDynamicCSS' ), 110 );
		if(function_exists('znklfw_regenerate_dynamic_css')){
			znklfw_regenerate_dynamic_css();
		}
	}

}
znSideHeader::get_instance();

require_once (dirname(__FILE__).'/includes/walker.php');