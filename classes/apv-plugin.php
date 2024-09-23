<?php

class APV_Plugin {

    /**
     * install
     *
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
     * deactivate
     *
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
     * uninstall
     *
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
     * __construct
     *
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
            add_filter( 'plugin_action_links_' . APV_BASENAME . '/ai-post-visualizer.php', array( $this, 'add_settings_link' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
            add_action( 'admin_menu', array( $this, 'admin_page' ) );
            add_action( 'init', array( $this, 'apv_register_history_post_type' ) );
            add_action( 'init', array( $this, 'apv_load_plugin_textdomain' ) );

            // AJAX actions
            add_action( 'wp_ajax_apv_update_viewer_mode', array( $this, 'apv_update_viewer_mode' ) );
            add_action( 'wp_ajax_apv_save_clear_data_setting', array( $this, 'apv_save_clear_data_setting' ) );
        }

    }

    /**
     * apv_clear_data
     *
     * Clears plugin-specific data from the database.
     *
     * @return void
     */
    public function apv_clear_data() {

		// Set $wpdb to access db
        global $wpdb;

		// Delete options related to the plugin
		$deleted_rows = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE '%apv_%'" );

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
     * apv_save_clear_data_setting
     *
     * Saves the setting for clearing data on uninstall.
     *
     * @return void
     */
    public function apv_save_clear_data_setting() {

        // Verify nonce for security to prevent CSRF
        $nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Sanitize user input
        $clear_data = sanitize_text_field( $_GET['clear_data'] );

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
     * apv_register_history_post_type
     *
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
     * add_settings_link
     *
     * Adds a settings link on the plugin page.
     *
     * @param array $links The links array.
     * @return array The updated links array.
     */
    public function add_settings_link( $links ) {
        $links[] = '<a href="' . $this->get_admin_url() . '">' . __( 'Settings' ) . '</a>';
        return $links;
    }

    /**
     * admin_enqueue
     *
     * Registers and enqueues admin styles and scripts.
     *
     * @return void
     */
    public function admin_enqueue() {

        // Only enqueue scripts and styles on the settings page
        if ( strpos( $this->get_current_admin_url(), $this->get_admin_url() ) !== false ) {

            // Enqueue Google Fonts (Poppins)
            wp_enqueue_style( 'font-poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap', array(), null );

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
     * admin_page
     *
     * Registers the admin page and menu.
     *
     * @return void
     */
    public function admin_page() {
        add_submenu_page(
            'options-general.php',
            __( 'AI Post Visualizer', 'ai-post-visualizer' ),
            __( 'AI Post Visualizer', 'ai-post-visualizer' ),
            'administrator',
            APV_DIRNAME,
            array( $this, 'admin_page_settings' ),
            100
        );
    }

    /**
     * admin_page_settings
     *
     * Renders the admin settings page.
     *
     * @return void
     */
    public function admin_page_settings() {
        require_once APV_DIRNAME . '/admin/view.php';
    }

    /**
     * get_current_admin_url
     *
     * Gets the current admin URL.
     *
     * @return string The current admin URL.
     */
    public function get_current_admin_url() {

		// Get uri
        $uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
        if ( ! $uri ) {
            return '';
        }

		// Return admin url
        return remove_query_arg( array( '_wpnonce' ), admin_url( $uri ) );

    }

    /**
     * get_admin_url
     *
     * Gets the admin URL for the plugin settings page.
     *
     * @return string The admin URL.
     */
    public function get_admin_url() {
        return admin_url( 'options-general.php?page=' . APV_BASENAME );
    }

    /**
     * apv_load_plugin_textdomain
     *
     * Loads the plugin's textdomain for translation.
     *
     * @return void
     */
    public function apv_load_plugin_textdomain() {
        load_plugin_textdomain( 'ai-post-visualizer', false, APV_BASENAME . '/languages' );
    }

    /**
     * apv_update_viewer_mode
     *
     * Updates the viewer mode (light/dark).
     *
     * @return void
     */
    public function apv_update_viewer_mode() {

        // Verify nonce for security to prevent CSRF
        $nonce_check = !isset( $_GET['apv_nonce'] ) || !wp_verify_nonce( $_GET['apv_nonce'], 'apv_nonce_action' );
        if ( $nonce_check ) {
            wp_send_json_error( array( 'message' => 'Invalid nonce' ) );
            return false;
        }

        // Sanitize the mode input
        $mode = sanitize_text_field( $_GET['mode'] );

        // Update the viewer mode option
        update_option( 'apv_viewer_mode', $mode );

    }

}