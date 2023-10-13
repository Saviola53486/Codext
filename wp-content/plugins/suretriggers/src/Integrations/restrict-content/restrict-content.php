<?php
/**
 * RestrictContent core integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\RestrictContent;

use RC_Requirements_Check;
use RCP_Requirements_Check;
use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\RestrictContent
 */
class RestrictContent extends Integrations {


	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'RestrictContent';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'Restrict Content Pro', 'suretriggers' );
		$this->description = __( 'Connect with your fans, faster your community.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/restrict-content.svg';

		parent::__construct();
	}

	/**
	 * Get customer context data.
	 *
	 * @param array|object $membership level.
	 *
	 * @return array
	 */
	public static function get_rcp_membership_detail_context( $membership ) {

		$membership_level = rcp_get_membership_level( $membership->get_object_id() );

		$context['membership_level_id']          = $membership_level->get_id();
		$context['membership_level']             = $membership_level->get_name();
		$context['membership_initial_payment']   = $membership->get_initial_amount();
		$context['membership_recurring_payment'] = $membership->get_recurring_amount();
		$context['membership_expiry_date']       = $membership->get_expiration_date();
		$context['membership_customer_id']       = $membership->get_customer_id();

		return $context;
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( RC_Requirements_Check::class ) || class_exists( RCP_Requirements_Check::class );
	}

}

IntegrationsController::register( RestrictContent::class );
