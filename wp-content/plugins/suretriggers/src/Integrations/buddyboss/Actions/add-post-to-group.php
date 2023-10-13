<?php
/**
 * AddPostToActivityStreamOfGroup.
 * php version 5.6
 *
 * @category AddPostToActivityStreamOfGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Integrations\BuddyBoss\Actions;

use Exception;
use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * AddPostToActivityStreamOfGroup
 *
 * @category AddPostToActivityStreamOfGroup
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class AddPostToActivityStreamOfGroup extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'BuddyBoss';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'bb_add_post_to_activity_stream_of_group';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 *
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Add post to activity stream of group', 'suretriggers' ),
			'action'   => $this->action,
			'function' => [ $this, 'action_listener' ],
		];

		return $actions;
	}

	/**
	 * Action listener.
	 *
	 * @param int   $user_id user_id.
	 * @param int   $automation_id automation_id.
	 * @param array $fields fields.
	 * @param array $selected_options selectedOptions.
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		if ( empty( $selected_options['bb_author'] ) || ! is_email( $selected_options['bb_author'] ) ) {
			throw new Exception( 'Invalid email.' );
		}
		$user_id = email_exists( $selected_options['bb_author'] );

		if ( false === $user_id ) {
			throw new Exception( 'User with email ' . $selected_options['bb_author'] . ' does not exists.' );
		}

		$group_id    = $selected_options['bb_group'];
		$content     = $selected_options['bb_activity_content'];
		$action      = ( isset( $selected_options['bb_activity_action'] ) ) ? $selected_options['bb_activity_action'] : '';
		$activity_id = bp_activity_add(
			[
				'action'       => $action,
				'item_id'      => $group_id,
				'content'      => $content,
				'component'    => 'groups',
				'type'         => 'activity_update',
				'user_id'      => $user_id,
				'primary_link' => $selected_options['bb_activity_action_link'],
			]
		);
		$context     = bp_activity_get( [ 'in' => $activity_id ] );
		if ( isset( $context['activities'] ) && ! empty( $context['activities'] ) ) {
			return $context['activities'][0];
		}
		throw new Exception( SURE_TRIGGERS_ACTION_ERROR_MESSAGE );
	}
}

AddPostToActivityStreamOfGroup::get_instance();
