<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="template template-settings active <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="settings">
  <div class="settings">

    <!-- DALL·E API Key Settings -->
    <h3><?php esc_html_e( 'DALL·E API Key Settings', 'ai-post-visualizer' ); ?></h3>
    <div class="setting">

      <div class="label">
        <?php
			// Display instructions for entering the DALL·E API key.
			printf(
			  // Translators: %1$s and %2$s are opening and closing anchor tags for the OpenAI login link, %3$s and %4$s are for the API keys page link.
			  esc_html__( 'Enter an OpenAI API key. For security, this field is never displayed after saving. If you don\'t have an API key, login %1$shere%2$s then visit %3$sthe API keys page%4$s.', 'ai-post-visualizer' ),
			  '<a href="' . esc_url( 'https://platform.openai.com/' ) . '" target="_blank" rel="noopener noreferrer">', '</a>',
			  '<a href="' . esc_url( 'https://platform.openai.com/api-keys' ) . '" target="_blank" rel="noopener noreferrer">', '</a>'
			);
        ?>
      </div>

			<?php if ( isset( $api_key_source ) && in_array( $api_key_source, array( 'env', 'constant' ), true ) ) { ?>
				<div class="label">
					<?php esc_html_e( 'This site is configured to use a server-managed API key (environment variable/constant). The key cannot be edited here.', 'ai-post-visualizer' ); ?>
				</div>
			<?php } ?>

      <!-- Input field for DALL·E API Key -->
      <input 
        type="password" 
        name="dalleApiKey" 
        class="dalle-api-key-input" 
        aria-label="<?php esc_attr_e( 'Insert DALL·E API Key', 'ai-post-visualizer' ); ?>"
        placeholder="<?php echo ( isset( $api_key_source ) && in_array( $api_key_source, array( 'env', 'constant' ), true ) ) ? esc_attr__( 'Managed by server configuration', 'ai-post-visualizer' ) : esc_attr__( 'Insert OpenAI API Key', 'ai-post-visualizer' ); ?>" 
        min="1"
			<?php echo ( isset( $api_key_source ) && in_array( $api_key_source, array( 'env', 'constant' ), true ) ) ? 'disabled' : ''; ?>
      />

    </div>

    <!-- Data Retention Settings -->
    <h3><?php esc_html_e( 'Data Retention Settings', 'ai-post-visualizer' ); ?></h3>
    <div class="setting retention">

      <div class="label">
        <?php esc_html_e( 'If you would like for all AI Post Visualizer data to be removed after uninstalling the plugin, click the toggle below.', 'ai-post-visualizer'); ?>
      </div>

      <!-- Toggle button for data retention -->
      <div class="toggle-button">
        <input 
          type="checkbox" 
          id="toggle" 
          class="toggle-input" 
          aria-label="<?php esc_attr_e( 'Toggle data retention', 'ai-post-visualizer' ); ?>"
          <?php echo $clear_data ? 'checked' : ''; ?> 
        />
        <label for="toggle" class="toggle-label">
          <span class="toggle-circle"></span>
        </label>
      </div>

    </div>

  </div>
</div>
