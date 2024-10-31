<?php

/**
 * Creates phando video shortcode
 */
class Phando_Video_Shortcode {

	//Creates phando video shortcode hook
	public static function create_video_shortcode() {

		add_shortcode( 'phando-video', array( 'Phando_Video_Shortcode', 'shortcode_content' ) );

	}

	//Adds phando video shortcode content
	public static function shortcode_content( $phando_video_sc ) {

		if ( get_option( 'phando_video_player_username' ) ) {
			$media = $phando_video_sc['media'];
			$src   = PHANDO_VIDEO_PLAYER_API_URL . '/media/watch/' . $media . '?&output=embed';
			return '<iframe frameborder="0" allowfullscreen="true" webkitallowfullscreen="true" mozallowfullscreen="true" width="100%" height="480" src="' . $src . '" frameborder="0" ></iframe>';
		}
		return '';

	}

}
