<?php

class GF_REST_Form_Submissions_Controller extends GF_REST_Controller {

	public $rest_base = 'forms/(?P<form_id>[\d]+)/submissions';

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {

		$namespace = $this->namespace;

		$base = $this->rest_base;

		register_rest_route( $namespace, '/' . $base, array(
			array(
				'methods'         => WP_REST_Server::CREATABLE,
				'callback'        => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'            => $this->get_collection_params(),
			),
		) );

		register_rest_route( $namespace, '/' . $base . '/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Create one item from the collection.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function create_item( $request ) {
		$form_id = $request['form_id'];

		$input_values = $request['input_values'];
		$field_values = $request['field_values'];
		$target_page  = $request['target_page'];
		$source_page  = $request['source_page'];

		$result = GFAPI::submit_form( $form_id, $input_values, $field_values, $target_page, $source_page );

		$response = $this->prepare_item_for_response( $result, $request );

		return $response;
	}

	/**
	 * Check if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|boolean
	 */
	public function create_item_permissions_check( $request ) {
		return true;
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

