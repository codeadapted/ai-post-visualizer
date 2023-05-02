<?php

class APV_Posts {

	/**
	* __construct
	*
	* @param   void
	* @return  void
	**/
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'wp_ajax_apv_get_posts', array( $this, 'apv_get_posts' ) );
			add_action( 'wp_ajax_apv_get_current_fi', array( $this, 'apv_get_current_fi' ) );
			add_action( 'wp_ajax_apv_check_fi_revert', array( $this, 'apv_check_fi_revert' ) );
			add_action( 'wp_ajax_apv_get_history', array( $this, 'apv_get_history' ) );
		}
	}

	/**
	* apv_get_posts
	*
	* Get posts to render in Posts admin panel
	*
	* @param   void
	* @return  string $content
	**/
	public function apv_get_posts() {

		// Only if admin
		if( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$args = array(
			'posts_per_page' => 18,
			'status'         => 'publish',
			'public'         => 'true'
		);

		if( isset( $_GET['exclude'] ) && $_GET['exclude'] ) {
			$args['post__not_in'] = $_GET['exclude'];
		}

		if( isset( $_GET['post_type'] ) && $_GET['post_type'] ) {
			$args['post_type'] = $_GET['post_type'];
		} else {
			$args['post_type'] = 'any';
		}

		if( isset( $_GET['alphabetical'] ) && $_GET['alphabetical'] ) {
			$args['orderby'] = 'post_title';
			$args['order'] = $_GET['alphabetical'];
		}

		if( isset( $_GET['date'] ) && $_GET['date'] ) {
			$args['orderby'] = 'date';
			$args['order'] = $_GET['date'];
		}

		if( isset( $_GET['search'] ) && $_GET['search'] ) {
			$args['search_prod_title'] = $_GET['search'];
			add_filter( 'posts_where', array( $this, 'apv_search_by_title_only' ), 10, 2 );
			$posts = new WP_Query( $args );
			remove_filter( 'posts_where',  array( $this, 'apv_search_by_title_only' ), 10, 2 );
		} else {
			$posts = new WP_Query( $args );
		}

		$content = '';

		if ( $posts->have_posts() ) {
			$total_posts = $posts->found_posts;
			$i = 0;
			while ( $posts->have_posts() ) {

				$posts->the_post();
				$post_id = get_the_ID();
				$missing = false;

				if( get_the_post_thumbnail_url( $post_id ) ) {
					$thumbnail = get_the_post_thumbnail_url( $post_id, 'medium' );
				} else {
					$thumbnail = APV_PLUGIN_DIR . 'admin/img/missing_image_bg.png';
					$missing = true;
				}

				$content .= '<div class="post-card" data-post="' . $post_id  . '">';
					if( !$missing ) {
						$content .= '<div class="image" style="background-image: url(' . $thumbnail . ')"></div>';
					} else {
						$content .= '<div class="image" style="background-image: url(' . $thumbnail . ')">';
							$content .= '<div class="missing-image">';
								$content .= '<div class="icon"><img src="' . APV_PLUGIN_DIR . 'admin/img/missing_image.svg" /></div>';
								$content .= '<div class="text">' . __( 'Featured Image <br>Missing', 'ai-post-visualizer' ) . '</div>';
							$content .= '</div>';
						$content .= '</div>';
					}
					$content .= '<div class="card-title">';
						$content .= '<div class="post-type">' . get_post_type() . '</div>';
						$content .= '<div class="text">' . get_the_title() . '</div>';
					 	$content .= '<div class="btn"><span>' . __( 'Generate New Image', 'ai-post-visualizer' ) . '</span></div>';
					$content .= '</div>';
				$content .= '</div>';

			}

			wp_reset_postdata();
		} else {
			$content .= '<div class="no-results">' . __( 'No posts were found. Please try your query again.', 'ai-post-visualizer' ) . '</div>';
		}

		if( isset( $_GET['post_type'] ) || isset( $_GET['search'] ) ) {
			wp_send_json( array( 'content' => $content, 'total_posts' => $total_posts ) );
		} else {
			return array( 'content' => $content, 'total_posts' => $total_posts );
		}

	}

	/**
	* apv_search_by_title_only
	*
	* Only search by post name
	*
	* @param   string $where Search query
	* @return  object $wp_query Main WP Query Object
	**/
	public function apv_search_by_title_only( $where, &$wp_query ){
		global $wpdb;
		if( $search_term = $wp_query->get( 'search_prod_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $search_term ) ) . '%\'';
		}
		return $where;
	}

	/**
	* apv_get_post_types
	*
	* Get post types
	*
	* @param   int $post_type Post Type
	* @return  string $content
	**/
	public function apv_get_post_types() {

		// Only if admin
		if( !current_user_can( 'manage_options' ) ) {
			return false;
		}

		$content = '';

		$post_types = get_post_types( array(
			'public'   => true,
		) );

		unset( $post_types['attachment'] );

		foreach( $post_types as $post_type ) {
			$content .= '<div class="type-block" data-type="' . $post_type . '">' . $post_type . '</div>';
		}

		return $content;

	}

	/**
	* apv_get_current_fi
	*
	* Get post types
	*
	* @return  string $url
	**/
	public function apv_get_current_fi() {

		$post_id = $_GET['post_id'];
		$thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );

		if( $thumbnail ) {
			wp_send_json( $thumbnail );
		} else {
			wp_send_json( APV_PLUGIN_DIR . 'admin/img/missing_image_bg.png' );
		}

	}

	/**
	* apv_check_fi_revert
	*
	* Check if post already has thumbnail revert
	*
	* @return  string $url
	**/
	public function apv_check_fi_revert() {

		$post_id = $_GET['post_id'];
		$revert = get_post_meta( $post_id, 'apv_revert', true );

		if( $revert ) {
			wp_send_json( $revert );
		} else {
			wp_send_json( false );
		}

	}

	/**
	* apv_get_history
	*
	* Get post types
	*
	* @return  string $url
	**/
	public function apv_get_history() {

		$is_ajax = false;
		if( isset( $_GET['is_ajax'] ) && $_GET['is_ajax'] ) {
			$is_ajax = true;
		}

		$args = array(
			'post_type' => 'apv_history',
			'posts_per_page' => -1,
			'status'    => 'publish'
		);

		$posts = new WP_Query( $args );

		$content = '';

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();
				$post_id = get_the_ID();
				$prompt = get_post_meta( $post_id, 'prompt', true );
				$images = get_post_meta( $post_id, 'images', true );

				$content .= '<div class="history-row" data-history="' . $post_id . '">';
					$content .= '<div class="row-images">';
						$i = 0;
						foreach( $images as $img ) {

							$image_url = wp_get_attachment_url( $img );

							if( count( $images ) > 4 ) {
								$remaining_imgs = count( $images ) - 4;
								if( $i == 3 ) {
									$content .= '<div class="row-image" style="background-image: url(' . $image_url .')"><div class="remaining">+' . $remaining_imgs . '</div></div>';
								} else if ( $i < 3 ) {
									$content .= '<div class="row-image" style="background-image: url(' . $image_url .')"></div>';
								}
							} else {
								$content .= '<div class="row-image" style="background-image: url(' . $image_url .')"></div>';
							}
							$i++;
						}
					$content .= '</div>';
					$content .= '<div class="load-images">' . __( 'Load', 'ai-post-visualizer' ) . '</div>';
				$content .= '</div>';
				$content .= '<div class="history-row-prompt">' . sanitize_text_field( $prompt ) . '</div>';

			}
		}

		if( $content ) {
			if( $is_ajax ) {
				wp_send_json( $content );
			} else {
				return $content;
			}
		}

	}

}
