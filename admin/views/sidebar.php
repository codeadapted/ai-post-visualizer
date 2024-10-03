<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="sidebar">

    <!-- Mode toggle for switching between light and dark modes -->
    <div class="mode-toggle">
        <!-- Light mode button: active class added if viewer_mode is 'light' -->
        <div class="mode light<?php echo $viewer_mode === 'light' ? ' active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 1a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0V2a1 1 0 0 1 1-1ZM1 12a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2H2a1 1 0 0 1-1-1Zm19 0a1 1 0 0 1 1-1h1a1 1 0 1 1 0 2h-1a1 1 0 0 1-1-1Zm-8 8a1 1 0 0 1 1 1v1a1 1 0 1 1-2 0v-1a1 1 0 0 1 1-1Zm0-12a4 4 0 1 0 0 8 4 4 0 0 0 0-8Zm-6 4a6 6 0 1 1 12 0 6 6 0 0 1-12 0Zm-.364-7.778a1 1 0 1 0-1.414 1.414l.707.707A1 1 0 0 0 6.343 4.93l-.707-.707ZM4.222 18.364a1 1 0 1 0 1.414 1.414l.707-.707a1 1 0 1 0-1.414-1.414l-.707.707ZM17.657 4.929a1 1 0 1 0 1.414 1.414l.707-.707a1 1 0 0 0-1.414-1.414l-.707.707Zm1.414 12.728a1 1 0 1 0-1.414 1.414l.707.707a1 1 0 0 0 1.414-1.414l-.707-.707Z" clip-rule="evenodd"></path>
            </svg>
        </div>
        
        <!-- Dark mode button: active class added if viewer_mode is 'dark' -->
        <div class="mode dark<?php echo $viewer_mode === 'dark' ? ' active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.784 2.47a1 1 0 0 1 .047.975A8 8 0 0 0 20 15h.057a1 1 0 0 1 .902 1.445A10 10 0 0 1 12 22C6.477 22 2 17.523 2 12c0-5.499 4.438-9.961 9.928-10a1 1 0 0 1 .856.47ZM10.41 4.158a8 8 0 1 0 7.942 12.707C13.613 16.079 10 11.96 10 7c0-.986.143-1.94.41-2.842Z" clip-rule="evenodd"></path>
            </svg>
        </div>
    </div>

    <!-- Sidebar navigation for the Posts section -->
    <div class="item posts" data-tab="posts">
        <div class="icon">
            <!-- Use esc_url to sanitize the URL and esc_attr for the alt and title attributes -->
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/posts.svg' ); ?>" alt="<?php esc_attr_e( 'Posts', 'ai-post-visualizer' ); ?>" title="<?php esc_attr_e( 'Posts', 'ai-post-visualizer' ); ?>" />
        </div>
        <div class="name"><?php esc_html_e( 'Posts', 'ai-post-visualizer' ); ?></div>
    </div>

    <!-- Sidebar navigation for the Generate section -->
    <div class="item generate" data-tab="generate">
        <div class="icon">
            <!-- Sanitize URL and attributes for security -->
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/generate.svg' ); ?>" alt="<?php esc_attr_e( 'Generate', 'ai-post-visualizer' ); ?>" title="<?php esc_attr_e( 'Generate', 'ai-post-visualizer' ); ?>" />
        </div>
        <div class="name"><?php esc_html_e( 'Generate', 'ai-post-visualizer' ); ?></div>
    </div>

    <!-- Sidebar navigation for the Settings section (set as active by default) -->
    <div class="item settings active" data-tab="settings">
        <div class="icon">
            <!-- Sanitize URL and attributes for security -->
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/settings.svg' ); ?>" alt="<?php esc_attr_e( 'Settings', 'ai-post-visualizer' ); ?>" title="<?php esc_attr_e( 'Settings', 'ai-post-visualizer' ); ?>" />
        </div>
        <div class="name"><?php esc_html_e( 'Settings', 'ai-post-visualizer' ); ?></div>
    </div>

</div>