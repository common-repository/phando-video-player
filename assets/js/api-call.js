(function ($) {

	"use strict";

	var phandovideo = {

		searchTimeout: 1000,//Make a search call after this no. of milliseconds after the user is stops typing
		searchTimerId: null,

		/*
		 * Add video item in video list
		 */
		generateVideoListItem: function ( video, counter ) {

			var videoHtml = '', videoThumbnailUrl, phandoCdnUrl = '', videoThumbnailUrlSecondary = '', videoItemClass, videoThumbnail  = "";

			//phandoVideoFields is set in admin.php
			var staticErrorFallbackUrl = phandoVideoFields.pluginUrl + 'assets/images/video-player.png';

			var videoShortcode = '[phando-video media = ' + video.media_id + ']';

			if ( video.cdn_url ) {
				phandoCdnUrl = video.cdn_url;
			} else if ( video.cdn_url1 ) {
				phandoCdnUrl = video.cdn_url1;
			}

			if ( video.thumbnail ) {
				videoThumbnailUrl = video.thumbnail;
				videoThumbnail    = "url('" + videoThumbnailUrl + "')";
			} else {
				videoThumbnailUrl          = phandoCdnUrl + video.media_id + '/' + video.media_id + '.png';
				videoThumbnailUrlSecondary = phandoCdnUrl + video.media_id + '.png';

				videoThumbnail             = "url('" + videoThumbnailUrl + "')";
				videoThumbnail             = videoThumbnail + "," + "url('" + videoThumbnailUrlSecondary + "')";
			}

			videoThumbnail = videoThumbnail + "," + "url('" + staticErrorFallbackUrl + "')";
			//Multiple urla are added to ensure atleast thumbnail image is present

			videoItemClass = counter ? ((counter % 2) ? 'phandovideoplaye-video-item-container even' : 'phandovideoplaye-video-item-container odd') : 'phandovideoplaye-video-item-container odd';

			videoHtml = $( '<li>' )
				.attr( 'class', videoItemClass )
				.append(
					$( '<div>' )
						.attr( 'class', 'phandovideoplaye-video-item' )
						.css( 'background-image', videoThumbnail )
						.append(
							$( '<span>' )
								.attr( 'class', 'phando-video-title' )
								.attr( 'title', video.title )
								.text( video.title )
						)
						.append(
							$( '<span>' )
								.attr( 'class', 'btn-add-video' )
								.text( 'Add' )
						)
				);

			$( 'div.phandovideoplaye-video-item', videoHtml ).click(function () {

				if ( phandovideo.widgets.isMediaWindow ) {
					parent.send_to_editor( videoShortcode );
				} else {
					window.send_to_editor( videoShortcode );
				}

			});

			return videoHtml;
		},

		/*
		 * Show Wait cursor while waiting for the video data to be loaded
		 */
		showWaitCursor: function () {
			phandovideo.widgets.videoBox.addClass( 'phando-loading-cursor' );
		},

		/*
		 * Remove Wait cursor after data is loaded
		 */
		hideWaitCursor: function () {
			phandovideo.widgets.videoBox.removeClass( 'phando-loading-cursor' );
		},

		/* List videos in widget
		 * If searchText is provided then filter and list videos based on that parameter
		 */
		listVideos: function ( searchText ) {

			phandovideo.showWaitCursor();

			phandovideo.widgets.list.empty().addClass( 'phando-video-loader' );
			var params = {
				action: "phandovideoplayer_ajax_api_proxy"
			};

			if ( ! phandoVideoFields.videoListSize ) {
				phandovideo.widgets.list.empty().removeClass( 'phando-video-loader' );
				phandovideo.hideWaitCursor();
				phandovideo.widgets.list.append( "Zero video displayed - Check settings " );
				return;
			}

			var numVideoList = phandoVideoFields.videoListSize;

			params.limit = numVideoList;
			params.order = 'desc';

			if ( searchText === undefined || searchText === '' ) {
				params.method = '/auth/medialist';
			} else {
				//perform search
				params.method      = '/auth/mediabysearch';
				params.query       = searchText;
				params.language_id = 1;
			}

			$.ajax({
				type: 'GET',
				url: ajaxurl,
				data: params,
				dataType: 'json',
				success: function ( data ) {
					phandovideo.hideWaitCursor();
					phandovideo.widgets.list.removeClass( 'phando-video-loader' );

					if ( data && data.error ) {
						if ( data.message === 'inactive user' ) {
							phandovideo.widgets.list.append( 'Some error has occured. Try re-authorizing phando plugin' );
							return;
						}
						phandovideo.widgets.list.append( 'Some error has occured.!!' );
						return;
					}
					if ( data && data.success ) {

						if ( data.data.length === 0 ) {
							phandovideo.widgets.list.append( 'No videos found' );
							return;
						}

						var videosList = data.data, videoItem;
						for ( var counter = 0; counter < videosList.length; counter++ ) {
							videoItem = phandovideo.generateVideoListItem( videosList[counter], counter );
							phandovideo.widgets.list.append( videoItem );
						}
					} else {
						phandovideo.widgets.list.append( 'Some error has occured.' );
					}

				},
				error: function () {
					phandovideo.hideWaitCursor();
					phandovideo.widgets.list.append( 'Some error has occured.Please try again' );
					phandovideo.widgets.list.removeClass( 'phando-video-loader' );

				}
			});

		}
	};

	$( function () {

		phandovideo.widgets = {
			videoBox: $( '#phandovideoplayer-video-list-widget' ),
			search: $( '#phando-search-box' ),
			list: $( '#phandovideoplayer-videos-list' )
		};

		phandovideo.widgets.search.val( '' );

		if ( phandovideo.widgets.videoBox.length === 0 ) {
			return;
		}

		phandovideo.widgets.isMediaWindow = phandovideo.widgets.videoBox.hasClass( 'phando-media-widget' );

		phandovideo.widgets.search.keyup(function ( event ) {

			if ( event.keyCode !== 13 ) {

				if ( phandovideo.searchTimerId !== null ) {
					window.clearTimeout( phandovideo.searchTimerId );
				}

				var searchText = $.trim( $( this ).val() );
				phandovideo.searchTimerId = window.setTimeout( function () {
					phandovideo.searchTimerId = null;
					phandovideo.listVideos( searchText );
				}, phandovideo.searchTimeout );

			}

		});

		phandovideo.widgets.search.keydown(function ( event ) {

			if ( event.keyCode === 13 ) {
				var searchText = $.trim( $( this ).val() );
				if ( phandovideo.searchTimerId !== null ) {
					window.clearTimeout( phandovideo.searchTimerId );
				}
				phandovideo.listVideos( searchText );
				return false;
			}

		});

		phandovideo.listVideos( phandovideo.widgets.search.val() );
	});

})( window.jQuery );
