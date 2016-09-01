<?php

class GF_REST_Form_Entries_Controller extends GF_REST_Controller {

	public $rest_base = 'forms/(?P<form_id>[\S]+)/entries';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		$namespace = $this->namespace;

		$base = $this->rest_base;

		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_collection_params(),
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
		) );
		register_rest_route( $namespace, '/' . $base . '/fields/(?P<field_ids>[\S]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => array(),
			),
		) );
	}

	/**
	 * Get a collection of entries
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$entry_id = $this->maybe_explode_url_param( $request, 'entry_id' );

		$field_ids = $this->maybe_explode_url_param( $request, 'field_ids' );

		$data = array();
		if ( $entry_id ) {
			foreach ( $entry_id as $id ) {
				$result = GFAPI::get_entry( $id );
				if ( ! is_wp_error( $result ) ) {
					$result                = $this->maybe_json_encode_list_fields( $result );
					$data[ $id ] = $result;
					if ( ! empty( $field_ids ) && ( ! empty( $data[ $id ] ) ) ) {
						$data[ $id ] = $this->filter_entry_fields( $data[ $id ], $field_ids );
					}
				}
			}
		} else {
			$entry_search_params = $this->parse_entry_search_params( $request );

			$entry_count = 0;

			$form_id = $this->maybe_explode_url_param( $request, 'form_id' );

			if ( empty( $form_id ) ) {
				$form_id = 0;
			}

			$entries = GFAPI::get_entries( $form_id, $entry_search_params['search_criteria'], $entry_search_params['sorting'], $entry_search_params['paging'], $entry_count );

			$data = array();
			if ( ! is_wp_error( $entries ) ) {
				foreach ( $entries as &$entry ) {
					$entry = $this->maybe_json_encode_list_fields( $entry );
					if ( ! empty( $field_ids ) && ! empty( $entry ) ) {
						$entry = $this->filter_entry_fields( $entry, $field_ids );
					}
				}
				$data = array( 'total_count' => $entry_count, 'entries' => $entries );
			}
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		$entry = $this->prepare_item_for_database( $request );

		$form_id = $this->maybe_explode_url_param( $request, 'form_id' );

		if ( $form_id && ! is_array( $form_id ) ) {
			$entry['form_id'] = absint( $form_id );
		}

		$entry_id = GFAPI::add_entry( $entry );

		if ( is_wp_error( $entry_id ) ) {
			$status = $this->get_error_status( $entry_id );
			return new WP_Error( $entry_id->get_error_code(), $entry_id->get_error_message(), array( 'status' => $status ) );
		}

		return new WP_REST_Response( $entry_id, 201 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {

		$capability = apply_filters( 'gform_web_api_capability_get_entries', 'gravityforms_view_entries', $request );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		$capability = apply_filters( 'gform_web_api_capability_post_entries', 'gravityforms_edit_entries' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|array $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		$entry = $request->get_json_params();
		if ( empty( $entry ) ) {
			$entry = $request->get_body_params();
		}

		$entry = $this->maybe_serialize_list_fields( $entry );
		return $entry;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'sorting'                   => array(
				'description'        => 'The sorting criteria.',
				'type'               => 'array',
			),
			'paging'               => array(
				'description'        => 'The paging criteria.',
				'type'               => 'array',
			),
			'search'                 => array(
				'description'        => 'The search criteria.',
				'type'               => 'string',
			),
		);
	}

	/**
	 * Get the Entry schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'entry',
			'type'       => 'object',
			'properties' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'gravityforms' ),
					'type'        => 'integer',
					'readonly'    => true,
				),
				'form_id' => array(
					'description' => __( 'The Form ID for the entry.', 'gravityforms' ),
					'type'        => 'integer',
					'required'    => true,
					'readonly'    => false,
				),
				'date_created' => array(
					'description' => __( 'The date the entry was created, in UTC.', 'gravityforms' ),
					'type'        => 'date-time',
					'readonly'    => false,
				),
				'is_starred' => array(
					'description' => __( 'Whether the entry is starred.', 'gravityforms' ),
					'type'        => 'integer',
					'readonly'    => false,
				),
				'is_read' => array(
					'description' => __( 'Whether the entry has been read.', 'gravityforms' ),
					'type'        => 'integer',
					'readonly'    => false,
				),
				'ip' => array(
					'description' => __( 'The IP address of the entry creator.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'source_url' => array(
					'description' => __( 'The URL where the form was embedded.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'user_agent' => array(
					'description' => __( 'The user agent string for the browser used to submit the entry.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'payment_status' => array(
					'description' => __( 'The status of the payment, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'payment_date' => array(
					'description' => __( 'The date of the payment, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'payment_amount' => array(
					'description' => __( 'The amount of the payment, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'payment_method' => array(
					'description' => __( 'The payment method for the payment, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'transaction_id' => array(
					'description' => __( 'The transaction ID for the payment, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'is_fulfilled' => array(
					'description' => __( 'Whether the transaction has been fulfilled, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'created_by' => array(
					'description' => __( 'The user ID of the entry submitter.', 'gravityforms' ),
					'type'        => 'integer',
					'readonly'    => false,
				),
				'transaction_type' => array(
					'description' => __( 'The type of the transaction, if applicable.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
				'status' => array(
					'description' => __( 'The status of the entry.', 'gravityforms' ),
					'type'        => 'string',
					'readonly'    => false,
				),
			),
		);
		return $schema;
	}
}
