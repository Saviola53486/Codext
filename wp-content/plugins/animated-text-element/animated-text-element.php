<?php

/**
 * Plugin Name: Animated Text Element
 * Plugin URI: http://hogash.com
 * Description: This plugin will add a new element to Kallyas Pagebuilder.
 * Version: 1.0.0
 * Author: Dragos George
 * Author URI: http://hogash.com
 * License: GPL2
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class Zn_Animated_Text_Element{

	/**
	 * Holds the plugin current version
	 * @var string
	 */
	var $version = '1.0.0';

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
		$this->url     = plugin_dir_url( __FILE__ );
		$this->path    = plugin_dir_path( __FILE__ );

		add_action( 'after_setup_theme', array( $this, 'init_plugin' ) );
		load_plugin_textdomain('zn_animated_text_element', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Fire up the plugin or show a notice in case Kallyas theme is not installed
	 */
	function init_plugin(){
		if( ! function_exists( 'ZNPB' ) ){
			add_action( 'admin_notices', array( $this, 'show_admin_notice' ) );
		}
		else{
			add_action( 'wp_enqueue_scripts', array($this, 'scripts') );
			add_filter(  'zn_pb_dirs', array( $this, 'register_elements_dir' ) );
			require dirname( __FILE__ ) . '/animated-text-element-shortcode.php';
		}
	}


	/**
	 * Shows an admin notice telling you that the Kallyas theme is not installed.
	 */
	function show_admin_notice(){

		$class = 'notice notice-error is-dismissible';
		$buy_kallyas_url = 'https://themeforest.net/item/kallyas-responsive-multipurpose-wordpress-theme/4091658';
		$buy_link = sprintf( '<a class="button button button-primary" href="%1$s" target="_blank">%2$s</a>', $buy_kallyas_url, __( 'Get Kallyas theme from here', 'zn_animated_text_element' ) );
		$message = __( 'Kallyas theme is not installed! Animated Text Element only works with Kallyas theme.', 'zn_animated_text_element' );

		printf( '<div class="%1$s"><p>%2$s</p><p>%3$s</p></div>', $class, $message, $buy_link );

	}


	/**
	 * Add the path to our elements folder
	 * @param  array $dirs the folders that were already loaded
	 * @return array       the complete folders list
	 */
	function register_elements_dir( $dirs ){

		$dirs[] = array(
			'url' => trailingslashit( $this->url .'elements' ),
			'path' => trailingslashit( $this->path .'elements' ),
		);

		return $dirs;
	}

	function scripts() {
		$assetsDir = $this->url .'/assets/';
		wp_enqueue_script( 'zn_animated_text_typed', $assetsDir .'js/typed.min.js', array('jquery'), $this->version, true );
		wp_enqueue_script( 'zn_animated_text_script', $assetsDir .'js/script.js', array('jquery'), $this->version, true );
	}

}
Zn_Animated_Text_Element::get_instance();