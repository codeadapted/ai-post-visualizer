<?php

class APV_Plugin {

    /**
     * Run installation functions.
     * Sets initial options when the plugin is activated.
     *
     * @return void
     */
    public static function install() {

        // Set a flag that the plugin has been activated
        update_option( 'apv_activated', true );

        // Set the viewer mode to dark if it has not been set
        if ( !get_option( 'apv_viewer_mode' ) ) {
            update_option( 'apv_viewer_mode', 'dark' );
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
        delete_option( 'apv_activated' );

    }

    /**
     * Run uninstall functions.
     * Cleans up data if the user chooses to clear data on uninstall.
     *
     * @return void
     */
    public static function uninstall() {

        // If the user has opted to clear data, clear it on uninstall
        if ( sanitize_text_field( get_option( 'apv_clear_data' ) ) ) {
            self::apv_clear_data();
            delete_option( 'apv_clear_data' );
        }

    }

    /**
     * Initializes the plugin hooks and filters.
     *
     * @return void
     */
    public function __construct() {

        // Register uninstall, deactivate, and activate hooks
        register_uninstall_hook( APV_FILE, array( __CLASS__, 'uninstall' ) );
        register_deactivation_hook( APV_FILE, array( __CLASS__, 'deactivate' ) );
        register_activation_hook( APV_FILE, array( __CLASS__, 'install' ) );

        if ( is_admin() ) {
            // Add admin page links, load admin scripts, register custom post types
            add_filter( 'plugin_action_links_' . APV_BASENAME . '/ai-post-visualizer.php', array( $this, 'apv_add_settings_link' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'apv_admin_enqueue' ) );
            add_action( 'admin_menu', array( $this, 'apv_admin_page' ) );
            add_action( 'init', array( $this, 'apv_register_history_post_type' ) );
            add_action( 'plugins_loaded', array( $this, 'apv_load_plugin_textdomain' ) );

            // AJAX actions
            add_action( 'wp_ajax_apv_update_viewer_mode', array( $this, 'apv_update_viewer_mode' ) );
            add_action( 'wp_ajax_apv_save_clear_data_setting', array( $this, 'apv_save_clear_data_setting' ) );
            add_action( 'wp_ajax_apv_set_dalle_api_key', array( $this, 'apv_set_dalle_api_key' ) );
        }

    }

    /**
     * Clears plugin-specific data from the database.
     *
     * @return void
     */
    public function apv_clear_data() {

		// Set $wpdb to access db
        global $wpdb;

        // Set apv options array
        $options = array( 'apv_dalle_api_key', 'apv_clear_data', 'apv_viewer_mode' );

        // Loop through options and delete them
        foreach ( $options as $option ) {
            delete_option( $option );
        }

		// Query for all posts of custom post type 'apv_history'
		$apv_history_posts = get_posts( array(
			'post_type'      => 'apv_history',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );

		// Loop through and delete each post along with its associated post meta
		if ( !empty( $apv_history_posts ) ) {
			foreach ( $apv_history_posts as $post_id ) {

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
    public function apv_save_clear_data_setting() {

        // Nonce validation
		check_ajax_referer( 'apv_nonce_action', 'apv_nonce' );

        // Sanitize user input
        $clear_data = isset( $_GET['clear_data'] ) ? sanitize_text_field( wp_unslash( $_GET['clear_data'] ) ) : '';

        // Update or delete the clear data option
        if ( $clear_data && $clear_data === 'true' ) {
            update_option( 'apv_clear_data', true );
        } else {
            delete_option( 'apv_clear_data' );
        }

        // Respond with success message
        wp_send_json_success( __( 'AI Post Visualizer data set to be cleared on uninstall', 'ai-post-visualizer' ) );

    }

    /**
     * Registers the custom post type for storing image generation history.
     *
     * @return void
     */
    public function apv_register_history_post_type() {
        register_post_type( 'apv_history', [
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
    public function apv_add_settings_link( $links ) {
        $links[] = '<a href="' . $this->apv_get_admin_url() . '">' . __( 'Settings' ) . '</a>';
        return $links;
    }

    /**
     * Registers and enqueues admin styles and scripts.
     *
     * @return void
     */
    public function apv_admin_enqueue() {

        // Only enqueue scripts and styles on the settings page
        if ( strpos( $this->apv_get_current_admin_url(), $this->apv_get_admin_url() ) !== false ) {

            // Enqueue Google Fonts (Poppins)
            wp_enqueue_style( 'font-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), '1.0.0' );

            // Enqueue the main admin stylesheet
            wp_enqueue_style( 'apv_stylesheet', APV_PLUGIN_DIR . 'admin/css/admin.css', array(), '1.0.0' );

            // Create a nonce for secure AJAX requests
            $nonce = wp_create_nonce( 'apv_nonce_action' );

            // Enqueue the main admin script (dependent on jQuery)
            wp_enqueue_script( 'apv_script', APV_PLUGIN_DIR . 'admin/js/admin.js', array( 'jquery' ), '1.0.0', true );

            // Localize the script to pass AJAX URL and nonce to the JavaScript file
            wp_localize_script( 'apv_script', 'apv_obj',
                array(
                    'ajax_url' => admin_url( 'admin-ajax.php' ), // The admin AJAX URL
                    'apv_nonce' => $nonce // The nonce for AJAX security
                )
            );
        }

    }

    /**
     * Registers the admin page and menu.
     *
     * @return void
     */
    public function apv_admin_page() {
        add_menu_page(
            __( 'AI Post Visualizer', 'ai-post-visualizer' ),
            __( 'AI Post Visualizer', 'ai-post-visualizer' ),
            'manage_options',
            APV_DIRNAME,
            array( $this, 'apv_admin_page_settings' ),
            APV_PLUGIN_DIR . '/admin/views/img/menu_icon.png',
            100
        );
    }

    /**
     * Renders the admin settings page.
     *
     * @return void
     */
    public function apv_admin_page_settings() {
        require_once APV_DIRNAME . '/admin/view.php';
    }

    /**
     * Gets the current admin URL.
     *
     * @return string The current admin URL.
     */
    public function apv_get_current_admin_url() {

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
    public function apv_get_admin_url() {
        return add_query_arg( array( 'page' => APV_BASENAME ), admin_url( 'admin.php' ) );
    }

    /**
     * Loads the plugin's textdomain for translation.
     *
     * @return void
     */
    public function apv_load_plugin_textdomain() {
        load_plugin_textdomain( 'ai-post-visualizer', false, APV_BASENAME . '/languages' );
    }

    /**
     * Updates the viewer mode (light/dark).
     *
     * @return void
     */
    public function apv_update_viewer_mode() {

        // Nonce validation
		check_ajax_referer( 'apv_nonce_action', 'apv_nonce' );

        // Sanitize the mode input
        $mode = isset( $_GET['mode'] ) ? sanitize_text_field( wp_unslash( $_GET['mode'] ) ) : 'dark';

        // Update the viewer mode option
        update_option( 'apv_viewer_mode', $mode );

    }

    /**
	 * Set Dalle API Key 
	 *
	 * @param   void
	 * @return  void
	 */
	public function apv_set_dalle_api_key() {

        // Nonce validation
		check_ajax_referer( 'apv_nonce_action', 'apv_nonce' );

        // Set api key
        $api_key = isset( $_GET['api_key'] ) ? sanitize_text_field( wp_unslash( $_GET['api_key'] ) ) : '';

		// Set dalle api key option if added
		if( $api_key ) {
			update_option( 'apv_dalle_api_key', $api_key );
		}

        // Send json success
        wp_send_json_success( array( 'message' => 'API key successfully updated' ) );

	}

}