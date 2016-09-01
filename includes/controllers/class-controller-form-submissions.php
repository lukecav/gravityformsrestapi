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
				'args'            => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
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

		$params = $request->get_json_params();
		if ( empty( $params ) ) {
			$params = $request->get_body_params();
		}

		$input_values = $params['input_values'];
		$field_values = isset( $params['field_values'] ) ? $params['field_values'] : array();
		$target_page  = isset( $params['target_page'] ) ? $params['target_page'] : 0;
		$source_page  = isset( $params['source_page'] ) ? $params['source_page'] : 0;

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
		return array();
	}

	/**
	 * Get the Entry schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'form-submission',
			'type'       => 'object',
			'properties' => array(
				'input_values'                   => array(
					'description'        => __( 'An array of input values', 'gravityforms' ),
					'type'               => 'array',
				),
				'field_values'               => array(
					'description'        => __( 'The field values.', 'gravityforms' ),
					'type'               => 'array',
				),
				'target_page'                 => array(
					'description'        => 'The target page number.',
					'type'               => 'integer',
				),
				'source_page'                 => array(
					'description'        => 'The source page number.',
					'type'               => 'integer',
				),
			),
		);
		return $schema;
	}
}

