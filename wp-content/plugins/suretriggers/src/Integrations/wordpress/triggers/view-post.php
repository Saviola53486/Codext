<?php
/**
 * ViewPost.
 * php version 5.6
 *
 * @category ViewPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\Wordpress\Triggers;

use SureTriggers\Controllers\AutomationController;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Traits\SingletonLoader;

/**
 * ViewPost
 *
 * @category ViewPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class ViewPost {


	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'WordPress';

	/**
	 * Trigger name.
	 *
	 * @var string
	 */
	public $trigger = 'wp_view_post';

	use SingletonLoader;


	/**
	 * Constructor
	 *
	 * @since  1.0.0
	 */
	public function __construct() {
		add_filter( 'sure_trigger_register_trigger', [ $this, 'register' ] );
	}

	/**
	 * Register action.
	 *
	 * @param array $triggers trigger data.
	 * @return array
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User views any post', 'suretriggers' ),
			'action'        => $this->trigger,
			'common_action' => 'template_redirect',
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 1,
		];

		return $triggers;

	}


	/**
	 * Trigger listener.
	 *
	 * @return void
	 */
	public function trigger_listener() {

		if ( ! is_singular() ) {
			return;
		}

		$post_id         = get_queried_object_id();
		$user_id         = ap_get_current_user_id();
		$context         = WordPress::get_post_context( $post_id );
		$context         = array_merge( $context, WordPress::get_user_context( $user_id ) );
		$context['post'] = $post_id;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);

	}

}


ViewPost::get_instance();
