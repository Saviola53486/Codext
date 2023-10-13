<?php
/**
 * Event Calendar integrations file
 *
 * @since 1.0.0
 * @package SureTrigger
 */

namespace SureTriggers\Integrations\TheEventCalendar;

use SureTriggers\Controllers\IntegrationsController;
use SureTriggers\Integrations\Integrations;
use SureTriggers\Traits\SingletonLoader;
use Tribe__Events__Main;

/**
 * Class SureTrigger
 *
 * @package SureTriggers\Integrations\EventCalendar
 */
class TheEventCalendar extends Integrations {

	use SingletonLoader;

	/**
	 * ID
	 *
	 * @var string
	 */
	protected $id = 'TheEventCalendar';

	/**
	 * SureTrigger constructor.
	 */
	public function __construct() {
		$this->name        = __( 'The Events Calendar', 'suretriggers' );
		$this->description = __( 'Easily create and manage an events calendar on your WordPress site with The Events Calendar plugin.', 'suretriggers' );
		$this->icon_url    = SURE_TRIGGERS_URL . 'assets/icons/the-events-calendar.svg';

		parent::__construct();
	}

	/**
	 * Fetch event context.
	 *
	 * @param int $product_id product id.
	 * @param int $order_id order id.
	 * @return array
	 */
	public static function get_event_context( $product_id, $order_id ) {
		$event     = tribe_events_get_ticket_event( $product_id );
		$attendees = tribe_tickets_get_attendees( $order_id );
		return array_merge(
			[
				'event'     => $event,
				'attendies' => $attendees[0],
			]
		);
	}

	/**
	 * Is Plugin depended on plugin is installed or not.
	 *
	 * @return bool
	 */
	public function is_plugin_installed() {
		return class_exists( Tribe__Events__Main::class );
	}
}

IntegrationsController::register( TheEventCalendar::class );
