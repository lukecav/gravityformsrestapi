<?php

class GF_REST_Forms_Controller extends GF_REST_Controller {

	/**
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @var string
	 */
	public $rest_base = 'forms';

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
		register_rest_route( $namespace, '/' . $base . '/(?P<id>[\d]+)', array(
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

		register_rest_route( $namespace, '/' . $base . '/(?P<form_id>[0-9;]+$)', array(
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
	 * Get a collection of items.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {

		$form_id = $this->maybe_explode_url_param( $request, 'form_id' );

		$data = array();
		if ( $form_id && is_array( $form_id ) ) {
			foreach ( $form_id as $id ) {
				$form = GFAPI::get_form( $id );
				$data[ $id ] = $form;
			}
		} else {
			$forms = GFFormsModel::get_forms( true );
			foreach ( $forms as $form ) {
				$form_id              = $form->id;
				$totals               = GFFormsModel::get_form_counts( $form_id );
				$form_info            = array(
					'id'      => $form_id,
					'title'   => $form->title,
					'entries' => rgar( $totals, 'total' ),
				);
				$data[ $form_id ] = $form_info;
			}
		}

		return new WP_REST_Response( $data, 200 );
	}

	/**
	 * Get one item from the collection.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		//get parameters from request
		$form_id = $request['id'];
		$form = GFAPI::get_form( $form_id );


		//return a response or error based on some conditional
		if ( $form ) {
			return new WP_REST_Response( $form, 200 );
		} else {
			return new WP_Error( 'gf_not_found', __( 'Form not found', 'gravityforms' ) );
		}
	}

	/**
	 * Create one item from the collection.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function create_item( $request ) {

		$form = $this->prepare_item_for_database( $request );

		if ( is_wp_error( $form ) ) {
			return new WP_Error( $form->get_error_code(), $form->get_error_message(), array( 'status' => 400 ) );
		}

		$form_id = GFAPI::add_form( $form );

		if ( is_wp_error( $form_id ) ) {
			$status = $this->get_error_status( $form_id );
			return new WP_Error( $form_id->get_error_code(), $form_id->get_error_message(), array( 'status' => $status ) );
		}

		return new WP_REST_Response( $form_id, 201 );
	}

	/**
	 * Update one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function update_item( $request ) {
		$form_id = $request['id'];
		$form = $this->prepare_item_for_database( $request );

		$result = GFAPI::update_form( $form, $form_id );

		if ( is_wp_error( $result ) ) {
			$status = $this->get_error_status( $result );
			return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => $status ) );
		}

		return new WP_REST_Response( __( 'Form updated successfully', 'gravityforms' ), 200 );
	}

	/**
	 * Delete one item from the collection
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Request
	 */
	public function delete_item( $request ) {
		$form_id = $request['id'];
		$result = GFAPI::delete_form( $form_id );

		if ( is_wp_error( $result ) ) {
			$status = $this->get_error_status( $result );
			return new WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => $status ) );
		}

		return new WP_REST_Response( __( 'Form deleted successfully', 'gravityforms' ), 200 );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Filters the capability required to get forms via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_get_forms', 'gravityforms_edit_forms' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to get a specific item
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to get forms via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_get_forms', 'gravityforms_edit_forms' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to create items
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function create_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to create forms via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_post_forms', 'gravityforms_create_form' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to update a specific item.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function update_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to update forms via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_put_forms', 'gravityforms_create_form' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Check if a given request has access to delete a specific item.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function delete_item_permissions_check( $request ) {
		/**
		 * Filters the capability required to delete forms via the REST API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_delete_forms', 'gravityforms_delete_forms' );
		return GFAPI::current_user_can_any( $capability );
	}

	/**
	 * Prepare the item for create or update operation.
	 *
	 * The Form object must be sent as a JSON string in order to preserve boolean values.
	 *
	 * @since  1.0-beta-1
	 * @access protected
	 *
	 * @param WP_REST_Request $request Request object
	 * @return WP_Error|object $prepared_item
	 */
	protected function prepare_item_for_database( $request ) {
		$form = $request->get_json_params();
		if ( ! $form ) {

			$form_json = $request->get_body_params();

			if ( empty( $form_json ) || is_array( $form_json ) ) {
				return new WP_Error( 'missing_form_json', __( 'The Form object must be sent as a JSON string in the request body with the content-type header set to application/json.', 'gravityforms' ) );
			}

			$form = json_decode( $form_json, true );
		}

		return $form;
	}

	/**
	 * Prepare the item for the REST response
	 *
	 * @since  1.0-beta-1
	 * @access public
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
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'page'                   => array(
				'description'        => 'Current page of the collection.',
				'type'               => 'integer',
				'default'            => 1,
				'sanitize_callback'  => 'absint',
			),
			'per_page'               => array(
				'description'        => 'Maximum number of items to be returned in result set.',
				'type'               => 'integer',
				'default'            => 10,
				'sanitize_callback'  => 'absint',
			),
			'search'                 => array(
				'description'        => 'The search criteria.',
				'type'               => 'array',
				'sanitize_callback'  => 'sanitize_text_field',
			),
		);
	}
}
