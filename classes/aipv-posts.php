<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AIPV_Posts {

    /**
     * Register AJAX actions for admin.
     *
     * @param   void
     * @return  void
     **/
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'wp_ajax_aipv_get_posts', array( $this, 'aipv_get_posts' ) );
            add_action( 'wp_ajax_aipv_get_current_fi', array( $this, 'aipv_get_current_fi' ) );
            add_action( 'wp_ajax_aipv_check_fi_revert', array( $this, 'aipv_check_fi_revert' ) );
            add_action( 'wp_ajax_aipv_get_history', array( $this, 'aipv_get_history' ) );
        }
    }

    /**
     * Get posts to render in the Posts admin panel.
     *
     * @param   void
     * @return  string $content  JSON response containing the post content and total posts.
     **/
    public function aipv_get_posts() {

        // Only allow admin users to access this function
        if ( !current_user_can( 'manage_options' ) ) {
            return false;
        }
    
        // AJAX check
        $ajax_check = isset( $_GET['post_type'] ) || isset( $_GET['search'] );
    
        // Nonce validation
        if ( $ajax_check ) {
		    check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );
        }
    
        // Set up pagination
        $paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
    
        // Set up the WP_Query arguments
        $args = array(
            'posts_per_page' => 18,  // Limit to 18 posts per page
            'post_status'    => 'publish',  // Only get published posts
            'public'         => true,  // Get public posts
            'paged'          => $paged,  // Use pagination
            'fields'         => 'ids',  // Only return post IDs for better performance
        );
    
        // Handle the 'post_type' parameter
        $args['post_type'] = isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : 'any';
    
        // Handle alphabetical sorting
        if ( isset( $_GET['alphabetical'] ) && !empty( $_GET['alphabetical'] ) ) {
            $order = sanitize_text_field( wp_unslash( $_GET['alphabetical'] ) );
            $args['orderby'] = 'title';
            $args['order'] = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';
        }
    
        // Handle date sorting
        if ( isset( $_GET['date'] ) && !empty( $_GET['date'] ) ) {
            $order = sanitize_text_field( wp_unslash( $_GET['date'] ) );
            $args['orderby'] = 'date';
            $args['order'] = in_array( $order, array( 'ASC', 'DESC' ) ) ? $order : 'ASC';
        }
    
        // Handle search functionality
        if ( isset( $_GET['search'] ) && !empty( $_GET['search'] ) ) {
            $args['s'] = sanitize_text_field( wp_unslash( $_GET['search'] ) );
        }
    
        // Execute the WP_Query
        $posts = new WP_Query( $args );
    
        $content = '';
        $total_posts = $posts->found_posts;  // Get total posts found
    
        if ( $posts->have_posts() ) {
            foreach ( $posts->posts as $post_id ) {
    
                // Check if missing
                $missing = false;
    
                // Get post thumbnail or fallback to a missing image placeholder
                if ( has_post_thumbnail( $post_id ) ) {
                    $thumbnail = get_the_post_thumbnail_url( $post_id, 'medium' );
                } else {
                    $thumbnail = plugins_url( 'admin/views/img/missing_image_bg.png', AIPV_PLUGIN_FILE );
                    $missing = true;
                }
    
                // Generate HTML structure for each post card
                $content .= '<div class="post-card" data-post="' . esc_attr( $post_id ) . '">';
                if ( !$missing ) {
                    $content .= '<div class="image" style="background-image: url(' . esc_url( $thumbnail ) . ')"></div>';
                } else {
                    $content .= '<div class="image" style="background-image: url(' . esc_url( $thumbnail ) . ')">';
                    $content .= '<div class="missing-image">';
                    $content .= '<div class="icon"><img src="' . esc_url( plugins_url( 'admin/views/img/missing_image.svg', AIPV_PLUGIN_FILE ) ) . '" /></div>';
                    $content .= '<div class="text">' . esc_html__( 'Featured Image Missing', 'ai-post-visualizer' ) . '</div>';
                    $content .= '</div>';
                    $content .= '</div>';
                }
    
                // Add title and button for generating a new image
                $content .= '<div class="card-title">';
                $content .= '<div class="post-type">' . esc_html( get_post_type( $post_id ) ) . '</div>';
                $content .= '<div class="text">' . esc_html( get_the_title( $post_id ) ) . '</div>';
                $content .= '<div class="btn"><span>' . esc_html__( 'Generate New Image', 'ai-post-visualizer' ) . '</span></div>';
                $content .= '</div>';
                $content .= '</div>';
            }
    
            // Reset post data after the loop
            wp_reset_postdata();
    
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
     * Get all public post types except attachments.
     *
     * @param   void
     * @return  string $content  HTML structure of post types
     **/
    public function aipv_get_post_types() {

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
     * Get the current featured image for a post.
     *
     * @return  string $url  JSON response containing the URL of the featured image
     **/
    public function aipv_get_current_fi() {

		// Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize the post ID
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        // Get the post thumbnail URL or return a default image
        $thumbnail = get_the_post_thumbnail_url( $post_id, 'full' );

        // Setup missing image
        $missing_image_bg = esc_url( plugins_url( 'admin/views/img/missing_image_bg.png', AIPV_PLUGIN_FILE ) );
        $missing_image_text = '<div class="missing-image">
            <div class="icon">
                <img src="' . esc_url( plugins_url( 'admin/views/img/missing_image.svg', AIPV_PLUGIN_FILE ) ) . '">
            </div>
            <div class="text">' . esc_html__( 'Featured Image Missing', 'ai-post-visualizer' ) . '</div>
        </div>';

		// check if $thumbnail is set
        if ( $thumbnail ) {
            wp_send_json( array( 'imageUrl' => $thumbnail ) );
        } else {
            wp_send_json( array( 'imageUrl' => $missing_image_bg, 'text' => $missing_image_text ) );
        }
    }

    /**
     * Check if the post already has a thumbnail revert saved.
     *
     * @return  string $url  JSON response containing the revert URL or false
     **/
    public function aipv_check_fi_revert() {

		// Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize the post ID
        $post_id = isset( $_GET['post_id'] ) ? absint( $_GET['post_id'] ) : 0;

        // Get the revert meta field from the post
        $revert = get_post_meta( $post_id, 'aipv_revert', true );

		// Check if $revert is set
        if ( $revert ) {
            wp_send_json( esc_url( $revert ) );
        } else {
            wp_send_json( false );
        }
    }

    /**
     * Get post history, including prompts and generated images.
     *
     * @return  string $content  HTML structure of history rows or JSON response
     **/
    public function aipv_get_history() {

		// Check if is ajax call
        $is_ajax = isset( $_GET['is_ajax'] ) && sanitize_text_field( wp_unslash( $_GET['is_ajax'] ) ) ? true : false;

		// Nonce validation
        if ( $is_ajax ) {
            check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );
        }

        // Set up the query arguments to get history posts
        $args = array(
            'post_type'      => 'aipv_history',
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
                $content .= '<div class="history-row-prompt prompt"><strong>Prompt:</strong> ' . esc_html( $capitalized_prompt ) . '</div>';
                $content .= '<div class="history-row-prompt image-count"><strong>Image Count:</strong> ' . esc_html( count( $images ) ) . '</div>';
                $content .= '<div class="history-row-prompt image-res"><strong>Image Resolution:</strong> ' . esc_html( $resolution ) . '</div>';
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
