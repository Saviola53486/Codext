<?php
/**
 * GlobalSearchController.
 * php version 5.6
 *
 * @category GlobalSearchController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 */

namespace SureTriggers\Controllers;

use DOMDocument;
use FluentCrm\App\Models\CustomContactField;
use FluentCrm\App\Models\Subscriber;
use FluentCrm\App\Models\Tag;
use memberpress\courses\lib as lib;
use memberpress\courses\models as models;
use FluentCrm\Framework\Support\Arr;
use GFCommon;
use GFFormsModel;
use Give_Payment;
use MeprBaseRealGateway;
use MeprOptions;
use PrestoPlayer\Models\Video;
use RGFormsModel;
use SureTriggers\Integrations\AffiliateWP\AffiliateWP;
use SureTriggers\Integrations\EDD\EDD;
use SureTriggers\Integrations\FunnelKitAutomations\FunnelKitAutomations;
use SureTriggers\Integrations\JetpackCRM\JetpackCRM;
use SureTriggers\Integrations\LearnDash\LearnDash;
use SureTriggers\Integrations\LifterLMS\LifterLMS;
use SureTriggers\Integrations\MemberPress\MemberPress;
use SureTriggers\Integrations\MemberPressCourse\MemberPressCourse;
use SureTriggers\Integrations\ModernEventsCalendar\ModernEventsCalendar;
use SureTriggers\Integrations\PeepSo\PeepSo;
use SureTriggers\Integrations\RafflePress\RafflePress;
use SureTriggers\Integrations\RestrictContent\RestrictContent;
use SureTriggers\Integrations\TheEventCalendar\TheEventCalendar;
use SureTriggers\Integrations\WishlistMember\WishlistMember;
use SureTriggers\Integrations\WooCommerce\WooCommerce;
use SureTriggers\Integrations\WordPress\WordPress;
use SureTriggers\Integrations\WpPolls\WpPolls;
use SureTriggers\Models\Utilities;
use SureTriggers\Traits\SingletonLoader;
use Tribe__Tickets__Tickets_Handler;
use WC_Subscription;
use WC_Subscriptions_Product;
use WP_Query;
use WP_Comment_Query;
use WP_REST_Request;
use WP_REST_Response;
use WPForms_Form_Handler;
use CP_V2_Popups;
use Project_Huddle;
use FrmForm;
use Forminator_API;
use SureTriggers\Integrations\LearnPress\LearnPress;

/**
 * GlobalSearchController- Add ajax related functions here.
 *
 * @category GlobalSearchController
 * @package  SureTriggers
 * @author   BSF <username@example.com>
 * @license  https://www.gnu.org/licenses/gpl-3.0.html GPLv3
 * @link     https://www.brainstormforce.com/
 * @since    1.0.0
 *
 * @psalm-suppress UndefinedTrait
 */
class GlobalSearchController {

	use SingletonLoader;

	/**
	 * Search post by post type.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_post( $data ) {
		$result = [];
		$posts  = Utilities::find_posts_by_title( $data );

		foreach ( $posts['results'] as $post ) {
			$result[] = [
				'label' => $post['post_title'],
				'value' => $post['ID'],
			];
		}

		return [
			'options' => $result,
			'hasMore' => $posts['has_more'],
		];
	}

	/**
	 * Search Course.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_ld_course( $data ) {
		$courses = get_posts(
			[

				'post_type'   => 'product',
				'meta_key'    => '_related_course',
				'post_status' => 'publish',
			]
		);
		$options = [];
		foreach ( $courses as $course ) {
			$options[] = [
				'label' => $course->post_title,
				'value' => $course->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search achievement by post type.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_achievements( $data ) {
		$post = get_post( $data['dynamic'] );
		$slug = $post->post_name;

		$achievements = get_posts(
			[
				'post_type'   => $slug,
				'post_status' => 'publish',
			]
		);
		$options      = [];
		foreach ( $achievements as $achievement ) {
			$options[] = [
				'label' => $achievement->post_title,
				'value' => (string) $achievement->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Course.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_tutor_course( $data ) {
		$courses = get_posts(
			[
				'post_type'   => tutor()->course_post_type,
				'post_status' => 'publish',
				'numberposts' => '-1',
			]
		);
		$options = [];
		foreach ( $courses as $course ) {
			$options[] = [
				'label' => $course->post_title,
				'value' => $course->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Products.
	 *
	 * @param array $data quesry params.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_product( $data ) {
		$result = [];
		$posts  = Utilities::find_posts_by_title( $data );

		foreach ( $posts['results'] as $post ) {
			$result[] = [
				'label' => $post['post_title'],
				'value' => $post['post_title'],
			];
		}

		return [
			'options' => $result,
			'hasMore' => $posts['has_more'],
		];
	}

	/**
	 * Search Product Categories.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_product_category( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );
		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->name,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Search Product Tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_product_tags( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );

		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->name,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Global ajax search.
	 * Here you need to add the field action name to work.
	 *
	 * @param WP_REST_Request $request Request data.
	 *
	 * @return WP_REST_Response
	 * @since 1.0.0
	 */
	public function global_search( $request ) {
		$post_type   = $request->get_param( 'post_type' );
		$dynamic     = $request->get_param( 'dynamic' );
		$search_term = $request->get_param( 'term' );
		$identifier  = $request->get_param( 'field_identifier' );
		$page        = max( 1, $request->get_param( 'page' ) );
		$taxonomy    = $request->get_param( 'taxonomy' ) ? $request->get_param( 'taxonomy' ) : [];

		$filter = $request->get_param( 'filter' ) ? json_decode( stripslashes( $request->get_param( 'filter' ) ), true ) : [];

		$data     = [
			'dynamic'     => $dynamic,
			'search_term' => $search_term,
			'page'        => $page,
			'taxonomy'    => $taxonomy,
			'filter'      => $filter,
			'post_type'   => $post_type,
		];
		$response = [
			'hasMore' => false,
			'options' => [],
		];

		$method_name = 'search_' . $identifier;

		if ( method_exists( $this, $method_name ) ) {
			$response = $this->{$method_name}( $data );
		} else {
			return RestController::error_message( 'Invalid field Identifier param.' );
		}

		return RestController::success_message( $response );
	}

	/**
	 * Search Taxonomy Terms.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_term( $data ) {
		if ( ! empty( $data['dynamic'] ) ) {
			$taxonomy = $data['dynamic'];
		} else {
			$taxonomy = isset( $data['taxonomy'] ) ? $data['taxonomy'] : '';
		}

		$term   = $data['term'];
		$result = [];
		$terms  = Utilities::get_terms( $term, $data['page'], $taxonomy );
		foreach ( $terms['result'] as $tax_term ) {
			$result[] = [
				'label' => $tax_term->name,
				'value' => $tax_term->term_id,
			];
		}

		return [
			'options' => $result,
			'hasMore' => $terms['has_more'],
		];
	}

	/**
	 * Search users.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_user( $data ) {
		$result = [];
		$page   = $data['page'];
		$users  = Utilities::get_users( $data, $page );

		if ( is_array( $users['results'] ) ) {
			foreach ( $users['results'] as $user ) {
				$result[] = [
					'label' => $user->user_login,
					'value' => $user->ID,
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $users['has_more'],
		];

	}

	/**
	 * Search WPForm fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_pluggable_wpform_fields( $data ) {
		$result        = [];
		$page          = $data['page'];
		$form_id       = absint( $data['dynamic'] );
		$wpform_fields = Utilities::get_wpform_fields( $data['search_term'], $page, $form_id );

		if ( is_array( $wpform_fields['results'] ) ) {
			foreach ( $wpform_fields['results'] as $field ) {
				$result[] = [
					'label' => $field['label'],
					'value' => '{' . $field['id'] . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $wpform_fields['has_more'],
		];
	}

	/**
	 * Prepare variable products.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_variable_products( $data ) {
		$products = Utilities::get_variable_products( $data['search_term'], $data['page'] );
		$options  = [];

		foreach ( $products['result'] as $product ) {
			$options[] = [
				'label' => $product->get_title(),
				'value' => (string) $product->get_id(),
			];
		}

		return [
			'options' => $options,
			'hasMore' => $products['has_more'],
		];
	}

	/**
	 * Prepare variable products.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_product_variations( $data ) {
		$variations = Utilities::get_product_variations( $data['dynamic'] );
		$options    = [];

		foreach ( $variations['result'] as $product ) {
			$options[] = [
				'label' => ! empty( $product->post_excerpt ) ? $product->post_excerpt : $product->post_title,
				'value' => (string) $product->ID,
			];
		}

		return [
			'options' => $options,
			'hasMore' => $variations['has_more'],
		];
	}

	/**
	 * Search WooCommerce Subscriptions.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_subscription_variation( $data ) {
		$subscription_products = Utilities::get_subscription_variation( $data['search_term'], $data['page'] );
		$result                = [];

		if ( ! function_exists( 'wc_get_products' ) ) {
			return $result;
		}

		foreach ( $subscription_products['result'] as $post ) {
			if ( $data['search_term'] ) {
				if ( false !== stripos( $post->get_title(), $data['search_term'] ) ) {
					$result[] = [
						'label' => $post->get_title(),
						'value' => (string) $post->get_id(),
					];
				}
			} else {
				$result[] = [
					'label' => $post->get_title(),
					'value' => (string) $post->get_id(),
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => $subscription_products['has_more'],
		];
	}

	/**
	 * Prepare WooCommerce Payment Methods.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_payment_methods( $data ) {
		$payment_methods = WC()->payment_gateways->get_available_payment_gateways();
		$options         = [];

		if ( ! empty( $payment_methods ) ) {
			foreach ( $payment_methods as $payment ) {
				$options[] = [
					'label' => $payment->title,
					'value' => $payment->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Order Status List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_order_status_list( $data ) {
		$order_status = wc_get_order_statuses();
		$options      = [];

		if ( ! empty( $order_status ) ) {
			foreach ( $order_status as $key => $status ) {
				$options[] = [
					'label' => $status,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Country List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_country_list( $data ) {
		$countries = WC()->countries->get_countries();
		$options   = [];

		if ( ! empty( $countries ) ) {
			foreach ( $countries as $key => $country ) {
				$options[] = [
					'label' => $country,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare WooCommerce Country States List.
	 *
	 * @param array $data Search Params.
	 * @return array[]
	 */
	public function search_woo_country_state_list( $data ) {
		if ( ! empty( $data['dynamic']['shipping_country'] ) ) {
			$cc = $data['dynamic']['shipping_country'];
		} else {
			$cc = $data['dynamic'];
		}

		$states = WC()->countries->get_states( $cc );

		$options = [];
		if ( ! empty( $states ) ) {
			foreach ( $states as $key => $state ) {
				$options[] = [
					'label' => $state,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress gatways (payment methods) for  subscription.
	 *
	 * @param array $data QueryParams.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_memberpress_gayways( $data ) {
		$mp_options = MeprOptions::fetch();

		$pms      = array_keys( $mp_options->integrations );
		$gateways = [];

		foreach ( $pms as $pm_id ) {
			$obj = $mp_options->payment_method( $pm_id );
			if ( $obj instanceof MeprBaseRealGateway ) {
				$gateways[] = [
					'label' => sprintf( '%1$s (%2$s)', $obj->label, $obj->name ),
					'value' => $obj->id,
				];
			}
		}

		return [
			'options' => $gateways,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare roles.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_roles( $data ) {
		$roles   = wp_roles()->roles;
		$options = [];
		foreach ( $roles as $role => $details ) {

			$options[] = [
				'label' => $details['name'],
				'value' => $role,
			];

		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Fetch operators.
	 *
	 * @return array
	 */
	public function search_condition_operators() {
		return [
			'options' => EventHelperController::get_instance()->prepare_operators(),
			'hasMore' => false,
		];
	}

	/**
	 * Prepare post types.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_post_types( $data ) {
		$post_types = get_post_types( [ 'public' => true ], 'object' );
		$post_types = apply_filters( 'suretriggers_post_types', $post_types );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$options = [];
		foreach ( $post_types as $post_type => $details ) {
			$options[] = [
				'label' => $details->label,
				'value' => $post_type,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get post statuses.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_post_statuses( $data ) {
		$post_statuses = get_post_stati( [], 'objects' );
		$post_statuses = apply_filters( 'suretriggers_post_types', $post_statuses );
		$options       = [];

		foreach ( $post_statuses as $post_status => $details ) {
			if ( 'woocommerce' === $details->label_count['domain'] ) {
				$options[] = [
					'label' => 'WooCommerce - ' . $details->label,
					'value' => $post_status,
				];
			} else {
				$options[] = [
					'label' => $details->label,
					'value' => $post_status,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Taxonomies.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_taxonomy_list( $data ) {
		$taxonomies = get_taxonomies( [ 'public' => true ], 'objects' );
		$options    = [];
		$options[0] = [
			'label' => 'Any Taxonomy',
			'value' => -1,
		];

		foreach ( $taxonomies as $taxonomy => $taxonomy_obj ) {
			$options[] = [
				'label' => $taxonomy_obj->label,
				'value' => $taxonomy_obj->name,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WPForms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_forms( $data ) {
		if ( ! class_exists( 'WPForms_Form_Handler' ) ) {
			return;
		}

		$wpforms = new WPForms_Form_Handler();
		$forms   = $wpforms->get( '', [ 'orderby' => 'title' ] );
		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->post_title,
					'value' => $form->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Gravity Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_gravity_forms( $data ) {
		if ( ! class_exists( 'GFFormsModel' ) ) {
			return;
		}

		$forms   = GFFormsModel::get_forms();
		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->title,
					'value' => $form->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get tag & contact details.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_fluentcrm_contact_added_to_tags( $data ) {
		$context        = [];
		$pluggable_data = [];
		$tag_id         = $data['filter'];

		if ( $tag_id > 0 ) {
			$tags = Tag::where( 'id', $tag_id )->first();
		} else {
			$tags = Tag::orderBy( 'id', 'DESC' )->first();
		}
		$contact = Subscriber::orderBy( 'id', 'DESC' )->first();
		if ( $contact ) {
			$pluggable_data['contact'] = $contact;
			$context['tag_id']         = $tag_id;
			$pluggable_data['tag']     = $tags;
			$context['response_type']  = 'live';
		} else {
			$pluggable_data['conatct']['full_name']      = 'Test User';
			$pluggable_data['conatct']['first_name']     = 'Test';
			$pluggable_data['conatct']['last_name']      = 'User';
			$pluggable_data['conatct']['company_id']     = 112;
			$pluggable_data['conatct']['email']          = 'testuser@gmail.com';
			$pluggable_data['conatct']['address_line_1'] = '33, Vincent Road';
			$pluggable_data['conatct']['address_line_2'] = 'Chicago Street';
			$pluggable_data['conatct']['postal_code']    = '212342';
			$pluggable_data['conatct']['city']           = 'New York City';
			$pluggable_data['conatct']['state']          = 'New York';
			$pluggable_data['conatct']['country']        = 'USA';
			$pluggable_data['conatct']['phone']          = '9992191911';
			$pluggable_data['conatct']['status']         = 'subscribed';
			$pluggable_data['conatct']['contact_type']   = 'lead';
			$pluggable_data['conatct']['source']         = '';
			$pluggable_data['conatct']['date_of_birth']  = '2022-11-09';
			$context['tag_id']                           = 1;
			$pluggable_data['tag']                       =
				[
					'id'          => '1',
					'title'       => 'new',
					'slug'        => 'new',
					'description' => null,
					'created_at'  => '2023-01-19 10:23:23',
					'updated_at'  => '2023-01-19 10:23:23',
					'pivot'       => [
						'subscriber_id' => '1',
						'object_id'     => '1',
						'object_type'   => 'FluentCrm\\App\\Models\\Tag',
						'created_at'    => '2023-01-19 10:42:55',
						'updated_at'    => '2023-01-19 10:42:55',

					],
				];
			$context['response_type'] = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Get FluentCRM contact details.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_fluentcrm_contact_added( $data ) {
		$context        = [];
		$pluggable_data = [];

		$contact = Subscriber::orderBy( 'id', 'DESC' )->first();
		if ( $contact ) {
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$subscriber                           = Subscriber::with( [ 'tags', 'lists' ] )->find( $contact->id );
			$customer_fields                      = $subscriber->custom_fields();
			$pluggable_data['contact']['details'] = $subscriber;
			$pluggable_data['contact']['custom']  = $customer_fields;
			$context['response_type']             = 'live';
		} else {
			$pluggable_data['contact']['details']['full_name']      = 'Test User';
			$pluggable_data['contact']['details']['first_name']     = 'Test';
			$pluggable_data['contact']['details']['last_name']      = 'User';
			$pluggable_data['contact']['details']['company_id']     = 112;
			$pluggable_data['contact']['details']['email']          = 'testuser@gmail.com';
			$pluggable_data['contact']['details']['address_line_1'] = '33, Vincent Road';
			$pluggable_data['contact']['details']['address_line_2'] = 'Chicago Street';
			$pluggable_data['contact']['details']['postal_code']    = '212342';
			$pluggable_data['contact']['details']['city']           = 'New York City';
			$pluggable_data['contact']['details']['state']          = 'New York';
			$pluggable_data['contact']['details']['country']        = 'USA';
			$pluggable_data['contact']['details']['phone']          = '9992191911';
			$pluggable_data['contact']['details']['status']         = 'subscribed';
			$pluggable_data['contact']['details']['contact_type']   = 'lead';
			$pluggable_data['contact']['details']['source']         = '';
			$pluggable_data['contact']['details']['date_of_birth']  = '2022-11-09';
			$context['response_type']                               = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Get Divi Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_divi_forms( $data ) {
		$form_posts = Utilities::get_divi_forms();
		$options    = [];

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$pattern_regex = '/\[et_pb_contact_form(.*?)](.+?)\[\/et_pb_contact_form]/';
			preg_match_all( $pattern_regex, $form_post['post_content'], $forms, PREG_SET_ORDER );
			if ( empty( $forms ) ) {
				continue;
			}

			$count = 0;

			foreach ( $forms as $form ) {
				$pattern_form = get_shortcode_regex( [ 'et_pb_contact_form' ] );
				preg_match_all( "/$pattern_form/", $form[0], $forms_extracted, PREG_SET_ORDER );

				if ( empty( $forms_extracted ) ) {
					continue;
				}

				foreach ( $forms_extracted as $form_extracted ) {
					$form_attrs = shortcode_parse_atts( $form_extracted[3] );
					$form_id    = isset( $form_attrs['_unique_id'] ) ? $form_attrs['_unique_id'] : '';
					if ( empty( $form_id ) ) {
						continue;
					}
					$form_id    = sprintf( '%d-%s', $form_post['ID'], $form_id );
					$form_title = isset( $form_attrs['title'] ) ? $form_attrs['title'] : '';
					$form_title = sprintf( '%s %s', $form_post['post_title'], $form_title );
					$options[]  = [
						'label' => $form_title,
						'value' => $form_id,
					];
				}
				$count++;
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Comment Pluggable data.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_pluggables_wp_insert_comment( $data ) {
		$context   = [];
		$post_data = [];
		$args      = [
			'number' => '1',
			'status' => 'approve',
		];

		if ( isset( $data['filter']['post']['value'] ) ) {
			$post_id = $data['filter']['post']['value'];
			if ( $post_id > 0 ) {
				$args['post_id'] = $post_id;
			}
		}

		$comments = get_comments( $args );
		if ( empty( $comments ) ) {
			unset( $args['post_id'] );
			$comments = get_comments( $args );
		}
		$context['context_data'] = $data;
		$context['context_args'] = $args;
		if ( ! empty( $comments ) ) {
			foreach ( $comments as $comment ) :
				if ( is_object( $comment ) ) {
					$comment = get_object_vars( $comment );
				}
				if ( is_array( $comment ) && isset( $comment['comment_post_ID'] ) ) {
					$post = get_post( absint( $comment['comment_post_ID'] ) );
					if ( is_object( $post ) ) {
						if ( property_exists( $post, 'ID' ) || property_exists( $post, 'post_author' ) || property_exists( $post, 'post_title' ) ) {
							$post_id    = $post->ID;
							$postauthor = (int) $post->post_author;
							if ( is_array( $comment ) ) {
								$context['pluggable_data'] = [
									'post'                 => $post_id,
									'post_title'           => $post->post_title,
									'post_author'          => get_the_author_meta( 'display_name', $postauthor ),
									'post_link'            => get_the_permalink( $post_id ),
									'comment_id'           => $comment['comment_ID'],
									'comment'              => $comment['comment_content'],
									'comment_author'       => $comment['comment_author'],
									'comment_author_email' => $comment['comment_author_email'],
									'comment_date'         => $comment['comment_date'],
								];
							}
						}
					}
				}
				if ( is_array( $comment ) && isset( $comment['comment_author_email'] ) ) {
					$user_email = $comment['comment_author_email'];
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					$user = get_user_by( 'email', $user_email );
					if ( $user ) {
						$context['pluggable_data']['wp_user_id']     = $user->ID;
						$context['pluggable_data']['user_login']     = $user->user_login;
						$context['pluggable_data']['display_name']   = $user->display_name;
						$context['pluggable_data']['user_firstname'] = $user->user_firstname;
						$context['pluggable_data']['user_lastname']  = $user->user_lastname;
						$context['pluggable_data']['user_email']     = $user->user_email;
						$context['pluggable_data']['user_role']      = $user->roles;
					}
				}

				$context['response_type'] = 'live';
			endforeach;
		} else {
			$sample_comment                   = [
				'post'       => 100,
				'post_title' => 'Sample Post',
				'comment_id' => 101,
				'comment'    => 'Sample Comment',
			];
			$sample_comment['wp_user_id']     = 7;
			$sample_comment['user_login']     = 'testuser@gmail.com';
			$sample_comment['display_name']   = 'Test User';
			$sample_comment['user_firstname'] = 'Test';
			$sample_comment['user_lastname']  = 'User';
			$sample_comment['user_email']     = 'testuser@gmail.com';
			$sample_comment['user_role']      = [ 'Subscriber' ];

			$context['pluggable_data'] = $sample_comment;
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * User reset password.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_user_reset_password( $data ) {
		$user_context                                   = $this->search_pluggables_add_user_role( $data );
		$user_context['pluggable_data']['new_password'] = '***password***';
		return $user_context;
	}

	/**
	 * User pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_add_user_role( $data ) {
		$context = [];
		$args    = [
			'order'   => 'DESC',
			'number'  => 1,
			'orderby' => 'ID',
		];

		if ( isset( $data['filter']['role']['value'] ) ) {
			$role         = $data['filter']['role']['value'];
			$args['role'] = $role;
		}
		if ( isset( $data['filter']['new_role']['value'] ) ) {
			$role         = $data['filter']['new_role']['value'];
			$args['role'] = $role;
		}

		$users = get_users( $args );

		if ( isset( $data['filter']['meta_key']['value'] ) ) {
			$meta_key            = $data['filter']['meta_key']['value'];
			$args['st_meta_key'] = $meta_key;
		}

		if ( isset( $data['filter']['profile_field']['value'] ) ) {
			$meta_key              = $data['filter']['profile_field']['value'];
			$args['profile_field'] = $meta_key;
		}

		if ( ! empty( $users ) ) {
			$user                             = $users[0];
			$pluggable_data                   = [];
			$pluggable_data['wp_user_id']     = $user->ID;
			$pluggable_data['user_login']     = $user->user_login;
			$pluggable_data['display_name']   = $user->display_name;
			$pluggable_data['user_firstname'] = $user->user_firstname;
			$pluggable_data['user_lastname']  = $user->user_lastname;
			$pluggable_data['user_email']     = $user->user_email;
			$pluggable_data['user_role']      = $user->roles;

			if ( isset( $args['st_meta_key'] ) ) {
				$pluggable_data['meta_key']   = $args['st_meta_key'];
				$pluggable_data['meta_value'] = get_user_meta( $user->ID, $args['st_meta_key'], true );
			}
			if ( isset( $args['profile_field'] ) ) {
				$userdata = get_userdata( $user->ID );
				$userdata = json_decode( wp_json_encode( $userdata->data ), true );

				$pluggable_data['profile_field']       = $args['profile_field'];
				$pluggable_data['profile_field_value'] = $userdata[ $args['profile_field'] ];
			}
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'admin',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			if ( isset( $args['st_meta_key'] ) ) {
				$context['pluggable_data']['meta_key']   = $args['st_meta_key'];
				$context['pluggable_data']['meta_value'] = 'test meta value';
			}
			if ( isset( $args['profile_field'] ) ) {
				$context['pluggable_data']['profile_field']       = $args['profile_field'];
				$context['pluggable_data']['profile_field_value'] = 'Profile Field Value';
			}
			$context['response_type'] = 'sample';
		}
		return $context;
	}

	/**
	 * User pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_last_user_login( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => 'st_last_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                             = $users[0];
			$pluggable_data                   = [];
			$pluggable_data['wp_user_id']     = $user->ID;
			$pluggable_data['user_login']     = $user->user_login;
			$pluggable_data['display_name']   = $user->display_name;
			$pluggable_data['user_firstname'] = $user->user_firstname;
			$pluggable_data['user_lastname']  = $user->user_lastname;
			$pluggable_data['user_email']     = $user->user_email;
			$pluggable_data['user_role']      = $user->roles;

			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'admin',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Donation pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wordpress_post( $data ) {
		$context = [];
		$args    = [
			'post_type'      => 'any',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		];

		if ( isset( $data['filter']['post_type']['value'] ) ) {
			$post_type         = $data['filter']['post_type']['value'];
			$args['post_type'] = $post_type;
		}

		if ( isset( $data['filter']['status']['value'] ) ) {
			$post_status         = $data['filter']['status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['post_status']['value'] ) ) {
			$post_status         = $data['filter']['post_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['post']['value'] ) ) {
			$post_id = $data['filter']['post']['value'];
			if ( $post_id > 0 ) {
				$args['p'] = $post_id;
				unset( $args['post_status'] );
			}
		}

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = $posts[0];
			$custom_metas              = get_post_meta( $posts[0]->ID );
			if ( property_exists( $context['pluggable_data'], 'post' ) ) {
				$context['pluggable_data']->post = $posts[0]->ID;
			}
			if ( is_object( $context['pluggable_data'] ) ) {
				$context['pluggable_data'] = get_object_vars( $context['pluggable_data'] );
			}
			$context['pluggable_data']['custom_metas'] = $custom_metas;
			$context['response_type']                  = 'live';
		} else {
			$context['pluggable_data'] = [
				'ID'                    => 557,
				'post'                  => 557,
				'post_author'           => 1,
				'post_date'             => '2022-11-18 12:18:14',
				'post_date_gmt'         => '2022-11-18 12:18:14',
				'post_content'          => 'Test Post Content',
				'post_title'            => 'Test Post',
				'post_excerpt'          => '',
				'post_status'           => $args['post_status'],
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => 'test-post',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2022-11-18 12:18:14',
				'post_modified_gmt'     => '2022-11-18 12:18:14',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'https://abc.com/test-post/',
				'menu_order'            => 0,
				'post_type'             => 'post',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			];
			$context['response_type']  = 'sample';
		}

		return $context;
	}

	/**
	 * Donation pluggable data
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_givewp_donation_via_form( $data ) {
		global $wpdb;
		$context        = [];
		$pluggable_data = [];

		$payment = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE post_type=%s ORDER BY id DESC LIMIT 1", 'give_payment' ) );

		if ( ! empty( $payment ) ) {
			$payment      = new Give_Payment( $payment->ID );
			$address_data = $payment->address;

			$pluggable_data['first_name']        = $payment->first_name;
			$pluggable_data['last_name']         = $payment->last_name;
			$pluggable_data['email']             = $payment->email;
			$pluggable_data['currency']          = $payment->currency;
			$pluggable_data['donated_amount']    = $payment->subtotal;
			$pluggable_data['donation_amount']   = $payment->subtotal;
			$pluggable_data['form_id']           = (int) $payment->form_id;
			$pluggable_data['form_title']        = $payment->form_title;
			$pluggable_data['name_title_prefix'] = $payment->title_prefix;
			$pluggable_data['date']              = $payment->date;

			if ( is_array( $address_data ) ) {
				$pluggable_data['address_line_1'] = $address_data['line1'];
				$pluggable_data['address_line_2'] = $address_data['line2'];
				$pluggable_data['city']           = $address_data['city'];
				$pluggable_data['state']          = $address_data['state'];
				$pluggable_data['zip']            = $address_data['zip'];
				$pluggable_data['country']        = $address_data['country'];
			}
			$donor_comment             = give_get_donor_donation_comment( $payment->ID, $payment->donor_id );
			$pluggable_data['comment'] = isset( $doner['comment_content'] ) ? $donor_comment : '';
			$context['response_type']  = 'live';
		} else {
			$pluggable_data['first_name']        = 'Test';
			$pluggable_data['last_name']         = 'User';
			$pluggable_data['email']             = 'testuser@gmail.com';
			$pluggable_data['currency']          = 'USD';
			$pluggable_data['donated_amount']    = 100;
			$pluggable_data['donation_amount']   = 100;
			$pluggable_data['form_id']           = 23;
			$pluggable_data['form_title']        = 'Test Donation';
			$pluggable_data['name_title_prefix'] = 'Mr';
			$pluggable_data['date']              = '2022-11-07 16:06:05';
			$pluggable_data['address_line_1']    = '33, Vincent Road';
			$pluggable_data['address_line_2']    = 'Chicago Street';
			$pluggable_data['city']              = 'New York City';
			$pluggable_data['state']             = 'New York';
			$pluggable_data['zip']               = '223131';
			$pluggable_data['country']           = 'USA';
			$pluggable_data['comment']           = 'Test Comment';
			$context['response_type']            = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search Divi Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_pluggable_divi_form_fields( $data ) {
		$result     = [];
		$form_id    = absint( $data['dynamic'] );
		$form_posts = Utilities::get_divi_forms();

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$fields = [];
		foreach ( $form_posts as $form_post ) {
			$pattern_regex = '/\[et_pb_contact_form(.*?)](.+?)\[\/et_pb_contact_form]/';
			preg_match_all( $pattern_regex, $form_post['post_content'], $forms, PREG_SET_ORDER );
			if ( empty( $forms ) ) {
				continue;
			}

			$count = 0;

			foreach ( $forms as $form ) {
				$pattern = get_shortcode_regex( [ 'et_pb_contact_field' ] );

				preg_match_all( "/$pattern/", $form[0], $contact_fields, PREG_SET_ORDER );

				if ( empty( $contact_fields ) ) {
					return $fields;
				}

				foreach ( $contact_fields as $contact_field ) {
					$contact_field_attrs = shortcode_parse_atts( $contact_field[3] );
					$field_id            = strtolower( self::array_get( $contact_field_attrs, 'field_id' ) );
					$fields[]            = [
						'field_title' => self::array_get( $contact_field_attrs, 'field_title', __( 'No title', 'suretriggers' ) ),
						'field_id'    => $field_id,
					];
				}
			}
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$result[] = [
					'label' => $field['field_title'],
					'value' => '{' . $field['field_id'] . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Pseudo function copied from Divi
	 *
	 * @param array        $array An array which contains value located at `$address`.
	 * @param string|array $address The location of the value within `$array` (dot notation).
	 * @param mixed        $default Value to return if not found. Default is an empty string.
	 *
	 * @return mixed The value, if found, otherwise $default.
	 */
	public static function array_get( $array, $address, $default = '' ) {
		$keys  = is_array( $address ) ? $address : explode( '.', $address );
		$value = $array;

		foreach ( $keys as $key ) {
			if ( ! empty( $key ) && isset( $key[0] ) && '[' === $key[0] ) {
				$index = substr( $key, 1, -1 );

				if ( is_numeric( $index ) ) {
					$key = (int) $index;
				}
			}

			if ( ! isset( $value[ $key ] ) ) {
				return $default;
			}

			$value = $value[ $key ];
		}

		return $value;
	}

	/**
	 * Get UAG Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_spectra_forms( $data ) {
		$form_posts = Utilities::get_uag_forms();

		$options = [];
		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$blocks = parse_blocks( $form_post['post_content'] );
			$i      = 1;
			foreach ( $blocks as $key => $block ) {
				if ( 'uagb/forms' === $block['blockName'] ) {
					$options[] = [
						'label' => $form_post['post_title'] . ' (Form ' . ( $i ) . ')',
						'value' => $block['attrs']['block_id'],
					];
					$i++;
				} elseif ( 'uagb/forms' !== $block['blockName'] && isset( $block['innerBlocks'] ) ) {
					$container_key = self::get_column_by_value( $block['innerBlocks'], 'uagb/container' );
					if ( isset( $container_key ) && '' !== $container_key ) {
						$options[] = [
							'label' => $form_post['post_title'] . ' (Form ' . ( $i ) . ')',
							'value' => $block['innerBlocks'][ $container_key ]['attrs']['block_id'],
						];
							$i++; 
					}
				}           
			}
		}
	
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Check array recursive.
	 *
	 * @param array  $array Array.
	 * @param string $value search params.
	 * @since 1.0.0
	 *
	 * @return array|void
	 */
	public static function get_column_by_value( $array, $value ) {

		foreach ( $array as $key => $sub_array ) {
			
			if ( is_array( $sub_array ) ) {
				$result = self::get_column_by_value( $sub_array, $value );
				if ( null !== $result ) {
					return $key;
				}
			} else {
				return $key;
			}
		}
			return null;
			
	}


	/**
	 * Search UAG Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function search_spectraform_fields( $data ) {
		$result     = [];
		$form_id    = absint( $data['dynamic'] );
		$form_posts = Utilities::get_uag_forms();

		if ( empty( $form_posts ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		foreach ( $form_posts as $form_post ) {
			$blocks = parse_blocks( $form_post['post_content'] );

			foreach ( $blocks as $block ) {
				if ( (int) $block['attrs']['block_id'] === $form_id ) {
					$doc            = new DOMDocument();
					$rendered_block = render_block( $block );
					$doc->loadHTML( $rendered_block );
					$child_node_list = $doc->getElementsByTagName( 'div' );
					for ( $i = 0; $i < $child_node_list->length; $i++ ) {
						$temp = $child_node_list->item( $i );
						if ( $temp && stripos( $temp->getAttribute( 'class' ), 'uagb-forms-input-label' ) !== false ) {
							$nodes[] = $temp;
						}
					}

					foreach ( $nodes as $node ) {
						$result[] = [
                            'label' => $node->textContent, //phpcs:ignore
                            'value' => $node->textContent, //phpcs:ignore
						];
					}
				}
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of MetForms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_met_forms( $data ) {
		$args = [
			'post_type'   => 'metform-form',
			'post_status' => 'publish',
			'numberposts' => -1,
		];

		$forms = get_posts( $args );

		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->post_title,
					'value' => $form->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of Ninja Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_ninja_forms( $data ) {
		$options = [];

		if ( function_exists( 'Ninja_Forms' ) ) {
			foreach ( Ninja_Forms()->form()->get_forms() as $form ) {
				$options[] = [
					'label' => $form->get_setting( 'title' ),
					'value' => $form->get_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms of Pie Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pie_forms( $data ) {
		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pf_forms' ) ) ) {

			$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'pf_forms WHERE post_status = "published"' );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->form_title,
						'value' => $result->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Fluent Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_fluent_forms( $data ) {

		if ( ! function_exists( 'wpFluent' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		$forms = wpFluent()->table( 'fluentform_forms' )
			->select( [ 'id', 'title' ] )
			->orderBy( 'id', 'DESC' )
			->get();

		$options = [];
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form->title,
					'value' => $form->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get Fluent Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_bricks_builder_forms( $data ) {
		$bricks_theme = wp_get_theme( 'bricks' );
		if ( ! $bricks_theme->exists() ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}

		$args = [
			'post_type'      => 'bricks_template',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		];

		$templates = get_posts( $args );

		$options = [];
		if ( ! empty( $templates ) ) {
			foreach ( $templates as $template ) {
				$fetch_content = get_post_meta( $template->ID, BRICKS_DB_PAGE_CONTENT, true );
				if ( is_array( $fetch_content ) ) {
					foreach ( $fetch_content as $content ) {
						if ( 'form' === $content['name'] ) {
							$options[] = [
								'label' => $template->post_title . ' - ' . ( isset( $content['label'] ) ? $content['label'] : 'Form' ),
								'value' => $content['id'],
							];
						}
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Bricks builder form fields.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggable_bricks_builder_form_fields( $data ) {
		$result        = [];
		$fields        = [];
		$form_id_str   = $data['dynamic'];
		$ids           = explode( '_', $form_id_str );
		$post_id       = $ids[0];
		$form_id       = $ids[1];
		$fetch_content = get_post_meta( $post_id, BRICKS_DB_PAGE_CONTENT, true );
		if ( is_array( $fetch_content ) ) {
			foreach ( $fetch_content as $content ) {
				if ( 'form' === $content['name'] && $form_id === $content['id'] ) {
					$fields = $content['settings']['fields'];
					break;
				}
			}
		}

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$result[] = [
					'label' => $field['label'],
					'value' => '{' . strtolower( $field['label'] ) . '}',
				];
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Get fluent form fields
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	public function search_pluggable_fluent_form_fields( $data ) {
		$result  = [];
		$form_id = absint( $data['dynamic'] );

		$fluentform_fields = Utilities::get_fluentform_fields( $data['search_term'], -1, $form_id );

		if ( is_array( $fluentform_fields['results'] ) ) {
			foreach ( $fluentform_fields['results'] as $field ) {
				$result[] = [
					'label' => $field['text'],
					'value' => "{{$field['value']}}",
				];
			}
		}

		$result[] = [
			'value' => '{form_id}',
			'label' => 'Form ID',
		];

		$result[] = [
			'value' => '{form_title}',
			'label' => 'Form Title',
		];
		$result[] = [
			'value' => '{entry_id}',
			'label' => 'Entry ID',
		];

		$result[] = [
			'value' => '{entry_source_url}',
			'label' => 'Entry Source URL',
		];

		$result[] = [
			'value' => '{submission_date}',
			'label' => 'Submission Date',
		];

		$result[] = [
			'value' => '{user_ip}',
			'label' => 'User IP',
		];

		return [
			'options' => $result,
			'hasMore' => false,
		];
	}

	/**
	 * Search Gravity Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_gform_fields( $data ) {
		if ( ! class_exists( 'RGFormsModel' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$result  = [];
		$page    = $data['page'];
		$form_id = absint( $data['dynamic'] );

		$form = RGFormsModel::get_form_meta( $form_id );

		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $input ) {
						if ( ! isset( $input['isHidden'] ) || ( isset( $input['isHidden'] ) && ! $input['isHidden'] ) ) {
							$result[] = [
								'value' => $input['id'],
								'label' => GFCommon::get_label( $field, $input['id'] ),
							];
						}
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$result[] = [
						'value' => $field['id'],
						'label' => GFCommon::get_label( $field ),
					];
				}
			}
		}

		return [
			'options' => $result,
			'hasMore' => false,
		];

	}

	/**
	 * Search Gravity Form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @since 1.0.0
	 */
	public function search_pluggable_gravity_form_fields( $data ) {
		if ( ! class_exists( 'RGFormsModel' ) ) {
			return [
				'options' => [],
				'hasMore' => false,
			];
		}
		$result  = [];
		$form_id = absint( $data['dynamic'] );

		$form = RGFormsModel::get_form_meta( $form_id );

		if ( is_array( $form['fields'] ) ) {
			foreach ( $form['fields'] as $field ) {
				if ( isset( $field['inputs'] ) && is_array( $field['inputs'] ) ) {
					foreach ( $field['inputs'] as $input ) {
						if ( ! isset( $input['isHidden'] ) || ( isset( $input['isHidden'] ) && ! $input['isHidden'] ) ) {
							$result[] = [
								'value' => '{' . $input['id'] . '}',
								'label' => GFCommon::get_label( $field, $input['id'] ),
							];
						}
					}
				} elseif ( ! rgar( $field, 'displayOnly' ) ) {
					$result[] = [
						'value' => '{' . $field['id'] . '}',
						'label' => GFCommon::get_label( $field ),
					];
				}
			}
		}

		$result[] = [
			'value' => '{gravity_form}',
			'label' => 'Form ID',
		];
		$result[] = [
			'value' => '{form_title}',
			'label' => 'Form Title',
		];
		$result[] = [
			'value' => '{entry_id}',
			'label' => 'Entry ID',
		];
		$result[] = [
			'value' => '{user_ip}',
			'label' => 'User IP',
		];
		$result[] = [
			'value' => '{entry_source_url}',
			'label' => 'Entry Source URL',
		];
		$result[] = [
			'value' => '{entry_submission_date}',
			'label' => 'Entry Submission Date',
		];

		return [
			'options' => $result,
			'hasMore' => false,
		];

	}

	/**
	 * Prepare fluentcrm lists.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_lists( $data ) {

		$list_api  = FluentCrmApi( 'lists' );
		$all_lists = $list_api->all();
		$options   = [];

		if ( ! empty( $all_lists ) ) {
			foreach ( $all_lists as $list ) {
				$options[] = [
					'label' => $list->title,
					'value' => $list->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_contact_status( $data ) {

		$options = [
			[
				'label' => __( 'Subscribed', 'suretriggers' ),
				'value' => 'subscribed',
			],
			[
				'label' => __( 'Pending', 'suretriggers' ),
				'value' => 'pending',
			],
			[
				'label' => __( 'Unsubscribed', 'suretriggers' ),
				'value' => 'unsubscribed',
			],
			[
				'label' => __( 'Bounced', 'suretriggers' ),
				'value' => 'bounced',
			],
			[
				'label' => __( 'Complained', 'suretriggers' ),
				'value' => 'complained',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_fetch_custom_fields( $data ) {

		$options = [
			[
				'label' => __( 'Yes', 'suretriggers' ),
				'value' => 'true',
			],
			[
				'label' => __( 'No', 'suretriggers' ),
				'value' => 'false',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare fluentcrm tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_fluentcrm_tags( $data ) {

		$tag_api  = FluentCrmApi( 'tags' );
		$all_tags = $tag_api->all();
		$options  = [];

		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag->title,
					'value' => $tag->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Contact tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_contact_tags( $data ) {

		if ( ! function_exists( 'zeroBSCRM_getCustomerTags' ) ) {
			return [];
		}

		$all_tags = zeroBSCRM_getCustomerTags();
		$options  = [];

		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag['name'],
					'value' => $tag['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Company tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_company_tags( $data ) {

		if ( ! defined( 'ZBS_TYPE_COMPANY' ) ) {
			return [];
		}

		global $wpdb;
		$all_tags = $wpdb->get_results( $wpdb->prepare( "SELECT `ID`,`zbstag_name` FROM `{$wpdb->prefix}zbs_tags` WHERE zbstag_objtype = %d", ZBS_TYPE_COMPANY ) );

		$options = [];
		if ( ! empty( $all_tags ) ) {
			foreach ( $all_tags as $tag ) {
				$options[] = [
					'label' => $tag->zbstag_name,
					'value' => $tag->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM Companies list.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_companies_list( $data ) {

		if ( ! function_exists( 'zeroBS_getCompanies' ) ) {
			return [];
		}

		$all_companies = zeroBS_getCompanies();
		$options       = [];

		if ( ! empty( $all_companies ) ) {
			foreach ( $all_companies as $company ) {
				$options[] = [
					'label' => $company['name'],
					'value' => $company['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare JetpackCRM contact status.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_jetpack_crm_contact_statuses( $data ) {

		$options = [
			[
				'label' => __( 'Lead', 'suretriggers' ),
				'value' => 'Lead',
			],
			[
				'label' => __( 'Customer', 'suretriggers' ),
				'value' => 'Customer',
			],
			[
				'label' => __( 'Refused', 'suretriggers' ),
				'value' => 'Refused',
			],
			[
				'label' => __( 'Blacklisted', 'suretriggers' ),
				'value' => 'Blacklisted',
			],
			[
				'label' => __( 'Cancelled by Customer', 'suretriggers' ),
				'value' => 'Cancelled by Customer',
			],
			[
				'label' => __( 'Cancelled by Us (Pre-Quote)', 'suretriggers' ),
				'value' => 'Cancelled by Us (Pre-Quote)',
			],
			[
				'label' => __( 'Cancelled by Us (Post-Quote)', 'suretriggers' ),
				'value' => 'Cancelled by Us (Post-Quote)',
			],
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare FunnelKit Automations' lists.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_funnel_kit_automations_lists( $data ) {

		if ( ! class_exists( 'BWFCRM_Lists' ) ) {
			return [];
		}

		$bwfcrm_lists = \BWFCRM_Lists::get_lists();

		$options = [];

		foreach ( $bwfcrm_lists as $list ) {
			$options[] = [
				'label' => $list['name'],
				'value' => $list['ID'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare FunnelKit Automations' tags.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array
	 */
	public function search_funnel_kit_automations_tags( $data ) {

		if ( ! class_exists( 'BWFCRM_Tag' ) ) {
			return [];
		}

		$bwfcrm_tags = \BWFCRM_Tag::get_tags();

		$options = [];

		foreach ( $bwfcrm_tags as $tag ) {
			$options[] = [
				'label' => $tag['name'],
				'value' => $tag['ID'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Wishlist Memberlists level.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_wishlistmember_lists( $data ) {

		$wlm_levels = wlmapi_get_levels();
		$options    = [];

		if ( ! empty( $wlm_levels ) ) {
			foreach ( $wlm_levels['levels']['level'] as $list ) {
				$options[] = [
					'label' => $list['name'],
					'value' => (string) $list['id'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor popups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_elementor_popups( $data ) {

		$posts = get_posts(
			[
				'post_type'   => 'elementor_library',
				'orderby'     => 'title',
				'order'       => 'ASC',
				'post_status' => 'publish',
				'meta_query'  => [
					[
						'key'     => '_elementor_template_type',
						'value'   => 'popup',
						'compare' => '=',
					],
				],
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare givewp forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_givewp_forms( $data ) {

		$posts = get_posts(
			[
				'post_type'   => 'give_forms',
				'orderby'     => 'title',
				'order'       => 'ASC',
				'post_status' => 'publish',
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss group users.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_bb_group_users( $data ) {
		$options = [];

		$group_id = $data['dynamic'];
		$admins   = groups_get_group_admins( $group_id );

		if ( ! empty( $admins ) ) {
			foreach ( $admins as $admin ) {
				$admin_user = get_user_by( 'id', $admin->user_id );
				$options[]  = [
					'label' => $admin_user->display_name,
					'value' => $admin_user->ID,
				];
			}
		}

		$members = groups_get_group_members( [ 'group_id' => $group_id ] );

		if ( isset( $members['members'] ) && ! empty( $members['members'] ) ) {
			foreach ( $members['members'] as $member ) {
				$options[] = [
					'label' => $member->display_name,
					'value' => $member->ID,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss groups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_buddyboss_groups( $data ) {
		global $wpdb;

		$options = [];
		$groups  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups" );
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss public groups.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_buddyboss_public_groups( $data ) {
		$options = [];
		$groups  = groups_get_groups();
		if ( isset( $groups['groups'] ) && ! empty( $groups['groups'] ) ) {
			foreach ( $groups['groups'] as $group ) {
				if ( 'public' === $group->status ) {
					$options[] = [
						'label' => $group->name,
						'value' => $group->id,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare buddyboss profile types list.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_bb_profile_type_list( $data ) {
		$options = [];

		if ( function_exists( 'bp_get_active_member_types' ) ) {
			$types = bp_get_active_member_types(
				[
					'fields' => '*',
				]
			);
			if ( $types ) {
				foreach ( $types as $type ) {
					$options[] = [
						'label' => $type->post_title,
						'value' => $type->ID,
					];
				}
			}
		}

		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_elementor_forms( $data ) {

		$elementor_forms = Utilities::get_elementor_forms();

		$options = [];
		if ( ! empty( $elementor_forms ) ) {
			foreach ( $elementor_forms as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => $key,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor forms.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_new_elementor_forms( $data ) {

		global $wpdb;
		$elementor_forms = [];
		$post_metas      = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT pm.post_id, pm.meta_value
		FROM $wpdb->postmeta pm
			LEFT JOIN $wpdb->posts p
				ON p.ID = pm.post_id
		WHERE p.post_type IS NOT NULL
		AND p.post_status = %s
		AND pm.meta_key = %s
		AND pm.`meta_value` LIKE %s",
				'publish',
				'_elementor_data',
				'%%form_fields%%'
			)
		);

		if ( ! empty( $post_metas ) ) {
			foreach ( $post_metas as $post_meta ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$inner_forms = Utilities::search_elementor_forms( json_decode( $post_meta->meta_value ) );
				if ( ! empty( $inner_forms ) ) {
					foreach ( $inner_forms as $form ) {
						/**
						 *
						 * Ignore line
						 *
						 * @phpstan-ignore-next-line
						 */
						$elementor_forms[ $post_meta->post_id . '_' . $form->id ] = $form->settings->form_name . ' (' . $form->id . ')';
					}
				}
			}
		}

		$options = [];
		if ( ! empty( $elementor_forms ) ) {
			foreach ( $elementor_forms as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => $key,
				];
			}
		}

		/**
		 *
		 * Ignore line
		 *
		 * @phpstan-ignore-next-line
		 */
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare elementor form fields.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_pluggable_elementor_form_fields( $data ) {
		$result                = [];
		$form_id               = absint( $data['dynamic'] );
		$elementor_form_fields = ( new Utilities() )->get_elementor_form_fields( $data );
		$options               = [];
		if ( ! empty( $elementor_form_fields ) ) {
			foreach ( $elementor_form_fields as $key => $value ) {
				$options[] = [
					'label' => $value,
					'value' => '{' . $key . '}',
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get all events
	 *
	 * @param array $data Data array.
	 *
	 * @return array
	 */
	public function search_event_calendar_event( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'tribe_events',
				'orderby'        => 'title',
				'order'          => 'ASC',
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		$count = count( $options );

		return [
			'options' => $options,
			'hasMore' => $count > $limit && $count > $offset,
		];
	}

	/**
	 * Prepare rsvp event calendar events.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_event_calendar_rsvp_event( $data ) {

		$posts = get_posts(
			[
				'post_type'   => 'tribe_events',
				'orderby'     => 'title',
				'order'       => 'ASC',
				'post_status' => 'publish',
			]
		);

		$options = [];
		if ( ! empty( $posts ) ) {
			$ticket_handler = new Tribe__Tickets__Tickets_Handler();
			foreach ( $posts as $post ) {

				$get_rsvp_ticket = $ticket_handler->get_event_rsvp_tickets( $post->ID );

				if ( ! empty( $get_rsvp_ticket ) ) {
					$options[] = [
						'label' => $post->post_title,
						'value' => $post->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Restrict Content Membership Level.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_restrictcontent_membership_level( $data ) {

		$rcp_memberships = rcp_get_membership_levels();
		$options         = [];

		if ( ! empty( $rcp_memberships ) ) {
			foreach ( $rcp_memberships as $list ) {
				$options[] = [
					'label' => ucfirst( $list->name ),
					'value' => $list->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Restrict Content Customer.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_restrictcontent_customer( $data ) {

		$rcp_users = rcp_get_memberships();
		$options   = [];

		if ( ! empty( $rcp_users ) ) {
			foreach ( $rcp_users as $list ) {
				$user       = get_user_by( 'ID', $list->get_user_id() );
				$user_label = $user->user_email;

				if ( $user->display_name !== $user->user_email ) {
					$user_label .= ' (' . $user->display_name . ')';
				}

				$options[] = [
					'label' => $user_label,
					'value' => $list->get_customer_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}


	/**
	 * Fetch the Presto Player video List.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_ap_presto_player_video_list( $data ) {

		$videos  = ( new Video() )->all();
		$options = [];
		if ( ! empty( $videos ) ) {
			foreach ( $videos as $video ) {
				$options[] = [
					'label' => $video->__get( 'title' ),
					'value' => (string) $video->__get( 'id' ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Presto Player Video percentage.
	 *
	 * @param array $data Search Params.
	 *
	 * @return array[]
	 */
	public function search_prestoplayer_video_percent( $data ) {

		$percents = [ 10, 20, 30, 40, 50, 60, 70, 80, 90, 100 ];
		$options  = [];

		foreach ( $percents as $percent ) {
			$options[] = [
				'label' => $percent . '%',
				'value' => (string) $percent,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get user profile field options.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_user_field_options() {

		$options = apply_filters(
			'sure_trigger_get_user_field_options',
			[
				[
					'label' => __( 'User Name', 'suretriggers' ),
					'value' => 'user_login',
				],
				[
					'label' => __( 'User Email', 'suretriggers' ),
					'value' => 'user_email',
				],
				[
					'label' => __( 'Display Name', 'suretriggers' ),
					'value' => 'display_name',
				],
				[
					'label' => __( 'User Password', 'suretriggers' ),
					'value' => 'user_pass',
				],
				[
					'label' => __( 'Website', 'suretriggers' ),
					'value' => 'user_url',
				],
			]
		);

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get user post field options.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function search_post_field_options() {

		return [
			'options' => [
				[
					'label'         => __( 'Type', 'suretriggers' ),
					'value'         => 'post_type',
					'dynamic_field' => [
						'type'           => 'select-creatable',
						'ajaxIdentifier' => 'post_types',
					],
				],
				[
					'label'         => __( 'Status', 'suretriggers' ),
					'value'         => 'post_status',
					'dynamic_field' => [
						'type'           => 'select-async',
						'ajaxIdentifier' => 'post_statuses',
					],
				],
				[
					'label'         => __( 'Author', 'suretriggers' ),
					'value'         => 'post_author',
					'dynamic_field' => [
						'type'           => 'select-async',
						'ajaxIdentifier' => 'user',
					],
				],
				[
					'label'         => __( 'Title', 'suretriggers' ),
					'value'         => 'post_title',
					'dynamic_field' => [
						'type' => 'select-creatable',
					],
				],
				[
					'label'         => __( 'Slug', 'suretriggers' ),
					'value'         => 'post_slug',
					'dynamic_field' => [
						'type' => 'select-creatable',
					],
				],
				[
					'label'         => __( 'Content', 'suretriggers' ),
					'value'         => 'post_content',
					'dynamic_field' => [
						'type' => 'html-editor',
					],
				],
			],
			'hasMore' => false,
		];
	}

	/**
	 * Bricksbuilder grouped data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_bb_groups( $data ) {

		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'bp_groups' ) ) ) {

			$results = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM %s', $wpdb->prefix . 'bp_groups' ) );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->name,
						'value' => $result->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search forms.
	 *
	 * @return array
	 */
	public function search_bb_forums() {
		$options        = [];
		$allowed_atatus = [ 'publish', 'private' ];
		$forum_args     = [
			'post_type'      => bbp_get_forum_post_type(),
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'any',
		];
		$forums         = get_posts( $forum_args );

		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum ) {
				if ( in_array( $forum->post_status, $allowed_atatus, true ) ) {
					$options[] = [
						'label' => $forum->post_title,
						'value' => $forum->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_affiliate_wp_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$affiliate_data = [
			'affiliate_id'    => 1,
			'rest_id'         => '',
			'user_id'         => 1,
			'rate'            => '',
			'rate_type'       => '',
			'flat_rate_basis' => '',
			'payment_email'   => 'admin@bsf.io',
			'status'          => 'active',
			'earnings'        => 0,
			'unpaid_earnings' => 0,
			'referrals'       => 0,
			'visits'          => 0,
			'date_registered' => '2023-01-18 13:35:22',
			'dynamic_coupon'  => 'KDJSKS',
		];

		$referral_data = [
			'referral_id'  => 1,
			'affiliate_id' => 1,
			'visit_id'     => 0,
			'rest_id'      => '',
			'customer_id'  => 0,
			'parent_id'    => 0,
			'description'  => 'Testing',
			'status'       => 'unpaid',
			'amount'       => '12.00',
			'currency'     => '',
			'custom'       => 'custom',
			'context'      => '',
			'campaign'     => '',
			'reference'    => 'Reference',
			'products'     => '',
			'date'         => '2023-01-18 16:36:59',
			'type'         => 'opt-in',
			'payout_id'    => 0,
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( in_array( $term, [ 'affiliate_approved', 'affiliate_awaiting_approval' ], true ) ) {
			$status    = 'affiliate_approved' === $term ? 'active' : 'pending';
			$affiliate = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_affiliates WHERE affiliate_id = ( SELECT max(affiliate_id) FROM {$wpdb->prefix}affiliate_wp_affiliates )" );

			if ( ! empty( $affiliate ) ) {
				$affiliate                = affwp_get_affiliate( $affiliate->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$context['response_type'] = 'live';
			}
			$affiliate_data['status']  = $status;
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data );

		} else {
			$referral = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}affiliate_wp_referrals WHERE referral_id = ( SELECT max(referral_id) FROM {$wpdb->prefix}affiliate_wp_referrals )" );

			if ( ! empty( $referral ) ) {
				$referral                 = affwp_get_referral( $referral->referral_id );
				$affiliate                = affwp_get_affiliate( $referral->affiliate_id );
				$affiliate_data           = get_object_vars( $affiliate );
				$user_data                = WordPress::get_user_context( $affiliate->user_id );
				$referral_data            = get_object_vars( $referral );
				$context['response_type'] = 'live';
			}
			$context['pluggable_data'] = array_merge( $user_data, $affiliate_data, $referral_data );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_jetpack_crm_triggers_last_data( $data ) {
		
		if ( ! function_exists( 'zeroBS_getCompanyCount' ) || ! function_exists( 'zeroBS_getCustomerCount' ) || ! function_exists( 'zeroBS_getQuoteCount' ) ) {
			return [];
		}

		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$company_id     = [ 'company_id' => 1 ];
		$contact_id     = [ 'contact_id' => 1 ];
		$quote_id       = [ 'quote_id' => 1 ];
		$event_id       = [ 'event_id' => 1 ];
		$invoice_id     = [ 'invoice_id' => 1 ];
		$transaction_id = [ 'transaction_id' => 1 ];

		$company = [
			'company_id'                 => 1,
			'company_status'             => 'Lead',
			'company_name'               => 'Example Company',
			'company_email'              => 'info@example.com',
			'main_address_line_1'        => '123 Main Street',
			'main_address_line_2'        => 'Suite 456',
			'main_address_city'          => 'New York',
			'main_address_state'         => 'NY',
			'main_address_postal_code'   => '10001',
			'main_address_country'       => 'United States',
			'second_address_line_1'      => '789 Second Avenue',
			'second_address_line_2'      => 'Floor 10',
			'second_address_city'        => 'Los Angeles',
			'second_address_state'       => 'CA',
			'second_address_postal_code' => '90001',
			'second_address_country'     => 'United States',
			'main_telephone'             => '+1 123-456-7890',
			'secondary_telephone'        => '+1 987-654-3210',
		];

		$contact = [
			'contact_id'                 => 1,
			'status'                     => 'Lead',
			'prefix'                     => 'Mr.',
			'full_name'                  => 'John Doe',
			'first_name'                 => 'John',
			'last_name'                  => 'Doe',
			'email'                      => 'johndoe@example.com',
			'main_address_line_1'        => '123 Main Street',
			'main_address_line_2'        => 'Suite 456',
			'main_address_city'          => 'New York',
			'main_address_state'         => 'NY',
			'main_address_postal_code'   => '10001',
			'main_address_country'       => 'United States',
			'second_address_line_1'      => '789 Second Avenue',
			'second_address_line_2'      => 'Floor 10',
			'second_address_city'        => 'Los Angeles',
			'second_address_state'       => 'CA',
			'second_address_postal_code' => '90001',
			'second_address_country'     => 'United States',
			'home_telephone'             => '+1 123-456-7890',
			'work_telephone'             => '+1 987-654-3210',
			'mobile_telephone'           => '+1 555-555-5555',
		];

		$quote = [
			'quote_id'      => 1,
			'contact_id'    => 2,
			'contact_email' => 'john@example.com',
			'contact_name'  => 'John Doe',
			'status'        => '<strong>Created, not yet accepted</strong>',
			'title'         => 'Sample Quote',
			'value'         => 1000,
			'date'          => '2023-05-23',
			'content'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
			'notes'         => 'Additional notes about the quote.',
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		switch ( $term ) {
			case 'company_created':
				if ( zeroBS_getCompanyCount() > 0 ) {
					$company_data             = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_companies WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_companies )" );
					$company                  = JetpackCRM::get_company_context( $company_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $company;
				break;
			case 'company_deleted':
				$context['pluggable_data'] = $company_id;
				break;
			case 'contact_created':
				if ( zeroBS_getCustomerCount() > 0 ) {
					$contact_data             = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_contacts WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_contacts )" );
					$contact                  = JetpackCRM::get_contact_context( $contact_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $contact;
				break;
			case 'contact_deleted':
				$context['pluggable_data'] = $contact_id;
				break;
			case 'event_deleted':
				$context['pluggable_data'] = $event_id;
				break;
			case 'invoice_deleted':
				$context['pluggable_data'] = $invoice_id;
				break;
			case 'quote_accepted':
			case 'quote_created':
				if ( zeroBS_getQuoteCount() > 0 ) {
					$quote_data               = $wpdb->get_row( "SELECT ID FROM {$wpdb->prefix}zbs_quotes WHERE ID = ( SELECT max(ID) FROM {$wpdb->prefix}zbs_quotes )" );
					$quote                    = JetpackCRM::get_quote_context( $quote_data->ID );
					$context['response_type'] = 'live';
				}
				$context['pluggable_data'] = $quote;
				break;
			case 'quote_deleted':
				$context['pluggable_data'] = $quote_id;
				break;
			case 'transaction_deleted':
				$context['pluggable_data'] = $transaction_id;
				break;
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_funnel_kit_automations_triggers_last_data( $data ) {

		if ( ! class_exists( 'BWFCRM_Contact' ) || ! class_exists( 'BWFCRM_Lists' ) || ! class_exists( 'BWFCRM_Tag' ) ) {
			return [];
		}

		$context                  = [];
		$context['response_type'] = 'sample';

		$contact = [
			'contact_id'    => '1',
			'wpid'          => '0',
			'uid'           => '9e74246335fd81b1c4a9123842c12549',
			'email'         => 'johndoe@example.com',
			'first_name'    => 'John',
			'last_name'     => 'Doe',
			'contact_no'    => '+1 555-555-5555',
			'state'         => 'NY',
			'country'       => 'United States',
			'timezone'      => 'New York',
			'creation_date' => '2023-05-29 15:26:03',
			'last_modified' => '2023-05-29 17:08:30',
			'source'        => '',
			'type'          => 'Los Angeles',
			'status'        => '0',
			'tags'          => '["1"]',
			'lists'         => '["2","1"]',
		];

		$list = [
			'list_id'   => 1,
			'list_name' => 'Sample List',
		];

		$tag = [
			'tag_id'   => 1,
			'tag_name' => 'Sample Tag',
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		$recent_contacts = \BWFCRM_Contact::get_recent_contacts( 0, 1, [] );
		$contact_email   = count( $recent_contacts['contacts'] ) > 0 && isset( $recent_contacts['contacts'][0]['email'] ) ? $recent_contacts['contacts'][0]['email'] : '';

		$real_contact = false;
		if ( ! empty( $contact_email ) ) {
			$contact_obj = \BWFCRM_Contact::get_contacts( $contact_email, 0, 1, [], [], OBJECT );

			if ( isset( $contact_obj['contacts'][0] ) ) {
				$contact      = FunnelKitAutomations::get_contact_context( $contact_obj['contacts'][0]->contact );
				$real_contact = true;
			}
		}

		if ( 'contact_added_to_list' === $term || 'contact_removed_from_list' === $term ) {
			$list_id = (int) ( isset( $data['filter']['list_id']['value'] ) ? $data['filter']['list_id']['value'] : '-1' );

			if ( -1 === $list_id ) {
				$lists   = \BWFCRM_Lists::get_lists( [], '', 0, 1 );
				$list_id = count( $lists ) > 0 ? $lists[0]['ID'] : -1;
			}


			if ( -1 !== $list_id ) {
				$list                     = FunnelKitAutomations::get_list_context( $list_id );
				$context['response_type'] = $real_contact ? 'live' : 'sample';
			}

			$context['pluggable_data'] = array_merge( $list, $contact );
		} else {
			$tag_id = (int) ( isset( $data['filter']['tag_id']['value'] ) ? $data['filter']['tag_id']['value'] : '-1' );

			if ( -1 === $tag_id ) {
				$tags   = \BWFCRM_Tag::get_tags( [], '', 0, 1 );
				$tag_id = count( $tags ) > 0 ? $tags[0]['ID'] : -1;
			}


			if ( -1 !== $tag_id ) {
				$tag                      = FunnelKitAutomations::get_tag_context( $tag_id );
				$context['response_type'] = $real_contact ? 'live' : 'sample';
			}

			$context['pluggable_data'] = array_merge( $tag, $contact );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_edd_triggers_last_data( $data ) {
		global $wpdb;
		$context                  = [];
		$context['response_type'] = 'sample';

		$order_data = [
			'order_id'            => '1',
			'customer_email'      => 'john_doe@bsf.io',
			'customer_first_name' => 'John',
			'customer_last_name'  => 'Doe',
			'ordered_items'       => 'Any Sample Book',
			'currency'            => 'USD',
			'status'              => 'complete',
			'discount_codes'      => 'KDJSKS',
			'order_discounts'     => '2.00',
			'order_subtotal'      => '48.00',
			'order_tax'           => '0.00',
			'order_total'         => '48.00',
			'payment_method'      => 'stripe',
		];

		$term        = isset( $data['search_term'] ) ? $data['search_term'] : '';
		$download_id = (int) ( isset( $data['filter']['download_id']['value'] ) ? $data['filter']['download_id]']['value'] : '-1' );
		if ( 'order_created' === $term ) {
			$order_data['purchase_key'] = '06d3b7d923ca922dc889354f9bc33fbb';

			$args     = [
				'number' => 1,
				'status' => [ 'complete', 'refunded', 'partially_refunded', 'renewal' ],
			];
			$payments = edd_get_payments( $args );
			if ( count( $payments ) > 0 ) {
				$order_data               = EDD::get_product_purchase_context( $payments[0] );
				$context['response_type'] = 'live';
			}
		} elseif ( 'stripe_payment_refunded' === $term ) {
			$args     = [
				'number' => 1,
				'status' => 'complete',
				'type'   => 'refund',
			];
			$payments = edd_get_payments( $args );

			if ( count( $payments ) > 0 ) {
				$order_data               = EDD::get_purchase_refund_context( $payments[0] );
				$context['response_type'] = 'live';
			}
		} else {    
			$status = isset( $data['post_type'] ) ? $data['post_type'] : '';    
			if ( ! empty( $status ) ) {
				if ( $download_id > 0 ) {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE status= %s AND download_id=%d ORDER BY id DESC", $status, $download_id ) );
				} else {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE status= %s ORDER BY id DESC", $status ) );
				}           
			} else {
				if ( $download_id > 0 ) {
					$licesnses = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}edd_licenses WHERE download_id=%d ORDER BY id DESC", $download_id ) );
				} else {
					$licesnses = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}edd_licenses ORDER BY id DESC" );
				}           
			}
			if ( ! empty( $licesnses ) ) {
				$order_data               = EDD::edd_get_license_data( $licesnses->id, $licesnses->download_id, $licesnses->payment_id );
				$context['response_type'] = 'live';
			} else {
				$order_data = [
					'ID'               => 1,
					'key'              => '23232323232',
					'customer_email'   => 'suretest@example.com',
					'customer_name'    => 'Sure Test',
					'product_id'       => 1,
					'download_id'      => 1,
					'product_name'     => 'Test',
					'activation_limit' => 2,
					'activation_count' => 1,
					'activated_urls'   => 'https://example.com',
					'expiration'       => '1686297914',
					'is_lifetime'      => '0',
					'status'           => $status,
				];
			
			}
		}

		$context['pluggable_data'] = $order_data;
		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_presto_player_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$video_data = [
			'pp_video'            => '1',
			'pp_video_percentage' => '100',
			'video_id'            => '1',
			'video_title'         => 'SureTriggers Is Here 🎉 The Easiest Automation Builder WordPress Websites & Apps',
			'video_type'          => 'youtube',
			'video_external_id'   => '-cYbNYgylLs',
			'video_attachment_id' => '0',
			'video_post_id'       => '127',
			'video_src'           => 'https://www.youtube.com/watch?v=-cYbNYgylLs',
			'video_created_by'    => '1',
			'video_created_at'    => '2022-11-10 00:28:25',
			'video_updated_at'    => '2022-11-10 00:34:40',
			'video_deleted_at'    => '',
		];

		$videos = ( new Video() )->all();

		if ( count( $videos ) > 0 ) {
			$video_id                          = '-1' === $data['filter']['pp_video']['value'] ? $videos[0]->id : $data['filter']['pp_video']['value'];
			$video_data                        = ( new Video( $video_id ) )->toArray();
			$video_data['pp_video']            = $video_id;
			$video_data['pp_video_percentage'] = isset( $data['filter']['pp_video_percentage']['value'] ) ? $data['filter']['pp_video_percentage']['value'] : '100';
			$user_data                         = WordPress::get_user_context( $video_data['created_by'] );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $video_data );

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_member_press_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_id'                 => '190',
			'membership_title'              => 'Sample Membership',
			'amount'                        => '12.00',
			'total'                         => '12.00',
			'tax_amount'                    => '0.00',
			'tax_rate'                      => '0.00',
			'trans_num'                     => 't_63a03f3334f44',
			'status'                        => 'complete',
			'subscription_id'               => '0',
			'membership_url'                => site_url() . '/register/premium/',
			'membership_featured_image_id'  => '521',
			'membership_featured_image_url' => SURE_TRIGGERS_URL . 'assets/images/sample.svg',
		];

		$membership_id = (int) ( isset( $data['filter']['membership_id']['value'] ) ? $data['filter']['membership_id']['value'] : '-1' );

		if ( $membership_id > 0 ) {

			$subscription = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}mepr_transactions WHERE product_id= %s ORDER BY id DESC LIMIT 1", $membership_id ) );
		} else {
			$subscription = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}mepr_transactions ORDER BY id DESC LIMIT 1" );
		}

		if ( ! empty( $subscription ) ) {
			$membership_data = MemberPress::get_membership_context( $subscription );
			$user_data       = WordPress::get_user_context( $subscription->user_id );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wishlist_member_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_level_id'   => '1',
			'membership_level_name' => 'Sample Membership Level',
		];

		$membership_level_id = (int) ( isset( $data['filter']['membership_level_id']['value'] ) ? $data['filter']['membership_level_id']['value'] : '-1' );

		if ( $membership_level_id > 0 ) {
			$membership = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wlm_userlevels WHERE level_id= %s ORDER BY id DESC LIMIT 1", $membership_level_id ) );
		} else {
			$membership = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}wlm_userlevels ORDER BY id DESC LIMIT 1" );
		}
		if ( ! empty( $membership ) ) {
			$membership_data = WishlistMember::get_membership_detail_context( (int) $membership->level_id );
			$user_data       = WordPress::get_user_context( $membership->user_id );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_peepso_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$post_data = [
			'post_id'      => '1',
			'activity_id'  => '2',
			'post_author'  => '1',
			'post_content' => 'New sample post...!',
			'post_title'   => 'Sample Post',
			'post_excerpt' => 'sample',
			'post_status'  => 'publish',
			'post_type'    => 'peepso-post',
		];

		$post = $wpdb->get_row( "SELECT act_id, act_owner_id, act_external_id FROM {$wpdb->prefix}peepso_activities ORDER BY act_id DESC LIMIT 1" );

		if ( ! empty( $post ) ) {
			$post_data = PeepSo::get_pp_activity_context( (int) $post->act_external_id, (int) $post->act_id );
			$user_data = WordPress::get_user_context( $post->act_owner_id );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $post_data );

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_restrict_content_pro_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$user_data = WordPress::get_sample_user_context();

		$membership_data = [
			'membership_level_id'          => '190',
			'membership_level'             => 'Sample Membership',
			'membership_initial_payment'   => '0.00',
			'membership_recurring_payment' => '0.00',
			'membership_expiry_date'       => 'January 22, 2023',
		];

		$customer_id   = (int) ( isset( $data['filter']['membership_customer_id']['value'] ) ? $data['filter']['membership_customer_id']['value'] : '-1' );
		$membership_id = (int) ( isset( $data['filter']['membership_level_id']['value'] ) ? $data['filter']['membership_level_id']['value'] : '-1' );

		$args = [
			'status'  => 'expired',
			'number'  => 1,
			'orderby' => 'id',
		];

		if ( -1 !== $customer_id ) {
			$args['customer_id'] = $customer_id;
		}

		if ( -1 !== $membership_id ) {
			$args['object_id'] = $membership_id;
		}

		$memberships = rcp_get_memberships( $args );
		if ( count( $memberships ) > 0 ) {
			$membership_data = RestrictContent::get_rcp_membership_detail_context( $memberships[0] );
			$user_data       = WordPress::get_user_context( $memberships[0]->get_user_id() );

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = array_merge( $user_data, $membership_data );

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_events_calendar_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$event_data = [
			'event'     => [
				'ID'                    => 58,
				'post_author'           => 1,
				'post_date'             => '2023-01-19 09:27:58',
				'post_date_gmt'         => '2023-01-19 09:27:58',
				'post_content'          => '',
				'post_title'            => 'New event',
				'post_excerpt'          => '',
				'post_status'           => 'publish',
				'comment_status'        => 'open',
				'ping_status'           => 'closed',
				'post_password'         => '',
				'post_name'             => 'new-event',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2023-01-19 09:44:25',
				'post_modified_gmt'     => '2023-01-19 09:44:25',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'http://connector.com/?post_type=tribe_events&#038;p=58',
				'menu_order'            => -1,
				'post_type'             => 'tribe_events',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			],
			'attendies' => [
				'order_id'           => 68,
				'purchaser_name'     => 'sapna Rana',
				'purchaser_email'    => 'sapnar@bsf.io',
				'provider'           => 'Tribe__Tickets__RSVP',
				'provider_slug'      => 'rsvp',
				'purchase_time'      => '2023-01-19 09:48:43',
				'optout'             => 1,
				'ticket'             => 'Prime',
				'attendee_id'        => 68,
				'security'           => '2cefc3b53e',
				'product_id'         => 65,
				'check_in'           => '',
				'order_status'       => 'yes',
				'order_status_label' => 'Going',
				'user_id'            => 1,
				'ticket_sent'        => 1,
				'event_id'           => 58,
				'ticket_name'        => 'Prime',
				'holder_name'        => 'sapna Rana',
				'holder_email'       => 'sapnar@bsf.io',
				'ticket_id'          => 68,
				'qr_ticket_id'       => 68,
				'security_code'      => '2cefc3b53e',
				'attendee_meta'      => '',
				'is_subscribed'      => '',
				'is_purchaser'       => 1,
				'ticket_exists'      => 1,
			],
		];

		$event_id = (int) ( isset( $data['filter']['event_id']['value'] ) ? $data['filter']['event_id']['value'] : '-1' );

		$args = [
			'post_type'   => 'tribe_rsvp_attendees',
			'orderby'     => 'ID',
			'order'       => 'DESC',
			'post_status' => 'publish',
			'numberposts' => 1,
		];

		if ( -1 !== $event_id ) {
			$args['meta_query'] = [
				[
					'key'   => '_tribe_rsvp_event',
					'value' => $event_id,
				],
			];
		}

		$attendees = get_posts( $args );

		if ( count( $attendees ) > 0 ) {
			$attendee    = $attendees[0];
			$attendee_id = $attendee->ID;

			$product_id = get_post_meta( $attendee_id, '_tribe_rsvp_product', true );
			$order_id   = get_post_meta( $attendee_id, '_tribe_rsvp_order', true );

			$event_data               = TheEventCalendar::get_event_context( $product_id, $order_id );
			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = $event_data;

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_triggers_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];
		$user_data                 = WordPress::get_sample_user_context();

		$product_data['product'] = [
			'id'                => '169',
			'name'              => 'Sample Product',
			'description'       => 'This is description of sample product.',
			'short_description' => 'This is short description of sample product.',
			'image_url'         => SURE_TRIGGERS_URL . 'assets/images/sample.svg',
			'slug'              => 'sample-product',
			'status'            => 'publish',
			'type'              => 'simple',
			'price'             => '89',
			'featured'          => '0',
			'sku'               => 'hoodie-blue-sm',
			'regular_price'     => '90',
			'sale_price'        => '89',
			'total_sales'       => '21',
			'category'          => 'Uncategorized',
			'tags'              => 'sample, new, 2022',
		];

		$comment_data = [
			'comment_id'           => '1',
			'comment'              => 'This is a sample comment..!',
			'comment_author'       => 'testsure',
			'comment_date'         => '2023-06-23 10:10:40',
			'comment_author_email' => 'testsure@example.com',
		];

		$order_data = [
			'order_id'             => '500',
			'total_order_value'    => '45',
			'currency'             => 'USD',
			'shipping_total'       => '5',
			'order_payment_method' => 'cod',
			'billing_firstname'    => 'John',
			'billing_lastname'     => 'Doe',
			'billing_company'      => 'BSF',
			'billing_address_1'    => '1004 Beaumont',
			'billing_address_2'    => '',
			'billing_city'         => 'Casper',
			'billing_state'        => 'Wyoming',
			'billing_postcode'     => '82601',
			'billing_country'      => 'US',
			'billing_email'        => 'john_doe@bsf.io',
			'billing_phone'        => '(307) 7626541',
			'shipping_firstname'   => 'John',
			'shipping_lastname'    => 'Doe',
			'shipping_company'     => 'BSF',
			'shipping_address_1'   => '1004 Beaumont',
			'shipping_address_2'   => '',
			'shipping_city'        => 'Casper',
			'shipping_state'       => 'Wyoming',
			'shipping_postcode'    => '82601',
			'shipping_country'     => 'US',
			'coupon_codes'         => 'e3mstekq, f24sjakb',
			'total_items_in_order' => '1',
			'user_id'              => '1',
		];

		$variation_data = [
			'product_variation_id' => '626',
			'product_variation'    => 'Color: Silver',
		];

        $order_sample_data = json_decode( '{"id":37,"parent_id":0,"status":"processing","currency":"USD","version":"7.3.0","prices_include_tax":false,"date_created":{"date":"2023-01-18 08:00:49.000000","timezone_type":1,"timezone":"+00:00"},"date_modified":{"date":"2023-01-18 08:00:50.000000","timezone_type":1,"timezone":"+00:00"},"discount_total":"0","discount_tax":"0","shipping_total":"0","shipping_tax":"0","cart_tax":"0","total":"22.00","total_tax":"0","customer_id":1,"order_key":"wc_order_VdLfjJ9vP7pDs","billing":{"first_name":"John","last_name":"Rana","company":"","address_1":"test","address_2":"","city":"Mohali","state":"AL","postcode":"12344","country":"US","email":"test@example.com","phone":"13232323"},"shipping":{"first_name":"","last_name":"","company":"","address_1":"","address_2":"","city":"","state":"","postcode":"","country":"","phone":""},"payment_method":"cod","payment_method_title":"Cash on delivery","transaction_id":"","customer_ip_address":"::1","customer_user_agent":"Mozilla\/5.0 (X11; Linux x86_64) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/108.0.0.0 Safari\/537.36","created_via":"checkout","customer_note":"","date_completed":null,"date_paid":null,"cart_hash":"10b8e2799df0f88e1506edc0f3ed99c9","order_stock_reduced":true,"download_permissions_granted":true,"new_order_email_sent":true,"recorded_sales":true,"recorded_coupon_usage_counts":true,"number":"37","meta_data":[{"id":204,"key":"is_vat_exempt","value":"no"}],"line_items":{"id":"2, 3","order_id":"37, 37","name":"Variable product - Red, Test product","product_id":"34, 31","variation_id":"35, 0","quantity":"1, 1","tax_class":", ","subtotal":"12, 10","subtotal_tax":"0, 0","total":"12, 10","total_tax":"0, 0","taxes":", ","meta_data":", "},"tax_lines":[],"shipping_lines":[],"fee_lines":[],"coupon_lines":[],"products":[{"id":2,"order_id":37,"name":"Variable product - Red","product_id":34,"variation_id":35,"quantity":1,"tax_class":"","subtotal":"12","subtotal_tax":"0","total":"12","total_tax":"0","taxes":{"total":[],"subtotal":[]},"meta_data":{"19":{"key":"color","value":"Red","display_key":"Color","display_value":"<p>Red<\/p>\n"}}},{"id":3,"order_id":37,"name":"Test product","product_id":31,"variation_id":0,"quantity":1,"tax_class":"","subtotal":"10","subtotal_tax":"0","total":"10","total_tax":"0","taxes":{"total":[],"subtotal":[]},"meta_data":[]}],"quantity":"1, 1","wp_user_id":1,"user_login":"john","display_name":"john smith","user_firstname":"John","user_lastname":"Smith","user_email":"test@example.com","user_role":["subscriber"]}', true ); //phpcs:ignore

		$product_id = (int) ( isset( $data['filter']['product_id']['value'] ) ? $data['filter']['product_id']['value'] : -1 );
		$term       = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( in_array( $term, [ 'product_added_to_cart', 'product_viewed' ], true ) ) {
			if ( -1 === $product_id ) {
				$args     = [
					'post_type'   => 'product',
					'orderby'     => 'ID',
					'order'       => 'DESC',
					'post_status' => 'publish',
					'numberposts' => 1,
				];
				$products = get_posts( $args );

				if ( count( $products ) > 0 ) {
					$product_id = $products[0]->ID;
				}
			}

			if ( -1 !== $product_id ) {
				$post                       = get_post( $product_id );
				$user_data                  = WordPress::get_user_context( $post->post_author );
				$product_data['product_id'] = $product_id;
				$product_data['product']    = WooCommerce::get_product_context( $product_id );
				$terms                      = get_the_terms( $product_id, 'product_cat' );
				if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
					$cat_name = [];
					foreach ( $terms as $cat ) {
						$cat_name[] = $cat->name;
					}
					$product_data['product']['category'] = implode( ', ', $cat_name );
				}
				$terms_tags = get_the_terms( $product_id, 'product_tag' );
				if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
					$tag_name = [];
					foreach ( $terms_tags as $tag ) {
						$tag_name[] = $tag->name;
					}
					$product_data['product']['tag'] = implode( ', ', $tag_name );
				}
                unset( $product_data['product']['id'] ); //phpcs:ignore
				$context['response_type'] = 'live';
			}

			if ( 'product_added_to_cart' === $term ) {
				$product_data['product_quantity'] = 1;
			}

			$context['pluggable_data'] = array_merge( $product_data, $user_data );

		} elseif ( 'product_reviewed' === $term ) {
			$comment_args = [
				'number'  => 1,
				'type'    => 'review',
				'orderby' => 'comment_ID',
				'post_id' => -1 !== $product_id ? $product_id : 0,
			];

			$comments = get_comments( $comment_args );

			if ( count( $comments ) > 0 ) {
				$comment      = $comments[0];
				$comment_data = [
					'comment_id'           => $comment->comment_ID,
					'comment'              => $comment->comment_content,
					'comment_author'       => $comment->comment_author,
					'comment_date'         => $comment->comment_date,
					'comment_author_email' => $comment->comment_author_email,
				];
				$product_data = WooCommerce::get_product_context( $comment->comment_post_ID );
				if ( is_object( $comment ) ) {
					$terms = get_the_terms( (int) $comment->comment_post_ID, 'product_cat' );
					if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
						$cat_name = [];
						foreach ( $terms as $cat ) {
							$cat_name[] = $cat->name;
						}
						$product_data['product']['category'] = implode( ', ', $cat_name );
					}
					$terms_tags = get_the_terms( (int) $comment->comment_post_ID, 'product_tag' );
					if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
						$tag_name = [];
						foreach ( $terms_tags as $tag ) {
							$tag_name[] = $tag->name;
						}
						$product_data['product']['tag'] = implode( ', ', $tag_name );
					}
				}
				$user_data                = WordPress::get_user_context( $comment->user_id );
				$context['response_type'] = 'live';
			}

			$context['pluggable_data'] = array_merge( $product_data, $user_data, $comment_data );

		} elseif ( 'product_purchased' === $term ) {
			$order_id                 = 0;
			$product_data['quantity'] = '1';
			if ( -1 !== $product_id ) {
				$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );
				if ( count( $order_ids ) > 0 ) {
					$order_id = $order_ids[0];
				}
			} else {
				$orders = wc_get_orders( [ 'numberposts' => 1 ] );
				if ( count( $orders ) > 0 ) {
					$order_id = $orders[0]->get_id();
				}
			}

			if ( 0 !== $order_id ) {
				$order = wc_get_order( $order_id );

				if ( $order ) {
					$user_id = $order->get_customer_id();
					$items   = $order->get_items();

					$product_ids = [];

					$iteration = 0;
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$item_id = $item->get_product_id();
							if ( -1 === $product_id && 0 === $iteration ) {
								$product_ids[] = $item_id;
								break;
							} elseif ( $item_id === $product_id ) {
								$product_ids[] = $item_id;
								break;
							}
						}

						$iteration++;
					}
					$order_data                         = WooCommerce::get_order_context( $order_id );
					$user_data                          = WordPress::get_user_context( $user_id );
					$order_data['total_items_in_order'] = count( $product_ids );
					$product_data                       = [];
					foreach ( $product_ids as $key => $product_id ) {
						$product_data[ 'product' . $key ] = WooCommerce::get_product_context( $product_id );
						$terms                            = get_the_terms( $product_id, 'product_cat' );
						if ( ! empty( $terms ) && is_array( $terms ) && isset( $terms[0] ) ) {
							$cat_name = [];
							foreach ( $terms as $cat ) {
								$cat_name[] = $cat->name;
							}
							$product_data[ 'product' . $key ]['category'] = implode( ', ', $cat_name );
						}
						$terms_tags = get_the_terms( $product_id, 'product_tag' );
						if ( ! empty( $terms_tags ) && is_array( $terms_tags ) && isset( $terms_tags[0] ) ) {
							$tag_name = [];
							foreach ( $terms_tags as $tag ) {
								$tag_name[] = $tag->name;
							}
							$product_data[ 'product' . $key ]['tag'] = implode( ', ', $tag_name );
						}
						$product = wc_get_product( $product_id );
						/**
						 *
						 * Ignore line
						 *
						 * @phpstan-ignore-next-line
						 */
						if ( $product->is_downloadable() ) {
							/**
							 *
							 * Ignore line
							 *
							 * @phpstan-ignore-next-line
							 */
							foreach ( $product->get_downloads() as $key_download_id => $download ) {
								$download_name                                = $download->get_name();
								$download_link                                = $download->get_file();
								$download_id                                  = $download->get_id();
								$download_type                                = $download->get_file_type();
								$download_ext                                 = $download->get_file_extension();
								$product_data[ 'product' . $key ]['download'] = [
									'download_name' => $download_name,
									'download_link' => $download_link,
									'download_id'   => $download_id,
									'download_type' => $download_type,
									'download_ext'  => $download_ext,
								];
							}
						}                       
					}
					$context['response_type'] = 'live';
				}
			}

			$context['pluggable_data'] = array_merge( $order_data, $product_data, $user_data );

		} elseif ( 'variable_product_purchased' === $term ) {
			$product_variation_id = (int) ( isset( $data['filter']['product_variation_id']['value'] ) ? $data['filter']['product_variation_id']['value'] : -1 );
			$order_ids            = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			foreach ( $order_ids as $order_id ) {
				$order = wc_get_order( $order_id );

				if ( $order ) {
					$user_id            = $order->get_customer_id();
					$items              = $order->get_items();
					$product_variations = [];

					$iteration = 0;
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_variation_id' ) ) {
							$variation_id = $item->get_variation_id();  
							if ( -1 === $product_variation_id && 0 === $iteration ) {
								$product_variations[] = $variation_id;
								break;
							} elseif ( $variation_id === $product_variation_id ) {
								$product_variations[] = $variation_id;
								break;
							}
						}

						$iteration++;
					}

					if ( count( $product_variations ) > 0 ) {
						$product_data   = WooCommerce::get_product_context( $product_variation_id );
						$order_data     = WooCommerce::get_order_context( $order_id );
						$user_data      = WordPress::get_user_context( $user_id );
						$variation_data = [
							'product_variation_id' => $product_variations[0],
							'product_variation'    => get_the_excerpt( $product_variations[0] ),
						];

						$context['response_type'] = 'live';
						break;
					}
				}
			}

			$context['pluggable_data'] = array_merge( $order_data, $user_data, $variation_data );

		} elseif ( 'variable_subscription_purchased' === $term ) {
			$product_data['quantity']       = '1';
			$product_data['product_name']   = 'Sample Product';
			$product_data['billing_period'] = '2021-2022';

			$context['pluggable_data'] = array_merge( $order_data, $product_data, $user_data );

			$subscription_order_id = 0;
			$order_ids             = [];

			if ( -1 !== $product_id ) {
				$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			} else {
				$orders = wc_get_orders( [] );
				if ( count( $orders ) > 0 ) {
					$order_ids[] = $orders[0]->get_id();
				}
			}

			foreach ( $order_ids as $order_id ) {
				$query_args          = [
					'post_type'      => 'shop_subscription',
					'orderby'        => 'ID',
					'order'          => 'DESC',
					'post_status'    => 'wc-active',
					'posts_per_page' => 1,
					'post_parent'    => $order_id,
				];
				$query_result        = new WP_Query( $query_args );
				$subscription_orders = $query_result->get_posts();

				if ( count( $subscription_orders ) > 0 ) {
					$subscription_order_id = $subscription_orders[0]->ID;
					break;
				}
			}

			if ( 0 !== $subscription_order_id ) {
				$subscription = wcs_get_subscription( $subscription_order_id );
				if ( $subscription instanceof WC_Subscription ) {
					$last_order_id = $subscription->get_last_order();
					if ( ! empty( $last_order_id ) && $last_order_id === $subscription->get_parent_id() ) {
						$user_id = wc_get_order( $last_order_id )->get_customer_id();
						$items   = $subscription->get_items();

						foreach ( $items as $item ) {
							$product = $item->get_product();
							if ( class_exists( '\WC_Subscriptions_Product' ) && WC_Subscriptions_Product::is_subscription( $product ) ) {
								if ( $product->is_type( [ 'subscription', 'subscription_variation', 'variable-subscription' ] ) ) {

									$product_data = WooCommerce::get_variable_subscription_product_context( $item, $last_order_id );
									$user_data    = WordPress::get_user_context( $user_id );

									$context['response_type']  = 'live';
									$context['pluggable_data'] = array_merge( $product_data, $user_data );
								}
							}
						}
					}
				}
			}
		} elseif ( 'order_created' === $term ) {
			$orders   = wc_get_orders( [ 'numberposts' => 1 ] );
			$order_id = '';
			if ( count( $orders ) > 0 ) {
				$order_id                 = $orders[0]->get_id();
				$order                    = wc_get_order( $order_id );
				$user_id                  = $order->get_customer_id();
				$order_sample_data        = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $order_sample_data;

		}

		return $context;
	}

	/**
	 * Search LMS data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_lifter_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$meta_key  = '_is_complete';
		$trigger   = $data['search_term'];
		$context   = [];
		if ( 'lifterlms_purchase_course' === $trigger ) {
			$product_type = 'course';
			$post_id      = $data['filter']['course_id']['value'];
		} elseif ( 'lifterlms_purchase_membership' === $trigger ) {
			$product_type = 'membership';
			$post_id      = $data['filter']['membership_id']['value'];
		} elseif ( 'lifterlms_lesson_completed' === $trigger ) {
			$post_id = $data['filter']['lesson']['value'];
		} elseif ( 'lifterlms_course_completed' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
		}

		$where = 'postmeta.post_id = "' . $post_id . '" AND';

		if ( 'llms_order' === $post_type ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='llms_order' AND posts.post_status= 'llms-completed' AND postmeta.meta_value=%s AND postmeta.meta_key= '_llms_product_type'", $product_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='llms_order' AND posts.post_status= 'llms-completed' AND postmeta.meta_value=%s AND postmeta.meta_key= '_llms_product_id'", $post_id ) );
			}
		} else {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.meta_value='yes' AND postmeta.meta_key=%s AND posts.post_type=%s", $meta_key, $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}lifterlms_user_postmeta  as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.post_id WHERE postmeta.post_id = %s AND postmeta.meta_value='yes' AND postmeta.meta_key=%s AND posts.post_type=%s", $post_id, $meta_key, $post_type ) );
			}
		}

		$response = [];
		if ( ! empty( $result ) ) {
			$result_post_id = $result[0]->post_id;
			$result_user_id = $result[0]->user_id;

			switch ( $trigger ) {
				case 'lifterlms_lesson_completed':
					$context = array_merge(
						WordPress::get_user_context( $result_user_id ),
						LifterLMS::get_lms_lesson_context( $result_post_id )
					);

					$context['course'] = get_the_title( get_post_meta( $result_post_id, '_llms_parent_course', true ) );
					if ( '' !== ( get_post_meta( $result_post_id, '_llms_parent_section', true ) ) ) {
						$context['parent_section'] = get_the_title( get_post_meta( $result_post_id, '_llms_parent_section', true ) );
					}
					break;
				case 'lifterlms_course_completed':
					$context = array_merge(
						WordPress::get_user_context( $result_user_id ),
						LifterLMS::get_lms_course_context( $result_post_id )
					);
					break;
				case 'lifterlms_purchase_course':
					$user_id                      = get_post_meta( $result_post_id, '_llms_user_id', true );
					$context['course_id']         = get_post_meta( $result_post_id, '_llms_product_id', true );
					$context['course_name']       = get_post_meta( $result_post_id, '_llms_product_title', true );
					$context['course_amount']     = get_post_meta( $result_post_id, '_llms_original_total', true );
					$context['currency']          = get_post_meta( $result_post_id, '_llms_currency', true );
					$context ['order']            = WordPress::get_post_context( $result_post_id );
					$context['order_type']        = get_post_meta( $result_post_id, '_llms_order_type', true );
					$context['trial_offer']       = get_post_meta( $result_post_id, '_llms_trial_offer', true );
					$context['billing_frequency'] = get_post_meta( $result_post_id, '_llms_billing_frequency', true );
					$context                      = array_merge( $context, WordPress::get_user_context( $user_id ) );
					break;
				case 'lifterlms_purchase_membership':
					$user_id                      = get_post_meta( $result_post_id, '_llms_user_id', true );
					$context['membership_id']     = get_post_meta( $result_post_id, '_llms_product_id', true );
					$context['membership_name']   = get_post_meta( $result_post_id, '_llms_product_title', true );
					$context['membership_amount'] = get_post_meta( $result_post_id, '_llms_original_total', true );
					$context['currency']          = get_post_meta( $result_post_id, '_llms_currency', true );
					$context ['order']            = WordPress::get_post_context( $result_post_id );
					$context['order_type']        = get_post_meta( $result_post_id, '_llms_order_type', true );
					$context['trial_offer']       = get_post_meta( $result_post_id, '_llms_trial_offer', true );
					$context['billing_frequency'] = get_post_meta( $result_post_id, '_llms_billing_frequency', true );
					$context                      = array_merge( $context, WordPress::get_user_context( $user_id ) );
					break;
				default:
					return;

			}
			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';

		}

		return $response;

	}

	/**
	 * Search SM data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_suremember_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$meta_key  = '_is_complete';
		$trigger   = $data['search_term'];
		$post_id   = $data['filter']['group_id']['value'];

		if ( 'suremember_updated_group' === $trigger ) {
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts WHERE posts.post_type=%s", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}posts as posts WHERE posts.ID=%s AND posts.post_type=%s", $post_id, $post_type ) );
			}
		} else {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}usermeta as usermeta WHERE usermeta.meta_key = %s", 'suremembers_user_access_group_' . $post_id ) );
		}

		$response = [];

		if ( ! empty( $result ) ) {
			$context = [];
			switch ( $trigger ) {
				case 'suremember_updated_group':
					$group_id                                   = $result[0]->ID;
					$suremembers_post['rules']                  = get_post_meta( $group_id, 'suremembers_plan_include', true );
					$suremembers_post['exclude']                = get_post_meta( $group_id, 'suremembers_plan_exclude', true ); //phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
					$suremembers_post['suremembers_user_roles'] = get_post_meta( $group_id, 'suremembers_user_roles', true );
					$suremembers_post['title']                  = get_the_title( $group_id );
					$suremembers_post['restrict']               = get_post_meta( $group_id, 'suremembers_plan_rules', true )['restrict'];
					$context['group']                           = array_merge( WordPress::get_post_context( $group_id ), $suremembers_post );
					$context['group_id']                        = $group_id;
					unset( $context['group']['ID'] );
					$response['pluggable_data'] = $context;
					$response['response_type']  = 'live';
					break;
				case 'suremember_user_added_in_group':
					foreach ( $result as $res ) {
						$meta_value = unserialize( $res->meta_value );
						if ( 'active' === $meta_value['status'] ) {
							$context                    = WordPress::get_user_context( $res->user_id );
							$context['group']           = WordPress::get_post_context( $post_id );
							$response['pluggable_data'] = $context;
							$response['response_type']  = 'live';
						}
					}
					break;
				case 'suremember_user_removed_from_group':
					foreach ( $result as $res ) {
						$meta_value = unserialize( $res->meta_value );
						if ( 'revoked' === $meta_value['status'] ) {
							$context                    = WordPress::get_user_context( $res->user_id );
							$context['group']           = WordPress::get_post_context( $post_id );
							$response['pluggable_data'] = $context;
							$response['response_type']  = 'live';
						}
					}
					break;
				default:
					return;

			}
		}

		return $response;

	}

	/**
	 * Search CartFlows data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_cartflows_last_data( $data ) {
		global $wpdb;
		$trigger = $data['search_term'];
		$context = [];
		if ( 'cartflows_offer_accepted' === $trigger ) {
			$result = $wpdb->get_results( "SELECT * FROM  {$wpdb->prefix}posts as posts  JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='shop_order' AND postmeta.meta_value='upsell' AND postmeta.meta_key= '_cartflows_offer_type'" );
		}
		$response = [];
		if ( ! empty( $result ) ) {
			$context                    = [];
			$order_upsell_id            = $result[0]->post_id;
			$step_id                    = get_post_meta( $order_upsell_id, '_cartflows_offer_step_id', true );
			$order_id                   = get_post_meta( $order_upsell_id, '_cartflows_offer_parent_id', true );
			$order                      = wc_get_order( $order_id );
			$upsell_order               = wc_get_order( $order_upsell_id );
			$variation_id               = $upsell_order->get_items()[0]['product_id'];
			$input_qty                  = $upsell_order->get_items()[0]['quantity'];
			$offer_product              = wcf_pro()->utils->get_offer_data( $step_id, $variation_id, $input_qty, $order_id );
			$user_id                    = get_post_meta( $order_upsell_id, '_customer_user', true );
			$context                    = WordPress::get_user_context( $user_id );
			$context['order']           = $order->get_data();
			$context['upsell']          = $offer_product;
			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';
		}

		return $response;

	}


	/**
	 * Fetch user context.
	 *
	 * @param int $initiator_id initiator id.
	 * @param int $friend_id friend id.
	 * @return array
	 */
	public function get_user_context( $initiator_id, $friend_id ) {
		$context = WordPress::get_user_context( $initiator_id );

		$friend_context = WordPress::get_user_context( $friend_id );

		$avatar = get_avatar_url( $initiator_id );

		$context['avatar_url'] = ( $avatar ) ? $avatar : '';

		$context['friend_id']         = $friend_id;
		$context['friend_first_name'] = $friend_context['user_firstname'];
		$context['friend_last_name']  = $friend_context['user_lastname'];
		$context['friend_email']      = $friend_context['user_email'];

		$friend_avatar                = get_avatar_url( $friend_id );
		$context['friend_avatar_url'] = $friend_avatar;
		return $context;
	}

	/**
	 * Search BP data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_friendships( $data ) {
		global $wpdb, $bp;
		$context                  = [];
		$sample['pluggable_data'] = [
			'wp_user_id'        => 4,
			'user_login'        => 'katy1ßßßß',
			'display_name'      => 'Katy Smith',
			'user_firstname'    => 'Katy',
			'user_lastname'     => 'Smith',
			'user_email'        => 'katy1@gmail.com',
			'user_role'         => [ 'subscriber' ],
			'avatar_url'        => 'http://pqr.com/avatar',
			'friend_id'         => 1,
			'friend_first_name' => 'John',
			'friend_last_name'  => 'Wick',
			'friend_email'      => 'john@gmail.com',
			'friend_avatar_url' => 'http://abc.com/avatar',
		];
		$sample['response_type']  = 'sample';

		$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}bp_friends'" );
		if ( $table_exists ) {
			$friendships = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_friends LIMIT 1" );
			if ( ! empty( $friendships ) ) {
				$friendship                = $friendships[0];
				$initiator_id              = $friendship->initiator_user_id;
				$friend_user_id            = $friendship->friend_user_id;
				$context['pluggable_data'] = $this->get_user_context( $initiator_id, $friend_user_id );
				$context['response_type']  = 'live';
			} else {
				$context = $sample;
			}
		} else {
			$context = $sample;
		}
		
		
		return $context;
	}
	
	/**
	 * Search Buddyboss profile types data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_profile_types( $data ) {
		global $wpdb, $bp;
		$context                  = [];
		$sample['pluggable_data'] = [
			'wp_user_id'           => 4,
			'user_login'           => 'katy1ßßßß',
			'display_name'         => 'Katy Smith',
			'user_firstname'       => 'Katy',
			'user_lastname'        => 'Smith',
			'user_email'           => 'katy1@gmail.com',
			'user_role'            => [ 'subscriber' ],
			'bb_profile_type'      => '10',
			'bb_profile_type_name' => 'student',
		];
		$sample['response_type']  = 'sample';

		$post_id      = $data['filter']['bb_profile_type']['value'];
		$get_existing = get_post_meta( $post_id, '_bp_member_type_key', true );

		$type_term = get_term_by(
			'name',
			/**
			 *
			 * Ignore line
			 *
			 * @phpstan-ignore-next-line
			 */
			$get_existing,
			'bp_member_type'
		);

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT u.ID FROM {$wpdb->prefix}users u INNER JOIN {$wpdb->prefix}term_relationships r 
				ON u.ID = r.object_id WHERE u.user_status = 0 AND 
				r.term_taxonomy_id = %d ORDER BY RAND() LIMIT 1",
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$type_term->term_id 
			)
		);

		if ( ! empty( $results ) ) {
			$user                      = $results[0];
			$context['pluggable_data'] = WordPress::get_user_context( $user->ID );
			$context['pluggable_data']['bb_profile_type']      = $post_id;
			$context['pluggable_data']['bb_profile_type_name'] = get_post_meta( $post_id, '_bp_member_type_label_singular_name', true );
			$context['response_type']                          = 'live';
		} else {
			$context = $sample;
		}
		
		return $context;
	}
	/**
	 * Search BP data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_bp_groups( $data ) {
		global $wpdb, $bp;
		$context    = [];
		$group_data = [];
		$args       = [
			'orderby' => 'user_nicename',
			'order'   => 'ASC',
			'number'  => 1,
		];

		$users = get_users( $args );

		if ( isset( $data['filter']['group_id']['value'] ) ) {
			$group_id         = $data['filter']['group_id']['value'];
			$args['group_id'] = $group_id;
			if ( $group_id > 0 ) {
				$group                           = groups_get_group( $group_id );
				$group_data['group_id']          = ( property_exists( $group, 'id' ) ) ? (int) $group->id : '';
				$group_data['group_name']        = ( property_exists( $group, 'name' ) ) ? $group->name : '';
				$group_data['group_description'] = ( property_exists( $group, 'description' ) ) ? $group->description : '';
			} else {
				$table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}bp_groups'" );
				if ( $table_exists ) {
					$groups            = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups LIMIT 1" );
					$context['groups'] = $groups;
					if ( ! empty( $groups ) ) {
						foreach ( $groups as $group ) {
							$group_data['group_id']          = $group->id;
							$group_data['group_name']        = $group->name;
							$group_data['group_description'] = $group->description;
						}
					}
				}
			}
		}

		if ( ! empty( $users ) ) {
			$user           = $users[0];
			$pluggable_data = $group_data;

			$avatar                           = get_avatar_url( $user->ID );
			$pluggable_data['wp_user_id']     = $user->ID;
			$pluggable_data['avatar_url']     = ( $avatar ) ? $avatar : '';
			$pluggable_data['user_login']     = $user->user_login;
			$pluggable_data['display_name']   = $user->display_name;
			$pluggable_data['user_firstname'] = $user->user_firstname;
			$pluggable_data['user_lastname']  = $user->user_lastname;
			$pluggable_data['user_email']     = $user->user_email;
			$pluggable_data['user_role']      = $user->roles;
			$context['pluggable_data']        = $pluggable_data;
			$context['response_type']         = 'live';
		} else {
			$context['pluggable_data'] = [
				'wp_user_id'        => 1,
				'user_login'        => 'admin',
				'display_name'      => 'Test User',
				'user_firstname'    => 'Test',
				'user_lastname'     => 'User',
				'user_email'        => 'testuser@gmail.com',
				'user_role'         => [ 'subscriber' ],
				'group_id'          => 112,
				'group_name'        => 'Test Group',
				'group_description' => 'Test Group Description',
			];
			$context['response_type']  = 'sample';
		}

		return $context;
	}

	/**
	 * Search complete courses.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_course( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_course_id']['value'] ) ) {
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		if ( -1 === $course_id ) {
			$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='course' AND activity.activity_status= %d AND activity.course_id= %d", 1, $course_id ) );
		} else {
			$courses = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='course' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $course_id, $course_id ) );
		}

		if ( ! empty( $courses ) ) {
			$course                                   = $courses[0];
			$course_data['course_name']               = $course->post_title;
			$course_data['sfwd_course_id']            = $course->ID;
			$course_data['course_url']                = get_permalink( $course->ID );
			$course_data['course_featured_image_id']  = get_post_meta( $course->ID, '_thumbnail_id', true );
			$course_data['course_featured_image_url'] = get_the_post_thumbnail_url( $course->ID );
			$context['response_type']                 = 'live';
		} else {
			$course_data['course_name']               = 'Test Course';
			$course_data['sfwd_course_id']            = 112;
			$course_data['course_url']                = 'https://abc.com/test-course';
			$course_data['course_featured_image_id']  = 113;
			$course_data['course_featured_image_url'] = 'https://pqr.com/test-course-img';
			$context['response_type']                 = 'sample';
		}

		$users_data = $this->search_pluggables_add_user_role( [] );
		$user_data  = $users_data['pluggable_data'];

		$context['pluggable_data'] = array_merge( $course_data, $user_data );
		return $context;
	}

	/**
	 * Search lessons.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_lesson( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_lesson_id']['value'] ) ) {
			$lesson_id = $data['filter']['sfwd_lesson_id']['value'];
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		$course         = get_post( $course_id );
		$pluggable_data = LearnDash::get_course_context( $course );

		if ( -1 === $lesson_id ) {
			$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='lesson' AND activity.activity_status= %d AND activity.course_id= %d", 1, $course_id ) );
		} else {
			$lessons = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='lesson' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $lesson_id, $course_id ) );
		}

		if ( ! empty( $lessons ) ) {
			$lesson = $lessons[0];

			$pluggable_data                              = WordPress::get_user_context( $lesson->user_id );
			$pluggable_data['lesson_name']               = $lesson->post_title;
			$pluggable_data['sfwd_lesson_id']            = $lesson->ID;
			$pluggable_data['lesson_url']                = get_permalink( $lesson->ID );
			$pluggable_data['lesson_featured_image_id']  = get_post_meta( $lesson->ID, '_thumbnail_id', true );
			$pluggable_data['lesson_featured_image_url'] = get_the_post_thumbnail_url( $lesson->ID );
			$context['response_type']                    = 'live';
		} else {
			$pluggable_data                              = WordPress::get_sample_user_context();
			$pluggable_data['lesson_name']               = 'Test Lesson';
			$pluggable_data['sfwd_lesson_id']            = 114;
			$pluggable_data['lesson_url']                = 'https://abc.com/test-lesson';
			$pluggable_data['lesson_featured_image_id']  = 116;
			$pluggable_data['lesson_featured_image_url'] = 'https://pqr.com/test-lesson-img';
			$context['response_type']                    = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search topics.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_complete_topic( $data ) {
		global $wpdb;
		$context = [];

		if ( isset( $data['filter']['sfwd_topic_id']['value'] ) ) {
			$topic_id  = $data['filter']['sfwd_topic_id']['value'];
			$course_id = $data['filter']['sfwd_course_id']['value'];
		}
		$course         = get_post( $course_id );
		$pluggable_data = LearnDash::get_course_context( $course );

		if ( -1 === $topic_id ) {
			$topics = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID WHERE activity.activity_type ='topic' AND activity.activity_status= %d AND activity.course_id= %d", 1, $course_id ) );
		} else {
			$topics = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}learndash_user_activity as activity JOIN {$wpdb->prefix}posts as post ON activity.post_id=post.ID  WHERE activity.activity_type ='topic' AND activity.activity_status= %d AND activity.post_id= %d AND activity.course_id= %d", 1, $topic_id, $course_id ) );
		}

		if ( ! empty( $topics ) ) {
			$topic                                      = $topics[0];
			$pluggable_data                             = WordPress::get_user_context( $topics[0]->user_id );
			$pluggable_data['topic_name']               = $topic->post_title;
			$pluggable_data['sfwd_topic_id']            = $topic->ID;
			$pluggable_data['topic_url']                = get_permalink( $topic->ID );
			$pluggable_data['topic_featured_image_id']  = get_post_meta( $topic->ID, '_thumbnail_id', true );
			$pluggable_data['topic_featured_image_url'] = get_the_post_thumbnail_url( $topic->ID );
			$context['response_type']                   = 'live';
		} else {
			$pluggable_data                             = WordPress::get_sample_user_context();
			$pluggable_data['topic_name']               = 'Test Topic';
			$pluggable_data['sfwd_topic_id']            = 117;
			$pluggable_data['topic_url']                = 'https://abc.com/test-topic';
			$pluggable_data['topic_featured_image_id']  = 118;
			$pluggable_data['topic_featured_image_url'] = 'https://pqr.com/test-topic-img';
			$context['response_type']                   = 'sample';
		}

		$context['pluggable_data'] = $pluggable_data;
		return $context;
	}

	/**
	 * Search purchase courses.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_purchase_course( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$purchase_data = [
			'course_product_id'   => '1',
			'course_product_name' => 'Sample Course',
			'currency'            => 'USD',
			'total_amount'        => '100',
			'first_name'          => 'John',
			'last_name'           => 'Doe',
			'email'               => 'john_doe@bsf.io',
			'phone'               => '+923007626541',
		];

		$product_id = (int) ( isset( $data['filter']['course_product_id']['value'] ) ? $data['filter']['course_product_id']['value'] : '-1' );
		$order_id   = 0;

		if ( -1 !== $product_id ) {
			$order_ids = ( new Utilities() )->get_orders_ids_by_product_id( $product_id );

			if ( count( $order_ids ) > 0 ) {
				$order_id = $order_ids[0];
			}
		} else {
			$orders = wc_get_orders( [] );
			if ( count( $orders ) > 0 ) {
				foreach ( $orders as $order ) {
					$items = $order->get_items();

					if ( count( $items ) > 1 ) {
						continue;
					}

					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$product_id = $item->get_product_id();
							if ( ! empty( get_post_meta( $item->get_product_id(), '_related_course', true ) ) ) {
								$order_id = $order->get_id();
								break;
							}
						}
					}
				}
			}
		}

		if ( 0 !== $order_id ) {
			$order = wc_get_order( $order_id );

			if ( $order ) {

				$purchase_data = LearnDash::get_purchase_course_context( $order );

				$context['response_type'] = 'live';
			}
		}

		$context['pluggable_data'] = $purchase_data;

		return $context;
	}

	/**
	 * Fetch BB templates.
	 *
	 * @return array
	 */
	public function get_beaver_builder_templates() {
		$allowed_types = [ 'subscribe-form', 'contact-form' ];
		$templates     = [];
		$all_templates = get_posts(
			[
				'post_type'      => 'fl-builder-template',
				'meta_key'       => '_fl_builder_data',
				'posts_per_page' => -1,
			]
		);
		$posts         = get_posts(
			[
				'post_type'      => 'any',
				'meta_key'       => '_fl_builder_data',
				'posts_per_page' => -1,
			]
		);
		$posts         = array_merge( $all_templates, $posts );

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$meta = get_post_meta( $post->ID, '_fl_builder_data', true );
				foreach ( (array) $meta as $node_id => $node ) {
					if ( isset( $node->type ) && 'module' === $node->type ) {
						$settings = $node->settings;
						if ( in_array( $settings->type, $allowed_types, true ) ) {
							$label = $post->post_title;
							if ( '' !== $settings->node_label ) {
								$label .= ' - ' . $settings->node_label;
							}
							$templates[] = [
								'label' => $label,
								'value' => $node_id,
							];
						}
					}
				}
			}
		}
		return $templates;
	}

	/**
	 * Search beaver builder forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_beaver_builder_forms( $data ) {
		$templates = $this->get_beaver_builder_templates();
		return [
			'options' => $templates,
			'hasMore' => false,
		];
	}

	/**
	 * Search fluentcrm fields.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluentcrm_custom_fields( $data ) {
		$context           = [];
		$custom_fields     = ( new CustomContactField() )->getGlobalFields()['fields'];
		$context['fields'] = $custom_fields;
		return $context;
	}

	/**
	 * Fetch WP JOB Manager Last Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_job_manger_last_data( $data ) {
		global $wpdb;
		$job_type = $data['filter']['job_type']['value'];
		$args     = [
			'posts_per_page' => 1,
			'post_type'      => 'job_listing',
			'orderby'        => 'id',
			'order'          => 'DESC',
		];

		if ( -1 !== $job_type ) {
			$args['tax_query'] = [              // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => 'job_listing_type',
					'field'    => 'term_id',
					'terms'    => $job_type,
				],
			];
		}
		$posts = get_posts( $args );
		if ( empty( $posts ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"ID":145,"wpjob_author":"1","wpjob_date":"2023-01-22 17:38:03","wpjob_date_gmt":"2023-01-22 17:38:03","wpjob_content":"","wpjob_title":"PHP Developer","wpjob_excerpt":"","wpjob_status":"publish","comment_status":"closed","ping_status":"closed","wpjob_password":"","wpjob_name":"project-manager","to_ping":"","pinged":"","wpjob_modified":"2023-01-23 03:23:35","wpjob_modified_gmt":"2023-01-23 03:23:35","wpjob_content_filtered":"","wpjob_parent":0,"guid":"http:\/\/connector.com\/?post_type=job_listing&#038;p=145","menu_order":-1,"wpjob_type":"job_listing","wpjob_mime_type":"","comment_count":"0","filter":"raw","_filled":["0"],"_featured":["1"],"_tribe_ticket_capacity":["0"],"_tribe_ticket_version":["5.5.6"],"_edit_lock":["1674444219:1"],"_job_expires":["2023-02-21"],"_tracked_submitted":["1674409083"],"_edit_last":["1"],"_job_location":[""],"_application":["johnsmith@bexample.com"],"_company_name":["test"],"_company_website":[""],"_company_tagline":[""],"_company_twitter":[""],"_company_video":[""],"_remote_position":["1"],"_llms_reviews_enabled":[""],"_llms_display_reviews":[""],"_llms_num_reviews":["0"],"_llms_multiple_reviews_disabled":[""],"wp_user_id":1,"user_login":"john","display_name":"john","user_firstname":"john","user_lastname":"smith","user_email":"johnsmith@bexample.com","user_role":["administrator","subscriber","tutor_instructor"]}}', true );
			return $context;
		}

		$post         = $posts[0];
		$post_content = WordPress::get_post_context( $post->ID );
		$post_meta    = WordPress::get_post_meta( $post->ID );
		$job_data     = array_merge( $post_content, $post_meta, WordPress::get_user_context( $post->post_author ) );
		foreach ( $job_data as $key => $job ) {
			$newkey = str_replace( 'post', 'wpjob', $key );
			unset( $job_data[ $key ] );
			$job_data[ $newkey ] = $job;
		}
		$context['response_type']  = 'live';
		$context['pluggable_data'] = $job_data;
		return $context;

	}

	/**
	 * Get Amelia Appointment Category.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_category_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$categories = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name FROM {$wpdb->prefix}amelia_categories WHERE status = %s ORDER BY name ASC LIMIT %d OFFSET %d",
				[ 'visible', $limit, $offset ]
			),
			OBJECT
		);

		$categories_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $categories ) ) {
			foreach ( $categories as $category ) {
				$options[] = [
					'label' => $category->name,
					'value' => $category->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $categories_count > $limit && $categories_count > $offset,
		];

	}

	/**
	 * Get Amelia Appointment Services.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_service_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$services = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name FROM {$wpdb->prefix}amelia_services 
				WHERE categoryId = %d AND status = %s 
				ORDER BY name ASC LIMIT %d OFFSET %d",
				[ $data['dynamic'], 'visible', $limit, $offset ]
			),
			OBJECT
		);

		$services_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $services ) ) {
			foreach ( $services as $category ) {
				$options[] = [
					'label' => $category->name,
					'value' => $category->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $services_count > $limit && $services_count > $offset,
		];

	}

	/**
	 * Get Amelia Events.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_amelia_events_list( $data ) {

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$events = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS id, name from {$wpdb->prefix}amelia_events WHERE status = %s ORDER BY name ASC LIMIT %d OFFSET %d",
				[ 'approved', $limit, $offset ]
			),
			OBJECT
		);

		$list_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$options[] = [
					'label' => $event->name,
					'value' => $event->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $list_count > $limit && $list_count > $offset,
		];

	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_booked_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments )',
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments ) AND appointments.serviceId = %d',
					[ $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 13:00:00","bookingEnd":"2023-02-28 14:00:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"1","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"test\",\"lastName\":\"test\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 13:00:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"test","lastName":"test","email":"test@test.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_new_event_attendee_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$event_selected = $data['filter']['amelia_events_list']['value'];

		if ( -1 === $event_selected ) {
			$result = $wpdb->get_row(
				'SELECT * 
				FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
				INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
				ON customer.id = event_period.customerBookingId 
				WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods )',
				ARRAY_A
			);
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * 
					FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer 
					INNER JOIN ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods as event_period 
					ON customer.id = event_period.customerBookingId 
					WHERE event_period.customerBookingId = ( Select max(customerBookingId) From ' . $wpdb->prefix . 'amelia_customer_bookings_to_events_periods ) AND eventPeriodId = %d',
					[ $event_selected ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$event = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_events WHERE id = %d',
					[ $result['eventPeriodId'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $event, $customer_result, $coupon_result );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{""id":"1","appointmentId":null,"customerId":"1","status":"visible","price":"10","persons":"1","couponId":null,"token":"6485b07ce9","info":"{\"firstName\":\"test\",\"lastName\":\"test\",\"phone\":\"+213551223123\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":null,"created":"2023-02-02 06:35:18","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"105","eventPeriodId":"5","parentId":null,"name":"Testing Event 5","bookingOpens":null,"bookingCloses":"2023-02-09 08:00:00","bookingOpensRec":"same","bookingClosesRec":"same","ticketRangeRec":"calculate","recurringCycle":null,"recurringOrder":null,"recurringInterval":null,"recurringMonthly":null,"monthlyDate":null,"monthlyOnRepeat":null,"monthlyOnDay":null,"recurringUntil":null,"maxCapacity":"12","maxCustomCapacity":null,"maxExtraPeople":null,"locationId":null,"customLocation":"test","description":null,"color":"#1788FB","show":"1","notifyParticipants":"1","settings":"{\"payments\":{\"onSite\":true,\"payPal\":{\"enabled\":false},\"stripe\":{\"enabled\":false},\"mollie\":{\"enabled\":false},\"razorpay\":{\"enabled\":false}},\"general\":{\"minimumTimeRequirementPriorToCanceling\":null,\"redirectUrlAfterAppointment\":null},\"zoom\":{\"enabled\":false},\"lessonSpace\":{\"enabled\":false}}","zoomUserId":null,"bringingAnyone":"1","bookMultipleTimes":"1","translations":"{\"defaultLanguage\":\"en_US\"}","depositPayment":"disabled","depositPerPerson":"1","fullPayment":"0","deposit":"0","customPricing":"0","organizerId":"2","closeAfterMin":null,"closeAfterMinBookings":"0","type":"customer","externalId":"91","firstName":"test","lastName":"test","email":"test@test.com","birthday":null,"phone":"+213551223123","gender":null,"note":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"countryPhoneIso":"dz","timeZone":null}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_rescheduled_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments )',
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE customer.appointmentId = ( SELECT max(id) FROM ' . $wpdb->prefix . 'amelia_appointments ) AND appointments.serviceId = %d',
					[ $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$appointment_data['isRescheduled'] = '1';
			$context['pluggable_data']         = array_merge( $result, $fields_arr, $appointment_data, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']          = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 15:30:00","bookingEnd":"2023-02-28 16:30:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"54","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"test\",\"lastName\":\"test\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","isRescheduled":"1","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 15:30:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"test","lastName":"test","email":"test@test.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}


	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_amelia_appointment_cancelled_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$appointment_category = $data['filter']['amelia_category_list']['value'];
		$appointment_service  = $data['filter']['amelia_service_list']['value'];

		if ( -1 === $appointment_service ) {
			// If service exists as per category selected.
			$service_exist = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT id, name, description FROM ' . $wpdb->prefix . 'amelia_services WHERE categoryId = %d',
					[ $appointment_category ]
				),
				ARRAY_A
			);

			if ( empty( $service_exist ) ) {
				$result = [];
			} else {
				$result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.status = %s order by RAND() DESC LIMIT 1',
						[ 'canceled' ]
					),
					ARRAY_A
				);
			}
		} else {
			$result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT appointments.*, customer.* FROM ' . $wpdb->prefix . 'amelia_customer_bookings as customer INNER JOIN ' . $wpdb->prefix . 'amelia_appointments as appointments ON customer.appointmentId=appointments.id WHERE appointments.status = %s AND appointments.serviceId = %d order by RAND() DESC LIMIT 1',
					[ 'canceled', $appointment_service ]
				),
				ARRAY_A
			);
		}

		if ( ! empty( $result ) ) {

			$payment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_payments WHERE customerBookingId = %d',
					[ $result['id'] ]
				),
				ARRAY_A
			);

			$customer_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . $wpdb->prefix . 'amelia_users WHERE id = %d',
					[ $result['customerId'] ]
				),
				ARRAY_A
			);

			$service_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS serviceName, description AS serviceDescription, categoryId FROM ' . $wpdb->prefix . 'amelia_services WHERE id = %d',
					[ $result['serviceId'] ]
				),
				ARRAY_A
			);

			$category_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT name AS categoryName FROM ' . $wpdb->prefix . 'amelia_categories WHERE id = %d',
					[ $service_result['categoryId'] ]
				),
				ARRAY_A
			);

			if ( $result['couponId'] ) {
				$coupon_result = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT code AS couponCode, expirationDate AS couponExpirationDate FROM ' . $wpdb->prefix . 'amelia_coupons WHERE id = %d',
						[ $result['couponId'] ]
					),
					ARRAY_A
				);
			} else {
				$coupon_result = [];
			}

			if ( ! empty( $result['customFields'] ) ) {
				$custom_fields = json_decode( $result['customFields'], true );

				$fields_arr = [];
				foreach ( (array) $custom_fields as $fields ) {
					if ( is_array( $fields ) ) {
						$fields_arr[ $fields['label'] ] = $fields['value'];
					}
				}
				unset( $result['customFields'] );
			} else {
				$fields_arr = [];
			}

			$context['pluggable_data'] = array_merge( $result, $fields_arr, $payment_result, $customer_result, $service_result, $category_result, $coupon_result );
			$context['response_type']  = 'live';
		} else {

			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id":"1","status":"visible","bookingStart":"2023-02-28 15:30:00","bookingEnd":"2023-02-28 16:30:00","notifyParticipants":"1","serviceId":"4","packageId":null,"providerId":"2","locationId":null,"internalNotes":"","googleCalendarEventId":null,"googleMeetUrl":null,"outlookCalendarEventId":null,"zoomMeeting":null,"lessonSpace":null,"parentId":null,"appointmentId":"54","customerId":"1","price":"15","persons":"1","couponId":null,"token":"02cf0988c6","info":"{\"firstName\":\"test\",\"lastName\":\"test\",\"phone\":\"1 (234) 789\",\"locale\":\"en_US\",\"timeZone\":\"Asia\\\/Kolkata\",\"urlParams\":null}","utcOffset":null,"aggregatedPrice":"1","packageCustomerServiceId":null,"duration":"3600","created":"2023-02-08 11:16:03","actionsCompleted":"1","Do You Know Automation?":"Yes","When Are You Coming?":"2023-04-20","Upload Something":"","Tell Us About You!":"Hey there!","customerBookingId":"103","amount":"0","dateTime":"2023-02-28 15:30:00","gateway":"onSite","gatewayTitle":"","data":"","packageCustomerId":null,"entity":"appointment","wcOrderId":null,"type":"customer","externalId":"89","firstName":"test","lastName":"test","email":"test@test.com","birthday":null,"phone":"1 (234) 789","gender":null,"note":null,"description":null,"pictureFullPath":null,"pictureThumbPath":null,"password":null,"usedTokens":null,"zoomUserId":null,"countryPhoneIso":"us","translations":"{\"defaultLanguage\":\"en_US\"}","timeZone":null,"serviceName":"demo service","serviceDescription":"","categoryId":"2","categoryName":"New Category1"}}', true );
		}

		return $context;
	}

	/**
	 * Get MailPoet Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_forms( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forms = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT SQL_CALC_FOUND_ROWS * FROM ' . $wpdb->prefix . 'mailpoet_forms WHERE `deleted_at` IS NULL AND `status` = %s ORDER BY id LIMIT %d OFFSET %d',
				[ 'enabled', $limit, $offset ]
			)
		);

		$form_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];

		if ( ! empty( $forms ) ) {
			if ( is_array( $forms ) ) {
				foreach ( $forms as $form ) {
					$options[] = [
						'label' => $form->name,
						'value' => $form->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];

	}

	/**
	 * Get MailPoet List.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_list( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		$mailpoet = \MailPoet\API\API::MP( 'v1' );
		$lists    = $mailpoet->getLists();

		$options = [];

		if ( ! empty( $lists ) ) {
			if ( is_array( $lists ) ) {
				foreach ( $lists as $list ) {
					$options[] = [
						'label' => $list['name'],
						'value' => $list['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MailPoet Subscriber Status.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_subscriber_status( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		$subscriber_status = [
			'subscribed'   => 'Subscribed',
			'unconfirmed'  => 'Unconfirmed',
			'unsubscribed' => 'Unsubscribed',
			'inactive'     => 'Inactive',
			'bounced'      => 'Bounced',
		];

		$options = [];
		foreach ( $subscriber_status as $key => $status ) {
			$options[] = [
				'label' => $status,
				'value' => $key,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MailPoet Subscribers.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mailpoet_subscribers( $data ) {
		if ( ! class_exists( '\MailPoet\API\API' ) ) {
			return;
		}

		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$subscribers = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT SQL_CALC_FOUND_ROWS id,email FROM ' . $wpdb->prefix . 'mailpoet_subscribers ORDER BY id DESC LIMIT %d OFFSET %d',
				[ $limit, $offset ]
			)
		);

		$subscribers_count = $wpdb->get_var( 'SELECT FOUND_ROWS();' );

		$options = [];

		if ( ! empty( $subscribers ) ) {
			if ( is_array( $subscribers ) ) {
				foreach ( $subscribers as $subscriber ) {
					$options[] = [
						'label' => $subscriber->email,
						'value' => $subscriber->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $subscribers_count > $limit && $subscribers_count > $offset,
		];
	}

	/**
	 * Get ConvertPro Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_convertpro_form_list( $data ) {
		if ( ! class_exists( '\Cp_V2_Loader' ) ) {
			return;
		}

		$cp_popups_inst = CP_V2_Popups::get_instance();
		$popups         = $cp_popups_inst->get_all();

		$form_count = count( $popups );

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$options = [];

		if ( ! empty( $popups ) ) {
			if ( is_array( $popups ) ) {
				foreach ( $popups as $form ) {
					$options[] = [
						'label' => $form->post_title,
						'value' => $form->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];

	}

	/**
	 * Get ProjectHuddle Websites.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_project_huddle_websites( $data ) {

		$sites = new WP_Query(
			[
				'post_type'      => 'ph-website',
				'posts_per_page' => - 1,
				'fields'         => 'ids',
			]
		);

		$site_ids = (array) $sites->posts;

		$options = [];
		if ( ! empty( $site_ids ) ) {
			if ( is_array( $site_ids ) ) {
				foreach ( $site_ids as $site_id ) {
					$options[] = [
						'label' => get_the_title( $site_id ),
						'value' => $site_id,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];

	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_project_huddle_comment_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		if ( -1 !== $data['dynamic'] ) {
			$threads = get_posts(
				[
					'post_type'      => 'phw_comment_loc',
					'posts_per_page' => 1,
					'meta_value'     => $data['dynamic'],
					'meta_key'       => 'project_id',
					'orderby'        => 'asc',
				]
			);
		} else {
			$threads = [];
		}

		if ( ! empty( $threads ) ) {
			$comment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT  ' . $wpdb->prefix . 'comments.comment_ID
					FROM ' . $wpdb->prefix . 'comments 
					WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_comment") AND comment_post_ID = %d
					ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
					LIMIT 0,1',
					$threads[0]->ID
				),
				ARRAY_A
			);

			if ( ! empty( $comment_result ) ) {
				$comment_id                  = get_comment( $comment_result['comment_ID'], ARRAY_A );
				$comments['comment_ID']      = isset( $comment_id['comment_ID'] ) ? $comment_id['comment_ID'] : '';
				$comments['comment_post_ID'] = isset( $comment_id['comment_post_ID'] ) ? $comment_id['comment_post_ID'] : '';

				$comments['comment_author'] = isset( $comment_id['comment_author'] ) ? $comment_id['comment_author'] : '';

				$comments['comment_author_email'] = isset( $comment_id['comment_author_email'] ) ? $comment_id['comment_author_email'] : '';

				$comments['comment_date'] = isset( $comment_id['comment_date'] ) ? $comment_id['comment_date'] : '';

				$comments['comment_content'] = isset( $comment_id['comment_content'] ) ? $comment_id['comment_content'] : '';

				$comments['comment_type'] = isset( $comment_id['comment_type'] ) ? $comment_id['comment_type'] : '';

				$context['pluggable_data'] = $comments;
				$context['response_type']  = 'live';
			} else {
				$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment"}}', true );
			}
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment"}}', true );
		}

		return $context;
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_project_huddle_resolved_comment_triggers_last_data( $data ) {
		global $wpdb;

		$context = [];

		$get_comments = $wpdb->get_row(
			'SELECT  ' . $wpdb->prefix . 'comments.comment_ID, ' . $wpdb->prefix . 'comments.comment_content
			FROM ' . $wpdb->prefix . 'comments 
			WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_status") AND comment_content = "Resolved"
			ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
			LIMIT 0,1'
		);

		if ( ! empty( $get_comments ) ) {
			$comment_id     = get_comment( $get_comments->comment_ID, ARRAY_A );
			$comment_result = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT  ' . $wpdb->prefix . 'comments.comment_ID
					FROM ' . $wpdb->prefix . 'comments 
					WHERE ( ( comment_approved = "0" OR comment_approved = "1" ) ) AND comment_type IN ("ph_comment") AND comment_post_ID = %d
					ORDER BY ' . $wpdb->prefix . 'comments.comment_date_gmt DESC
					LIMIT 0,1',
					isset( $comment_id['comment_post_ID'] ) ? $comment_id['comment_post_ID'] : ''
				),
				ARRAY_A
			);

			$actual_comment                   = get_comment( $comment_result['comment_ID'], ARRAY_A );
			$comments['comment_ID']           = isset( $actual_comment['comment_ID'] ) ? $actual_comment['comment_ID'] : '';
			$comments['comment_post_ID']      = isset( $actual_comment['comment_post_ID'] ) ? $actual_comment['comment_post_ID'] : '';
			$comments['comment_author']       = isset( $actual_comment['comment_author'] ) ? $actual_comment['comment_author'] : '';
			$comments['comment_author_email'] = isset( $actual_comment['comment_author_email'] ) ? $actual_comment['comment_author_email'] : '';
			$comments['comment_date']         = isset( $actual_comment['comment_date'] ) ? $actual_comment['comment_date'] : '';
			$comments['comment_content']      = isset( $actual_comment['comment_content'] ) ? $actual_comment['comment_content'] : '';
			$comments['comment_type']         = isset( $actual_comment['comment_type'] ) ? $actual_comment['comment_type'] : '';
			$comments['comment_status']       = $get_comments->comment_content;
			$context['pluggable_data']        = $comments;
			$context['response_type']         = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"comment_ID":"1","comment_post_ID":"1","comment_author":"test","comment_author_email":"test@test.com","comment_date":"2023-03-27 13:44:26","comment_content":"<p>Leave comment<\/p>","comment_type":"ph_comment","comment_status":"Resolved"}}', true );
		}

		return $context;
	}

	/**
	 * Get MasterStudy LMS Courses.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_courses( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'stm-courses',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$courses = get_posts( $args );

		$course_count = count( $courses );

		$options = [];
		if ( ! empty( $courses ) ) {
			if ( is_array( $courses ) ) {
				foreach ( $courses as $course ) {
					$options[] = [
						'label' => $course->post_title,
						'value' => $course->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $course_count > $limit && $course_count > $offset,
		];

	}

	/**
	 * Get MasterStudy LMS Lessons.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_lessons( $data ) {

		global $wpdb;
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$course_id = $data['dynamic'];

		$lessons = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title
				FROM $wpdb->posts
				WHERE FIND_IN_SET(
					ID,
					(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'curriculum')
				)
				AND post_type = 'stm-lessons'
				ORDER BY post_title ASC",
				absint( $course_id )
			)
		);

		if ( '-1' === $course_id ) {
			$lessons = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title FROM $wpdb->posts WHERE post_type = %s ORDER BY post_title ASC",
					'stm-lessons'
				)
			);
		}

		$lessons_count = count( $lessons );

		$options = [];
		if ( ! empty( $lessons ) ) {
			if ( is_array( $lessons ) ) {
				foreach ( $lessons as $lesson ) {
					$options[] = [
						'label' => $lesson->post_title,
						'value' => $lesson->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $lessons_count > $limit && $lessons_count > $offset,
		];

	}

	/**
	 * Get MasterStudy LMS Quiz.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ms_lms_quiz( $data ) {

		global $wpdb;
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$course_id = $data['dynamic'];

		$quizzes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title
				FROM $wpdb->posts
				WHERE FIND_IN_SET(
					ID,
					(SELECT meta_value FROM $wpdb->postmeta WHERE post_id = %d AND meta_key = 'curriculum')
				)
				AND post_type = 'stm-quizzes'
				ORDER BY post_title ASC
				",
				absint( $course_id )
			)
		);

		if ( '-1' === $course_id ) {
			$quizzes = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT ID, post_title
					FROM $wpdb->posts
					WHERE post_type = %s
					ORDER BY post_title ASC",
					'stm-quizzes'
				)
			);
		}

		$quizzes_count = count( $quizzes );

		$options = [];
		if ( ! empty( $quizzes ) ) {
			if ( is_array( $quizzes ) ) {
				foreach ( $quizzes as $quiz ) {
					$options[] = [
						'label' => $quiz->post_title,
						'value' => $quiz->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $quizzes_count > $limit && $quizzes_count > $offset,
		];

	}

	/**
	 * Search MasterStudy LMS data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_ms_lms_last_data( $data ) {
		global $wpdb;
		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( 'stm_lms_course_completed' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.progress_percent=100 AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_id = %s AND postmeta.progress_percent=100 AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_lesson_passed' === $trigger ) {
			$post_id = $data['filter']['lesson']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_lessons as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.lesson_id WHERE posts.post_type=%s order by postmeta.user_lesson_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_lessons as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.lesson_id WHERE postmeta.lesson_id=%s AND posts.post_type=%s order by postmeta.user_lesson_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_quiz_passed' === $trigger ) {
			$post_id = $data['filter']['quiz']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.status='passed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.quiz_id=%s AND postmeta.status='passed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_quiz_failed' === $trigger ) {
			$post_id = $data['filter']['quiz']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.status='failed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_quizzes as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.quiz_id WHERE postmeta.quiz_id=%s AND postmeta.status='failed' AND posts.post_type=%s order by postmeta.user_quiz_id DESC LIMIT 1", $post_id, $post_type ) );
			}
		} elseif ( 'stm_lms_user_enroll_course' === $trigger ) {
			$post_id = $data['filter']['course']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.status='enrolled' AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_type ) );
			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}stm_lms_user_courses as postmeta JOIN {$wpdb->prefix}posts as posts ON posts.ID=postmeta.course_id WHERE postmeta.course_id=%s AND postmeta.status='enrolled' AND posts.post_type=%s order by postmeta.user_course_id DESC LIMIT 1", $post_id, $post_type ) );
			}       
		}

		if ( ! empty( $result ) ) {

			switch ( $trigger ) {
				case 'stm_lms_course_completed':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;
					$course           = get_the_title( $result_course_id );
					$course_link      = get_the_permalink( $result_course_id );
					$featured_image   = get_the_post_thumbnail_url( $result_course_id );

					$data         = [
						'course_id'             => $result_course_id,
						'course_title'          => $course,
						'course_link'           => $course_link,
						'course_featured_image' => $featured_image,
						'course_progress'       => $result[0]->progress_percent,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_lesson_passed':
					$result_lesson_id = $result[0]->lesson_id;
					$result_user_id   = $result[0]->user_id;
					$lesson           = get_the_title( $result_lesson_id );
					$lesson_link      = get_the_permalink( $result_lesson_id );

					$data         = [
						'lesson_id'    => $result_lesson_id,
						'lesson_title' => $lesson,
						'lesson_link'  => $lesson_link,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_quiz_passed':
					$result_quiz_id = $result[0]->quiz_id;
					$result_user_id = $result[0]->user_id;
					$quiz_title     = get_the_title( $result_quiz_id );
					$quiz_link      = get_the_permalink( $result_quiz_id );

					$data         = [
						'quiz_id'    => $result_quiz_id,
						'quiz_title' => $quiz_title,
						'quiz_link'  => $quiz_link,
						'quiz_score' => $result[0]->progress,
						'result'     => 'passed',
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_quiz_failed':
					$result_quiz_id = $result[0]->quiz_id;
					$result_user_id = $result[0]->user_id;
					$quiz_title     = get_the_title( $result_quiz_id );
					$quiz_link      = get_the_permalink( $result_quiz_id );

					$data         = [
						'quiz_id'    => $result_quiz_id,
						'quiz_title' => $quiz_title,
						'quiz_link'  => $quiz_link,
						'quiz_score' => $result[0]->progress,
						'result'     => 'failed',
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				case 'stm_lms_user_enroll_course':
					$result_course_id = $result[0]->course_id;
					$result_user_id   = $result[0]->user_id;

					$course         = get_the_title( $result_course_id );
					$course_link    = get_the_permalink( $result_course_id );
					$featured_image = get_the_post_thumbnail_url( $result_course_id );

					$data         = [
						'course_id'             => $result_course_id,
						'course_title'          => $course,
						'course_link'           => $course_link,
						'course_featured_image' => $featured_image,
					];
					$context_data = array_merge(
						WordPress::get_user_context( $result_user_id ),
						$data
					);
					break;
				default:
					return;
			}
			$context['pluggable_data'] = $context_data;
			$context['response_type']  = 'live';
		}

		return $context;

	}


	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_fluent_support_triggers_last_data( $data ) {
		$context                  = [];
		$context['response_type'] = 'sample';

		$ticket_data = [
			'id'                                   => '1',
			'customer_id'                          => '2',
			'agent_id'                             => '3',
			'product_id'                           => '5',
			'product_source'                       => 'local',
			'privacy'                              => 'private',
			'priority'                             => 'normal',
			'client_priority'                      => 'medium',
			'status'                               => 'active',
			'title'                                => 'Sample Ticket Title',
			'slug'                                 => 'sample-ticket-title',
			'hash'                                 => 'f8a8cfb946',
			'content_hash'                         => 'd65500d62621be8b493c22b1d888052c',
			'content'                              => '<p>Sample content.</p>',
			'last_customer_response'               => '2023-04-27 07:30:46',
			'waiting_since'                        => '2023-04-27 07:30:46',
			'response_count'                       => '2',
			'total_close_time'                     => '7042',
			'resolved at'                          => '2023-04-27 09:28:08',
			'closed_by'                            => '1',
			'created_at'                           => '2023-04-27 07:30:46',
			'updated_at'                           => '2023-04-27 10:28:08',
			'mailbox_id'                           => '1',
			'mailbox_name'                         => 'SureTriggers',
			'mailbox_slug'                         => 'suretriggers',
			'mailbox_box_type'                     => 'web',
			'mailbox email'                        => 'john_doe@sample.com',
			'mailbox_settings_admin_email_address' => 'john_doe@sample.com',
			'mailbox_created_by'                   => '1',
			'mailbox_is_default'                   => 'yes',
			'mailbox_created_at'                   => '2023-04-26 06:29:01',
			'mailbox_updated_at'                   => '2023-04-26 06:29:01',
		];

		$customer_data = [
			'id'          => '1',
			'first_name'  => 'John',
			'last_name'   => 'Doe',
			'email'       => 'john_doe@sample.com',
			'person_type' => 'agent',
			'status'      => 'active',
			'hash'        => '3b2b5f0432561cb81b1302b8a16b93a0',
			'user_id'     => '1',
			'created_at'  => '2023-04-27 07:30:46',
			'updated_at'  => '2023-04-27 10:28:08',
			'full_name'   => 'John Doe',
			'photo'       => 'https://www.gravatar.com/avatar/c2b06ae950033b392998ada50767b50e?s=128',
		];

		$reply_data = [
			'ticket_id'          => '1',
			'conversation_type'  => 'response',
			'content'            => '<p>Sample content.</p>',
			'source'             => 'web',
			'content_hash'       => '2cc0e35d8fb92a0675d67999b073b3a4',
			'created_at'         => '2023-04-27 07:30:46',
			'updated_at'         => '2023-04-27 10:28:08',
			'id'                 => '1',
			'person_id'          => '2',
			'person_first_name'  => 'John',
			'person_last_name'   => 'Doe',
			'person_email'       => 'john_doe@sample.com',
			'person_person_type' => 'agent',
			'person_status'      => 'active',
			'person_hash'        => '3b2b5f0432561cb81b1302b8a16b93a0',
			'person_user_id'     => '1',
			'person_created_at'  => '2023-04-27 07:30:46',
			'person_updated_at'  => '2023-04-27 10:28:08',
			'person_full_name'   => 'John Doe',
			'person_photo'       => 'https://www.gravatar.com/avatar/c2b06ae950033b392998ada50767b50e?s=128',
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( in_array( $term, [ 'response_added_by_agent', 'response_added_by_customer' ], true ) ) {
			$context['pluggable_data'] = array_merge(
				[
					'reply'    => $reply_data,
					'ticket'   => $ticket_data,
					'customer' => $customer_data,
				]
			);
		} else {
			$context['pluggable_data'] = array_merge(
				[
					'ticket'   => $ticket_data,
					'customer' => $customer_data,
				]
			);
		}

		return $context;
	}

	/**
	 * Prepare Ultimate Member user_roles.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_um_user_roles( $data ) {
		if ( function_exists( 'get_editable_roles' ) ) {
			$roles = get_editable_roles();
		} else {
			$roles = wp_roles()->roles;
			$roles = apply_filters( 'editable_roles', $roles );
		}

		$options = [];
		foreach ( $roles as $role => $details ) {

			$options[] = [
				'label' => $details['name'],
				'value' => $role,
			];

		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Prepare Ultimate Member forms_list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_um_forms_list( $data ) {

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_type'      => 'um_form',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		];

		$forms_list = get_posts( $args );

		$forms_list_count = count( $forms_list );

		$options = [];
		if ( ! empty( $forms_list ) ) {
			foreach ( $forms_list as $form ) {
				$options[] = [
					'label' => get_the_title( $form ),
					'value' => $form,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $forms_list_count > $limit && $forms_list_count > $offset,
		];
	}

	/**
	 * Get last data for Ultimate Member Login trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_logsin( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => '_um_last_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'test',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Register trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_registers( $data ) {
		$context = [];
		$args    = [
			'orderby'  => 'meta_value',
			'meta_key' => 'um_user_profile_url_slug_user_login',
			'order'    => 'DESC',
			'number'   => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'     => 1,
				'user_login'     => 'test',
				'display_name'   => 'Test User',
				'user_firstname' => 'Test',
				'user_lastname'  => 'User',
				'user_email'     => 'testuser@gmail.com',
				'user_role'      => [ $role ],
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Register trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_inactive( $data ) {
		$context = [];
		$args    = [
			'orderby'    => 'user_id',
			'meta_key'   => 'account_status',
			'meta_value' => 'inactive',
			'order'      => 'ASC',
			'number'     => 1,
		];
		$users   = get_users( $args );

		if ( ! empty( $users ) ) {
			$user                                  = $users[0];
			$pluggable_data                        = [];
			$pluggable_data[]                      = WordPress::get_user_context( $user->ID );
			$pluggable_data['user_account_status'] = 'inactive';
			$context['pluggable_data']             = $pluggable_data;
			$context['response_type']              = 'live';
		} else {
			$role                      = 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'          => 1,
				'user_login'          => 'test',
				'display_name'        => 'Test User',
				'user_firstname'      => 'Test',
				'user_lastname'       => 'User',
				'user_email'          => 'testuser@gmail.com',
				'user_role'           => [ $role ],
				'user_account_status' => 'inactive',
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get last data for Ultimate Member Change Role trigger.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_ultimate_member_user_role_change( $data ) {
		$context = [];

		$role = $data['filter']['role']['value'];

		$args  = [
			'number' => 1,
			'role'   => $role,
		];
		$users = get_users( $args );
		shuffle( $users );
		if ( ! empty( $users ) ) {
			$user                      = $users[0];
			$pluggable_data            = WordPress::get_user_context( $user->ID );
			$context['pluggable_data'] = $pluggable_data;
			$context['response_type']  = 'live';
		} else {
			$role                      = isset( $args['role'] ) ? $args['role'] : 'subscriber';
			$context['pluggable_data'] = [
				'wp_user_id'          => 1,
				'user_login'          => 'test',
				'display_name'        => 'Test User',
				'user_firstname'      => 'Test',
				'user_lastname'       => 'User',
				'user_email'          => 'testuser@gmail.com',
				'user_role'           => [ $role ],
				'user_account_status' => 'inactive',
			];
			$context['response_type']  = 'sample';
		}
		return $context;
	}

	/**
	 * Get JetEngine WP Posttypes.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_je_posttype_list( $data ) {

		$post_types = get_post_types( [ 'public' => true ], 'object' );
		$post_types = apply_filters( 'suretriggers_post_types', $post_types );
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		$options = [];
		foreach ( $post_types as $post_type => $details ) {
			$options[] = [
				'label' => $details->label,
				'value' => $post_type,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get JetEngine WP fields.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_je_field_list( $data ) {

		$post_type = $data['dynamic'];

		$metaboxes = (array) get_option( 'jet_engine_meta_boxes', [] );

		$post_fields = array_filter(
			$metaboxes,
			function( $metabox ) {
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				return 'post' === $metabox['args']['object_type'];
			}
		);

		$post_fields_count = count( $post_fields );

		$options = [];
		if ( ! empty( $post_fields ) ) {
			if ( is_array( $post_fields ) ) {
				foreach ( $post_fields as $post_field ) {
					if ( in_array( $post_type, $post_field['args']['allowed_post_type'], true ) ) {
						foreach ( $post_field['meta_fields']  as $meta_field ) {
							$options[] = [
								'label' => $meta_field['title'],
								'value' => $meta_field['name'],
							];
						}
					}
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_jet_engine_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];

		if ( -1 === $field ) {
			$metaboxes = (array) get_option( 'jet_engine_meta_boxes', [] );

			$post_fields = array_filter(
				$metaboxes,
				function( $metabox ) {
					/**
					 *
					 * Ignore line
					 *
					 * @phpstan-ignore-next-line
					 */
					return 'post' === $metabox['args']['object_type'];
				}
			);

			$options = [];
			if ( ! empty( $post_fields ) ) {
				if ( is_array( $post_fields ) ) {
					foreach ( $post_fields as $post_field ) {
						if ( in_array( $post_type, $post_field['args']['allowed_post_type'], true ) ) {
							foreach ( $post_field['meta_fields']  as $meta_field ) {
								$options[] = $meta_field['name'];
							}
						}
					}
				}
			}
			$random_key   = array_rand( $options );
			$random_value = $options[ $random_key ];
			$string       = '%' . $random_value . '%';
		} else {
			$string = '%' . $data['filter']['field_id']['value'] . '%';
		}

		$result = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT post_id FROM ' . $wpdb->prefix . 'postmeta WHERE meta_key LIKE %s',
				[ $string ]
			),
			ARRAY_A
		);

		$ids = [];

		if ( ! empty( $result ) ) {
			foreach ( $result as $val ) {
				$ids[] = $val['post_id'];
			}
		}

		$lastupdated_args = [
			'post_type'      => $post_type,
			'orderby'        => 'modified',
			'post__in'       => $ids,
			'posts_per_page' => 1,
		];
		$lastupdated_loop = get_posts( $lastupdated_args );

		$response = [];
		if ( ! empty( $result ) ) {
			$context['post'] = $lastupdated_loop[0];

			$meta_value = get_post_meta( $lastupdated_loop[0]->ID, sprintf( '%s', $data['filter']['field_id']['value'] ), true );
			$meta_key   = sprintf( '%s', $data['filter']['field_id']['value'] );

			$context[ $meta_key ] = $meta_value;

			$response['pluggable_data'] = $context;
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"post":{"ID":198,"post_author":"1","post_date":"2023-02-08 13:31:13","post_date_gmt":"2023-02-08 13:31:13","post_content":"New Category1 - content","post_title":"jennjennn - Post - jenn","post_excerpt":"","post_status":"publish","comment_status":"open","ping_status":"open","post_password":"","post_name":"jennjennn-post-jenn","to_ping":"","pinged":"","post_modified":"2023-04-10 06:23:40","post_modified_gmt":"2023-04-10 06:23:40","post_content_filtered":"","post_parent":0,"guid":"https:\/\/suretriggerswp.local\/jennjennn-post-jenn\/","menu_order":0,"post_type":"post","post_mime_type":"","comment_count":"0","filter":"raw"},"enter-post-extra-content-title":"dummy"}}', true );
		}

		return $response;

	}

	/**
	 * Get Formidable Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_formidable_form_list( $data ) {
		if ( ! class_exists( 'FrmForm' ) ) {
			return;
		}

		$page     = $data['page'];
		$term     = $data['search_term'];
		$limit    = Utilities::get_search_page_limit();
		$offset   = $limit * ( $page - 1 );
		$per_page = 10;

		$query                = [
			[
				'or'             => 1,
				'name LIKE'      => $term,
				'parent_form_id' => null,
			],
		];
		$query['is_template'] = 0;
		$query['status !']    = 'trash';
		$forms_list           = FrmForm::getAll( $query, '', $offset . ',' . $per_page );
		$form_count           = FrmForm::getAll( $query );
		$form_count           = count( $form_count );
		$options              = [];

		if ( ! empty( $forms_list ) ) {
			if ( is_array( $forms_list ) ) {
				foreach ( $forms_list as $form ) {
					$options[] = [
						'label' => $form->name,
						'value' => $form->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $form_count > $limit && $form_count > $offset,
		];
	}

	/**
	 * Get JetFormBuilder Form List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_jetform_list( $data ) {
		if ( ! class_exists( '\Jet_Form_Builder\Classes\Tools' ) ) {
			return;
		}

		$forms = \Jet_Form_Builder\Classes\Tools::get_forms_list_for_js();

		$options = [];
		foreach ( $forms as $form ) {

			if ( ! empty( $form['value'] ) ) {
				$options[] = [
					'label' => esc_html( $form['label'] ),
					'value' => esc_attr( $form['value'] ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Forminator Form List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_forminator_form_list( $data ) {
		if ( ! class_exists( 'Forminator_API' ) ) {
			return;
		}

		$forms = Forminator_API::get_forms( null, 1, 10 );

		$options = [];
		foreach ( $forms as $form ) {
			$options[] = [
				'label' => isset( $form->settings ) && isset( $form->settings['form_name'] ) ? $form->settings['form_name'] : $form->name,
				'value' => $form->id,
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}


	/**
	 * Get BbPress topics list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bbp_topic_list( $data ) {
		$page     = $data['page'];
		$forum_id = $data['dynamic'];
		$limit    = Utilities::get_search_page_limit();
		$offset   = $limit * ( $page - 1 );
		$args     = [
			'post_type'  => 'topic',
			'offset'     => $offset,
			'meta_query' => [
				[
					'key'     => '_bbp_forum_id',
					'value'   => $forum_id,
					'compare' => '==',
				],           
			],
		];

		$topics       = get_posts( $args );
		$topics_count = count( $topics );

		$options = [];
		if ( ! empty( $topics ) ) {
			if ( is_array( $topics ) ) {
				foreach ( $topics as $topic ) {
					$options[] = [
						'label' => $topic->post_title,
						'value' => $topic->ID,
					];
				}
			}
		}
		return [
			'options' => $options,
			'hasMore' => $topics_count > $limit && $topics_count > $offset,
		];
	}


	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_bbp_last_data( $data ) {
		global $wpdb;

		$post_type = $data['post_type'];
		$trigger   = $data['search_term'];
		$context   = [];

		if ( 'topic' === $post_type ) {
			$post_id = $data['filter']['forum']['value'];
			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'topic' AND postmeta.meta_key= '_bbp_forum_id' ORDER BY posts.ID DESC LIMIT 1" );

			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='topic' AND postmeta.meta_key= '_bbp_forum_id' AND postmeta.meta_value=%s ORDER BY posts.ID DESC LIMIT 1", $post_id ) );

			}
		} elseif ( 'reply' === $post_type ) {
			$post_id = $data['filter']['topic']['value'];

			if ( -1 === $post_id ) {
				$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID = postmeta.post_id WHERE posts.post_type = 'reply' AND postmeta.meta_key= '_bbp_topic_id' ORDER BY posts.ID DESC LIMIT 1" );

			} else {
				$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts as posts JOIN {$wpdb->prefix}postmeta as postmeta ON posts.ID=postmeta.post_id WHERE posts.post_type ='reply' AND postmeta.meta_key= '_bbp_topic_id' AND postmeta.meta_value=%s ORDER BY posts.ID DESC LIMIT 1", $post_id ) );
			}
		}


		$response = [];
		if ( ! empty( $result ) ) {
			if ( 'bbpress_topic_created' === $trigger ) {
				$topic_id          = $result[0]->post_id;
				$forum_id          = $result[0]->meta_value;
				$topic             = get_the_title( $topic_id );
				$topic_link        = get_the_permalink( $topic_id );
				$topic_description = get_the_content( $topic_id );
				$topic_status      = get_post_status( $topic_id );

				$forum             = get_the_title( $forum_id );
				$forum_link        = get_the_permalink( $forum_id );
				$forum_description = get_the_content( $forum_id );
				$forum_status      = get_post_status( $forum_id );

				$forum = [
					'forum'             => $forum_id,
					'forum_title'       => $forum,
					'forum_link'        => $forum_link,
					'forum_description' => $forum_description,
					'forum_status'      => $forum_status,
				];

				$topic = [
					'topic_title'       => $topic,
					'topic_link'        => $topic_link,
					'topic_description' => $topic_description,
					'topic_status'      => $topic_status,
				];

				$user_id = $result[0]->post_author;
				$context = array_merge(
					WordPress::get_user_context( $user_id ),
					$forum,
					$topic
				);

				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$reply_id          = $result[0]->post_id;
				$topic_id          = $result[0]->meta_value;
				$forum_id          = get_post_meta( $topic_id, '_bbp_forum_id', true );
				$forum_id          = intval( '"' . $forum_id . '"' );
				$reply             = get_the_title( $reply_id );
				$reply_link        = get_the_permalink( $reply_id );
				$reply_description = get_the_content( $reply_id );
				$reply_status      = get_post_status( $reply_id );


				$topic             = get_the_title( $topic_id );
				$topic_link        = get_the_permalink( $topic_id );
				$topic_description = get_the_content( $topic_id );
				$topic_status      = get_post_status( $topic_id );

				$forum             = get_the_title( $forum_id );
				$forum_link        = get_the_permalink( $forum_id );
				$forum_description = get_the_content( null, false, $forum_id );
				$forum_status      = get_post_status( $forum_id );

				$forum = [
					'forum'             => $forum_id,
					'forum_title'       => $forum,
					'forum_link'        => $forum_link,
					'forum_description' => $forum_description,
					'forum_status'      => $forum_status,
				];

				$topic = [
					'topic_title'       => $topic,
					'topic_link'        => $topic_link,
					'topic_description' => $topic_description,
					'topic_status'      => $topic_status,
				];

				$reply   = [
					'reply_title'       => $reply,
					'reply_link'        => $reply_link,
					'reply_description' => $reply_description,
					'reply_status'      => $reply_status,
				];
				$user_id = $result[0]->post_author;
				$context = array_merge(
					WordPress::get_user_context( $user_id ),
					$forum,
					$topic, 
					$reply
				);

				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			}       
		}

		return $response;
	}

	/**
	 * Search Last Updated Field Data.
	 *
	 * @param array $data data.
	 * @return array|void
	 */
	public function search_happyform_list( $data ) {
		if ( ! function_exists( 'happyforms_get_form_controller' ) ) {
			return;
		}

		$form_controller = happyforms_get_form_controller();

		$forms   = $form_controller->do_get();
		$options = [];
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				$options[] = [
					'label' => $form['post_title'],
					'value' => $form['ID'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress Course List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_mpc_lessons_list( $data ) {
		if ( ! class_exists( '\memberpress\courses\models\Lesson' ) ) {
			return;
		}
		global $wpdb;
		$options   = [];
		$course_id = $data['dynamic'];
		$result    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  {$wpdb->prefix}mpcs_sections WHERE course_id =%s", $course_id ) );
		$sections  = [];
		foreach ( $result as $rec ) {
			$sections[] = [
				'id'    => $rec->id,
				'title' => $rec->title,
			];
		}
		if ( is_array( $sections ) && count( $sections ) > 0 ) {
			foreach ( $sections as $section ) {
				$post_types_string = \memberpress\courses\models\Lesson::lesson_cpts();
				$post_types_string = implode( "','", $post_types_string );

				$query = $wpdb->prepare(
					"SELECT * FROM {$wpdb->posts} AS p
					JOIN {$wpdb->postmeta} AS pm
						ON p.ID = pm.post_id
						AND pm.meta_key = %s
						AND pm.meta_value = %s
					JOIN {$wpdb->postmeta} AS pm_order
						ON p.ID = pm_order.post_id
						AND pm_order.meta_key = %s
					WHERE p.post_type in ( %s ) AND p.post_status <> 'trash'
					ORDER BY pm_order.meta_value * 1",
					models\Lesson::$section_id_str,
					$section['id'],
					models\Lesson::$lesson_order_str,
					stripcslashes( $post_types_string )
				);

				$db_lessons = $wpdb->get_results( stripcslashes( $query ) ); //phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				foreach ( $db_lessons as $lesson ) {
					$options[] = [
						'label' => $section['title'] . '->' . $lesson->post_title,
						'value' => $lesson->ID,
					];
				}           
			}
		}
		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Memberpress Course List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_rank_type_list( $data ) {
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT ID, post_name, post_title, post_type
										FROM $wpdb->posts
										WHERE post_type LIKE 'rank-type' AND post_status = 'publish' ORDER BY post_title ASC"
		);

		$posts_count = count( $posts );

		$options = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->post_name,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get MPC last data.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mpc_last_data( $data ) {
		global $wpdb;
		$trigger     = $data['search_term'];
		$course_data = [];
		$lesson_data = [];
		$context     = [];

		if ( 'mpc_course_completed' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course]']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID= %s ORDER BY id DESC LIMIT 1", $course_id ) );
			} else {
				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts where post_type = 'mpcs-course' ORDER BY id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data = [
					'course_id'                 => $course->ID,
					'course_title'              => get_the_title( $course_id ),
					'course_url'                => get_permalink( $course_id ),
					'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
					'course_featured_image_url' => get_the_post_thumbnail_url( $course_id ),
				];
			}
			$user_progress = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mpcs_user_progress WHERE course_id=%s", $course_id ) );
			if ( ! empty( $user_progress ) ) {
				$context['response_type']  = 'live';
				$context['pluggable_data'] = array_merge( WordPress::get_user_context( $user_progress->user_id ), $course_data, $lesson_data );
			} else {
				$sample_data = '{"pluggable_data":{"wp_user_id":1,"user_login":"suretriggers","display_name":"suretriggers","user_firstname":"suretriggers","user_lastname":"suretriggers","user_email":"hello@suretriggers.io","user_role":["administrator","subscriber","tutor_instructor","bbp_keymaster"],"course_id":617,"course_title":"Course One","course_url":"https:\/\/connector.com\/courses\/course-one\/","course_featured_image_id":"","course_featured_image_url":false}
				,"response_type":"sample"}  ';
				$context     = json_decode( $sample_data, true );
			}
		} elseif ( 'mpc_lesson_completed' === $trigger ) {
			$lesson_id = (int) ( isset( $data['filter']['lesson']['value'] ) ? $data['filter']['lesson']['value'] : '-1' );
			$course_id = (int) $data['filter']['course']['value'];
			if ( $lesson_id > 0 ) {

				$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}posts WHERE ID= %s ORDER BY id DESC LIMIT 1", $lesson_id ) );
			} else {
				$lesson = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}posts where post_type = 'mpcs-lesson' ORDER BY id DESC LIMIT 1" );
			}

			if ( ! empty( $lesson ) ) {
				$lesson_data = [
					'lesson_id'                 => $lesson->ID,
					'lesson_title'              => get_the_title( $lesson_id ),
					'lesson_url'                => get_permalink( $lesson_id ),
					'lesson_featured_image_id'  => get_post_meta( $lesson_id, '_thumbnail_id', true ),
					'lesson_featured_image_url' => get_the_post_thumbnail_url( $lesson_id ),
				];

				$lesson_section_id = get_post_meta( $lesson->ID, '_mpcs_lesson_section_id', true );

				$section = $wpdb->get_row( $wpdb->prepare( "SELECT course_id FROM {$wpdb->prefix}mpcs_sections WHERE ID= %s", $lesson_section_id ) );

				$course_data = [
					'course_id'                 => $course_id,
					'course_title'              => get_the_title( $course_id ),
					'course_url'                => get_permalink( $course_id ),
					'course_featured_image_id'  => get_post_meta( $course_id, '_thumbnail_id', true ),
					'course_featured_image_url' => get_the_post_thumbnail_url( $section->course_id ),
				];
			}

			$user_progress = $wpdb->get_row( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}mpcs_user_progress WHERE lesson_id= %s AND course_id=%s", $lesson_id, $course_id ) );
			if ( ! empty( $user_progress ) ) {
				$context['response_type']  = 'live';
				$context['pluggable_data'] = array_merge( WordPress::get_user_context( $user_progress->user_id ), $course_data, $lesson_data );
			} else {
				$sample_data = '{"pluggable_data":{"wp_user_id":1,"user_login":"suretriggers","display_name":"suretriggers","user_firstname":"suretriggers","user_lastname":"dev","user_email":"hello@suretriggers.com","user_role":["administrator","subscriber","tutor_instructor","bbp_keymaster"],"lesson_id":620,"lesson_title":"second section","lesson_url":"https:\/\/connector.com\/courses\/course-one\/lessons\/second-section\/","lesson_featured_image_id":"","lesson_featured_image_url":false,"course_id":617,"course_title":"Course One","course_url":"https:\/\/connector.com\/courses\/course-one\/","course_featured_image_id":"","course_featured_image_url":false},"response_type":"sample"}';
				$context     = json_decode( $sample_data, true );
			}
		}


		return $context;
	}

	/** Get GamiPress Rank List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_rank_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => $data['dynamic']['rank_type'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$rank_type = get_posts( $args );
		
		$count_args = [
			'post_type'      => $data['dynamic']['rank_type'],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$rank_posts      = get_posts( $count_args );
		$rank_type_count = count( $rank_posts );

		$options = [];
		if ( $rank_type ) {
			foreach ( $rank_type as $rank ) {
				$options[] = [
					'label' => $rank->post_title,
					'value' => $rank->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $rank_type_count > $limit && $rank_type_count > $offset,
		];
	}

	/**
	 * Get GamiPress PointType List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_point_type_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'points-type',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$point_type = get_posts( $args );

		$count_args = [
			'post_type'      => 'points-type',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$count_point_type = get_posts( $count_args );
		$point_type_count = count( $count_point_type );

		$options = [];
		if ( $point_type ) {
			foreach ( $point_type as $point ) {
				$options[] = [
					'label' => $point->post_title,
					'value' => $point->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $point_type_count > $limit && $point_type_count > $offset,
		];
	}

	/**
	 * Get GamiPress AchievementType List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_achivement_type_list( $data ) {
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT ID, post_name, post_title, post_type
			FROM $wpdb->posts
			WHERE post_type LIKE 'achievement-type' AND post_status = 'publish' ORDER BY post_title ASC"
		);

		$posts_count = count( $posts );

		$options = [];
		if ( $posts ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->post_name,
				];
			}
		}

		$options[] = [
			'label' => 'Points awards',
			'value' => 'points-award',
		];
		$options[] = [
			'label' => 'Step',
			'value' => 'step',
		];
		$options[] = [
			'label' => 'Rank requirement',
			'value' => 'rank-requirement',
		];

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get GamiPress Award List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_gp_award_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => $data['dynamic']['achivement_type'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$award_type = get_posts( $args );
		$count_args = [
			'post_type'      => $data['dynamic']['achivement_type'],
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			's'              => $data['search_term'],
		];

		$count_award_type = get_posts( $count_args );
		$award_type_count = count( $count_award_type );
		$options          = [];
		if ( $award_type ) {
			foreach ( $award_type as $award ) {
				$options[] = [
					'label' => $award->post_title,
					'value' => $award->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $award_type_count > $limit && $award_type_count > $offset,
		];
	}

	/**
	 * Get Woocommerce Subscription Product List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_subscription_product_list( $data ) {
		global $wpdb;

		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
	LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
								WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
									AND posts.post_type = %s
									AND posts.post_status = %s
								UNION ALL
								SELECT ID, post_title FROM $wpdb->posts
								WHERE post_type = %s
									AND post_status = %s
			ORDER BY post_title",
				'product',
				'publish',
				'shop_subscription',
				'publish'
			)
		);

		$options = [];
		if ( $subscriptions ) {
			foreach ( $subscriptions as $post ) {
				$options[] = [
					'label' => $post->post_title,
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Woocommerce Subscriptions Variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_variable_subscription_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		if ( ! function_exists( 'wc_get_products' ) ) {
			return;
		}
		$subscription_products = wc_get_products(
			[
				'type'           => [ 'variable-subscription' ],
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'orderby'        => 'date',
				'order'          => 'DESC',
			]
		);

		$subscription_products_count = count( (array) $subscription_products );

		$options = [];
		if ( $subscription_products ) {
			foreach ( (array) $subscription_products as $product ) {
				$options[] = [
					'label' => $product->get_title(),
					'value' => $product->get_id(),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $subscription_products_count > $limit && $subscription_products_count > $offset,
		];
	}

	/**
	 * Get Woocommerce Variation list.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_variation_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'product_variation',
			'post_parent'    => $data['dynamic']['variable_subscription'],
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];

		$variation       = get_posts( $args );
		$variation_count = count( $variation );

		$options = [];
		if ( $variation ) {
			foreach ( $variation as $product ) {
				$options[] = [
					'label' => ! empty( $product->post_excerpt ) ? $product->post_excerpt : $product->post_title,
					'value' => $product->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $variation_count > $limit && $variation_count > $offset,
		];
	}

	/**
	 * Get Membership List.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_membership_list( $data ) {
		global $wpdb;

		$levels  = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}pmpro_membership_levels ORDER BY id ASC" );
		$options = [];
		if ( $levels ) {
			foreach ( $levels as $level ) {
				$options[] = [
					'label' => $level->name,
					'value' => $level->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**

	 * Get EventsManager last data.
	 * 
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_events_manager_data( $data ) {
		global $wpdb;
		$trigger = $data['search_term'];
		$context = [];

		$post_id = (int) ( isset( $data['filter']['post_id']['value'] ) ? $data['filter']['post_id']['value'] : '-1' );
		if ( 'em_user_register_in_event' === $trigger ) {
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status NOT IN (2,3) AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
	
			}
		
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$location                  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				if ( ! empty( $location ) ) {
					$context['pluggable_data'] = array_merge( $context['pluggable_data'], json_decode( wp_json_encode( $location ), true ) );
				}
 
				$context['response_type'] = 'live';
			}
		} elseif ( 'em_user_unregister_from_event' === $trigger ) {
				
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status IN (2,3) AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status IN (2,3) AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
		
			}   
			
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				$context['response_type']  = 'live';
			}       
		} elseif ( 'em_user_booking_approved' === $trigger ) {
				
			if ( $post_id > 0 ) {
				$event_id_id  = get_post_meta( $post_id, '_event_id', true );
				$all_bookings = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status=1 AND b.event_id = %s AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1", $event_id_id ) );
			} else {
				$all_bookings = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}em_bookings as b INNER JOIN {$wpdb->prefix}em_events as e ON b.event_id = e.event_id WHERE e.event_status = 1 AND b.booking_status=1 AND e.event_end_date >= CURRENT_DATE ORDER BY b.booking_id DESC LIMIT 1" );
	
			}
	
			if ( ! empty( $all_bookings ) ) {
				$user_id                   = $all_bookings->person_id;
				$location                  = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}em_locations as b WHERE b.location_id  = %s", $all_bookings->location_id ) );
				$context['pluggable_data'] = array_merge(
					WordPress::get_user_context( $user_id ), 
					json_decode( wp_json_encode( $all_bookings ), true )
				);
				if ( ! empty( $location ) ) {
					$context['pluggable_data'] = array_merge( $context['pluggable_data'], json_decode( wp_json_encode( $location ), true ) );
				}

				$context['response_type'] = 'live';

			}
		}
		return $context;
	}

	/**

	 * Get learnpress last data.
	 * 
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_learnpress_lms_last_data( $data ) {
		global $wpdb;
		$trigger     = $data['search_term'];
		$course_data = [];
		$lesson_data = [];
		$context     = [];
	

		if ( 'learnpress_course_completed' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && user_id>0 && status= 'finished' ORDER BY item_id DESC LIMIT 1", $course_id ) );
			} else {

				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_course' && user_id>0 && status= 'finished' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data               = array_merge( WordPress::get_user_context( $course->user_id ), LearnPress::get_lpc_course_context( $course->item_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $course_data;
			}       
		} elseif ( 'learnpress_lesson_completed' === $trigger ) {
			$lesson_id = (int) ( isset( $data['filter']['lesson']['value'] ) ? $data['filter']['lesson']['value'] : '-1' );
			if ( $lesson_id > 0 ) {

				$lesson = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && user_id>0 && status= 'completed' ORDER BY item_id DESC LIMIT 1", $lesson_id ) );
			} else {

				$lesson = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_lesson' && user_id>0 && status= 'completed' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $lesson ) ) {
				$lesson_data               = array_merge( WordPress::get_user_context( $lesson->user_id ), LearnPress::get_lpc_lesson_context( $lesson->item_id ), LearnPress::get_lpc_course_context( $lesson->ref_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $lesson_data;
			}
		} elseif ( 'learnpress_user_enrolled_in_course' === $trigger ) {
			$course_id = (int) ( isset( $data['filter']['course']['value'] ) ? $data['filter']['course']['value'] : '-1' );
			if ( $course_id > 0 ) {

				$course = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_id= %s && status= 'enrolled' ORDER BY item_id DESC LIMIT 1", $course_id ) );
			} else {

				$course = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE item_type= 'lp_course' && user_id>0 && status= 'enrolled' ORDER BY item_id DESC LIMIT 1" );
			}

			if ( ! empty( $course ) ) {
				$course_data               = array_merge( WordPress::get_user_context( $course->user_id ), LearnPress::get_lpc_course_context( $course->item_id ) );
				$context['response_type']  = 'live';
				$context['pluggable_data'] = $course_data;

			}       
		}

		return $context;
	}

	/**
	 * Get Woocommerce Memberships Plan List.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_wc_membership_plan_list( $data ) {
		global $wpdb;

		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args = [
			'post_type'      => 'wc_membership_plan',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		];
		$loop = new WP_Query( $args );

		$plans       = (array) $loop->posts;
		$plans_count = count( $plans );

		$options = [];
		if ( ! empty( $plans ) ) {
			if ( is_array( $plans ) ) {
				foreach ( $plans as $plan_id ) {
					$options[] = [
						'label' => get_the_title( $plan_id ),
						'value' => $plan_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $plans_count > $limit && $plans_count > $offset,
		];
	}

		/**
		 * Get BuddyPress Private group.
		 *
		 * @param array $data data.
		 *
		 * @return array|void
		 */
	public function search_bp_private_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status = 'private'" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress Public group.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_public_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups WHERE status = 'public'" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress group.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_group_list( $data ) {
		global $wpdb;

		$groups = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bp_groups" );

		$options = [];
		if ( $groups ) {
			foreach ( $groups as $group ) {
				$options[] = [
					'label' => $group->name,
					'value' => $group->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress field.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_field_list( $data ) {
		global $wpdb;

		$base_group_id = 1;
		if ( function_exists( 'bp_xprofile_base_group_id' ) ) {
			$base_group_id = bp_xprofile_base_group_id();
		}

		$xprofile_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}bp_xprofile_fields WHERE parent_id = 0 AND group_id = %d ORDER BY field_order ASC", $base_group_id ) );

		$options = [];
		if ( ! empty( $xprofile_fields ) ) {
			foreach ( $xprofile_fields as $xprofile_field ) {
				$options[] = [
					'label' => $xprofile_field->name,
					'value' => $xprofile_field->id,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get BuddyPress member type.
	 *
	 * @param array $data data.
	 *
	 * @return array|void
	 */
	public function search_bp_member_type_list( $data ) {
		$options = [];      
		if ( function_exists( 'bp_get_member_types' ) ) {
			$types = bp_get_member_types( [] );
			if ( $types ) {
				foreach ( $types as $key => $type ) {
					$options[] = [
						'label' => $type,
						'value' => $key,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for WP All Import.
	 *
	 * @param array $data data.
	 * @return mixed
	 */
	public function search_wp_all_import_last_data( $data ) {
		global $wpdb;
		$post_type = $data['filter']['post_type']['value'];
		$trigger   = $data['search_term'];

		if ( 'wp_all_import_post_type_imported' === $trigger ) {
			if ( -1 == $post_type ) {
				$imports  = $wpdb->get_row( "SELECT post_id FROM {$wpdb->prefix}pmxi_posts ORDER BY id DESC LIMIT 1", ARRAY_A );
				$posts[0] = $imports['post_id'];        
			} else {
				$imports = $wpdb->get_results( "SELECT post_id FROM {$wpdb->prefix}pmxi_posts", ARRAY_A );
				$imports = array_column( $imports, 'post_id' );
				$args    = [
					'posts_per_page' => 1,
					'post_type'      => $post_type,
					'post__in'       => $imports,
				];
				$posts   = get_posts( $args );        
			}
		} elseif ( 'wp_all_import_completed' === $trigger ) {
			$imports = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pmxi_imports WHERE failed = 0 ORDER BY id DESC LIMIT 1", ARRAY_A );    
		} elseif ( 'wp_all_import_failed' === $trigger ) {
			$imports = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}pmxi_imports WHERE failed = 1 ORDER BY id DESC LIMIT 1", ARRAY_A );
		}

		if ( 'wp_all_import_post_type_imported' === $trigger && empty( $imports ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"ID": 1,"post_author": "1","post_date": "2023-07-12 06:31:35","post_date_gmt": "2023-07-12 06:31:35","post_content": "","post_title": "Test","post_excerpt": "","post_status": "publish","comment_status": "open","ping_status": "open","post_password": "","post_name": "test","to_ping": "","pinged": "","post_modified": "2023-07-12 06:31:35","post_modified_gmt": "2023-07-12 06:31:35","post_content_filtered": "","post_parent": 0,"guid": "https:\/\/example.com\/test\/","menu_order": 0,"post_type": "post","post_mime_type": "","comment_count": "0","filter": "raw"}}', true );
			return $context;
		} elseif ( empty( $imports ) ) {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"id": "1","parent_import_id": "0","name": "demowpinstawpxyz.WordPress.2023_07_12.xml","friendly_name": "","type": "upload","feed_type": "","path": "\/wpallimport\/uploads\/ee8816eebf7a373454cdd1189c831241\/demowpinstawpxyz.WordPress.2023_07_12.xml","xpath": "\/rss","registered_on": "2023-07-12 05:10:29","root_element": "rss","processing": "0","executing": "0","triggered": "0","queue_chunk_number": "0","first_import": "2023-07-12 05:09:41","count": "1","imported": "0","created": "0","updated": "0","skipped": "1","deleted": "0","changed_missing": "0","canceled": "0","canceled_on": "0000-00-00 00:00:00","failed": "0","failed_on": "0000-00-00 00:00:00","settings_update_on": "0000-00-00 00:00:00","last_activity": "2023-07-12 05:10:24","iteration": "1"}}', true );
			return $context;
		}

		$context['response_type'] = 'live';
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = WordPress::get_post_context( $posts[0] );
		} else {
			$context['pluggable_data'] = $imports;
		}
		
		return $context;
	}

	/**
	 * Get Wp Simple Pay Forms.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_simple_pay_forms( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$forms = get_posts(
			[
				'post_type'      => 'simple-pay',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
			]
		);

		$forms_count = count( $forms );

		$options = [];

		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form_id ) {
				if ( function_exists( 'simpay_get_form' ) ) {
					$form      = simpay_get_form( $form_id );
					$options[] = [
						'label' => null !== get_the_title( $form_id ) ? $form->company_name : get_the_title( $form_id ),
						'value' => $form_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $forms_count > $limit && $forms_count > $offset,
		];
	}

	/**
	 * Get Post list as per post type for metabox.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_posts_list( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => $data['dynamic'],
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'fields'         => 'ids',
			]
		);

		$all_posts = get_posts(
			[
				'post_type'      => $data['dynamic'],
				'posts_per_page' => -1,
				'fields'         => 'ids',
			]
		);

		$posts_count = count( $all_posts );

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post ),
					'value' => $post,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Metabox Custom box in Post list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_field_list( $data ) {
		
		if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
			return [];
		}

		$options = [];

		$metabox_fields = (array) rwmb_get_object_fields( $data['dynamic'] );

		foreach ( $metabox_fields as $metabox_field ) {

			if ( ! empty( $metabox_field['id'] ) && ! empty( $metabox_field['name'] ) ) {

				$options[] = [
					'label' => $metabox_field['name'],
					'value' => $metabox_field['id'],
				];

			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Metabox Custom box user list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_mb_user_field_list( $data ) {
		
		if ( ! function_exists( 'rwmb_get_object_fields' ) ) {
			return [];
		}

		$options = [];

		$metabox_fields = (array) rwmb_get_object_fields( null, 'user' );

		foreach ( $metabox_fields as $metabox_field ) {

			if ( ! empty( $metabox_field['id'] ) && ! empty( $metabox_field['name'] ) ) {

				$options[] = [
					'label' => $metabox_field['name'],
					'value' => $metabox_field['id'],
				];

			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data for MetaBox.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_meta_box_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];
		$post      = $data['filter']['wp_post']['value'];

		if ( -1 === $field ) {
			if ( function_exists( 'rwmb_get_object_fields' ) ) {
				$metaboxes = rwmb_get_object_fields( $post_type );
				
				if ( ! empty( $metaboxes ) ) {
					$random_key = array_rand( $metaboxes );
					$field      = $random_key;
				} else {
					$result = '';
				}
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}

		if ( function_exists( 'rwmb_meta' ) ) {
			$result = rwmb_meta( $field, '', $post );
		}

		$response = [];
		if ( ! empty( $result ) ) {
			$response['pluggable_data'] = array_merge( [ $field => $result ], WordPress::get_post_context( $post ) );
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"custom_description": "custom message", "ID": 1, "post_author": "1", "post_date": "2023-05-31 13:26:24", "post_date_gmt": "2023-05-31 13:26:24", "post_content": "", "post_title": "Test", "post_excerpt": "", "post_status": "publish", "comment_status": "open", "ping_status": "open", "post_password": "", "post_name": "test", "to_ping": "", "pinged": "", "post_modified": "2023-08-17 09:15:56", "post_modified_gmt": "2023-08-17 09:15:56", "post_content_filtered": "", "post_parent": 0, "guid": "https:\/\/example.com\/?p=1", "menu_order": 0, "post_type": "post", "post_mime_type": "", "comment_count": "2", "filter": "raw"}}', true );
		}

		return $response;
	}

		/**
		 * Search Last Updated User Field Data MetaBox.
		 *
		 * @param array $data data.
		 * @return array
		 */
	public function search_user_meta_box_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		if ( -1 === $field ) {
			if ( function_exists( 'rwmb_get_object_fields' ) ) {
				$metabox_fields = (array) rwmb_get_object_fields( null, 'user' );
				
				if ( ! empty( $metabox_fields ) ) {
					$random_key = array_rand( $metabox_fields );
					$field      = $random_key;
				} else {
					$result = '';
				}
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}

		$users = get_users(
			[
				'fields'   => 'ID',
				'meta_key' => $field,
			]
		);

		if ( ! empty( $users ) ) {
			$user_random_key = array_rand( $users );
			$user_id         = $user_random_key;
			if ( function_exists( 'rwmb_get_value' ) ) {
				$result = rwmb_get_value( $field, [ 'object_type' => 'user' ], $users[ $user_id ] );
			}

			$response = [];
			if ( ! empty( $result ) ) {
				$context                    = [
					'field_id' => $field,
					$field     => $result,
					'user'     => WordPress::get_user_context( $users[ $user_id ] ),
				];
				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$response = json_decode(
					'{
					"response_type": "sample",
					"pluggable_data": {
						"field_id": "gender",
						"user": {
							"wp_user_id": 114,
							"user_login": "test",
							"display_name": "test",
							"user_firstname": "test",
							"user_lastname": "test",
							"user_email": "test@test.com",
							"user_role": [ "subscriber" ]
						}
					}
				}',
					true 
				);
			}
		} else {
			$response = json_decode(
				'{
				"response_type": "sample",
				"pluggable_data": {
					"field_id": "gender",
					"user": {
						"wp_user_id": 114,
						"user_login": "test",
						"display_name": "test",
						"user_firstname": "test",
						"user_lastname": "test",
						"user_email": "test@test.com",
						"user_role": [ "subscriber" ]
					}
				}
			}',
				true 
			);
		}

		return $response;
	}

	/**
	 * Search forms of Pie Forms.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_list( $data ) {
		global $wpdb;
		$options = [];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pollsq' ) ) ) {

			$results = $wpdb->get_results( 'SELECT pollq_id, pollq_question FROM ' . $wpdb->prefix . 'pollsq WHERE pollq_active = 1' );

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->pollq_question,
						'value' => $result->pollq_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search answers of WP-Polls questions.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_answers( $data ) {
		global $wpdb;

		$options = [];
		$poll_id = $data['dynamic'];

		if ( $wpdb->query( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'pollsa' ) ) ) {

			if ( '-1' !== $poll_id ) {
				$results = $wpdb->get_results( $wpdb->prepare( 'SELECT polla_aid, polla_answers FROM ' . $wpdb->prefix . 'pollsa WHERE polla_qid = %d', $poll_id ) );
			} else {
				$results = $wpdb->get_results( 'SELECT polla_aid, polla_answers FROM ' . $wpdb->prefix . 'pollsa' );
			}

			if ( $results ) {
				foreach ( $results as $result ) {
					$options[] = [
						'label' => $result->polla_answers,
						'value' => $result->polla_aid,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_wp_polls_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$poll = [
			'poll_id'            => 1,
			'question'           => 'Which skills are you interested to learn?',
			'answers'            => 'Web Development, Graphic Designing, Content Writing, Digital Marketing',
			'start_date'         => '2023-08-29 17:19:13',
			'end_date'           => 'Not set',
			'selected_answers'   => 'Content Writing, Web Development',
			'selected_answer_id' => 2,
		];

		$poll_data = $wpdb->get_row( "SELECT pollip_qid AS poll_id, pollip_aid AS answer_id FROM {$wpdb->prefix}pollsip ORDER BY pollip_id DESC LIMIT 1" );

		if ( ! empty( $poll_data ) ) {
			$poll                       = WpPolls::get_poll_context( (string) $poll_data->answer_id, (int) $poll_data->poll_id );
			$poll['selected_answer_id'] = (int) $poll_data->answer_id;

			$context['response_type'] = 'live';
		}

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( 'poll_submitted' === $term ) {
			unset( $poll['selected_answer_id'] );
		}

		$context['pluggable_data'] = $poll;

		return $context;
	}

	/**
	 * Get ACF Custom fields list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_acf_post_field_list( $data ) {
		
		$post_id = $data['dynamic'];

		$args = [
			'post_id' => $post_id,
		];
		if ( ! is_numeric( $post_id ) ) {
			$args = [
				'post_type' => $post_id,
			];
		}
		$options = [];
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups_collection = acf_get_field_groups( $args );
			foreach ( $field_groups_collection as $field_group ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$field_groups[] = acf_get_fields( $field_group['key'] );
				}
			}
	
			if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {
				foreach ( $field_groups as $field_groups ) {
					foreach ( $field_groups as $field_group ) {
						$options[] = [
							'value' => $field_group['name'],
							'label' => $field_group['label'],
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get ACF Custom fields list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_acf_user_field_list( $data ) {
		
		if ( ! function_exists( 'acf_get_fields' ) ) {
			return [];
		}
		if ( ! function_exists( 'acf_get_field_groups' ) ) {
			return [];
		}
		$groups_user_form = [];
		$options          = [];
		if ( function_exists( 'acf_get_field_groups' ) ) {
			$field_groups = acf_get_field_groups();
			foreach ( $field_groups as $group ) {
				if ( ! empty( $group['location'] ) ) {
					foreach ( $group['location'] as $locations ) {
						foreach ( $locations as $location ) {
							if ( 'user_form' === $location['param'] ) {
								$groups_user_form[] = $group;
							}
						}
					}
				}
			}
			$field_groups = $groups_user_form;
			if ( empty( $field_groups ) ) {
				return [];
			}
			foreach ( $field_groups as $group ) {
				if ( function_exists( 'acf_get_fields' ) ) {
					$group_fields = acf_get_fields( $group['key'] );    
				}
				if ( ! empty( $group_fields ) ) {
					foreach ( $group_fields as $field ) {
						$options[] = [
							'value' => $field['name'],
							'label' => $field['label'],
						];
	
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Search Last Updated Field Data for ACF.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_acf_post_field_data( $data ) {
		$context = [];

		$field = ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		$post_type = $data['filter']['wp_post_type']['value'];
		$post      = $data['filter']['wp_post']['value'];

		if ( -1 === $field ) {
			$args = [
				'post_id' => $post,
			];
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$field_groups_collection = acf_get_field_groups( $args );   
			}
			if ( ! empty( $field_groups_collection ) ) {
				foreach ( $field_groups_collection as $field_group ) {
					if ( function_exists( 'acf_get_fields' ) ) {
						$field_groups[] = acf_get_fields( $field_group['key'] );
					}
				}
			}
			$fields = [];
			if ( ! empty( $field_groups ) && is_array( $field_groups ) ) {
				foreach ( $field_groups as $field_groups ) {
					$fields[] = $field_groups;
				}
			}
			if ( ! empty( $fields ) ) {
				$random_key = array_rand( $fields );
				$field      = $random_key;
			} else {
				$result = '';
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}
		if ( function_exists( ( 'get_field' ) ) ) {
			$result = get_field( $field, $post );
		}
		
		$response = [];
		if ( ! empty( $result ) ) {
			$response['pluggable_data'] = array_merge( [ $field => $result ], [ 'field_id' => $field ], [ 'post' => WordPress::get_post_context( $post ) ] );
			$response['response_type']  = 'live';
		} else {
			$response = json_decode( '{"response_type":"sample","pluggable_data":{"custom_description": "custom message", "ID": 1, "post_author": "1", "post_date": "2023-05-31 13:26:24", "post_date_gmt": "2023-05-31 13:26:24", "post_content": "", "post_title": "Test", "post_excerpt": "", "post_status": "publish", "comment_status": "open", "ping_status": "open", "post_password": "", "post_name": "test", "to_ping": "", "pinged": "", "post_modified": "2023-08-17 09:15:56", "post_modified_gmt": "2023-08-17 09:15:56", "post_content_filtered": "", "post_parent": 0, "guid": "https:\/\/example.com\/?p=1", "menu_order": 0, "post_type": "post", "post_mime_type": "", "comment_count": "2", "filter": "raw"}}', true );
		}

		return $response;
	}

	/**
	 * Search Last Updated User Field Data ACF.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_acf_user_field_data( $data ) {
		global $wpdb;

		$context = [];

		$field = (int) ( isset( $data['filter']['field_id']['value'] ) ? $data['filter']['field_id']['value'] : -1 );

		if ( -1 === $field ) {
			$groups_user_form = [];
			if ( function_exists( 'acf_get_field_groups' ) ) {
				$field_groups = acf_get_field_groups();
			}
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $group ) {
					if ( ! empty( $group['location'] ) ) {
						foreach ( $group['location'] as $locations ) {
							foreach ( $locations as $location ) {
								if ( 'user_form' === $location['param'] ) {
									$groups_user_form[] = $group;
								}
							}
						}
					}
				}
				$field_groups = $groups_user_form;
			}
			if ( empty( $field_groups ) ) {
				$result = '';
			}
			$fields = [];
			if ( ! empty( $field_groups ) ) {
				foreach ( $field_groups as $group ) {
					if ( function_exists( 'acf_get_fields' ) ) {
						$group_fields = acf_get_fields( $group['key'] );
					}
					if ( ! empty( $group_fields ) ) {
						foreach ( $group_fields as $field ) {
							$fields[] = $group_fields;          
						}
					}
				}
			}
			if ( ! empty( $fields ) ) {
				$random_key = array_rand( $fields );
				$field      = $random_key;
			} else {
				$result = '';
			}
		} else {
			$field = $data['filter']['field_id']['value'];
		}
		$users = get_users(
			[
				'fields'   => 'ID',
				'meta_key' => $field,
			]
		);

		if ( ! empty( $users ) ) {
			$user_random_key = array_rand( $users );
			$user_id         = $user_random_key;
			if ( function_exists( 'get_field' ) ) {
				$result = get_field( $field, 'user_' . $users[ $user_id ] );
			}
			$response = [];
			if ( ! empty( $result ) ) {
				$context                    = [
					'field_id' => $field,
					$field     => $result,
					'user'     => WordPress::get_user_context( $users[ $user_id ] ),
				];
				$response['pluggable_data'] = $context;
				$response['response_type']  = 'live';
			} else {
				$response = json_decode(
					'{
					"response_type": "sample",
					"pluggable_data": {
						"field_id": "gender",
						"user": {
							"wp_user_id": 114,
							"user_login": "test",
							"display_name": "test",
							"user_firstname": "test",
							"user_lastname": "test",
							"user_email": "test@test.com",
							"user_role": [ "subscriber" ]
						}
					}
				}',
					true 
				);
			}
		} else {
			$response = json_decode(
				'{
				"response_type": "sample",
				"pluggable_data": {
					"field_id": "gender",
					"user": {
						"wp_user_id": 114,
						"user_login": "test",
						"display_name": "test",
						"user_firstname": "test",
						"user_lastname": "test",
						"user_email": "test@test.com",
						"user_role": [ "subscriber" ]
					}
				}
			}',
				true 
			);
		}

		return $response;
	}

	/**
	 * Get WP Fusion Tags list.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_fusion_tag_list( $data ) {
		
		if ( ! function_exists( 'wp_fusion' ) ) {
			return [];
		}

		$options = [];
		$tags    = wp_fusion()->settings->get( 'available_tags' );

		if ( $tags ) {
			foreach ( $tags as $t_id => $tag ) {
				if ( is_array( $tag ) && isset( $tag['label'] ) ) {
					$options[] = [
						'value' => $t_id,
						'label' => $tag['label'],
					];
				} else {
					$options[] = [
						'value' => $t_id,
						'label' => $tag,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get list of events for Modern Events Calendar.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_events_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$args         = [
			'post_type'   => 'mec-events',
			'post_status' => [ 'publish', 'private' ],
		];
		$loop         = new WP_Query( $args );
		$events_count = count( $loop->posts );

		$args = [
			'post_type'      => 'mec-events',
			'posts_per_page' => $limit,
			'offset'         => $offset,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => [ 'publish', 'private' ],
		];

		$loop   = new WP_Query( $args );
		$events = $loop->posts;

		$options = [];
		if ( ! empty( $events ) ) {
			foreach ( $events as $event ) {
				if ( isset( $event->ID ) ) {
					$options[] = [
						'label' => get_the_title( $event ),
						'value' => $event->ID,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => $events_count > $limit && $events_count > $offset,
		];
	}

	/**
	 * Search tickets of MEC events.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_event_tickets( $data ) {
		$options  = [];
		$event_id = $data['dynamic'];

		$event_tickets = get_post_meta( $event_id, 'mec_tickets', true );

		if ( ! empty( $event_tickets ) && is_array( $event_tickets ) ) {
			foreach ( $event_tickets as $ticket_id => $event_ticket ) {
				if ( isset( $event_ticket['name'] ) ) {
					$options[] = [
						'label' => $event_ticket['name'],
						'value' => $ticket_id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_mec_triggers_last_data( $data ) {
		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$event = [
			'event_id'           => 1,
			'title'              => 'Sample Event',
			'description'        => 'Description of the sample event.',
			'categories'         => 'New, Sample',
			'start_date'         => 'September 13, 2023',
			'start_time'         => '8:00 AM',
			'end_date'           => 'September 13, 2023',
			'end_time'           => '11:00 AM',
			'location'           => 'City Hall',
			'organizer'          => 'John Doe',
			'cost'               => '5000',
			'featured_image_id'  => 1,
			'featured_image_url' => 'https://suretriggers.com/wp-content/uploads/2022/12/Screenshot_20221127_021332.png',
			'tickets'            => [
				[
					'id'          => 1,
					'name'        => 'Silver',
					'description' => 'Standard seat with reasonable price.',
					'price'       => '300',
					'price_label' => 'USD',
					'limit'       => '20',
				],
				[
					'id'          => 2,
					'name'        => 'Premium',
					'description' => 'VIP seat with high price.',
					'price'       => '500',
					'price_label' => 'USD',
					'limit'       => '10',
				],
			],
			'attendees'          => [
				[
					'id'    => 1,
					'email' => 'johndoe@test.com',
					'name'  => 'John Doe',
				],
				[
					'id'    => 2,
					'email' => 'adamsmith@test.com',
					'name'  => 'Adam Smith',
				],
			],
			'booking'            => [
				'title'               => 'johndoe@test.com - John Doe',
				'transaction_id'      => 'RSH59404',
				'amount_payable'      => '800',
				'price'               => '800',
				'time'                => '2023-09-07 06:40:32',
				'payment_gateway'     => 'Manual Pay',
				'confirmation_status' => 'Pending',
				'verification_status' => 'Verified',
				'attendees_count'     => 2,
			],
		];

		$term = isset( $data['search_term'] ) ? $data['search_term'] : '';

		$where = '';

		if ( 'cancelled' === $term ) {
			$where = 'WHERE verified = -1';
		} elseif ( 'confirmed' === $term ) {
			$where = 'WHERE confirmed = 1';
		} elseif ( 'pending' === $term ) {
			$where = 'WHERE confirmed = 0';
		}

		$event_id = (int) ( isset( $data['filter']['event_id']['value'] ) ? $data['filter']['event_id']['value'] : '-1' );

		if ( -1 !== $event_id ) {
			if ( ! empty( $where ) ) {
				$where .= ' AND event_id = ' . $event_id;
			} else {
				$where = 'WHERE event_id = ' . $event_id;
			}
		}

		$event_data = $wpdb->get_row( "SELECT booking_id FROM {$wpdb->prefix}mec_bookings $where ORDER BY id DESC LIMIT 1" ); // @phpcs:ignore

		if ( ! empty( $event_data ) ) {
			$event                    = ModernEventsCalendar::get_event_context( (int) $event_data->booking_id );
			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = $event;

		return $context;
	}

	/**
	 * Get form list Contact Form 7.
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_contact_form7_list( $data ) {
		
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => $limit,
				'offset'         => $offset,
			]
		);

		$all_posts = get_posts(
			[
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			]
		);

		$posts_count = count( $all_posts );

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Thrive Leads form list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_thrive_leads_forms_list( $data ) {
		$options = [];

		$lg_ids = get_posts(
			[
				'post_type'      => '_tcb_form_settings',
				'fields'         => 'id=>parent',
				'posts_per_page' => -1,
				'post_status'    => 'any',
			]
		);

		if ( function_exists( 'tve_leads_get_form_variations' ) ) {
			foreach ( $lg_ids as $lg_id => $lg_parent ) {
				$variations = tve_leads_get_form_variations( $lg_parent );
				foreach ( $variations as $variation ) {
					$options[] = [
						'label' => $variation['post_title'],
						'value' => $lg_parent,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get list for Woocommerce Subscriptions
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wc_subscription_variation_products( $data ) {
		$options = [];

		global $wpdb;

		if ( ! function_exists( 'wc_get_product' ) ) {
			return [];
		}
		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT posts.ID, posts.post_title FROM $wpdb->posts as posts
	LEFT JOIN $wpdb->term_relationships as rel ON (posts.ID = rel.object_id)
								WHERE rel.term_taxonomy_id IN (SELECT term_id FROM $wpdb->terms WHERE slug IN ('subscription','variable-subscription'))
									AND posts.post_type = %s
									AND posts.post_status = %s
								UNION ALL
								SELECT ID, post_title FROM $wpdb->posts
								WHERE post_type = %s
									AND post_status = %s
			ORDER BY post_title",
				'product',
				'publish',
				'shop_subscription',
				'publish'
			)
		);

		if ( $subscriptions ) {
			foreach ( $subscriptions as $product ) {
				$options[] = [
					'label' => $product->post_title . ' (#' . $product->ID . ')',
					'value' => (int) $product->ID,
				];
				$product_s = wc_get_product( $product->ID );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				if ( 'variable-subscription' == $product_s->product_type ) {
					$args = [
						'post_type'      => 'product_variation',
						'post_parent'    => $product->ID,
						'posts_per_page' => -1,
						'orderby'        => 'ID',
						'order'          => 'ASC',
						'post_status'    => 'publish',
					];

					$variations = get_posts( $args );

					foreach ( $variations as $var ) {
						$options[] = [
							'label' => $var->post_title . ' (#' . $var->ID . ')',
							'value' => $var->ID,
						];
					}
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get WS Forms form list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ws_forms_list( $data ) {
		$options = [];
		global $wpdb;

		$forms = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wsf_form", 'ARRAY_A' );    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $forms as $form ) {
			$options[] = [
				'label' => $form['label'],
				'value' => $form['id'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get Learndash Achievement list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ld_achievements_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'ld-achievement',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => [ 'publish' ],
			]
		);

		$posts_count = wp_count_posts( 'ld-achievement' )->publish;

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Get Advanced Ads list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_ads_list( $data ) {
		$page   = $data['page'];
		$limit  = Utilities::get_search_page_limit();
		$offset = $limit * ( $page - 1 );

		$posts = get_posts(
			[
				'post_type'      => 'advanced_ads',
				'posts_per_page' => $limit,
				'offset'         => $offset,
				'post_status'    => [ 'publish', 'draft' ],
			]
		);

		$posts_count = wp_count_posts( 'advanced_ads' )->publish;

		$options = [];

		if ( ! empty( $posts ) ) {
			foreach ( $posts as $post ) {
				$options[] = [
					'label' => get_the_title( $post->ID ),
					'value' => $post->ID,
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => $posts_count > $limit && $posts_count > $offset,
		];
	}

	/**
	 * Advanced Ads pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_ad_last_data( $data ) {
		$context = [];
		$args    = [
			'post_type'      => 'advanced_ads',
			'posts_per_page' => 1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		];

		if ( isset( $data['filter']['ad_status']['value'] ) ) {
			$post_status         = $data['filter']['ad_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
			$post_status         = $data['filter']['ad_new_status']['value'];
			$args['post_status'] = $post_status;
		}

		if ( isset( $data['filter']['ad_id']['value'] ) ) {
			$post_id = $data['filter']['ad_id']['value'];
			if ( -1 != $post_id ) {
				if ( $post_id > 0 ) {
					$args['p'] = $post_id;
				}
			}
		}

		$posts = get_posts( $args );
		if ( ! empty( $posts ) ) {
			$context['pluggable_data'] = $posts[0];
			$context['pluggable_data'] = WordPress::get_post_context( $posts[0]->ID );
			if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
				$context['pluggable_data']['ad_new_status'] = $posts[0]->post_status;
			}
			if ( isset( $data['filter']['ad_old_status']['value'] ) ) {
				$context['pluggable_data']['ad_old_status'] = $data['filter']['ad_old_status']['value'];
			}
			$context['pluggable_data']['ad_id'] = $posts[0]->ID;
			if ( isset( $data['filter']['ad_status']['value'] ) ) {
				$context['pluggable_data']['ad_status'] = $posts[0]->post_status;
			}
			$context['response_type'] = 'live';
		} else {
			$context['pluggable_data'] = [
				'ID'                    => 1,
				'post'                  => 1,
				'post_author'           => 1,
				'post_date'             => '2022-11-18 12:18:14',
				'post_date_gmt'         => '2022-11-18 12:18:14',
				'post_content'          => 'Ad Post Content',
				'post_title'            => 'Ad Post',
				'post_excerpt'          => '',
				'post_status'           => 'draft',
				'comment_status'        => 'open',
				'ping_status'           => 'open',
				'post_password'         => '',
				'post_name'             => 'ad-post',
				'to_ping'               => '',
				'pinged'                => '',
				'post_modified'         => '2022-11-18 12:18:14',
				'post_modified_gmt'     => '2022-11-18 12:18:14',
				'post_content_filtered' => '',
				'post_parent'           => 0,
				'guid'                  => 'https://example.com/ad-post/',
				'menu_order'            => 0,
				'post_type'             => 'advanced_ads',
				'post_mime_type'        => '',
				'comment_count'         => 0,
				'filter'                => 'raw',
			];
			if ( isset( $data['filter']['ad_new_status']['value'] ) ) {
				$context['pluggable_data']['ad_new_status'] = $data['filter']['ad_new_status']['value'];
			}
			if ( isset( $data['filter']['ad_old_status']['value'] ) ) {
				$context['pluggable_data']['ad_old_status'] = $data['filter']['ad_old_status']['value'];
			}
			$context['pluggable_data']['ad_id'] = 1;
			if ( isset( $data['filter']['ad_status']['value'] ) ) {
				$context['pluggable_data']['ad_status'] = $data['filter']['ad_status']['value'];
			}
			$context['response_type'] = 'sample';
		}

		return $context;
	}

	/**
	 * Get Newsletter lists
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_newsletter_lists( $data ) {
		
		$options = [];
		if ( class_exists( '\Newsletter' ) ) {

			$lists = \Newsletter::instance()->get_lists();

			if ( ! empty( $lists ) ) {
				foreach ( $lists as $list ) {
					$options[] = [
						'label' => $list->name,
						'value' => 'list_' . $list->id,
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Newsletter pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_newsletter_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$list = $data['filter']['list_id']['value'];

		if ( -1 == $list ) {
			$log = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . "newsletter where status='C' ORDER BY id DESC LIMIT 1" );
		} else {
			$num      = $list;
			$location = 1;
			$sql      = 'SELECT * FROM ' . $wpdb->prefix . "newsletter WHERE $num = %d AND status = 'C' ORDER BY id DESC LIMIT 1";
			$log      = $wpdb->get_results( $wpdb->prepare( $sql, $location ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $log ) ) {
			$lists_arr = get_option( 'newsletter_lists' );
			if ( -1 == $list ) {
				foreach ( $log[0] as $key => $val ) {
					if ( defined( 'NEWSLETTER_LIST_MAX' ) ) {
						for ( $i = 1; $i <= NEWSLETTER_LIST_MAX; $i++ ) {
							$list_key = "list_$i";
							
							if ( $key == $list_key ) {
								if ( 1 == $val ) {
									$context['pluggable_data']['list_id'] = $key;
									if ( is_array( $lists_arr ) ) {
										if ( isset( $lists_arr[ $key ] ) ) {
											$list_name                              = $lists_arr[ $key ];
											$context['pluggable_data']['list_name'] = $list_name;
										}
									}   
									continue;
								}
							}
						}
					}
					if ( 'email' == $key ) {
						$context['pluggable_data']['email'] = $val;
					}
				}
			} else {
				$context['pluggable_data']['list_id'] = $list;
				$context['pluggable_data']['email']   = $log[0]['email'];
				if ( is_array( $lists_arr ) ) {
					if ( isset( $lists_arr[ $list ] ) ) {
						$list_name                              = $lists_arr[ $list ];
						$context['pluggable_data']['list_name'] = $list_name;
					}
				}
			}
			$context['response_type'] = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"list_id": "list_1","email": "johnd@mailinator.com","list_name": "Contact List"}}', true );
		}

		return $context;
	}

	/**
	 * Get wpForo Forum list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_forum_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$forums = WPF()->forum->get_forums( [ 'type' => 'forum' ] );

		$options = [];

		if ( ! empty( $forums ) ) {
			foreach ( $forums as $forum ) {
				$options[] = [
					'label' => $forum['title'],
					'value' => $forum['forumid'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Topic list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_topic_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$forum_id = $data['dynamic'];

		$topics = WPF()->topic->get_topics( [ 'forumid' => $forum_id ] );

		$options = [];

		if ( ! empty( $topics ) ) {
			foreach ( $topics as $topic ) {
				$options[] = [
					'label' => $topic['title'],
					'value' => $topic['topicid'],
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Groups list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_user_groups_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$usergroups = WPF()->usergroup->get_usergroups();

		$options = [];

		if ( ! empty( $usergroups ) ) {
			foreach ( $usergroups as $group ) {
				$options[] = [
					'label' => $group['name'],
					'value' => intval( $group['groupid'] ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get wpForo Reputation list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_wp_foro_reputation_list( $data ) {

		if ( ! function_exists( 'WPF' ) ) {
			return [];
		}

		$levels  = WPF()->member->levels();
		$options = [];

		if ( ! empty( $levels ) ) {
			foreach ( $levels as $level ) {
				$options[] = [
					'label' => esc_attr__( 'Level', 'suretriggers' ) . ' ' . $level . ' - ' . WPF()->member->rating( $level, 'title' ),
					'value' => strval( $level ),
				];
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * WPForo new topic pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wpforo_topic_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$forum_id = $data['filter']['forum_id']['value'];

		if ( -1 == $forum_id ) {
			$results = $wpdb->get_results( 'SELECT * from ' . $wpdb->prefix . 'wpforo_topics WHERE closed = 0 ORDER BY topicid DESC LIMIT 1', ARRAY_A );
		} else {
			$forum   = $forum_id;
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'wpforo_topics WHERE forumid = %d AND closed = 0 ORDER BY topicid DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $forum ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$context['pluggable_data']['forum_id'] = $results[0]['forumid'];
			$context['pluggable_data']['topic_id'] = $results[0]['topicid'];
			
			if ( function_exists( 'WPF' ) ) {
				$context['pluggable_data']['forum'] = WPF()->forum->get_forum( $results[0]['forumid'] );
				$context['pluggable_data']['topic'] = WPF()->topic->get_topic( $results[0]['topicid'] );
			}
			$context['pluggable_data']['user'] = WordPress::get_user_context( $results[0]['userid'] );
			$context['response_type']          = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"forum_id": "2","topic_id": "1","forum": {"forumid": "2","title": "Main Forum","slug": "main-forum","description": "This is a simple parent forum","parentid": "1","icon": "fas fa-comments","cover": 0,"cover_height": "150","last_topicid": "1","last_postid": "2","last_userid": "4","last_post_date": "2023-09-19 11:40:19","topics": "1","posts": "2","permissions": "a:5:{i:1;s:4:\"full\";i:2;s:9:\"moderator\";i:3;s:8:\"standard\";i:4;s:9:\"read_only\";i:5;s:8:\"standard\";}","meta_key": "","meta_desc": "","status": "1","is_cat": "0","layout": "4","order": "0","color": "#888888","url": "https:\/\/example.com\/community\/main-forum\/","cover_url": ""},"topic": {"topicid": "1","forumid": "2","first_postid": "1","userid": "4","title": "New Forum topic title","slug": "new-forum-topic-title","created": "2023-09-19 11:39:01","modified": "2023-09-19 11:40:19","last_post": "2","posts": "2","votes": "0","answers": "0","views": "1","meta_key": "","meta_desc": "","type": "0","solved": "0","closed": "0","has_attach": "0","private": "0","status": "0","name": "","email": "","prefix": "","tags": "","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","short_url": "https:\/\/example.com\/community\/topicid\/1\/"},"user": {"wp_user_id": 4,"user_login": "john@d.com","display_name": "john@d.com","user_firstname": "john","user_lastname": "d","user_email": "john@d.com","user_role": ["customer"]}}}', true );// @phpcs:ignore
		}

		return $context;
	}

	/**
	 * WpForo Topic Reply pluggable data.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_pluggables_wpforo_topic_reply_last_data( $data ) {
		$context = [];
		global $wpdb;
		
		$forum_id = $data['filter']['forum_id']['value'];
		$topic_id = $data['filter']['topic_id']['value'];

		if ( -1 == $forum_id && -1 != $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts WHERE topicid = %d ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $topic_id ), ARRAY_A );// @phpcs:ignore
		} elseif ( -1 != $forum_id && -1 == $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts WHERE forumid = %d ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql, $forum_id ), ARRAY_A );// @phpcs:ignore
		} elseif ( -1 == $forum_id && -1 == $topic_id ) {
			$sql     = 'SELECT * from ' . $wpdb->prefix . 'wpforo_posts ORDER BY postid DESC LIMIT 1';
			$results = $wpdb->get_results( $wpdb->prepare( $sql ), ARRAY_A );// @phpcs:ignore
		} else {
			$sql     = 'SELECT * FROM ' . $wpdb->prefix . 'wpforo_posts WHERE forumid = %d AND topicid = %d ORDER BY postid DESC LIMIT 1';
			$results      = $wpdb->get_results( $wpdb->prepare( $sql, $forum_id, $topic_id ), ARRAY_A );// @phpcs:ignore
		}

		if ( ! empty( $results ) ) {
			$context['pluggable_data']['forum_id'] = $results[0]['forumid'];
			$context['pluggable_data']['topic_id'] = $results[0]['topicid'];
			if ( function_exists( 'WPF' ) ) {
				$context['pluggable_data']['forum'] = WPF()->forum->get_forum( $results[0]['forumid'] );
				$context['pluggable_data']['topic'] = WPF()->topic->get_topic( $results[0]['topicid'] );
				$context['pluggable_data']['reply'] = WPF()->post->get_post( $results[0]['postid'] );
			}
			$context['pluggable_data']['user'] = WordPress::get_user_context( $results[0]['userid'] );
			$context['response_type']          = 'live';
		} else {
			$context = json_decode( '{"response_type":"sample","pluggable_data":{"forum_id": "2","topic_id": "1","forum": {"forumid": "2","title": "Main Forum","slug": "main-forum","description": "This is a simple parent forum","parentid": "1","icon": "fas fa-comments","cover": 0,"cover_height": "150","last_topicid": "1","last_postid": "2","last_userid": "4","last_post_date": "2023-09-19 11:40:19","topics": "1","posts": "2","permissions": "a:5:{i:1;s:4:\"full\";i:2;s:9:\"moderator\";i:3;s:8:\"standard\";i:4;s:9:\"read_only\";i:5;s:8:\"standard\";}","meta_key": "","meta_desc": "","status": "1","is_cat": "0","layout": "4","order": "0","color": "#888888","url": "https:\/\/example.com\/community\/main-forum\/","cover_url": ""},"topic": {"topicid": "1","forumid": "2","first_postid": "1","userid": "4","title": "New Forum topic title","slug": "new-forum-topic-title","created": "2023-09-19 11:39:01","modified": "2023-09-19 11:40:19","last_post": "2","posts": "2","votes": "0","answers": "0","views": "1","meta_key": "","meta_desc": "","type": "0","solved": "0","closed": "0","has_attach": "0","private": "0","status": "0","name": "","email": "","prefix": "","tags": "","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/","short_url": "https:\/\/example.com\/community\/topicid\/1\/"},"reply_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","reply": {"postid": "2","parentid": "0","forumid": "2","topicid": "1","userid": 4,"title": "RE: New Forum topic title","body": "<p>new reply<\/p>","created": "2023-09-19 11:40:19","modified": "2023-09-19 11:40:19","likes": "0","votes": "0","is_answer": "0","is_first_post": "0","status": "0","name": "","email": "","private": "0","root": "-1","url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","full_url": "https:\/\/example.com\/community\/main-forum\/new-forum-topic-title\/#post-2","short_url": "https:\/\/example.com\/community\/postid\/2\/"},"user": {"wp_user_id": 4,"user_login": "john@d.com","display_name": "john@d.com","user_firstname": "john","user_lastname": "d","user_email": "john@d.com","user_role": ["customer"]}}}', true );// @phpcs:ignore
		}

		return $context;
	}

	/**
	 * Get RafflePress Giveaways list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_rp_giveaways_list( $data ) {

		global $wpdb;
		$options = [];

		$giveaways = $wpdb->get_results( "SELECT id,name FROM {$wpdb->prefix}rafflepress_giveaways WHERE deleted_at is null ORDER BY name ASC", ARRAY_A );
		foreach ( $giveaways as $giveaway ) {
			$options[] = [
				'label' => $giveaway['name'],
				'value' => $giveaway['id'],
			];
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get RafflePress Giveaway Actions list
	 *
	 * @param array $data data.
	 *
	 * @return array
	 */
	public function search_rp_giveaway_actions_list( $data ) {

		global $wpdb;
		$options = [];

		$giveaway = $wpdb->get_row( $wpdb->prepare( "SELECT settings FROM {$wpdb->prefix}rafflepress_giveaways WHERE id=%d", $data['dynamic'] ), ARRAY_A );

		if ( is_array( $giveaway ) && isset( $giveaway['settings'] ) ) {
			$settings = json_decode( $giveaway['settings'], true );

			if ( is_array( $settings ) && isset( $settings['entry_options'] ) ) {
				foreach ( $settings['entry_options'] as $action ) {
					$options[] = [
						'label' => $action['name'],
						'value' => $action['id'],
					];
				}
			}
		}

		return [
			'options' => $options,
			'hasMore' => false,
		];
	}

	/**
	 * Get last data for trigger.
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_raffle_press_triggers_last_data( $data ) {

		global $wpdb;

		$context                  = [];
		$context['response_type'] = 'sample';

		$pluggable_data = [
			'giveaway_id'               => 1,
			'giveaway_title'            => 'Sample Giveaway (ID#1)',
			'giveaway_start_date'       => 'September 20, 2023',
			'giveaway_end_date'         => 'October 6, 2023',
			'giveaway_entries'          => 9,
			'giveaway_user_count'       => 3,
			'giveaway_status'           => 'Active',
			'contestant_name'           => 'John Doe',
			'contestant_email'          => 'john_doe@gmail.com',
			'contestant_email_verified' => 'Yes',
			'action_id'                 => '0jnex',
			'action_name'               => 'Visit us on Instagram',
		];

		$giveaway_id = isset( $data['filter']['giveaway_id'] ) ? $data['filter']['giveaway_id']['value'] : null;
		$action_id   = isset( $data['filter']['action_id'] ) ? $data['filter']['action_id']['value'] : null;

		$query = "SELECT contestant_id, giveaway_id, action_id, meta FROM {$wpdb->prefix}rafflepress_entries";

		if ( $giveaway_id && -1 != $giveaway_id ) {
			$query .= ' WHERE giveaway_id = ' . $giveaway_id;

			if ( $action_id ) {
				$query .= " AND action_id = '" . $action_id . "'";
			}
		}

		$query .= ' ORDER BY created_at DESC LIMIT 1';

		$giveaway_data = $wpdb->get_row( $query, ARRAY_A ); // @phpcs:ignore

		if ( ! empty( $giveaway_data ) ) {
			$pluggable_data = array_merge(
				RafflePress::get_giveaway_context( $giveaway_data['giveaway_id'] ),
				RafflePress::get_contestant_context( $giveaway_data['contestant_id'] )
			);

			$pluggable_data['performed_action_id'] = isset( $giveaway_data['action_id'] ) ? $giveaway_data['action_id'] : 0;

			$giveaway_meta                           = isset( $giveaway_data['meta'] ) ? json_decode( $giveaway_data['meta'], true ) : [];
			$pluggable_data['performed_action_name'] = is_array( $giveaway_meta ) && isset( $giveaway_meta['action'] ) ? $giveaway_meta['action'] : '';

			$context['response_type'] = 'live';
		}

		$context['pluggable_data'] = $pluggable_data;

		return $context;
	}

	/**
	 * Get last data for trigger
	 *
	 * @param array $data data.
	 * @return array
	 */
	public function search_woo_commerce_shipstation_triggers_last_data( $data ) {
		$context                   = [];
		$context['response_type']  = 'sample';
		$context['pluggable_data'] = [];

        $order_sample_data = json_decode( '{"product_id": 47,"id": 49,"parent_id": 0,"status": "completed","currency": "USD","version": "8.1.1","prices_include_tax": false,"date_created": {"date": "2023-09-23 10:28:00.000000","timezone_type": 1,"timezone": "+00:00"},"date_modified": {"date": "2023-09-23 10:29:55.000000","timezone_type": 1,"timezone": "+00:00"},"discount_total": "0","discount_tax": "0", "shipping_total": "0","shipping_tax": "0","cart_tax": "0","total": "1.00","total_tax": "0","customer_id": 1,"order_key": "wc_order_64IaGkeQKRXdm","billing": {"first_name": "john","last_name": "d","company": "","address_1": "123 Main Street","address_2": "","city": "London","state": "Greater London","postcode": "SW1A 1AA","country": "GB","email": "johnd@d.com","phone": "9878988766"},"shipping": {"first_name": "","last_name": "","company": "","address_1": "","address_2": "","city": "","state": "","postcode": "","country": "","phone": ""},"payment_method": "cod","payment_method_title": "Cash on delivery","transaction_id": "","customer_ip_address": "182.184.87.226","customer_user_agent": "Mozilla\/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit\/537.36 (KHTML, like Gecko) Chrome\/116.0.0.0 Safari\/537.36","created_via": "checkout","customer_note": "", "date_completed": { "date": "2023-09-23 10:29:55.000000", "timezone_type": 1, "timezone": "+00:00" }, "date_paid": { "date": "2023-09-23 10:29:55.000000", "timezone_type": 1, "timezone": "+00:00" }, "cart_hash": "ac073abcdc9a52025211d0fad52cfa46", "order_stock_reduced": true, "download_permissions_granted": true, "new_order_email_sent": true, "recorded_sales": true, "recorded_coupon_usage_counts": true, "number": "49", "meta_data": [{ "id": 1206, "key":"is_vat_exempt", "value": "no"},{"id": 1207,"key": "weglot_language","value": "en"},{"id": 1208,"key": "_shipstation_exported","value": "yes"},{"id": 1209,"key": "_shipstation_shipped_item_count","value": "1"}],"line_items": {"id": "25","order_id": "49","name": "New Product","product_id": "47","variation_id": "0","quantity": "1","tax_class": "","subtotal": "1","subtotal_tax": "0","total": "1","total_tax": "0","taxes": "","meta_data": []},"tax_lines": [],"shipping_lines": [],"fee_lines": [],"coupon_lines": [],"coupons": [],"products": [{"id": 25,"order_id": 49,"name": "New Product","product_id": 47,"variation_id": 0,"quantity": 1,"tax_class": "","subtotal": "1","subtotal_tax": "0","total": "1","total_tax": "0","taxes": {"total": [],"subtotal": []},"meta_data": []}],"quantity": "1","wp_user_id": 1,"user_login": "johnd","display_name": "johnd","user_firstname": "john","user_lastname": "d","user_email": "johnd@d.com","user_role": ["administrator"],"shipping_tracking_number": "","shipping_carrier": "","ship_date": "",}', true ); //phpcs:ignore

		$product_id = (int) ( isset( $data['filter']['product_id']['value'] ) ? $data['filter']['product_id']['value'] : -1 );
		$condition  = $data['filter']['condition_compare']['value'];
		$price      = $data['filter']['price']['value'];
		$term       = isset( $data['search_term'] ) ? $data['search_term'] : '';

		if ( 'order_shipped' === $term ) {
			$orders = wc_get_orders( 
				[ 
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'status'      => 'completed',
					'meta_query'  => [
						[
							'key'     => '_shipstation_shipped_item_count',
							'compare' => 'EXISTS',
						],
					],
				] 
			);
			if ( count( $orders ) > 0 ) {
				$order_id    = $orders[0]->get_id();
				$order       = wc_get_order( $order_id );
				$user_id     = $order->get_customer_id();
				$product_ids = [];
				if ( $order ) {
					$items = $order->get_items();
					foreach ( $items as $item ) {
						if ( method_exists( $item, 'get_product_id' ) ) {
							$product_ids[] = $item->get_product_id();
						}
					}

					foreach ( $product_ids as $product_id ) {
						$context['product_id'] = $product_id;
					}
				}
				$context                             = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);
				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $context;
		} elseif ( 'specific_product_order_shipped' === $term ) {
			if ( -1 != $product_id ) {
				$product_ids = [ $product_id ];
				$orders      = wc_get_orders( 
					[ 
						'numberposts' => 1,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'status'      => 'completed',
						'meta_query'  => [
							[
								'key'     => '_shipstation_shipped_item_count',
								'compare' => 'EXISTS',
							],
							[
								'key'     => '_product_id',
								'value'   => $product_ids,
								'compare' => 'IN',
							],
						],
					] 
				);
			} else {
				$orders = wc_get_orders( 
					[ 
						'numberposts' => 1,
						'orderby'     => 'date',
						'order'       => 'DESC',
						'status'      => 'completed',
						'meta_query'  => [
							[
								'key'     => '_shipstation_shipped_item_count',
								'compare' => 'EXISTS',
							],
						],
					] 
				);
			}
			if ( count( $orders ) > 0 ) {
				$order_id   = $orders[0]->get_id();
				$order      = wc_get_order( $order_id );
				$user_id    = $order->get_customer_id();
				$productids = [];
				$items      = $order->get_items();
				foreach ( $items as $item ) {
					if ( method_exists( $item, 'get_product_id' ) ) {
						$productids[] = $item->get_product_id();
					}
				}
				$context = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);

				foreach ( $productids as $product_id ) {
					$context['product_id'] = $product_id;
				}

				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}
			
			$context['pluggable_data'] = $context;
		} elseif ( 'specific_amount_order_shipped' === $term ) {
			$orders = wc_get_orders( 
				[ 
					'numberposts' => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'status'      => 'completed',
					'meta_query'  => [
						[
							'key'     => '_shipstation_shipped_item_count',
							'compare' => 'EXISTS',
						],
						[
							'key'     => '_order_total',
							'value'   => $price,
							'compare' => $condition,
						],
					],
				] 
			);
			if ( count( $orders ) > 0 ) {
				$order_id    = $orders[0]->get_id();
				$order       = wc_get_order( $order_id );
				$user_id     = $order->get_customer_id();
				$product_ids = [];
				$items       = $order->get_items();
				foreach ( $items as $item ) {
					if ( method_exists( $item, 'get_product_id' ) ) {
						$product_ids[] = $item->get_product_id();
					}
				}
				$context = array_merge(
					WooCommerce::get_order_context( $order_id ),
					WordPress::get_user_context( $user_id )
				);

				foreach ( $product_ids as $product_id ) {
					$context['product_id'] = $product_id;
				}

				$context['shipping_tracking_number'] = $order->get_meta( '_tracking_number', true );
				$context['shipping_carrier']         = $order->get_meta( '_tracking_provider', true );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$timestamp = strtotime( $order->get_meta( '_date_shipped', true ) );
				/**
				 *
				 * Ignore line
				 *
				 * @phpstan-ignore-next-line
				 */
				$date                     = date_i18n( get_option( 'date_format' ), $timestamp );
				$context['ship_date']     = $date;
				$context['response_type'] = 'live';
			}

			$context['pluggable_data'] = $context;
		}

		return $context;
	}
}

GlobalSearchController::get_instance();
