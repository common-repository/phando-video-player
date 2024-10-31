<?php

/**
 * Handles login activies for phando user
 */
class Phando_Login {

	// Creates phando login page
	public static function create_page() {
		add_submenu_page(
			null,
			'Phando Authorization',
			'phando video player Login',
			'manage_options',
			'phandovideoplayer-login',
			array( 'Phando_Login', 'perform_login_page_action' )
		);
	}

	// Adds phando login page content
	public static function perform_login_page_action() {

		if ( ! current_user_can( 'manage_options' ) ) {
			self::show_error_message( 'You do not have sufficient privileges to access this page.' );
			return;
		}

		if ( ! isset( $_POST['phandousername'], $_POST['phandopassword'] ) ) {
			self::login_form();
			return;
		}

		// Check the nonce (counter XSRF)
		if ( isset( $_POST['_wpnonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'phandovideoplayer-login-nonce' ) ) {
				self::show_error_message( 'Failed to verify form data.' );
				self::login_form();
				return;
			}
		}

		$username = isset( $_POST['phandousername'] ) ? sanitize_text_field( wp_unslash( $_POST['phandousername'] ) ) : false;
		$password = isset( $_POST['phandopassword'] ) ? sanitize_text_field( wp_unslash( $_POST['phandopassword'] ) ) : false;

		$res = self::verify_phando_user( $username, $password );

		if ( null === $res ) {
			self::show_error_message( 'Some error has occurred .please try again' );
			self::login_form();
		} elseif ( 0 === $res ) {
			self::show_error_message( 'Invalid credentials' );
			self::login_form();
		} elseif ( 1 === $res ) {
			self::show_success_message( "Login successful. You can now view/add/search videos on your website directly from your phando account. Video list is available on post/page widget as well as on 'Add Media' window " );
			update_option( 'phando_video_player_username', $username );
			update_option( 'phando_video_player_password', $password );
		}
		return;
	}

	// Show error messages
	public static function show_error_message( $error_message ) {
		?>
			<div class='notice notice-error is-dismissible'>
			<p>
			<strong><?php echo esc_html( $error_message ); ?></strong>
			</p>
			</div>
		<?php
	}

	// Show success message
	public static function show_success_message( $message ) {
		?>
			<div class='notice notice-success is-dismissible'>
			<p>
			<strong><?php echo esc_html( $message ); ?></strong>
			</p>
			</div>
		<?php
	}

	// Generates phando login form content
	public static function login_form() {
		?>
			<div class="wrap">
				<h2>Plugin Authorization</h2>
				<form method="post" action="">
					<p>
						You need to authorize phando player plugin to use your phando account.
						Don't have phando account
						<a target="_blank" href="http://phando.com/user/register">Sign up</a>.
					</p>
					<p>
						Enter your Phando account credentials
					</p>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"> Username / Email</th>
							<td><input type="text" name="phandousername" /></td>
						</tr>
						<tr valign="top">
							<th scope="row">Password</th>
							<td><input type="password" name="phandopassword" /></td>
						</tr>
					</table>

					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'phandovideoplayer-login-nonce' ) ); ?>" />
					<p class="submit">
						<input type="submit" class="button-primary" value="Authorize plugin" />
					</p>

				</form>
			</div>
		<?php
	}

	// Verifies phando user
	public static function verify_phando_user( $username, $password ) {

		$api      = new Phando_Video_Player_API( $username, $password, null );
		$response = $api->auth_call( '/auth/authenticate' );

		return Phando_API_Controller::api_response_check( $response );
	}

}
