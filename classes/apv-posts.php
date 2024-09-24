<?php

class APV_Posts {

    /**
     * __construct
     *
     * Register AJAX actions for admin.
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
     * Get posts to render in the Posts admin panel.
     *
     * @param   void
     * @return  string $content  JSON response containing the post content and total posts.
     **/
    public function apv_get_posts() {

        // Only allow admin users to access this function
        if ( !current_user_can( 'manage_options' ) ) {
            return false;
        }

        // Verify nonce for security to prevent CSRF
		$ajax_check = isset( $_GET['post_type'] ) || isset( $_GET['search'] );
		$nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $ajax_check && $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Set up the default WP_Query arguments
        $args = array(
            'posts_per_page' => 18,  // Limit to 18 posts per page
            'post_status'    => 'publish',  // Only get published posts
            'public'         => true  // Get public posts
        );

        // Sanitize and handle the 'exclude' parameter, turning it into an array of integers
        if ( isset( $_GET['exclude'] ) && !empty( $_GET['exclude'] ) ) {
            $exclude_ids = explode( ',', sanitize_text_field( $_GET['exclude'] ) );
            $exclude_ids = array_map( 'absint', $exclude_ids );
            $args['post__not_in'] = $exclude_ids;
        }

        // Sanitize and handle the 'post_type' parameter, fallback to 'any' post type if not provided
        $args['post_type'] = isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : 'any';

        // Handle alphabetical sorting, default to ASC if invalid or not provided
        if ( isset( $_GET['alphabetical'] ) && !empty( $_GET['alphabetical'] ) ) {
            $order = sanitize_text_field( $_GET['alphabetical'] );
            $args['orderby'] = 'title';
            $args['order'] = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';
        }

        // Handle date sorting, default to ASC if invalid or not provided
        if ( isset( $_GET['date'] ) && !empty( $_GET['date'] ) ) {
            $order = sanitize_text_field( $_GET['date'] );
            $args['orderby'] = 'date';
            $args['order'] = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';
        }

        // Sanitize and handle the 'search' functionality
        if ( isset( $_GET['search'] ) && !empty( $_GET['search'] ) ) {
            $args['s'] = sanitize_text_field( $_GET['search'] );
        }

        // Execute the WP_Query with the arguments
        $posts = new WP_Query( $args );

        $content = '';
        $total_posts = $posts->found_posts;  // Get total posts found

        if ( $posts->have_posts() ) {
            while ( $posts->have_posts() ) {
                $posts->the_post();
                $post_id = get_the_ID();
                $missing = false;

                // Get post thumbnail or fallback to a missing image placeholder
                if ( has_post_thumbnail( $post_id ) ) {
                    $thumbnail = get_the_post_thumbnail_url( $post_id, 'medium' );
                } else {
                    $thumbnail = plugins_url( 'admin/views/img/missing_image_bg.png', APV_PLUGIN_FILE );
                    $missing = true;
                }

                // Generate HTML structure for each post card
                $content .= '<div class="post-card" data-post="' . esc_attr( $post_id ) . '">';
                if ( !$missing ) {
                    $content .= '<div class="image" style="background-image: url(' . esc_url( $thumbnail ) . ')"></div>';
                } else {
                    $content .= '<div class="image" style="background-image: url(' . esc_url( $thumbnail ) . ')">';
                    $content .= '<div class="missing-image">';
                    $content .= '<div class="icon"><img src="' . esc_url( plugins_url( 'admin/views/img/missing_image.svg', APV_PLUGIN_FILE ) ) . '" /></div>';
                    $content .= '<div class="text">' . esc_html__( 'Featured Image Missing', 'ai-post-visualizer' ) . '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                }

                // Add title and button for generating a new image
                $content .= '<div class="card-title">';
                $content .= '<div class="post-type">' . esc_html( get_post_type() ) . '</div>';
                $content .= '<div class="text">' . esc_html( get_the_title() ) . '</div>';
                $content .= '<div class="btn"><span>' . esc_html__( 'Generate New Image', 'ai-post-visualizer' ) . '</span></div>';
                $content .= '</div>';
                $content .= '</div>';
            }

            wp_reset_postdata();  // Reset post data after the loop
        } else {
            // If no posts are found, display a 'no results' message
            $content .= '<div class="no-results">' . esc_html__( 'No posts were found. Please try your query again.', 'ai-post-visualizer' ) . '</div>';
        }

        // Return the content and total posts count in a JSON response for AJAX requests
        if ( $ajax_check ) {
            wp_send_json( array( 'content' => $content, 'total_posts' => $total_posts ) );
        } else {
            return array( 'content' => $content, 'total_posts' => $total_posts );
        }
    }

    /**
     * apv_get_post_types
     *
     * Get all public post types except attachments.
     *
     * @param   void
     * @return  string $content  HTML structure of post types
     **/
    public function apv_get_post_types() {

        // Only allow admin users
        if ( !current_user_can( 'manage_options' ) ) {
            return false;
        }

		// Set empty content variable
        $content = '';

        // Get all public post types
        $post_types = get_post_types( array(
            'public' => true,
        ) );

		// Remove 'attachment' post type
        unset( $post_types['attachment'] );

        // Generate HTML structure for each post type
        foreach ( $post_types as $post_type ) {
            $content .= '<div class="type-block" data-type="' . esc_attr( $post_type ) . '">' . esc_html( $post_type ) . '</div>';
        }

        return $content;
    }

    /**
     * apv_get_current_fi
     *
     * Get the current featured image for a post.
     *
     * @return  string $url  JSON response containing the URL of the featured image
     **/
    public function apv_get_current_fi() {

		// Verify nonce for security to prevent CSRF
		$nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Sanitize the post ID
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        // Get the post thumbnail URL or return a default image
        $thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );

		// check if $thumbnail is set
        if ( $thumbnail ) {
            wp_send_json( esc_url( $thumbnail ) );
        } else {
            wp_send_json( esc_url( plugins_url( 'admin/views/img/missing_image_bg.png', APV_PLUGIN_FILE ) ) );
        }
    }

    /**
     * apv_check_fi_revert
     *
     * Check if the post already has a thumbnail revert saved.
     *
     * @return  string $url  JSON response containing the revert URL or false
     **/
    public function apv_check_fi_revert() {

		// Verify nonce for security to prevent CSRF
		$nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Sanitize the post ID
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        // Get the revert meta field from the post
        $revert = get_post_meta( $post_id, 'apv_revert', true );

		// Check if $revert is set
        if ( $revert ) {
            wp_send_json( esc_url( $revert ) );
        } else {
            wp_send_json( false );
        }
    }

    /**
     * apv_get_history
     *
     * Get post history, including prompts and generated images.
     *
     * @return  string $content  HTML structure of history rows or JSON response
     **/
    public function apv_get_history() {

		// Check if is ajax call
        $is_ajax = isset( $_GET['is_ajax'] ) && $_GET['is_ajax'] ? true : false;

		// Verify nonce for security to prevent CSRF
		$nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $is_ajax && $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Set up the query arguments to get history posts
        $args = array(
            'post_type'      => 'apv_history',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

        $posts = new WP_Query( $args );

        $content = '';

        if ( $posts->have_posts() ) {
            while ( $posts->have_posts() ) {
                $posts->the_post();
                $post_id = get_the_ID();
                $prompt = sanitize_text_field( get_post_meta( $post_id, 'prompt', true ) );
                $images = get_post_meta( $post_id, 'images', true );
                $resolution = get_post_meta( $post_id, 'resolution', true );
                $capitalized_prompt = ucfirst( $prompt );

                // Generate HTML structure for each history row
                $content .= '<div class="history-row" data-history="' . esc_attr( $post_id ) . '">';
                $content .= '<div class="row-images">';
                $i = 0;
                foreach ( $images as $img ) {
                    $image_url = esc_url( wp_get_attachment_url( $img ) );
                    if ( count( $images ) > 4 ) {
                        $remaining_imgs = count( $images ) - 4;
                        if ( $i == 3 ) {
                            $content .= '<div class="row-image" style="background-image: url(' . $image_url . ')"><div class="remaining">+' . esc_html( $remaining_imgs ) . '</div></div>';
                        } else if ( $i < 3 ) {
                            $content .= '<div class="row-image" style="background-image: url(' . $image_url . ')"></div>';
                        }
                    } else {
                        $content .= '<div class="row-image" style="background-image: url(' . $image_url . ')"></div>';
                    }
                    $i++;
                }
                $content .= '</div>';
                $content .= '<div class="history-row-prompt"><strong>Prompt:</strong> ' . esc_html( $capitalized_prompt ) . '</div>';
                $content .= '<div class="history-row-prompt"><strong>Image Count:</strong> ' . esc_html( count( $images ) ) . '</div>';
                $content .= '<div class="history-row-prompt"><strong>Image Resolution:</strong> ' . esc_html( $resolution ) . '</div>';
                $content .= '<div class="load-images btn"><span>' . esc_html__( 'Load Images', 'ai-post-visualizer' ) . '</span></div>';
                $content .= '</div>';
            }

			// Reset post data after the loop
            wp_reset_postdata();
        }

        // Return the content via AJAX or as an array based on the request
        if ( $content ) {
            if ( $is_ajax ) {
                wp_send_json( $content );
            } else {
                return $content;
            }
        }
    }

}
