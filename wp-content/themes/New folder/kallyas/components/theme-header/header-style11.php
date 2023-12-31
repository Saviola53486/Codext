<?php if(! defined('ABSPATH')){ return; }

/**
* Display the Style 11 header
* Hooks mostly, markup loaded through own template
*/

/**
 * ==========================================================
 * CSS custom classes customisations
 * ==========================================================
 */

/**
 * Add custom CSS classes to the <header> tag
 */
// Add the logo in the center of the menu
$header_class[] = 'kl-center-menu';


/**
 * Flexbox scheme override
 *
 * You can override the default markup's vertical and horizontal alignment, as well as "cell" stretch.
 * Also you can add custom CSS Classes.
 *
 */
$flexbox_scheme = array(
    'main' => array(
        'left' => array(
            'stretch' => 'fxb-basis-auto fxb-grow-0 hidden-xs hidden-sm',
        ),
        'right' => array(
            'stretch' => 'fxb-basis-auto fxb-grow-0 hidden-xs hidden-sm',
        ),
    ),
);


/**
 * Extra Rows CSS Classes
 *
 * These classes are added to their particular area's rows.
 */
// $top_extra_class = '';
$main_extra_class = 'fxb-sm-wrap';
// $bottom_extra_class = '';


/**
 * Extra CSS Class for "siteheader-container".
 *
 * This class will be added to the siteheader-container block.
 */
// $siteheader_container_class = '';


/**
 * Sticky classes.
 *
 * The area's that will hide on scroll.
 *
 * @Types: sticky-top-area AND/OR sticky-main-area AND/OR sticky-bottom-area
 */
$sticky_class = 'sticky-top-area';


/**
 * Default Text Color Scheme.
 *
 * This will define de default text color scheme of this header style.
 *
 * @Types: sh--light OR sh--gray OR sh--dark
 */
$headerTextScheme = 'sh--light';


/**
 * ==========================================================
 * Hook header's components
 * ==========================================================
 *
 * Components are loaded through hooks into their predefined area.
 * You can move or reorder components through simple remove_action / add_action through Kallyas child theme.
 */

/**** TOP LEFT */

if(zn_check_components('social_icons')) add_action( 'zn_head__top_left', 'zn_header_social_icons', 10 ); // SOCIAL ICONS
if(zn_check_components('custom_text')) add_action( 'zn_head__top_left', 'zn_header_head_text', 20 ); // CUSTOM TEXT


/**** TOP RIGHT */

if(zn_check_components('header_nav')) add_action( 'zn_head__top_right', 'zn_add_navigation', 10 ); // HEADER NAVIGATION
if(zn_check_components('hidden_panel')) add_action( 'zn_head__top_right', 'zn_hidden_pannel_link', 20 ); // HIDDEN PANEL LINK
if(zn_check_components('login')) add_action( 'zn_head__top_right', 'zn_login_text', 30 ); // LOGIN/LOGOUT TEXT
if(zn_check_components('register')) add_action( 'zn_head__top_right', 'zn_register_text', 40 ); // REGISTER TEXT
if(zn_check_components('flags')) add_action( 'zn_head__top_right', 'zn_wpml_language_switcher_def', 50 ); // WPML LANGUAGE SWITCHER
if(zn_check_components('search_box')) add_action( 'zn_head__top_right', 'zn_header_searchbox_def', 60 ); // SEARCH BOX


/**** MAIN LEFT */

add_action( 'zn_head__main_left', 'zn_woocomerce_cart_init', 10 ); // ADD CART PANEL


/**** MAIN CENTER */

add_action( 'zn_head__main_center', 'zn_header_display_logo' ); // LOGO MARKUP
add_action( 'zn_head__main_center', 'zn_header_main_menu' ); // MAIN NAVIGATION


/**** MAIN RIGHT */

add_action( 'zn_head__main_right', 'zn_header_calltoaction', 10 ); // CALL TO ACTION BUTTON


/**
 * ==========================================================
 * Load general HTML markup
 * ==========================================================
 *
 * The header's markup is loaded for all headers. If you plan on overriding the HTML markup,
 * first make sure you can do it through hooks. If you're sure, simply copy the markup, paste it inside this file and
 * copy this file to ../kallyas-child/components/theme-header/ folder.
 */
include(locate_template('components/theme-header/header-markup.php'));
