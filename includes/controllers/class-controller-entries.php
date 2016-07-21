<?php

class GF_REST_Entries_Controller extends GF_REST_Form_Entries_Controller {

	public $rest_base = 'entries';

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
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( true ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<entry_id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(
					'context'          => array(
						'default'      => 'view',
					),
				),
			),
			array(
				'methods'         => WP_REST_Server::EDITABLE,
				'callback'        => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'            => $this->get_endpoint_args_for_item_schema( false ),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'     => array(
					'force'    => array(
						'default'      => false,
					),
				),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<entry_id>[\S]+)', array(
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
		return parent::get_items( $request );
	}

	/**
	 * Get one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$entry_id = $request->get_param( 'entry_id' );

		$item = GFAPI::get_entry( $entry_id );
		if ( ! is_wp_error( $item ) ) {
			$item = $this->maybe_json_encode_list_fields( $item );
			if ( ! empty( $field_id ) && ( ! empty( $item ) ) ) {
				$item = $this->filter_entry_fields( $item, $field_id );
			}
		}

		$data = $this->prepare_item_for_response( $item, $request );

		return $data;
	}

	/**
	 * Create one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {
		return parent::create_item( $request );
	}

	/**
	 * Update one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		$data = GFAPI::update_entry( $item );

		if ( is_wp_error( $data ) ) {
			$status = $this->get_error_status( $data );
			new WP_Error( $data->get_error_code(), $data->get_error_message(), array( 'status' => $status ) );
		}

		$message = empty( $data ) ? __( 'Entries updated successfully', 'gravityforms' ) : __( 'Entry updated successfully', 'gravityforms' );

		return new WP_REST_Response( $message, 200 );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {
		$entry_id = $request['entry_id'];
		$data = GFAPI::delete_entry( $entry_id );

		if ( is_wp_error( $data ) ) {
			$message = $data->get_error_message();
			new WP_Error( 'gf_cannot_delete', $message, array( 'status' => 500 ) );
		}

		return new WP_REST_Response( __( 'Entry deleted successfully', 'gravityforms' ), 200 );
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
	 * Check if a given request has access to get a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
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
	 * Check if a given request has access to update a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		$capability = apply_filters( 'gform_web_api_capability_put_entries', 'gravityforms_edit_entries' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		$capability = apply_filters( 'gform_web_api_capability_delete_entries', 'gravityforms_delete_entries' );
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

	/**
	 * Prepare the item for the REST response
	 *
	 * @param mixed $item WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {

		$response = new WP_REST_Response( $item, 200 );
		return $response;
	}

	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'sorting'                   => array(
				'description'        => 'Current page of the collection.',
				'type'               => 'array',
				'sanitize_callback'  => 'is_array',
			),
			'paging'               => array(
				'description'        => 'Maximum number of items to be returned in result set.',
				'type'               => 'array',
				'sanitize_callback'  => 'is_array',
			),
			'search'                 => array(
				'description'        => 'The search criteria.',
				'type'               => 'string',
				'sanitize_callback'  => 'sanitize_text_field',
			),
		);
	}
}
