<?php

class APV_Plugin {

	/**
	 * install
	 *
	 * Run installation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function install() {

		update_option( 'apv_activated', true );

	}

	/**
	 * deactivate
	 *
	 * Run deactivation functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function deactivate() {

		delete_option( 'apv_activated' );

	}

	/**
	 * uninstall
	 *
	 * Run uninstall functions.
	 *
	 * @param   void
	 * @return  void
	 */
	public static function uninstall() {

		if( sanitize_text_field( get_option( 'apv_clear_data' ) ) ) {
			self::apv_clear_data();
			delete_option( 'apv_clear_data' );
		}

	}

	/**
	 * __construct
	 *
	 * @param   void
	 * @return  void
	 */
	public function __construct() {

		register_uninstall_hook( APV_FILE, array( __CLASS__, 'uninstall' ) );
		register_deactivation_hook( APV_FILE, array( __CLASS__, 'deactivate' ) );
		register_activation_hook( APV_FILE, array( __CLASS__, 'install' ) );

		if ( is_admin() ) {
			add_filter( 'plugin_action_links_' . APV_BASENAME . '/ai-post-visualizer.php', array( $this, 'add_settings_link' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
			add_action( 'admin_menu', array( $this, 'admin_page' ) );
			add_action( 'wp_ajax_apv_save_admin_page', array( $this, 'apv_save_admin_page' ) );
			add_action( 'init', array( $this, 'apv_register_history_post_type' ) );
			add_action( 'init', array( $this, 'apv_load_plugin_textdomain' ) );
		}

	}

	/**
	 * apv_clear_data
	 *
	 * Clear translated user bio data.
	 *
	 * @param   void
	 * @return  void
	 */
	public function apv_clear_data() {

		$main_site_id = get_main_site_id();

		if( function_exists('is_multisite') && is_multisite() ) {
			switch_to_blog( $main_site_id );
		}

		global $wpdb;

		$deleted_rows = $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE `meta_key` LIKE '%apv_profile_bio%'" );

		restore_current_blog();

	}

	/**
	 * apv_save_admin_page
	 *
	 * Save admin page data
	 *
	 * @param   void
	 * @return  void
	 */
	public function apv_save_admin_page() {
		$clear_data = sanitize_text_field( $_POST['clear_data'] );
		$main_site_id = get_main_site_id();

		if( function_exists('is_multisite') && is_multisite() ) {
			switch_to_blog( $main_site_id );
		}

		if( $clear_data ) {
			update_option( 'apv_clear_data', true );
		} else {
			delete_option( 'apv_clear_data' );
		}

		restore_current_blog();

		wp_send_json_success( __( 'AI Post Visualizer data set to be cleared on uninstall', 'ai-post-visualizer' ) );
	}

	/**
	 * apv_register_history_post_type
	 *
	 * Create apv_history post type
	 *
	 * @param   void
	 * @return  void
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
	 * Add settings link on plugin page
	 *
	 * @param   array $links The links array.
	 * @return  array The links array.
	 */
	public function add_settings_link( $links ) {
		$links[] = '<a href="' . $this->get_admin_url() . '">' . __( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	 * admin_enqueue
	 *
	 * Register and enqueue admin stylesheet & scripts
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_enqueue() {
		// only enqueue these things on the settings page
		if ( $this->get_current_admin_url() == $this->get_admin_url() ) {
			wp_enqueue_style( 'apv_stylesheet', APV_PLUGIN_DIR . 'admin/css/admin.css', array(), '1.0.0' );
			wp_enqueue_script( 'apv_script', APV_PLUGIN_DIR . 'admin/js/admin.js', array( 'jquery' ), '1.0.0' );
			wp_localize_script( 'apv_script', 'apv_obj',
				array(
					'ajax_url' => admin_url( 'admin-ajax.php' )
				)
			);
			wp_enqueue_script( 'apv_script' );
		}
	}

	/**
	 * admin_page
	 *
	 * Register admin page and menu.
	 *
	 * @param   void
	 * @return  void
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
	 * Render admin view
	 *
	 * @param   void
	 * @return  void
	 */
	public function admin_page_settings() {
		require_once APV_DIRNAME . '/admin/view.php';
	}

	/**
	 * get_current_admin_url
	 *
	 * Get the current admin url.
	 *
	 * @param   void
	 * @return  void
	 */
	public function get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );
		if ( ! $uri ) {
			return '';
		}
		return remove_query_arg( array( '_wpnonce' ), admin_url( $uri ) );
	}

	/**
	 * get_admin_url
	 *
	 * Add settings link on plugin page
	 *
	 * @param   void
	 * @return  string the admin url
	 */
	public function get_admin_url() {
		return admin_url( 'options-general.php?page=' . APV_BASENAME );
	}

	/**
	 * apv_load_plugin_textdomain
	 *
	 * Add settings link on plugin page
	 *
	 * @param   void
	 * @return  string the translation .mo file path
	 */
	public function apv_load_plugin_textdomain() {
		load_plugin_textdomain( 'ai-post-visualizer', false, APV_BASENAME . '/languages' );
	}

}
