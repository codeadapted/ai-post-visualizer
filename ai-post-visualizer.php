<?php
/**
 * Plugin Name:  AI Post Visualizer
 * Description:  Add featured images generated by Open AI's API into your posts all in one place.
 * Version:      1.0.0
 * Author:       CodeAdapted
 * Author URI:   https://codeadapted.com
 * License:      GPL2 or later
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:  ai-post-visualizer
 *
 * @package     AIPostVisualizer
 * @author      CodeAdapted
 * @copyright   Copyright (c) 2023, CodeAdapted LLC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! defined( 'APV_PLUGIN_FILE' ) ) {
    define( 'APV_PLUGIN_FILE', __FILE__ );
}

require_once __DIR__ . '/classes/apv-ai-processor.php';
require_once __DIR__ . '/classes/apv-plugin.php';
require_once __DIR__ . '/classes/apv-posts.php';

if ( ! class_exists( 'AIPostVisualizer' ) ) :

	class AIPostVisualizer {

		/** @var string The plugin version number. */
		var $version = '1.0.0';

		/** @var string Shortcuts. */
		var $plugin;
		var $posts;

		/**
		 * __construct
		 *
		 * A dummy constructor to ensure AIPostVisualizer is only setup once.
		 *
		 * @param   void
		 * @return  void
		 */
		function __construct() {
			// Do nothing.
		}

		/**
		 * initialize
		 *
		 * Sets up the AIPostVisualizer plugin.
		 *
		 * @param   void
		 * @return  void
		 */
		function initialize() {

			// Define constants.
			$this->define( 'APV', true );
			$this->define( 'APV_FILE', __FILE__ );
			$this->define( 'APV_DIRNAME', dirname( __FILE__ ) );
			$this->define( 'APV_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );
			$this->define( 'APV_BASENAME', basename( dirname( __FILE__ ) ) );

			// Do all the plugin stuff.
			$this->plugin    = new APV_Plugin();

			// Generate posts with appropriate metadata
			$this->posts     = new APV_Posts();

			// Ai Image processing
			$this->ai_processor = new APV_AI_PROCESSOR();

		}

		/**
		 * __call
		 *
		 * Sugar function to access class properties
		 *
		 * @param   string $name The property name.
		 * @return  void
		 */
		public function __call( $name, $arguments ) {
			return $this->{$name};
		}

		/**
		 * define
		 *
		 * Defines a constant if doesnt already exist.
		 *
		 * @param   string $name The constant name.
		 * @param   mixed  $value The constant value.
		 * @return  void
		 */
		function define( $name, $value = true ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

	}

	/*
	* apv
	*
	* The main function responsible for returning the one true AIPostVisualizer Instance to functions everywhere.
	* Use this function like you would a global variable, except without needing to declare the global.
	*
	* @param   void
	* @return  AIPostVisualizer
	*/
	function apv() {
		global $apv;
		// Instantiate only once.
		if ( ! isset( $apv ) ) {
			$apv = new AIPostVisualizer
			();
			$apv->initialize();
		}
		return $apv;
	}

	// Instantiate.
	apv();

endif; // class_exists check
