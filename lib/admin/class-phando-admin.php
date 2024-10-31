<?php
/**
	* Adds pando admin settings and styles
	*/

class Phando_Admin {

	// Show minimum php version needed as notice
	public static function show_minimum_php_version_notice() {

		$class = 'notice notice-error is-dismissible';

		echo '<div class="' . esc_attr( $class ) . '">';
		echo '<p>';
		echo 'You are using PHP version ';
		echo '<strong>' . esc_html( PHP_VERSION ) . '. </strong>';
		echo 'You need atleast ';
		echo '<strong>' . esc_html( PHANDO_VIDEO_PLAYER_PLUGIN_MINIMUM_PHP_VERSION ) . '</strong>';
		echo ' to use Phando video player plugin.';
		echo '</p>';
		echo '</div>';

	}

	// Show need to login notice
	public static function show_login_notice() {

		if ( isset( $_GET['page'] ) && 'phandovideoplayer-login' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) { // Input var okay
				return;
		} else {

				$class         = 'notice notice-warning is-dismissible';
				$login_url     = get_admin_url( null, 'admin.php?page=' . PHANDO_VIDEO_PLAYER_LOGIN_SLUG );

				echo '<div class="' . esc_attr( $class ) . '">';
				echo '<p>';
				echo '
					<strong>You need to
					<a href="' . esc_url( $login_url ) . '">authorize</a>
					Phando video plugin.
					</strong>
				';
				echo '</p>';
				echo '</div>';

		}

	}

	// Load styles
	public static function load_styles( $hook_suffix ) {

		$load_on_pages = array(
			'media-upload-popup',
			'post.php',
			'post-new.php',
		);

		if ( ! in_array( $hook_suffix, $load_on_pages, true ) ) {
			return;
		}

		wp_register_style( 'phandovideoplayer_css', PHANDO_VIDEO_PLAYER_PLUGIN_URL . 'assets/css/phando.css' );
		wp_enqueue_style( 'phandovideoplayer_css' );
		$logic_url            = PHANDO_VIDEO_PLAYER_PLUGIN_URL . 'assets/js/api-call.js';
		wp_enqueue_script( 'logic_phando_script', $logic_url );
		$num_videos_list_size = ( get_option( 'phando_video_player_videos_list_size' ) ? get_option( 'phando_video_player_videos_list_size' ) : 0 );

		?>
			<script type="text/javascript">
				var phandoVideoFields           = {};
				phandoVideoFields.videoListSize = <?php echo esc_js( $num_videos_list_size ); ?>;
				phandoVideoFields.pluginUrl     = "<?php echo esc_js( PHANDO_VIDEO_PLAYER_PLUGIN_URL ); ?>";
			</script>
		<?php
	}

	// Call actions needed for settings page
	public static function settings_init() {

		self::add_settings_page();
		self::add_settings_page_section();
		self::add_fields();

	}

	// Adds settings page
	public static function add_settings_page() {
		add_options_page(
			'phando video player settings',
			'phando video player',
			'manage_options',
			'phando_video_player_settings',
			array( 'Phando_Admin', 'settings_page' )
		);
	}


	public static function add_settings_page_section() {
		add_settings_section(
			'phando_video_player_settings_section',
			null,
			'__return_true',
			'phando_video_player_settings'
		);
	}


	// Adds fields on settings page
	public static function add_fields() {

		add_settings_field( 'phando_video_authorization', 'Authorization ', array( 'Phando_Admin', 'authorization_field' ), 'phando_video_player_settings', 'phando_video_player_settings_section' );

		if ( get_option( 'phando_video_player_username' ) ) {

			add_settings_field( 'phando_video_player_videos_list_size', 'No. of videos in widget', array( 'Phando_Admin', 'videos_list_size' ), 'phando_video_player_settings', 'phando_video_player_settings_section' );
			add_settings_field( 'phando_video_player_show_page_widget', 'Hide / Show videos on page widget', array( 'Phando_Admin', 'show_video_listing_page_widget' ), 'phando_video_player_settings', 'phando_video_player_settings_section' );
			register_setting( 'phando_video_player_settings', 'phando_video_player_videos_list_size', 'absint' );
			register_setting( 'phando_video_player_settings', 'phando_video_player_show_page_widget', array( 'Phando_Admin', 'validate_boolean_value' ) );

		}

	}

	// Validates boolean values
	public static function validate_boolean_value( $value ) {

		if ( $value ) {
			return true;
		}
		return false;

	}

	// Shows video list size field on settings page
	public static function videos_list_size() {

		$num_videos = get_option( 'phando_video_player_videos_list_size', PHANDO_VIDEO_PLAYER_VIDEOS_LIST_SIZE );
		echo '<input id="phando_video_player_videos_list_size" class="small-text" type="text" value="' . absint( $num_videos ) . '" name="phando_video_player_videos_list_size">videos';

	}

	// Shows authorization fields
	public static function authorization_field() {

		$logout_url  = get_admin_url( null, 'admin.php?page=' . PHANDO_VIDEO_PLAYER_LOGOUT_SLUG );
		$phando_user = '';

		if ( get_option( 'phando_video_player_username' ) ) {
			$phando_user = get_option( 'phando_video_player_username' );
			?>
				Authorized with <strong><?php echo esc_html( $phando_user ); ?></strong> .<a href="<?php echo esc_url( $logout_url ); ?>"> Deauthorize </a>
			<?php
		} // End if().
		else {
			$login_url = get_admin_url( null, 'admin.php?page=' . PHANDO_VIDEO_PLAYER_LOGIN_SLUG );
			?>
				Need <a href="<?php echo esc_url( $login_url ); ?>">authorization</a> to use Phando plugin
			<?php
		} //End else.
	}

	// Generates html for settings page
	public static function settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			return null;
		}
		?>
			<div class="wrap">
			<h1> Phando video settings</h1>
			<form method="post" action="options.php">
		<?php
		settings_fields( 'phando_video_player_settings' );
		do_settings_sections( 'phando_video_player_settings' );

		if ( get_option( 'phando_video_player_username' ) ) {
			submit_button();
		}
		?>
			</form>
			</div>
		<?php

	}

	// Generates html for settings field - whether to show videos list widget
	public static function show_video_listing_page_widget() {

		$show_videos_page_widget = get_option( 'phando_video_player_show_page_widget', PHANDO_VIDEO_PLAYER_SHOW_PAGE_WIDGET );
		$is_checked              = $show_videos_page_widget ? 'checked' : '';
		?>
			<input aria-describedby="phando-show-page-widget" type="checkbox" name="phando_video_player_show_page_widget" value="true" id="phando_video_player_show_page_widget" <?php echo esc_html( $is_checked ); ?> />
			<p class="description" id="phando-show-page-widget">
			Note: Video list will always be accessible from 'Add Media' window.
			</p>
		<?php

	}

}
