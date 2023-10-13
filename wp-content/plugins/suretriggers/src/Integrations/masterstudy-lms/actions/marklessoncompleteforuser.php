<?php
/**
 * MarkLessonCompleteForUser.
 * php version 5.6
 *
 * @category MarkLessonCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

use SureTriggers\Integrations\AutomateAction;
use SureTriggers\Traits\SingletonLoader;

/**
 * MarkLessonCompleteForUser
 *
 * @category MarkLessonCompleteForUser
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */
class MarkLessonCompleteForUser extends AutomateAction {

	/**
	 * Integration type.
	 *
	 * @var string
	 */
	public $integration = 'MasterStudyLms';

	/**
	 * Action name.
	 *
	 * @var string
	 */
	public $action = 'mark_lesson_complete';

	use SingletonLoader;

	/**
	 * Register a action.
	 *
	 * @param array $actions actions.
	 * @return array
	 */
	public function register( $actions ) {
		$actions[ $this->integration ][ $this->action ] = [
			'label'    => __( 'Mark a lesson complete for the user', 'suretriggers' ),
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
	 * @psalm-suppress UndefinedMethod
	 * @throws Exception Exception.
	 * 
	 * @return array|bool|void
	 */
	public function _action_listener( $user_id, $automation_id, $fields, $selected_options ) {
		// Get the lesson, user, and course IDs.
		$lesson_id_value = $selected_options['lesson'];
		$course_id       = (int) $selected_options['course'];
		$user_email      = $selected_options['wp_user_email'];

		$user = get_user_by( 'email', $user_email );
		if ( is_object( $user ) ) {
			$user    = get_object_vars( $user );
			$user_id = $user['ID'];
		}

		// Get the lessons and lesson associated with the course.
		$curriculum = get_post_meta( $course_id, 'curriculum', true );

		if ( ! empty( $curriculum ) ) {
			// Convert the curriculum to an array of numbers.
			if ( class_exists( '\STM_LMS_Helpers' ) ) {
				/** 
				 * Ignoring next line
				 * 
				 * @phpstan-ignore-next-line 
				 * */
				$curriculum_items = \STM_LMS_Helpers::only_array_numbers( explode( ',', $curriculum ) );
			
				// Get all lessons associated with the curriculum.
				$curriculum_posts = get_posts(
					[
						'post__in'       => $curriculum_items,
						'posts_per_page' => 100,
						'post_type'      => [ 'stm-lessons', 'stm-quizzes' ],
						'post_status'    => 'publish',
					]
				);
			}

			if ( ! empty( $curriculum_posts ) ) {

				// Enroll the user in the course if they are not already enrolled.
				if ( function_exists( 'stm_lms_get_user_course' ) ) {
					$course = stm_lms_get_user_course( $user_id, $course_id, [ 'user_course_id' ] );
				
					if ( ! count( $course ) ) {
						if ( class_exists( '\STM_LMS_Course' ) ) {
							\STM_LMS_Course::add_user_course( $course_id, $user_id, \STM_LMS_Course::item_url( $course_id, '' ), 0 );
							\STM_LMS_Course::add_student( $course_id );
						}
					}
				}

				foreach ( $curriculum_posts as $post_object ) {

					if ( 'stm-lessons' === $post_object->post_type ) {
						if ( 'all' === $lesson_id_value ) {
							$lesson_id = $post_object->ID;
						} else {
							$lesson_id = (int) $lesson_id_value;
						}

						// Process only if the lesson is selected.
						if ( $lesson_id !== $post_object->ID ) {
							continue;
						}

						if ( class_exists( '\STM_LMS_Lesson' ) ) {
							if ( \STM_LMS_Lesson::is_lesson_completed( $user_id, $course_id, $lesson_id ) ) {
								continue;
							}
						}

						$end_time = time();
						/** 
						 * Ignoring next line
						 * 
						 * @phpstan-ignore-next-line 
						 * */
						$start_time = get_user_meta( $user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}", true );

						if ( function_exists( 'stm_lms_add_user_lesson' ) ) {
							stm_lms_add_user_lesson( compact( 'user_id', 'course_id', 'lesson_id', 'start_time', 'end_time' ) );
						}

						if ( class_exists( '\STM_LMS_Course' ) ) {
							\STM_LMS_Course::update_course_progress( $user_id, $course_id );
						}

						do_action( 'stm_lms_lesson_passed', $user_id, $lesson_id );
						/** 
						 * Ignoring next line
						 * 
						 * @phpstan-ignore-next-line 
						 * */
						delete_user_meta( $user_id, "stm_lms_course_started_{$course_id}_{$lesson_id}" );
					}
				}

				if ( class_exists( '\STM_LMS_Course' ) ) {
					\STM_LMS_Course::update_course_progress( $user_id, $course_id );
				}

				// Set the success response.
				$response = [
					'status'   => esc_attr__( 'Success', 'suretriggers' ),
					'response' => esc_attr__( 'Lesson marked complete successfully.', 'suretriggers' ),
				];
			} else {
				$error = [
					'status'   => esc_attr__( 'Error', 'suretriggers' ),
					'response' => esc_attr__( 'Something went wrong. Please check the action step configuration.', 'suretriggers' ),
				];
	
				return $error;
			}
		} else {
			$error = [
				'status'   => esc_attr__( 'Error', 'suretriggers' ),
				'response' => esc_attr__( 'Something went wrong. Please check the action step configuration.', 'suretriggers' ),
			];

			return $error;
		}

		return $response;
	}
}

MarkLessonCompleteForUser::get_instance();
