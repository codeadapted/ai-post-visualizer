<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="aipv-header">
  <div class="content">

    <!-- Logo section -->
    <div class="logo">
      <img 
        src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/codeadapted_logo_no_text.svg' ); ?>" 
        alt="<?php esc_attr_e( 'CodeAdapted Logo', 'ai-post-visualizer' ); ?>" 
        title="<?php esc_attr_e( 'CodeAdapted', 'ai-post-visualizer' ); ?>" 
      />
    </div>

    <!-- Plugin Title -->
    <h1><?php esc_html_e( 'AI Post Visualizer', 'ai-post-visualizer' ); ?></h1>

  </div>

  <!-- Sizer image -->
  <img 
    class="sizer" 
    src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/header.png' ); ?>" 
    alt="" 
    role="presentation" 
  />

</div>