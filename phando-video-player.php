<?php
/**
 * @package Phando Video Player
 * @version 2.1.2
 */
/*
Plugin Name: Phando Video Player
Plugin URI: https://wordpress.org/plugins/phando-video-player/
Description: This plugin easily embeds phando videos into your website
Version: 2.1.2
Author: Phando
Author URI: https://corp.phando.com/
*/

define( 'PHANDO_VIDEO_PLAYER_PLUGIN_DIR', dirname( __FILE__ ) . '/' );
define( 'PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR', dirname( __FILE__ ) . '/lib/' );
define( 'PHANDO_VIDEO_PLAYER_PLUGIN_MINIMUM_PHP_VERSION', '5.4.0' );
define( 'PHANDO_VIDEO_PLAYER_PLUGIN_VERSION', '2.1.2' );
define( 'PHANDO_VIDEO_PLAYER_TYPE', 'default' );
define( 'PHANDO_VIDEO_PLAYER_SHOW_PAGE_WIDGET', true );
define( 'PHANDO_VIDEO_PLAYER_LOGIN_SLUG', 'phandovideoplayer-login' );
define( 'PHANDO_VIDEO_PLAYER_LOGOUT_SLUG', 'phandovideoplayer-logout' );
define( 'PHANDO_VIDEO_PLAYER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PHANDO_VIDEO_PLAYER_API_URL', 'http://phando.com' );

/** Number of videos to be shown in widget */
define( 'PHANDO_VIDEO_PLAYER_VIDEOS_LIST_SIZE', 5 );
define( 'PHANDO_VIDEO_PLAYER_SHORTCODE_STRING', 'phandoplayer' );

require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'utils.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'admin/class-phando-admin.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'auth/class-phando-login.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'auth/class-phando-logout.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'endpoints/class-phando-video-player-api.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'endpoints/class-phando-api-controller.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'shortcodes/class-phando-video-shortcode.php' );
require_once( PHANDO_VIDEO_PLAYER_PLUGIN_LIB_DIR . 'widgets/class-phando-video-listing-widget.php' );

//Add constants value in options table
function phandovideoplayer_add_options() {

	add_option( 'phando_video_player_plugin_version', PHANDO_VIDEO_PLAYER_PLUGIN_VERSION );
	add_option( 'phando_video_player_type', PHANDO_VIDEO_PLAYER_TYPE );
	add_option( 'phando_video_player_show_page_widget', PHANDO_VIDEO_PLAYER_SHOW_PAGE_WIDGET );
	add_option( 'phando_video_player_videos_list_size', PHANDO_VIDEO_PLAYER_VIDEOS_LIST_SIZE );
	add_option( 'phando_video_player_shortcode_string', PHANDO_VIDEO_PLAYER_SHORTCODE_STRING );

}

if ( version_compare( PHP_VERSION, PHANDO_VIDEO_PLAYER_PLUGIN_MINIMUM_PHP_VERSION, '<' ) ) {
	add_action( 'admin_notices', array( 'Phando_Admin', 'show_minimum_php_version_notice' ) );
	return;
} elseif ( get_option( 'phando_video_player_username' ) ) {
	add_action( 'admin_enqueue_scripts', array( 'Phando_API_Controller', 'generate_auth_token' ) );
} else {
	add_action( 'admin_notices', array( 'Phando_Admin', 'show_login_notice' ) );
}

if ( defined( 'WPCOM_IS_VIP_ENV' ) && ( true === WPCOM_IS_VIP_ENV ) ) {
	if ( ! get_option( phando_video_player_type ) ) {
		phandovideoplayer_add_options();
	}
} else {
	register_activation_hook( __FILE__, 'phandovideoplayer_add_options' );
}

add_action( 'admin_menu', array( 'Phando_Admin', 'settings_init' ) );
add_action( 'init', array( 'Phando_Video_Shortcode', 'create_video_shortcode' ) );

add_action( 'wp_ajax_phandovideoplayer_ajax_api_proxy', function() {
	if ( isset( $_GET['method'] ) ) {
		Phando_API_Controller::ajax_api_proxy();
		wp_die();
	}
} );

add_action( 'admin_enqueue_scripts', array( 'Phando_Admin', 'load_styles' ) );
add_filter( 'media_upload_tabs', 'phandovideoplayer_media_tab' );

// Add phando video tab in - Add media window
function phandovideoplayer_media_tab( $tabs ) {
	if ( get_option( 'phando_video_player_username' ) ) {
		$tabs['phandoVideoPlayer_uploadtab'] = 'Phando Videos';
		return $tabs;
	}
}

add_action( 'media_upload_phandoVideoPlayer_uploadtab', array( 'Phando_Video_Listing_Widget', 'add_upload_tab_video_widget' ) );
add_action( 'admin_menu', array( 'Phando_Login', 'create_page' ) );
add_action( 'admin_menu', array( 'Phando_Logout', 'create_page' ) );
add_action( 'admin_menu', array( 'Phando_Video_Listing_Widget', 'init_hook' ) );
