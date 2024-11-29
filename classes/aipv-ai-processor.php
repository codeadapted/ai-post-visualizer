<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AIPV_AI_Processor {

    /**
     * Constructor to initialize AJAX actions if in the admin.
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'wp_ajax_aipv_get_dalle_images', array( $this, 'aipv_get_dalle_images' ) );
            add_action( 'wp_ajax_aipv_set_dalle_image', array( $this, 'aipv_set_dalle_image' ) );
            add_action( 'wp_ajax_aipv_revert_featured_image', array( $this, 'aipv_revert_featured_image' ) );
            add_action( 'wp_ajax_aipv_load_dalle_history', array( $this, 'aipv_load_dalle_history' ) );
        }
    }

    /**
     * This function handles generating DALLE images via the OpenAI API.
     *
     * @return string JSON response with generated images or error message
     */
    public function aipv_get_dalle_images() {

        // Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize input
        $post_id = isset( $_GET['post_id'] ) ? intval( wp_unslash( $_GET['post_id'] ) ) : '';
        $prompt = isset( $_GET['prompt'] ) ? sanitize_text_field( wp_unslash( $_GET['prompt'] ) ) : '';
        $n = isset( $_GET['n'] ) ? intval( wp_unslash( $_GET['n'] ) ) : 1;
        $size = isset( $_GET['size'] ) ? sanitize_text_field( wp_unslash( $_GET['size'] ) ) : '256x256';

        // Sanitize prompt for use as image title
        $image_title = implode( '-', array_slice( explode( ' ', $prompt ), 0, 6 ) );

        // Send the API request
        $api_data = $this->aipv_api_request( $prompt, $n, $size );

		// Check if api data valid
        if( $api_data && !isset( $api_data->status ) ) {

			// Get urls and set empty content and generated_images variables
            $urls = $api_data['data'];
            $content = '';
            $generated_images = array();

            // Loop through the API response to generate images
            foreach ( $urls as $i => $url ) {

				// Get iamge url and update generated_images array
                $image_id = $this->aipv_upload_images_to_library( $url['url'], $image_title . '-' . $i );
                $image_url = wp_get_attachment_url( $image_id );
                $generated_images[] = $image_id;

                // Build the HTML content for the images
                $content .= '<div class="post-card" data-image="' . esc_attr( $image_id ) . '">';
                $content .= '<div class="image" style="background-image: url(' . esc_url( $image_url ) . ')"></div>';
                $content .= '<div class="set-image">';
                $content .= '<div class="plus">';
                $content .= '<img src="' . esc_url( AIPV_PLUGIN_DIR . 'admin/views/img/plus.svg' ) . '" />';
                $content .= '</div>';
                $content .= '<div class="set-text">' . __( 'Set Featured Image', 'ai-post-visualizer' ) . '</div>';
                $content .= '<div class="current-text">' . __( 'Current Featured Image', 'ai-post-visualizer' ) . '</div>';
                $content .= '</div></div>';
            }

            // Insert post history into the 'aipv_history' custom post type
            if ( !empty( $content ) ) {
                $history = wp_insert_post( [
                    'post_type'   => 'aipv_history',
                    'post_status' => 'publish',
                    'post_title'  => $prompt,
                    'post_name'   => uniqid( 'aipv_' ),
                ] );

                // Store meta data for the history
                update_post_meta( $history, 'prompt', $prompt );
                update_post_meta( $history, 'images', $generated_images );
                update_post_meta( $history, 'resolution', $size );

				// Send json response
                wp_send_json( $content );

            } else {

				// Send json error
                wp_send_json_error( 'Error with prompt.' );

            }

        } else {
            $content = '<div class="invalid-api-key">' . __( 'Please go to the Settings tab and sign up for a plan before continuing.', 'ai-post-visualizer' ) . '</div>';
            wp_send_json( $content );
        }

    }

    /**
     * Sets the DALLE image as the post's featured image.
     *
     * @return string JSON response with image URL
     */
    public function aipv_set_dalle_image() {

        // Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize input
        $post_id = isset( $_GET['post_id'] ) ? intval( wp_unslash( $_GET['post_id'] ) ) : '';
        $image_id = isset( $_GET['image_id'] ) ? intval( wp_unslash( $_GET['image_id'] ) ) : '';

        // Backup original featured image if not already done
        $original = get_post_thumbnail_id( $post_id );
        if ( !get_post_meta( $post_id, 'aipv_revert', true ) ) {
            update_post_meta( $post_id, 'aipv_revert', $original );
        }

        // Set the new featured image
        set_post_thumbnail( $post_id, $image_id );
        $image_url = wp_get_attachment_url( $image_id );

		// Send json response
        wp_send_json( $image_url );

    }

    /**
     * Reverts the post's featured image to its original state.
     *
     * @return string JSON response with image URL
     */
    public function aipv_revert_featured_image() {

        // Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize input
        $post_id = isset( $_GET['post_id'] ) ? intval( wp_unslash( $_GET['post_id'] ) ) : '';
        $original_img = intval( get_post_meta( $post_id, 'aipv_revert', true ) );

        // Revert to the original featured image
        set_post_thumbnail( $post_id, $original_img );
        delete_post_meta( $post_id, 'aipv_revert' );

		// Get image attachment url
        $image_url = wp_get_attachment_url( $original_img );

		// Send json response
        wp_send_json( $image_url );

    }

    /**
     * Loads stored DALLE images for a post.
     *
     * @return string JSON response with the image HTML
     */
    public function aipv_load_dalle_history() {

        // Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

        // Sanitize input
        $post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : '';
        $images = get_post_meta( $post_id, 'images', true );

		// Set empty content variable
        $content = '';

        // Loop through and generate HTML for each stored image
        foreach( $images as $img ) {

			// Get image attachment url
            $image_url = wp_get_attachment_url( $img );

			// Check if image url available and update content
            if ( $image_url ) {
                $content .= '<div class="post-card" data-image="' . esc_attr( $img ) . '">';
                $content .= '<div class="image" style="background-image: url(' . esc_url( $image_url ) . ')"></div>';
                $content .= '<div class="set-image">';
                $content .= '<div class="plus">';
                $content .= '<img src="' . esc_url( AIPV_PLUGIN_DIR . 'admin/views/img/plus.svg' ) . '" />';
                $content .= '</div>';
                $content .= '<div class="set-text">' . __( 'Set Featured Image', 'ai-post-visualizer' ) . '</div>';
                $content .= '<div class="current-text">' . __( 'Current Featured Image', 'ai-post-visualizer' ) . '</div>';
                $content .= '</div></div>';
            }
        }

		// Send json response
        wp_send_json( $content );

    }

    /**
     * Sends a request to the DALLE API.
     *
     * @param string $prompt The image generation prompt.
     * @param int    $n      Number of images to generate.
     * @param string $size   Size of the images.
     * @return array|bool    The API response or false on failure.
     */
    public function aipv_api_request( $prompt, $n, $size ) {

        // Get the DALLE API key from the options table
        $dalle_api_key = get_option( 'aipv_dalle_api_key' );
    
        // Ensure the API key exists
        if ( !$dalle_api_key ) {
            return false;
        }
    
        // API request headers
        $headers = [
            'Authorization' => 'Bearer ' . $dalle_api_key,
            'Content-Type'  => 'application/json',
        ];
    
        // Prepare the request data using wp_json_encode()
        $body = wp_json_encode([
            'prompt' => $prompt,
            'n'      => $n,
            'size'   => $size,
        ]);
    
        // Perform the request using wp_remote_post
        $response = wp_remote_post( 'https://api.openai.com/v1/images/generations', [
            'headers' => $headers,
            'body'    => $body,
            'timeout' => 45, // Set an appropriate timeout
        ]);
    
        // Check for errors
        if ( is_wp_error( $response ) ) {
            // error_log( 'HTTP request failed: ' . $response->get_error_message() );
            return false;
        }
    
        // Check if the response code is 200 OK
        $http_status = wp_remote_retrieve_response_code( $response );
        if ( $http_status !== 200 ) {
            // error_log( 'HTTP error: ' . $http_status . ' Response: ' . wp_remote_retrieve_body( $response ) );
            return false;
        }
    
        // Decode and return the response
        return json_decode( wp_remote_retrieve_body( $response ), true );

    }    

    /**
     * Uploads images to the WordPress media library.
     *
     * @param string $url   The URL of the image to upload.
     * @param string $title The title for the image (optional).
     * @return int|false    The attachment ID or false on failure.
     */
    public function aipv_upload_images_to_library ( $url, $title = null ) {

        // Setup required paths for image upload
		require_once( ABSPATH . '/wp-load.php' );
		require_once( ABSPATH . '/wp-admin/includes/image.php' );
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		require_once( ABSPATH . '/wp-admin/includes/media.php' );

		// Download url to a temp file
		$tmp = download_url( $url );
		if( is_wp_error( $tmp ) ) {
			return false;
		}

		// Get the filename and extension ("photo.png" => "photo", "png")
		$filename = pathinfo( $url, PATHINFO_FILENAME );
		$extension = pathinfo( $url, PATHINFO_EXTENSION );

		// An extension is required or else WordPress will reject the upload
		if( ! $extension ) {
			// Look up mime type, example: "/photo.png" -> "image/png"
			$mime = mime_content_type( $tmp );
			$mime = is_string( $mime ) ? sanitize_mime_type( $mime ) : false;

			// Only allow certain mime types because mime types do not always end in a valid extension (see the .doc example below)
			$mime_extensions = array(
				// mime_type         => extension (no period)
				'text/plain'         => 'txt',
				'text/csv'           => 'csv',
				'application/msword' => 'doc',
				'image/jpg'          => 'jpg',
				'image/jpeg'         => 'jpeg',
				'image/gif'          => 'gif',
				'image/png'          => 'png',
				'video/mp4'          => 'mp4',
			);

			if ( isset( $mime_extensions[$mime] ) ) {
				// Use the mapped extension
				$extension = $mime_extensions[$mime];
			} else{
				// Could not identify extension
				wp_delete_file( $tmp );
				return false;
			}
		}

		// Upload by "sideloading": "the same way as an uploaded file is handled by media_handle_upload"
		$filename = md5( uniqid( md5( $filename ), true ) ) . '_' . time();
		$args = array(
			'name' => "$filename.$extension",
			'tmp_name' => $tmp,
		);

		// Do the upload
		$attachment_id = media_handle_sideload( $args, 0, $title );

		// Cleanup temp file
		wp_delete_file( $tmp );

		// Error uploading
		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		// Success, return attachment ID (int)
		return (int) $attachment_id;

	}

}