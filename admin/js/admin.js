( function ( $, window, document ) {
	'use strict';
	$( document ).ready( function () {

		function filtering( _exclude = false ) {

			var search = $( '#apv-admin-view .search-bar .search-input' ).val();

			var dataObj = {
				action: 'apv_get_posts'
			};

			$( '#apv-admin-view .type-block.active' ).each( function() {

				var postType = $( this ).data( 'type' );
				var alphabetical = $( this ).data( 'alphabetical' );
				var date = $( this ).data( 'date' );

				if( Boolean( postType ) ) {
					dataObj.post_type = postType;
				}

				if( Boolean( alphabetical ) ) {
					dataObj.alphabetical = alphabetical;
				}

				if( Boolean( date ) ) {
					dataObj.date = date;
				}

			} );

			if( Boolean( search ) ) {
				dataObj.search = search;
			}

			if( Boolean( _exclude ) ) {
				dataObj.exclude = _exclude;
			}

			$.ajax({
				method: 'GET',
				url: ajaxURL,
				data: dataObj,
				success: function( response ) {

					if( Boolean( _exclude ) ) {
						$( '#apv-admin-view .posts-section .posts-wrapper' ).append( response.content );
					} else {
						$( '#apv-admin-view .posts-section .posts-wrapper' ).html( response.content );
					}

					if( response.total_posts > 18 ) {
						$( '#apv-admin-view .load-more' ).removeClass( 'hidden' );
					} else {
						$( '#apv-admin-view .load-more' ).addClass( 'hidden' );
					}

					postCardClickEvent();

					$( '#apv-admin-view .load-more .load-more-text' ).removeClass( 'loading' );
					$( '#apv-admin-view .load-more .rc-loader' ).removeClass( 'loading' );

				}
			});

		}

		function getCurrentFI( post ) {

			var dataObj = {
				action: 'apv_get_current_fi',
				post_id: post
			};

			$.ajax({
				method: 'GET',
				url: ajaxURL,
				data: dataObj,
				success: function( response ) {

					$( '#apv-admin-view .template.template-generate .featured-img' ).css( 'background-image', 'url(' + response + ')' );

				}
			});

		}

		function checkForFIRevert( post ) {

			const dataObj = {
				action: 'apv_check_fi_revert',
				post_id: post
			};

			$.ajax({
				method: 'GET',
				url: ajaxURL,
				data: dataObj,
				success: function( response ) {

					if( Boolean( response ) ) {
						console.log( response );
						$( '#apv-admin-view .template.template-generate .revert-to-original' ).removeClass( 'hidden' );
					}

				}
			});

		}

		function postCardClickEvent() {

			$( '#apv-admin-view .post-card .btn' ).click( function() {

				$( '#apv-admin-view .template.template-generate .featured-img' ).html( '' );
				$( '#apv-admin-view .template.template-generate .featured-img' ).css( 'background-image', '' );
				$( '#apv-admin-view .template.template-generate .current-post-title' ).html( '' );
				var postId = $( this ).parents( '.post-card' ).data( 'post' );
				var postTitle = $( this ).parents( '.post-card' ).find( '.card-title .text' ).html();
				$( '#apv-admin-view .sidebar .item' ).removeClass( 'active' );
				$( '#apv-admin-view .sidebar .item.generate' ).addClass( 'active' );
				$( '#apv-admin-view .template' ).removeClass( 'active' );
				$( '#apv-admin-view .template.template-generate' ).addClass( 'for-post active' );
				$( '#apv-admin-view .template.template-generate' ).data( 'post', postId );
				$( '#apv-admin-view .template.template-generate .current-post-title' ).html( postTitle );
				$( '#apv-admin-view .template.template-generate .revert-to-original' ).addClass( 'hidden' );
				if( $( this ).parents( '.post-card' ).find( '.missing-image' ).length > 0 ) {
					$( '#apv-admin-view .template.template-generate .featured-img' ).html( '<div class="missing-image"><div class="icon"><img src="/wp-content/plugins/ai-post-visualizer/admin/img/missing_image.svg"></div><div class="text">Featured Image <br>Missing</div></div>' );
				}

				getCurrentFI( postId );
				checkForFIRevert( postId )
				setHistoryHeight();

				$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );

			} );

		}

		function getDalleImages( _postId, _prompt, _n = false, _size = false ) {

			$( '#apv-admin-view .rendered-images .images-wrapper' ).html( '' );
			$( '#apv-admin-view .rendered-images' ).addClass( 'loaded' );
			$( '#apv-admin-view .rendered-images .rc-loader' ).addClass( 'loading' );

			$.ajax( {

				method: 'GET',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'apv_get_dalle_images',
					prompt: _prompt,
					n: _n,
					size: _size,
					post_id: _postId
				},
				success: function( response ) {

					$( '#apv-admin-view .rendered-images .rc-loader' ).removeClass( 'loading' );

					$( '#apv-admin-view .rendered-images .images-wrapper' ).html( response );

					$( 'html, body' ).animate( {
						scrollTop: $( '#apv-admin-view .rendered-images' ).offset().top
					}, 1000 );

					addNewHistoryRow();
					setHistoryHeight();
					renderedImageSetFI();

				}

			} );

		}

		function setDalleImage( _postId, _imageId ) {

			$.ajax( {

				method: 'GET',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'apv_set_dalle_image',
					post_id: _postId,
					image_id: _imageId
				},
				success: function( response ) {

					$( '#apv-admin-view .template-generate .current-featured .featured-img' ).css( 'background-image', 'url(' + response + ')' );
					$( '#apv-admin-view .template-posts .post-card[data-post="' + _postId + '"] .image' ).css( 'background-image', 'url(' + response + ')' );
					$( '#apv-admin-view .template.template-generate .revert-to-original' ).removeClass( 'hidden' );

					if( $( '#apv-admin-view .template-generate .current-featured .featured-img .missing-image' ).length > 0 ) {
						$( '#apv-admin-view .template-generate .current-featured .featured-img .missing-image' ).remove();
						$( '#apv-admin-view .template-posts .post-card[data-post="' + _postId + '"] .image .missing-image' ).remove();
						$( '#apv-admin-view .template.template-generate .revert-to-original' ).addClass( 'hidden' );
					}

				}

			} );

		}

		function revertFeaturedImage( _postId ) {

			$.ajax( {

				method: 'GET',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'apv_revert_featured_image',
					post_id: _postId
				},
				success: function( response ) {

					$( '#apv-admin-view .template-generate .current-featured .featured-img' ).css( 'background-image', 'url(' + response + ')' );
					$( '#apv-admin-view .template-posts .post-card[data-post="' + _postId + '"] .image' ).css( 'background-image', 'url(' + response + ')' );
					( '#apv-admin-view .template.template-generate .revert-to-original' ).addClass( 'hidden' );

				}

			} );

		}

		function loadDalleHistoryRow( _historyId ) {

			$( '#apv-admin-view .rendered-images' ).addClass( 'loaded' );

			$.ajax( {

				method: 'GET',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'apv_load_dalle_history',
					post_id: _historyId
				},
				success: function( response ) {

					$( '#apv-admin-view .rendered-images .rc-loader' ).removeClass( 'loading' );

					$( '#apv-admin-view .rendered-images .images-wrapper' ).html( response );

					$( 'html, body' ).animate( {
						scrollTop: $( '#apv-admin-view .rendered-images' ).offset().top
					}, 1000 );

					setHistoryHeight();
					renderedImageSetFI();

				}

			} );

		}

		function addNewHistoryRow() {

			$.ajax( {

				method: 'GET',
				url: '/wp-admin/admin-ajax.php',
				data: {
					action: 'apv_get_history',
					is_ajax: 1
				},
				success: function( response ) {

					$( '#apv-admin-view .history-rows' ).html( response );

					$( '#apv-admin-view .history .load-images' ).click( function( e ) {

						e.preventDefault();

						var historyId = $( this ).parents( '.history-row' ).data( 'history' );

						loadDalleHistoryRow( historyId );

					} );

				}

			} );

		}

		function setHistoryHeight() {

			var height = $( '#apv-admin-view .template.template-generate .settings' ).outerHeight( true );
			$( '#apv-admin-view .history' ).css( 'height', height );

		}

		function renderedImageSetFI() {

			$( '#apv-admin-view .rendered-images .post-card .set-image' ).click( function() {

				var postId = $( this ).parents( '.template-generate' ).data( 'post' );
				var imageId = $( this ).parents( '.post-card' ).data( 'image' );
				setDalleImage( postId, imageId );
				$( this ).addClass( 'current' );

			} );

		}

		var ajaxURL = apv_obj.ajax_url;

		$( '#apv-admin-view .accordions .accordion .title' ).click( function() {

			var typesHeight = $( this ).parents( '.accordion' ).find( '.types' ).height();
			if( typesHeight ) {
				$( this ).parents( '.accordion' ).find( '.types .types-wrapper' ).css( 'margin-top', 'calc((' + typesHeight + 'px + 1rem) * -1)' );
			} else {
				$( this ).parents( '.accordion' ).find( '.types .types-wrapper' ).attr( 'style', '' );
			}

		} );

		$( '#apv-admin-view .sidebar .item' ).click( function() {

			$( '#apv-admin-view .rendered-images' ).removeClass( 'loaded' );
			$( '#apv-admin-view .rendered-images .images-wrapper' ).html( '' );
			$( '#apv-admin-view .template.template-generate' ).removeClass( 'for-post' );
			$( '#apv-admin-view .template.template-generate' ).data( 'post', '' );
			$( '#apv-admin-view .template.template-generate .featured-img' ).html( '' );
			$( '#apv-admin-view .template.template-generate .featured-img' ).css( 'background-image', '' );
			$( '#apv-admin-view .template.template-generate .current-post-title' ).html( '' );
			$( '#apv-admin-view .template.template-generate .setting input' ).val('').change();
			$( '#apv-admin-view .template.template-generate .breakdown .num-images span' ).html( 1 );
			$( '#apv-admin-view .template.template-generate .setting select' ).prop( 'selectedIndex', 0 ).change();
			$( '#apv-admin-view .history' ).css( 'height', '' );
			var tab = $( this ).data( 'tab' );
			$( '.sidebar .item' ).removeClass( 'active' );
			$( this ).addClass( 'active' );
			$( '.main-content .template' ).removeClass( 'active' );
			$( '.main-content .template[data-tab="' + tab + '"]' ).addClass( 'active' );

			$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );

		} );

		$( '#apv-admin-view .search-bar .search-input' ).on( 'change', function ( e ) {

			e.preventDefault();

			filtering();

		});

		$( '#apv-admin-view .number-input' ).on( 'change', function ( e ) {

			e.preventDefault();

			var num = $( this ).val();
			var resolution = $( '#apv-admin-view .resolution-select select' ).val();
			var cost;

			if( resolution == '256x256' ) {
				cost = .016;
			} else if( resolution == '512x512' ) {
				cost = .018;
			} else if( resolution == '1024x1024' ) {
				cost = .02;
			}

			var total = parseFloat(num * cost).toFixed(3);

			$( '#apv-admin-view .breakdown .num-images span' ).html( num );

			$( '#apv-admin-view .breakdown .total span' ).html( '$' + total );

		});

		$( '#apv-admin-view .resolution-select select' ).on( 'change', function ( e ) {

			e.preventDefault();

			var num = $( '#apv-admin-view .number-input' ).val();
			var resolution = $( this ).val();
			var cost;

			if( resolution == '256x256' ) {
				cost = .016;
			} else if( resolution == '512x512' ) {
				cost = .018;
			} else if( resolution == '1024x1024' ) {
				cost = .02;
			}

			var total = parseFloat(num * cost).toFixed(3);

			$( '#apv-admin-view .breakdown .cost-per-img span' ).html( '$' + cost );

			$( '#apv-admin-view .breakdown .total span' ).html( '$' + total );

		});

		$( '#apv-admin-view .render.btn' ).click( function( e ) {

			e.preventDefault();

			var postId;
			if( $( '#apv-admin-view .template-generate' ).hasClass( 'for-post' ) ) {
				postId = $( '#apv-admin-view .template-generate' ).data( 'post' );
			} else {
				postId = false;
			}
			var prompt = $( '#apv-admin-view .keyword-input' ).val();
			var num = $( '#apv-admin-view .number-input' ).val();
			var resolution = $( '#apv-admin-view .resolution-select select' ).val();

			getDalleImages( postId, prompt, num, resolution );

		} );

		$( '#apv-admin-view .history .load-images' ).click( function( e ) {

			e.preventDefault();

			var historyId = $( this ).parents( '.history-row' ).data( 'history' );

			loadDalleHistoryRow( historyId );

		} );

		$( '#apv-admin-view .type-block' ).click( function( e ) {

			e.preventDefault();

			if( $( this ).hasClass( 'active' ) ) {
				$( this ).removeClass( 'active' );
			} else {
				if( $( this ).parents( '.accordion' ).hasClass( 'sort' ) ) {
					$( '.accordion.sort' ).find( '.type-block.active' ).removeClass( 'active' );
				} else {
					$( this ).parents( '.accordion' ).find( '.type-block.active' ).removeClass( 'active' );
				}
				$( this ).addClass( 'active' );
			}

			if( $( '.post-types .type-block.active' ).length == 0 ) {
				$( '.post-types .type-block[data-type="any"]' ).addClass( 'active' );
			}

			filtering();

		} );

		$( '#apv-admin-view .load-more' ).click( function( e ) {

			e.preventDefault();

			const exclude = [];

			$( '#apv-admin-view .posts-wrapper .post-card' ).each( function() {
				exclude.push( $( this ).data( 'post' ) );
			} );

			$( '#apv-admin-view .load-more .load-more-text' ).addClass( 'loading' );
			$( '#apv-admin-view .load-more .rc-loader' ).addClass( 'loading' );

			filtering( exclude );

		} );

		$( '#apv-admin-view .back-to-posts' ).click( function() {

			$( '#apv-admin-view .rendered-images' ).removeClass( 'loaded' );
			$( '#apv-admin-view .rendered-images .images-wrapper' ).html( '' );
			$( '#apv-admin-view .template.template-generate' ).removeClass( 'for-post' );
			$( '#apv-admin-view .template.template-generate' ).data( 'post', '' );
			$( '#apv-admin-view .template.template-generate .featured-img' ).html( '' );
			$( '#apv-admin-view .template.template-generate .featured-img' ).css( 'background-image', '' );
			$( '#apv-admin-view .template.template-generate .current-post-title' ).html( '' );
			$( '#apv-admin-view .template.template-generate .setting input' ).val('').change();
			$( '#apv-admin-view .template.template-generate .breakdown .num-images span' ).html( 1 );
			$( '#apv-admin-view .template.template-generate .setting select' ).prop( 'selectedIndex', 0 ).change();
			$( '#apv-admin-view .history' ).css( 'height', '' );
			$( '.sidebar .item' ).removeClass( 'active' );
			$( '.sidebar .item.posts' ).addClass( 'active' );
			$( '.main-content .template' ).removeClass( 'active' );
			$( '.main-content .template.template-posts' ).addClass( 'active' );

			$( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );

		} );

		$( '#apv-admin-view .revert-to-original' ).click( function() {

			var postId = $( this ).parents( '.template-generate' ).data( 'post' );
			revertFeaturedImage( postId );

		} );

		postCardClickEvent();

	});
} ( jQuery, window, document ) );
