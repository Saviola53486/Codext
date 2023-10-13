<?php
/**
 * WishlistMember core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\WishlistMember;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\WishlistMember
 */
class WishlistMember extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'WishlistMember';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'WishlistMember', 'suretriggers' );
		$this->description = __( 'Connect with your fans, faster your community.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/wishlistmember.svg';

		parent::__construct();
	}

	/**
	 * Get customer context data.
	 *
	 * @param array|int $level_id level.
	 *
	 * @return array
	 */
	public static function get_membership_detail_context( $level_id ) {
		$level_id             = is_int( $level_id ) ? $level_id : reset( $level_id );
		$wm_membership_detail = wlmapi_get_level( $level_id );

		if ( ! is_array( $wm_membership_detail ) ) {
			return [];
		}

		$context['membership_level_id']   = isset( $wm_membership_detail['level'] ) ? $wm_membership_detail['level']['id'] : '';
		$context['membership_level_name'] = isset( $wm_membership_detail['level'] ) ? $wm_membership_detail['level']['name'] : '';

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( 'WLMAPI' ) || class_exists( 'WishListMember' );
	}

}

IntegrationsController::register( WishlistMember::class );
