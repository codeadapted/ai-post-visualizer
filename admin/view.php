<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Ensure the user has the appropriate capability to manage options
if ( !current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'ai-post-visualizer' ) );
}

// Setup allowed html
$allowed_html = array(
    'div' => array(
        'class' => array(),
        'data-history' => array(),
        'data-post' => array(),
        'data-type' => array(),
        'data-alphabetical' => array(),
        'data-date' => array(),
        'data-current-page' => array(),
        'style' => array()
    ),
    'img' => array(
        'src' => array(),
        'alt' => array(),
    ),
    'span' => array(),
    'strong' => array(),
    'em' => array(),
    'a' => array()
);

// Check if the 'aipv_clear_data' option is set, otherwise set default to false
$clear_data = get_option( 'aipv_clear_data', false );

// Fetch posts, post types, and history using the custom methods from the plugin instance
$posts      = aipv()->posts()->aipv_get_posts();
$post_types = aipv()->posts()->aipv_get_post_types();
$history    = aipv()->posts()->aipv_get_history();

// Fetch admin URL for internal linking purposes
$admin_url  = aipv()->plugin()->aipv_get_admin_url();

// Variable to track if the API key is validated
$validation = false;

// Fetch DALLE API key from options
$dalle_api_key = get_option( 'aipv_dalle_api_key' );

// Fetch the viewer mode (light/dark) for setting the theme in the admin view
$viewer_mode = get_option( 'aipv_viewer_mode', 'dark' ); // Default to 'dark' if no mode is set

// Set validation flag if DALLE API key exists
$validation = !empty( $dalle_api_key );

// Begin rendering the admin page view
?>
<div id="aipv-admin-view" class="<?php echo esc_attr( $viewer_mode ); ?>">
    <?php 
    // Include the header view for the admin page
    include_once dirname( __FILE__ ) . '/views/header.php'; 
    ?>
    <div class="content-area">
        <?php 
        // Include the sidebar for navigation within the plugin
        include_once dirname( __FILE__ ) . '/views/sidebar.php'; 
        ?>
        <div class="main-content">
            <?php 
            // Include the posts view for managing posts within the plugin
            include_once dirname( __FILE__ ) . '/views/posts.php'; 
            
            // Include the generate view for generating new images
            include_once dirname( __FILE__ ) . '/views/generate.php'; 
            
            // Include the settings view for managing plugin settings
            include_once dirname( __FILE__ ) . '/views/settings.php'; 
            ?>
        </div>
    </div>
</div>
