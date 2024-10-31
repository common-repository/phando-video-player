<?php

/**
 * Handles logout activies for phando user
 */
class Phando_Logout {

	// Creates phando logout page
	public static function create_page() {
		add_submenu_page(
			null,
			'Phando Deauthorization',
			'phando video player Logout',
			'manage_options',
			'phandovideoplayer-logout',
			array( 'Phando_Logout', 'perform_logout_page_action' )
		);
	}

	// Adds phando logout page content
	public static function perform_logout_page_action() {

		if ( isset( $_POST['_wpnonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'phandovideoplayer-logout-nonce' ) ) {
				return;
			}
		}

		if ( ! isset( $_POST['deauthorizephando'] ) ) {
			self::logout_form();
			return;
		}

		update_option( 'phando_video_player_username', '' );
		update_option( 'phando_video_player_password', '' );
		update_option( 'phando_video_player_token', '' );

		$login_url = get_admin_url( null, 'admin.php?page=' . PHANDO_VIDEO_PLAYER_LOGIN_SLUG );
		?>
			<h2> Deauthorization successful</h2>
			<p>
				You can again connect with phando account <a href="<?php echo esc_attr( $login_url ); ?>">Authorize</a>.
			</p>
		<?php
	}

	// Generates phando logout form content
	public static function logout_form() {
		?>
			<div class="wrap">
				<h1>Deauthorize Phando plugin</h1>
				<form method="post" action="">
					<p>
						You can disconnect your phando account.<br />
						<strong>On deauthorizing the plugin, videos would not show up.</strong>
					</p>
					<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'phandovideoplayer-logout-nonce' ) ); ?>" />
					<p class="submit"><input type="submit" name="deauthorizephando" class="button-primary" value="Deuthorize" /></p>
				</form>
			</div>
		<?php
	}

}
