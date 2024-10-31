<?php

/**
*-----------------------------------------------------
* Phando_Video_Player_API class used for making api calls
* For Phando API documentation refer http://phando.com/apidoc/
*-----------------------------------------------------
*/

class Phando_Video_Player_API {

	private $_username, $_password;
	private $_url;
	private $_library;
	private $_accesstoken;

	// Constructor initializes value to username, password and auth token
	function __construct( $username, $password, $token ) {

		$this->_username     = $username;
		$this->_password     = $password;
		$this->_accesstoken  = $token;
		$this->_url          = PHANDO_VIDEO_PLAYER_API_URL;

		if ( defined( 'WPCOM_IS_VIP_ENV' ) && ( true === WPCOM_IS_VIP_ENV ) ) {
			$this->_library = 'wpvip';
		} else {
			$this->_library = 'wp';
		}

	}

	// Returns url with query parameters
	public function call_url( $call, $args = array() ) {
		return $this->_url . $call . '?' . http_build_query( $this->arguments( $args ), '', '&', PHP_QUERY_RFC3986 );
	}

	// Returns combined arguments
	private function arguments( $args ) {
		$args['email']     = $this->_username;
		$args['password']  = $this->_password;
		return $args;
	}

	// Makes remote phando API POST Call
	public function auth_call( $call, $args = array() ) {
		$url      = $this->call_url( $call, $args );
		$response = wp_remote_post( $url, array(
			'timeout'  => 30,
		) );
		return $response;
	}

	// Makes remote phando API GET call
	public function video_call( $call, $args = array() ) {

		$url         = $this->call_url( $call, $args );
		$response    = null;
		$auth_token  = 'Bearer ' . $this->_accesstoken;

		if ( 'wpvip' === $this->_library ) {

			$response = vip_safe_wp_remote_get( $url, null, 3, 1, 20, array(
				'headers' => array(
					'Authorization' => $auth_token,
				),
			));

		} else {
			$response = wp_remote_get( $url, array(
				'timeout' => 30,
				'headers' => array(
					'Authorization' => $auth_token,
				),
			));
		}

		if ( is_wp_error( $response ) || null ) {
			return 'Error: call to Phando player';
		}

		$response          = wp_remote_retrieve_body( $response );
		$decoded_response  = json_decode( $response, true );
		return $decoded_response;
	}

}
