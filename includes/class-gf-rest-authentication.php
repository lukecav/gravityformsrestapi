<?php
/**
 * REST API Authentication
 *
 * @author   Rocketgenius
 * @category API
 * @package  GravityForms/API
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GF_REST_Authentication {

	/**
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @var string
	 */
	public $namespace = 'gf/v2';

	/**
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @var null
	 */
	public $authentication_error = null;

	/**
	 * Initialize authentication actions.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 */
	public function __construct() {
		add_filter( 'determine_current_user', array( $this, 'authenticate' ), 20 );
		add_filter( 'rest_authentication_errors', array( $this, 'check_authentication_error' ) );
	}

	/**
	 * Check if is request to our REST API.
	 *
	 * @since  1.0-beta-1
	 * @access protected
	 *
	 * @return bool
	 */
	protected function is_request_to_rest_api() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		// Check if our endpoint.
		$is_gf_api = false !== strpos( $_SERVER['REQUEST_URI'], rest_get_url_prefix() . '/gf/' );

		return $is_gf_api;
	}

	/**
	 * Authenticate user.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param int|false $user_id User ID if one has been determined, false otherwise.
	 * @return int|false
	 */
	public function authenticate( $user_id ) {
		// Do not authenticate twice and check if is a request to our endpoint in the WP REST API.
		if ( ! empty( $user_id ) || ! $this->is_request_to_rest_api() ) {
			return $user_id;
		}

		return $this->perform_oauth_authentication();
	}

	/**
	 * Check for authentication error.
	 *
	 * @since  1.0-beta-1
	 * @access public
	 *
	 * @param WP_Error|null|bool $error
	 * @return WP_Error|null|bool
	 */
	public function check_authentication_error( $error ) {

		if ( ! empty( $error ) ) {
			return $error;
		}

		return $this->authentication_error;
	}

	/**
	 * Perform OAuth 1.0a "one-legged" (http://oauthbible.com/#oauth-10a-one-legged) authentication for non-SSL requests.
	 *
	 * This is required so API credentials cannot be sniffed or intercepted when making API requests over plain HTTP.
	 *
	 * This follows the spec for simple OAuth 1.0a authentication (RFC 5849) as closely as possible, with two exceptions:
	 *
	 * 1) There is no token associated with request/responses, only consumer keys/secrets are used.
	 *
	 * 2) The OAuth parameters are included as part of the request query string instead of part of the Authorization header,
	 *    This is because there is no cross-OS function within PHP to get the raw Authorization header.
	 *
	 * @link http://tools.ietf.org/html/rfc5849 for the full spec.
	 *
	 * @since  1.0-beta-1
	 * @access private
	 *
	 * @return int|bool
	 */
	private function perform_oauth_authentication() {
		$params = array( 'api_key', 'expires', 'signature' );

		// Check for required OAuth parameters.
		foreach ( $params as $param ) {
			if ( empty( $_GET[ $param ] ) ) {
				return false;
			}
		}

		// Fetch user by api key
		$user = $this->get_user_data_by_api_key( $_GET['api_key'] );

		if ( empty( $user ) ) {
			$this->authentication_error = new WP_Error( 'gravityforms_rest_authentication_error', __( 'API Key is invalid.', 'gravityforms' ), array( 'status' => 401 ) );

			return false;
		}

		// Perform OAuth validation.
		$this->authentication_error = $this->check_oauth_signature( $user['private_key'] );
		if ( is_wp_error( $this->authentication_error ) ) {
			return false;
		}

		return $user['user_id'];
	}

	/**
	 * Verify that the consumer-provided request signature matches our generated signature,
	 * this ensures the consumer has a valid key/secret.
	 *
	 * @since  1.0-beta-1
	 * @access private
	 *
	 * @param $private_key
	 * @return null|WP_Error
	 */
	private function check_oauth_signature( $private_key ) {

		$request_path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
		$wp_base      = get_home_url( null, '/', 'relative' );
		if ( substr( $request_path, 0, strlen( $wp_base ) ) === $wp_base ) {
			$request_path = substr( $request_path, strlen( $wp_base ) );
		}

		$rest_url_prefix = rest_get_url_prefix();
		$prefix = $rest_url_prefix . '/' . $this->namespace . '/';

		$route = str_replace( $prefix, '', $request_path );

		$route = untrailingslashit( $route );

		$expires = (int) rgget( 'expires' );

		$api_key = rgget( 'api_key' );

		$method  = strtoupper( $_SERVER['REQUEST_METHOD'] );

		$signature = rgget( 'signature' );

		$string_to_check = sprintf( '%s:%s:%s:%s', $api_key, $method, $route, $expires );

		$calculated_sig = $this->calculate_signature( $string_to_check, $private_key );

		if ( time() >= $expires ) {
			$is_valid = false;
		} else {
			$is_valid = hash_equals( $signature, $calculated_sig ) || hash_equals( $signature, rawurlencode( $calculated_sig ) );
		}

		if ( ! $is_valid ) {
			return new WP_Error( 'gravityforms_rest_authentication_error', __( 'Invalid Signature - provided signature does not match.', 'gravityforms' ), array( 'status' => 401 ) );
		}
		return $is_valid;
	}

	/**
	 *
	 * @since  1.0-beta-1
	 * @access private
	 *
	 * @param $string
	 * @param $private_key
	 *
	 * @return string
	 */
	private function calculate_signature( $string, $private_key ) {
		$hash = hash_hmac( 'sha1', $string, $private_key, true );
		$sig  = base64_encode( $hash );

		return $sig;
	}

	/**
	 * Return the user data for the given consumer_key.
	 *
	 * @since  1.0-beta-1
	 * @access private
	 *
	 * @param string $public_key
	 * @return array
	 */
	private function get_user_data_by_api_key( $public_key ) {

		$settings            = get_option( 'gravityformsaddon_gravityformswebapi_settings' );;
		$enabled             = rgar( $settings, 'enabled' );
		$settings_public_key = rgar( $settings, 'public_key' );

		if ( ! $enabled ) {
			return false;
		}

		if ( $settings_public_key === $public_key ) {
			$private_key = rgar( $settings, 'private_key' );
			if ( empty( $private_key ) ) {
				return false;
			}

			$impersonate_user = rgar( $settings, 'impersonate_account' );
			$user_id = $impersonate_user;
		} else {

			// Grab the private key from the user meta.
			$args = array(
				'meta_value' => $public_key,
			);

			$users = get_users( $args );

			if ( empty( $users ) ) {
				return false;
			}

			$user = $users[0];

			$private_key = $user->gf_private_key;

			if ( empty( $private_key ) ) {
				return false;
			}

			$user_id = $user->ID;
		}

		if ( empty( $user_id ) || empty( $private_key ) ) {
			return false;
		}

		$details = array(
			'user_id' => $user_id,
			'private_key' => $private_key,
		);

		return $details;
	}
}

new GF_REST_Authentication();
