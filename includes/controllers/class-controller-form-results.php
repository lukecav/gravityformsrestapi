<?php

class GF_REST_Form_Results_Controller extends GF_REST_Controller {

	public $rest_base = 'forms/(?P<form_id>[\d]+)/results';

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
		) );

		register_rest_route( $namespace, '/' . $base . '/schema', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get a collection of results
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$form_id = $request['form_id'];
		$search_criteria = $this->parse_entry_search_params( $request );
		$args = array(
			'page_size' => 100,
			'time_limit' => 5,
			'wait' => 5,
		);
		$data = GFAPI::get_results( $form_id, $search_criteria, $args );
		$response = $this->prepare_item_for_response( $data, $request );
		return $response;
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_items_permissions_check( $request ) {
		/**
		 * Filters the capability required to get form results via the web API.
		 *
		 * @since 1.9.2
		 */
		$capability = apply_filters( 'gform_web_api_capability_get_results', 'gravityforms_view_entries' );
		return GFAPI::current_user_can_any( $capability );
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

