<?php
/**
 * UpdateSpecificPost.
 * php version 5.6
 *
 * @category UpdateSpecificPost
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
use WP_Post;

/**
 * UpdateSpecificPost
 *
 * @category UpdateSpecificPost
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class UpdateSpecificPost {


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
	public $trigger = 'transition_post_status';

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
	 * @psalm-suppress PossiblyNullReference
	 */
	public function register( $triggers ) {

		$triggers[ $this->integration ][ $this->trigger ] = [
			'label'         => __( 'User updates a post in a specific status', 'suretriggers' ),
			'action'        => $this->trigger,
			'function'      => [ $this, 'trigger_listener' ],
			'priority'      => 10,
			'accepted_args' => 3,
		];

		return $triggers;
	}


	/**
	 * Trigger listener
	 *
	 * @param int     $new_status checks new status.
	 * @param int     $old_status checks old status.
	 * @param WP_Post $post post object.
	 *
	 * @return void
	 */
	public function trigger_listener( $new_status, $old_status, $post ) {

		// Bail if post status hasn't changed.
		if ( $old_status === $new_status ) {
			return;
		}
		if ( 'post' !== $post->post_type ) {
			return;
		}

		$user_id           = ap_get_current_user_id();
		$context           = WordPress::get_post_context( $post->ID );
		$context           = array_merge( $context, WordPress::get_user_context( $user_id ) );
		$context['post']   = $post->ID;
		$context['status'] = $new_status;

		AutomationController::sure_trigger_handle_trigger(
			[
				'trigger' => $this->trigger,
				'context' => $context,
			]
		);
	}
}

/**
 * Ignore false positive
 *
 * @psalm-suppress UndefinedMethod
 */
UpdateSpecificPost::get_instance();
