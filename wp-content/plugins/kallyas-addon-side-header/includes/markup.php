<?php if(! defined('ABSPATH')){ return; }

$classes = [];

$classes[] = 'znSdHead-alg--'.zget_option( 'panel_alg', 'general_options', false, 'left' );

$cont_alg = zget_option( 'panel_cont_alg', 'general_options', false, 'left' );
$classes[] = 'znSdHead-cAlg--'.$cont_alg;
$classes[] = 'text-'.$cont_alg;
// Theme
$classes[] = 'znSdHead-theme--'.zget_option( 'sha_panel_theme', 'general_options', false, 'dark' );

$attrs = [];

// Content Width
$minimize = self::getMinimizeWidth();
$attrs[] = 'data-minimize="'. $minimize .'"';


?>

<div id="zn-side-header" class="znSdHead <?php echo implode(' ', $classes); ?>" <?php echo implode(' ', $attrs); ?> >

	<div class="znSdHead-inner">

		<?php

		/**
		 * Logo
		 */
		$logo_type = zget_option( 'sha_logo_type', 'general_options', false, 'disabled' );
		$logo_output = '';
		if( $logo_type != 'disabled' ):
		$logo_output .= '<div class="znSdHeadLogo text-'. zget_option( 'sha_logo_align', 'general_options', false, 'inherit' ) .'">';
			$logo_output .= '<a href="'. esc_url( home_url( '/' ) ) .'">';
				// Image
				$logo = zget_option( 'sha_logo', 'general_options', false, '' );
				if( $logo_type == 'img' && !empty($logo) ){
					$logo_output .= '<img src="'. $logo.'" alt="'. get_bloginfo('name').'" title="'. get_bloginfo('description').'">';
				}
				// Text
				if($logo_type == 'text'){
					$logo_output .= '<span class="znSdHeadLogo-textHolder">';
					$logo_output .= zget_option( 'sha_text_logo', 'general_options', false, '' ) ? zget_option( 'sha_text_logo', 'general_options', false, '' ) : get_bloginfo('name');
					$logo_output .= '</span>';
				}
			$logo_output .= '</a>';
		$logo_output .= '</div>';
		endif;

		if($logo_output): ?>
		<div class="znSdHead-cell znSdHead-cell--top">
			<?php echo $logo_output; ?>
		</div>
		<?php endif; ?>

		<div class="znSdHead-cell znSdHead-cell--mid znSdHead-cellValign--<?php echo zget_option( 'sha_menu_vert', 'general_options', false, 'top' ); ?>">
			<?php

			// Nav. Menu
			$nav_menu = zget_option( 'sha_menu', 'general_options', false, '' );

			if ( $nav_menu != '' ) {

				$depth = zget_option( 'sha_depth', 'general_options', false, '1' );
				$breadcrumb = zget_option( 'sha_menu_brdc', 'general_options', false, 'yes' ) == 'yes' ? 'data-show-breadcrumbs="yes"':'';
				$btext = zget_option( 'sha_menu_brdc_all', 'general_options', false, 'all' );
				$btext_attr = !empty($breadcrumb) ? 'data-breadcrumb-text="'. esc_attr($btext) .'"':'';

				echo '<div class="side-main-menu side-main-menu--depth'. $depth .'" id="side-main-menu" '.$breadcrumb.' '.$btext_attr.'>';

					if($depth > 1){

						echo '
						<button id="znSdHead-menuBack" class="znSdHead-menuBack znSdHead-menuBack--hidden" aria-label="'. __('Go back', 'kallyas-addon-side-header') .'">
							<span class="icon-znshfont-arrow-left"></span>
						</button>';

						if(!empty($breadcrumb)){
							echo '<nav class="znSdHead-menuBrc" id="znSdHead-menuBrc"><a>'.$btext.'</a></nav>';
						}
					}

					wp_nav_menu( array (
						'menu'          => $nav_menu,
						'depth'     	=> $depth,
						'container' 	=> false,
						'menu_id' 		=> 'side-main-nav',
						'menu_class' 	=> 'side-main-nav znSdHead-menuList is-first nav-with-smooth-scroll',
						'walker' => new MLMWalker()
					) );

				echo '</div>';
			}
			else {
				echo sprintf(
					__('<p>Please access the <a href="%s" target="_blank"><u>Header SideBar options</u></a> and choose a menu.</p><p>If you accidentally enabled <strong>Kallyas Addon - Side Header</strong>, you can disable it in <a href="%s" target="_blank"><u>Plugins page</u></a>.</p>', 'kallyas-addon-side-header'),
					admin_url('admin.php?page=zn_tp_general_options#side_header_options'),
					admin_url('plugins.php')
				);
			}

		?>
		</div>

		<div class="znSdHead-cell znSdHead-cell--btm">
			<?php

			/**
			 * Social Icons
			 */
			$sicons = zget_option( 'sha_single_sc', 'general_options', false, array() );
			if( !empty( $sicons ) ){
				echo '<div class="znSdHeadSocial">';
					echo '<ul class="znSdHeadSocial-list clearfix">';

					foreach ( $sicons as $k => $icon ) {

						echo '<li>';

							$sha_icon_link = zn_extract_link(
								$icon['sha_icon_link'],
								'znSdHeadSocial-Link znSdHeadSocial-item'.$k ,
								' title="'.$icon['sha_icon_title'].'" '
							);

							echo $sha_icon_link['start'];

							if( !empty( $icon['sha_icon_icon'] ) ) {
								echo '<span class="znSdHeadSocial-icon" '.zn_generate_icon( $icon['sha_icon_icon'] ).'></span>';
							}

							echo $sha_icon_link['end'];

						echo '</li>';
					}
					echo '</ul>';
				echo '</div>';
			}

			/**
			 * Custom Text
			 */
			if( $custom_text = zget_option( 'sha_ctext', 'general_options', false, '' ) ):
				echo '<div class="znSdHeadText-wrapper">';
					echo '<div class="znSdHeadText">';
					echo do_shortcode( $custom_text );
					echo '</div>';
				echo '</div>';
			endif;

			?>
		</div>

		<noscript>
			<style type="text/css">
				.side-main-menu {overflow: visible; opacity: 1;}
				.side-main-nav {position: relative;}
			</style>
		</noscript>

	</div>
</div>

<div class="znSdHead-burger" id="znSdHead-burger">
	<span></span><span></span><span></span>
</div>