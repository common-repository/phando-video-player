<?php

/**
 * Handles API call between phando and WordPress website
 */
class Phando_API_Controller {

	// creates ajax API proxy
	public static function ajax_api_proxy() {

		$phando_proxy_methods = array(
			'/auth/medialist',
			'/auth/mediabysearch',
		);

		$phando_api_instance = self::get_instance();

		if ( null === $phando_api_instance ) {
			return;
		}

		$method = ! empty( $_GET['method'] ) ? sanitize_text_field( wp_unslash( $_GET['method'] ) ) : null ;

		if ( ! in_array( $method, $phando_proxy_methods, true ) ) {
			return;
		}

		$params = array();

		if ( '/auth/mediabysearch' === $method ) {
			$params['query']       = isset( $_GET['query'] ) ? sanitize_text_field( wp_unslash( $_GET['query'] ) ) : null;
			$params['language_id'] = isset( $_GET['language_id'] ) ? sanitize_text_field( wp_unslash( $_GET['language_id'] ) ) : null;
		}

		$params['limit'] = isset( $_GET['limit'] ) ? sanitize_text_field( wp_unslash( $_GET['limit'] ) ) : null;
		$params['order'] = isset( $_GET['order'] ) ? sanitize_text_field( wp_unslash( $_GET['order'] ) ) : 'desc';
		$response        = $phando_api_instance->video_call( $method, $params );

		echo wp_json_encode( $response );

	}

	// Checks API response
	public static function api_response_check( $response ) {

		if ( isset( $response ) && 200 === $response['response']['code'] ) {

			if ( isset( $response['body'] ) ) {

				$response_body_array = json_decode( $response['body'], true );

				if ( array_key_exists( 'error', $response_body_array ) ) {
					return 0;
				} elseif ( array_key_exists( 'token', $response_body_array ) ) {
					return 1;
				}
			}
		}

		return null;
	}

	// creates phando instance
	public static function get_instance() {

		$phando_username = get_option( 'phando_video_player_username' );
		$phando_password = get_option( 'phando_video_player_password' );
		$token           = get_option( 'phando_video_player_token' ) ? get_option( 'phando_video_player_token' ) : null;

		if ( $phando_username && $phando_password ) {
			return new Phando_Video_Player_API( $phando_username, $phando_password, $token );
		} else {
			phandovideoplayer_log( 'Unable to create API Instance' );
		}

	}

	// Generates authentication token used in API call
	public static function generate_auth_token() {

		$load_on_pages = array(
			'media-upload.php',
			'post.php',
			'post-new.php',
		);

		global $pagenow;
		$hook_suffix         = $pagenow;
		if ( ! in_array( $hook_suffix, $load_on_pages, true ) ) {
			return;
		}

		$phando_api_instance = self::get_instance();
		$api_response        = $phando_api_instance->auth_call( '/auth/authenticate' );
		$is_response_ok      = self::api_response_check( $api_response );

		if ( $is_response_ok ) {
			$token = json_decode( $api_response['body'] )->{'token'};
			update_option( 'phando_video_player_token', $token );
		} else {
			update_option( 'phando_video_player_token', '' );
		}

	}

}
