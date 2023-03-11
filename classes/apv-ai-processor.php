<?php

class APV_AI_PROCESSOR {


	/**
	 * __construct
	 *
	 * @param   void
	 * @return  void
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'wp_ajax_apv_get_dalle_images', array( $this, 'apv_get_dalle_images' ) );
			add_action( 'wp_ajax_apv_set_dalle_image', array( $this, 'apv_set_dalle_image' ) );
			add_action( 'wp_ajax_apv_revert_featured_image', array( $this, 'apv_revert_featured_image' ) );
			add_action( 'wp_ajax_apv_load_dalle_history', array( $this, 'apv_load_dalle_history' ) );
		}

	}

	/**
	 * apv_get_dalle_images
	 *
	 * Generate DALLE images
	 *
	 * @param   void
	 * @return  string $content The HTML for the generated images
	 */
	public function apv_get_dalle_images() {

		$post_id = $_GET['post_id'];
		$prompt = $_GET['prompt'];
		$n = $_GET['n'];
		$size = $_GET['size'];

		$image_title = explode( ' ', $prompt );
		$image_title = implode( '-', array_splice( $image_title, 0, 6 ) );

		if( $n ) {
			$n = intval( $n );
		} else {
			$n = 1;
		}

		if( $size ) {
			$size = $size;
		} else {
			$size = '256x256';
		}

		$api_data = $this->apv_api_request( $prompt, $n, $size );

		$urls = json_decode( $api_data )->data;

		$content = '';
		$generated_images = array();

		$i = 0;
		foreach( $urls as $url ) {

			$image_id = $this->upload_images_to_library( $url->url, $image_title . '-' . $i );
			$image_url = wp_get_attachment_url( $image_id );
			array_push( $generated_images, $image_id );

			$content .= '<div class="post-card" data-image="' . $image_id  . '">';
				$content .= '<div class="image" style="background-image: url(' . $image_url . ')"></div>';
				$content .= '<div class="set-image">';
					$content .= '<div class="plus">';
						$content .= '<img src="' . APV_PLUGIN_DIR  . 'admin/img/plus.svg" />';
					$content .= '</div>';
					$content .= '<div class="set-text">' . __( 'Set Featured Image', 'ai-post-visualizer' ) . '</div>';
					$content .= '<div class="current-text">' . __( 'Current Featured Image', 'ai-post-visualizer' ) . '</div>';
				$content .= '</div>';
			$content .= '</div>';



			$i++;

		}

		if( $content ) {

			$history = wp_insert_post( [
					'post_type'    => 'apv_history',
					'post_status'  => 'publish',
					'post_title'   => $prompt,
					'post_name'    => uniqid( 'apv_' )
			] );

			update_post_meta( $history, 'prompt', $prompt );
			update_post_meta( $history, 'images', $generated_images );

			wp_send_json( $content );

		} else {

			wp_send_json( 'Error with prompt.' );

		}

	}

	/**
	 * apv_set_dalle_image
	 *
	 * Set DALLE image as post featured image
	 *
	 * @param   void
	 * @return  string $image_url Url of new featured image
	 */
	public function apv_set_dalle_image() {

		$post_id = $_GET['post_id'];
		$image_id = $_GET['image_id'];

		$original = get_post_thumbnail_id( $post_id );

		if( !get_post_meta( $post_id, 'apv_revert', true ) ) {
			update_post_meta( $post_id, 'apv_revert', $original );
		}

		set_post_thumbnail( $post_id, $image_id );

		$image_url = wp_get_attachment_url( $image_id );

		wp_send_json( $image_url );

	}

	/**
	 * apv_revert_featured_image
	 *
	 * Revert back to initial featured image
	 *
	 * @param   void
	 * @return  string Success message
	 */
	public function apv_revert_featured_image() {

		$post_id = $_GET['post_id'];
		$original_img = get_post_meta( $post_id, 'apv_revert', true );

		set_post_thumbnail( $post_id, $original_img );

		delete_post_meta( $post_id, 'apv_revert' );

		$image_url = wp_get_attachment_url( $original_img );

		wp_send_json( $image_url );

	}

	/**
	 * apv_load_dalle_history
	 *
	 * Load stored DALLE images
	 *
	 * @param   void
	 * @return  string $content The HTML for the generated images
	 */
	public function apv_load_dalle_history() {

		$post_id = $_GET['post_id'];

		$images = get_post_meta( $post_id, 'images', true );

		$content = '';

		foreach( $images as $img ) {

			$image_url = wp_get_attachment_url( $img );

			if( $image_url ) {
				$content .= '<div class="post-card" data-image="' . $img . '">';
					$content .= '<div class="image" style="background-image: url(' . $image_url . ')"></div>';
					$content .= '<div class="set-image">';
						$content .= '<div class="plus">';
							$content .= '<img src="' . APV_PLUGIN_DIR  . 'admin/img/plus.svg" />';
						$content .= '</div>';
						$content .= '<div class="set-text">' . __( 'Set Featured Image', 'ai-post-visualizer' ) . '</div>';
						$content .= '<div class="current-text">' . __( 'Current Featured Image', 'ai-post-visualizer' ) . '</div>';
					$content .= '</div>';
				$content .= '</div>';
			} else {

			}

		}

		wp_send_json( $content );

	}

	/**
	 * apv_api_request
	 *
	 * Remove DALLE image as post featured image
	 *
	 * @param   void
	 * @return  Object $data Curl request response data
	 */
	public function apv_api_request( $prompt, $n, $size ) {

		$url = 'https://api.openai.com/v1/images/generations';

		$curl = curl_init();

		$fields = array(
			'prompt' => $prompt,
			'n' => $n,
			'size' => $size
		);

		$headers = array(
			'Content-Type: application/json',
			'Authorization: Bearer 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'
		);

		$json_string = json_encode( $fields );

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, TRUE );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $json_string );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true  );

		$data = curl_exec( $curl );

		curl_close( $curl );

		return $data;

	}

	/**
	 * upload_images_to_library
	 *
	 * Remove DALLE image as post featured image
	 *
	 * @param   void
	 * @return  integer $file_id Uploaded image id
	 */
	public function upload_images_to_library ( $url, $title = null ) {

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
				@unlink($tmp);
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
		@unlink($tmp);

		// Error uploading
		if ( is_wp_error( $attachment_id ) ) {
			return false;
		}

		// Success, return attachment ID (int)
		return (int) $attachment_id;

	}

}
