<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AIPV_Plugin {

  const OPTION_API_KEY_LEGACY = 'aipv_dalle_api_key';
  const OPTION_API_KEY_ENC    = 'aipv_dalle_api_key_enc';

  /**
   * Run installation functions.
   * Sets initial options when the plugin is activated.
   *
   * @return void
   */
  public static function install() {

    // Set a flag that the plugin has been activated
    update_option( 'aipv_activated', true );

    // Set the viewer mode to dark if it has not been set
    if ( !get_option( 'aipv_viewer_mode' ) ) {
      update_option( 'aipv_viewer_mode', 'dark' );
    }

  }

  /**
   * Run deactivation functions.
   * Removes specific options when the plugin is deactivated.
   *
   * @return void
   */
  public static function deactivate() {

    // Remove activation flag on deactivation
    delete_option( 'aipv_activated' );

  }

  /**
   * Run uninstall functions.
   * Cleans up data if the user chooses to clear data on uninstall.
   *
   * @return void
   */
  public static function uninstall() {

    // If the user has opted to clear data, clear it on uninstall
    if ( sanitize_text_field( get_option( 'aipv_clear_data' ) ) ) {
      self::aipv_clear_data();
      delete_option( 'aipv_clear_data' );
    }

  }

  /**
   * Initializes the plugin hooks and filters.
   *
   * @return void
   */
  public function __construct() {

    // Register uninstall, deactivate, and activate hooks
    register_uninstall_hook( AIPV_FILE, array( __CLASS__, 'uninstall' ) );
    register_deactivation_hook( AIPV_FILE, array( __CLASS__, 'deactivate' ) );
    register_activation_hook( AIPV_FILE, array( __CLASS__, 'install' ) );

    if ( is_admin() ) {
      // Add admin page links, load admin scripts, register custom post types
      add_filter( 'plugin_action_links_' . AIPV_BASENAME . '/ai-post-visualizer.php', array( $this, 'aipv_add_settings_link' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'aipv_admin_enqueue' ) );
      add_action( 'admin_menu', array( $this, 'aipv_admin_page' ) );
      add_action( 'init', array( $this, 'aipv_register_history_post_type' ) );
      add_action( 'plugins_loaded', array( $this, 'aipv_load_plugin_textdomain' ) );

      // AJAX actions
      add_action( 'wp_ajax_aipv_update_viewer_mode', array( $this, 'aipv_update_viewer_mode' ) );
      add_action( 'wp_ajax_aipv_save_clear_data_setting', array( $this, 'aipv_save_clear_data_setting' ) );
      add_action( 'wp_ajax_aipv_set_dalle_api_key', array( $this, 'aipv_set_dalle_api_key' ) );

		  // Migrate any legacy plaintext key to encrypted storage.
		  $this->aipv_maybe_migrate_plaintext_api_key();
    }

  }

  /**
   * Clears plugin-specific data from the database.
   *
   * @return void
   */
  public function aipv_clear_data() {

    // Set $wpdb to access db
    global $wpdb;

    // Set aipv options array
    $options = array( self::OPTION_API_KEY_LEGACY, self::OPTION_API_KEY_ENC, 'aipv_clear_data', 'aipv_viewer_mode' );

    // Loop through options and delete them
    foreach ( $options as $option ) {
        delete_option( $option );
    }

    // Query for all posts of custom post type 'aipv_history'
    $aipv_history_posts = get_posts( array(
      'post_type'      => 'aipv_history',
      'post_status'    => 'any',
      'posts_per_page' => -1,
      'fields'         => 'ids',
    ) );

    // Loop through and delete each post along with its associated post meta
    if ( !empty( $aipv_history_posts ) ) {
      foreach ( $aipv_history_posts as $post_id ) {

        // Delete the post along with associated post meta and attachments
        wp_delete_post( $post_id, true );

      }
    }

  }

  /**
   * Saves the setting for clearing data on uninstall.
   *
   * @return void
   */
  public function aipv_save_clear_data_setting() {

    // Nonce validation
    check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

    // Capability check
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'ai-post-visualizer' ) ), 403 );
    }

    // Sanitize user input
    $clear_data = isset( $_GET['clear_data'] ) ? sanitize_text_field( wp_unslash( $_GET['clear_data'] ) ) : '';

    // Update or delete the clear data option
    if ( $clear_data && $clear_data === 'true' ) {
      update_option( 'aipv_clear_data', true );
    } else {
      delete_option( 'aipv_clear_data' );
    }

    // Respond with success message
    wp_send_json_success( __( 'AI Post Visualizer data set to be cleared on uninstall', 'ai-post-visualizer' ) );

  }

  /**
   * Registers the custom post type for storing image generation history.
   *
   * @return void
   */
  public function aipv_register_history_post_type() {
    register_post_type( 'aipv_history', [
      'public'              => false,
      'show_ui'             => false,
      'show_in_menu'        => false,
      'show_in_nav_menus'   => false,
      'show_in_admin_bar'   => false,
      'can_export'          => true,
      'has_archive'         => false,
      'exclude_from_search' => true,
      'publicly_queryable'  => false,
      'capability_type'     => 'post',
      'supports'            => false,
      'rewrite'             => false,
    ] );
  }

  /**
   * Adds a settings link on the plugin page.
   *
   * @param array $links The links array.
   * @return array The updated links array.
   */
  public function aipv_add_settings_link( $links ) {
    $links[] = '<a href="' . $this->aipv_get_admin_url() . '">' . __( 'Settings', 'ai-post-visualizer' ) . '</a>';
    return $links;
  }

  /**
   * Registers and enqueues admin styles and scripts.
   *
   * @return void
   */
  public function aipv_admin_enqueue() {

    // Only enqueue scripts and styles on the settings page
    if ( strpos( $this->aipv_get_current_admin_url(), $this->aipv_get_admin_url() ) !== false ) {

      // Enqueue Google Fonts (Poppins)
      wp_enqueue_style( 'font-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), '1.0.0' );

      // Enqueue the main admin stylesheet
      wp_enqueue_style( 'aipv_stylesheet', AIPV_PLUGIN_DIR . 'admin/css/admin.css', array(), '1.0.0' );

      // Create a nonce for secure AJAX requests
      $nonce = wp_create_nonce( 'aipv_nonce_action' );

      // Enqueue the main admin script (dependent on jQuery)
      wp_enqueue_script( 'aipv_script', AIPV_PLUGIN_DIR . 'admin/js/admin.js', array( 'jquery' ), '1.0.0', true );

      // Localize the script to pass AJAX URL and nonce to the JavaScript file
      wp_localize_script( 'aipv_script', 'aipv_obj',
          array(
              'ajax_url' => admin_url( 'admin-ajax.php' ), // The admin AJAX URL
              'aipv_nonce' => $nonce // The nonce for AJAX security
          )
      );

    }

  }

  /**
   * Registers the admin page and menu.
   *
   * @return void
   */
  public function aipv_admin_page() {
    add_menu_page(
      __( 'AI Post Visualizer', 'ai-post-visualizer' ),
      __( 'AI Post Visualizer', 'ai-post-visualizer' ),
      'edit_posts',
      AIPV_DIRNAME,
      array( $this, 'aipv_admin_page_settings' ),
      AIPV_PLUGIN_DIR . '/admin/views/img/menu_icon.png',
      100
    );
  }

  /**
   * Renders the admin settings page.
   *
   * @return void
   */
  public function aipv_admin_page_settings() {
    require_once AIPV_DIRNAME . '/admin/view.php';
  }

  /**
   * Gets the current admin URL.
   *
   * @return string The current admin URL.
   */
  public function aipv_get_current_admin_url() {

    // Get the current request URI
    $uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
    
    // Ensure there's a valid URI
    if ( empty( $uri ) ) {
      return '';
    }
    
    // Sanitize and clean the URI
    $uri = esc_url_raw( $uri );
  
    // Strip the path to ensure we're only working within the wp-admin area
    $uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
  
    // Return the sanitized current admin URL, without _wpnonce
    return remove_query_arg( array( '_wpnonce' ), admin_url( $uri ) );

  }

  /**
   * Gets the admin URL for the plugin settings page.
   *
   * @return string The admin URL.
   */
  public function aipv_get_admin_url() {
    return add_query_arg( array( 'page' => AIPV_BASENAME ), admin_url( 'admin.php' ) );
  }

  /**
   * Loads the plugin's textdomain for translation.
   *
   * @return void
   */
  public function aipv_load_plugin_textdomain() {
    load_plugin_textdomain( 'ai-post-visualizer', false, AIPV_BASENAME . '/languages' );
  }

  /**
   * Updates the viewer mode (light/dark).
   *
   * @return void
   */
  public function aipv_update_viewer_mode() {

    // Nonce validation
    check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

    // Capability check
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'ai-post-visualizer' ) ), 403 );
    }

    // Sanitize the mode input
    $mode = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : 'dark';

    // Update the viewer mode option
    update_option( 'aipv_viewer_mode', $mode );

  }

  /**
	 * Set Dalle API Key 
	 *
	 * @param   void
	 * @return  void
	 */
	public function aipv_set_dalle_api_key() {

    // Nonce validation
		check_ajax_referer( 'aipv_nonce_action', 'aipv_nonce' );

    // Capability check
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'ai-post-visualizer' ) ), 403 );
    }

    // If key is provided by server config, do not allow overriding in the DB.
    if ( $this->aipv_get_server_managed_api_key() ) {
      wp_send_json_error(
        array( 'message' => __( 'API key is managed by server configuration and cannot be changed here.', 'ai-post-visualizer' ) ),
        400
      );
    }

    // Set api key (prefer POST to avoid leaking key in URLs/logs).
    $api_key = '';
    if ( isset( $_POST['api_key'] ) ) {
      $api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
    } elseif ( isset( $_GET['api_key'] ) ) {
      // Back-compat for older JS.
      $api_key = sanitize_text_field( wp_unslash( $_GET['api_key'] ) );
    }
    $api_key = trim( (string) $api_key );

    // Allow clearing a stored key.
    if ( $api_key === '' ) {
      delete_option( self::OPTION_API_KEY_ENC );
      delete_option( self::OPTION_API_KEY_LEGACY );
      wp_send_json_success( array( 'message' => __( 'API key cleared', 'ai-post-visualizer' ) ) );
    }

    if ( ! $this->aipv_crypto_available() ) {
      wp_send_json_error(
        array( 'message' => __( 'OpenSSL is not available on this server. Define AIPV_OPENAI_API_KEY as an environment variable or constant instead of storing it in the database.', 'ai-post-visualizer' ) ),
        500
      );
    }

    $payload = $this->aipv_encrypt_secret( $api_key );
    if ( ! $payload ) {
      wp_send_json_error( array( 'message' => __( 'Unable to store API key securely.', 'ai-post-visualizer' ) ), 500 );
    }

    // Store encrypted key (avoid autoloading secrets).
    update_option( self::OPTION_API_KEY_ENC, $payload, false );
    delete_option( self::OPTION_API_KEY_LEGACY );

    // Send json success
    wp_send_json_success( array( 'message' => __( 'API key successfully updated', 'ai-post-visualizer' ) ) );

	}

  /**
   * Returns the DALL·E/OpenAI API key.
   * Prefers server-managed configuration (env/constant), otherwise decrypts from the DB.
   *
   * @return string
   */
  public function aipv_get_dalle_api_key() {
    $server_key = $this->aipv_get_server_managed_api_key();
    if ( $server_key ) {
      return $server_key;
    }

    // Migrate legacy plaintext key if present.
    $this->aipv_maybe_migrate_plaintext_api_key();

    $payload = get_option( self::OPTION_API_KEY_ENC );
    if ( ! is_string( $payload ) || $payload === '' ) {
      return '';
    }

    $decrypted = $this->aipv_decrypt_secret( $payload );
    return is_string( $decrypted ) ? $decrypted : '';
  }

  /**
   * @return bool
   */
  public function aipv_has_dalle_api_key() {
    return $this->aipv_get_dalle_api_key() !== '';
  }

  /**
   * @return string One of: constant|env|encrypted_option|none
   */
  public function aipv_get_dalle_api_key_source() {
    if ( defined( 'AIPV_OPENAI_API_KEY' ) && is_string( AIPV_OPENAI_API_KEY ) && trim( AIPV_OPENAI_API_KEY ) !== '' ) {
      return 'constant';
    }
    $env = getenv( 'AIPV_OPENAI_API_KEY' );
    if ( is_string( $env ) && trim( $env ) !== '' ) {
      return 'env';
    }
    $payload = get_option( self::OPTION_API_KEY_ENC );
    if ( is_string( $payload ) && $payload !== '' ) {
      return 'encrypted_option';
    }
    return 'none';
  }

  /**
   * @return string
   */
  private function aipv_get_server_managed_api_key() {
    if ( defined( 'AIPV_OPENAI_API_KEY' ) && is_string( AIPV_OPENAI_API_KEY ) ) {
      $key = trim( AIPV_OPENAI_API_KEY );
      if ( $key !== '' ) {
        return $key;
      }
    }
    $env = getenv( 'AIPV_OPENAI_API_KEY' );
    if ( is_string( $env ) ) {
      $env = trim( $env );
      if ( $env !== '' ) {
        return $env;
      }
    }
    return '';
  }

  /**
   * Migrates a legacy plaintext key (if present) into encrypted storage.
   *
   * @return void
   */
  private function aipv_maybe_migrate_plaintext_api_key() {
    if ( $this->aipv_get_server_managed_api_key() ) {
      // If a server-managed key exists, remove any stored DB keys.
      delete_option( self::OPTION_API_KEY_ENC );
      delete_option( self::OPTION_API_KEY_LEGACY );
      return;
    }

    $legacy = get_option( self::OPTION_API_KEY_LEGACY );
    if ( ! is_string( $legacy ) || trim( $legacy ) === '' ) {
      return;
    }

    // Only migrate if we can encrypt.
    if ( ! $this->aipv_crypto_available() ) {
      return;
    }

    $payload = $this->aipv_encrypt_secret( trim( $legacy ) );
    if ( $payload ) {
      update_option( self::OPTION_API_KEY_ENC, $payload, false );
      delete_option( self::OPTION_API_KEY_LEGACY );
    }
  }

  /**
   * @return bool
   */
  private function aipv_crypto_available() {
    return function_exists( 'openssl_encrypt' ) && function_exists( 'openssl_decrypt' ) && function_exists( 'openssl_cipher_iv_length' );
  }

  /**
   * @return string Raw binary key
   */
  private function aipv_get_crypto_key() {
    return hash( 'sha256', wp_salt( 'aipv_openai_api_key' ), true );
  }

  /**
   * @param string $plaintext
   * @return string|false JSON payload
   */
  private function aipv_encrypt_secret( $plaintext ) {
    $plaintext = (string) $plaintext;
    if ( $plaintext === '' ) {
      return false;
    }

    $key = $this->aipv_get_crypto_key();
    $cipher = in_array( 'aes-256-gcm', openssl_get_cipher_methods(), true ) ? 'aes-256-gcm' : 'aes-256-cbc';
    $iv_len = openssl_cipher_iv_length( $cipher );
    if ( ! $iv_len ) {
      return false;
    }
    $iv = random_bytes( $iv_len );

    $tag = '';
    $ciphertext = openssl_encrypt( $plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag );
    if ( $ciphertext === false ) {
      return false;
    }

    $payload = array(
      'v'      => 1,
      'cipher' => $cipher,
      'iv'     => base64_encode( $iv ),
      'data'   => base64_encode( $ciphertext ),
    );
    if ( $cipher === 'aes-256-gcm' ) {
      $payload['tag'] = base64_encode( $tag );
    }

    return wp_json_encode( $payload );
  }

  /**
   * @param string $payload_json
   * @return string
   */
  private function aipv_decrypt_secret( $payload_json ) {
    if ( ! $this->aipv_crypto_available() ) {
      return '';
    }
    if ( ! is_string( $payload_json ) || $payload_json === '' ) {
      return '';
    }

    $payload = json_decode( $payload_json, true );
    if ( ! is_array( $payload ) || empty( $payload['cipher'] ) || empty( $payload['iv'] ) || empty( $payload['data'] ) ) {
      return '';
    }

    $cipher = (string) $payload['cipher'];
    $iv = base64_decode( (string) $payload['iv'], true );
    $data = base64_decode( (string) $payload['data'], true );
    if ( ! is_string( $iv ) || ! is_string( $data ) ) {
      return '';
    }

    $key = $this->aipv_get_crypto_key();
    $tag = '';
    if ( $cipher === 'aes-256-gcm' ) {
      $tag = isset( $payload['tag'] ) ? base64_decode( (string) $payload['tag'], true ) : '';
      if ( ! is_string( $tag ) ) {
        return '';
      }
    }

    $plaintext = openssl_decrypt( $data, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag );
    return $plaintext === false ? '' : (string) $plaintext;
  }

}