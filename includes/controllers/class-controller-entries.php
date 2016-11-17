<?php

class GF_REST_Entries_Controller extends GF_REST_Form_Entries_Controller {

	/**
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @var string
	 */
	public $rest_base = 'entries';

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 */
	public function register_routes() {

		$namespace = $this->namespace;

		$base = $this->rest_base;

		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<entry_id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'                => array(),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( false ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<entry_id>[0-9;]+$)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/(?P<entry_id>[0-9;]+)/fields/(?P<field_ids>[\S]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(),
			),
		) );

	}

	/**
	 * Get a collection of entries
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		return parent::get_items( $request );
	}

	/**
	 * Get one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {

		$entry_id = $request->get_param( 'entry_id' );

		$field_ids = $this->maybe_explode_url_param( $request, 'field_ids' );

		$labels = $request['labels'];

		$entry = GFAPI::get_entry( $entry_id );
		if ( ! is_wp_error( $entry ) ) {
			$entry = $this->maybe_json_encode_list_fields( $entry );
			if ( ! empty( $field_ids ) && ( ! empty( $entry ) ) ) {
				$entry = $this->filter_entry_fields( $entry, $field_ids );
			}
		}

		if ( $labels ) {
			$form = GFAPI::get_form( $entry['form_id'] );
			$entry['labels'] = $this->get_entry_labels( $form );
		}

		$data = $this->prepare_item_for_response( $entry, $request );

		return $data;
	}

	/**
	 * Create one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {
		return parent::create_item( $request );
	}

	/**
	 * Update one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$item = $this->prepare_item_for_database( $request );

		$data = GFAPI::update_entry( $item );

		if ( is_wp_error( $data ) ) {
			$status = $this->get_error_status( $data );
			return new WP_Error( $data->get_error_code(), $data->get_error_message(), array( 'status' => $status ) );
		}

		$message = empty( $data ) ? __( 'Entries updated successfully', 'gravityforms' ) : __( 'Entry updated successfully', 'gravityforms' );

		return new WP_REST_Response( $message, 200 );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function delete_item( $request ) {
		$entry_id = $request['entry_id'];
		$data = GFAPI::delete_entry( $entry_id );

		if ( is_wp_error( $data ) ) {
			$message = $data->get_error_message();
			return new WP_Error( 'gf_cannot_delete', $message, array( 'status' => 500 ) );
		}

		return new WP_REST_Response( __( 'Entry deleted successfully', 'gravityforms' ), 200 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Filters the capability required to get entries via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_get_entries', 'gravityforms_view_entries', $request );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		return $this->get_items_permissions_check( $request );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to create entries via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_post_entries', 'gravityforms_edit_entries' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to update a specific item
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to update entries via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_put_entries', 'gravityforms_edit_entries' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to delete a specific item
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to delete entries via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_delete_entries', 'gravityforms_delete_entries' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Prepare the item for create or update operation
	 *
	 * @since  1.0-beta-1
	 * @access protected
	 *
	 * @param WP_REST_Request $request Request object
	 *
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
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param mixed           $item    WordPress representation of the item.
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return mixed
	 */
	public function prepare_item_for_response( $item, $request ) {

		$response = new WP_REST_Response( $item, 200 );
		return $response;
	}
}
