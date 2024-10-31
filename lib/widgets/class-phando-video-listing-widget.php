<?php

/**
 * Creates phando video listing widget on post/pages
 */
class Phando_Video_Listing_Widget {

	//Adds video listing widget on posts/pages
	public static function init_hook() {

		if ( get_option( 'phando_video_player_show_page_widget' ) && get_option( 'phando_video_player_username' ) ) {
			add_meta_box( 'phandovideoplayer-video-list-widget', 'Insert media with Phando', array( 'Phando_Video_Listing_Widget', 'video_listing_widget_body' ), 'post', 'side', 'high' );
			add_meta_box( 'phandovideoplayer-video-list-widget', 'Insert media with Phando', array( 'Phando_Video_Listing_Widget', 'video_listing_widget_body' ), 'page', 'side', 'high' );
		}

	}

	// Adds video listing widget body
	public static function video_listing_widget_body() {
		?>
			<div class="phandovideoplayer-container">
				<input type = "text" placeholder = "Search videos..."  class="phando-search-video" id="phando-search-box" />
				<ul id = "phandovideoplayer-videos-list" class="phando-video-loader">
				</ul>
			</div>
		<?php
	}

	//Adds video listing tab on upload media window
	public static function upload_tab_content() {
		?>
			<div class="phando-media-widget" id="phandovideoplayer-video-list-widget">
				<div class="phando-media-heading">
					<h3>Choose or Search videos</h3>
				</div>
				<?php self::video_listing_widget_body(); ?>
			</div>
		<?php
	}

	//Adds video listing widget on upload media tab window
	public static function add_upload_tab_video_widget() {

		return wp_iframe( array( 'Phando_Video_Listing_Widget', 'upload_tab_content' ) );

	}

}
