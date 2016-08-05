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
				'args'            => array(),
			),
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
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
		register_rest_route( $namespace, '/' . $base . '/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_public_item_schema' ),
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
		$entry = $request->get_body_params();
		$entry = $this->maybe_serialize_list_fields( $entry );
		return $entry;
	}
}
