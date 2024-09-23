<div class="template template-settings active <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="settings">
    <div class="settings">

        <!-- DALL·E API Key Settings -->
        <h3><?php esc_html_e( 'DALL·E API Key Settings', 'ai-post-visualizer' ); ?></h3>
        <div class="setting">

            <div class="label">
                <?php
                // Display instructions for entering the DALL·E API key
                printf(
                    esc_html__('Type in DALL·E API key. If you don\'t have an API key, login to your account %1$shere%2$s then go to %3$sthe API keys page%4$s.', 'ai-post-visualizer'),
                    '<a href="' . esc_url( 'https://platform.openai.com/' ) . '" target="_blank">', '</a>',
                    '<a href="' . esc_url( 'https://platform.openai.com/api-keys' ) . '" target="_blank">', '</a>'
                );
                ?>
            </div>

            <!-- Input field for DALL·E API Key -->
            <input 
                type="password" 
                name="dalleApiKey" 
                class="dalle-api-key-input" 
                placeholder="<?php esc_attr_e( 'Insert DALL·E API Key', 'ai-post-visualizer' ); ?>" 
                min="1"
                <?php echo $dalle_api_key ? 'value="' . esc_attr( $dalle_api_key ) . '"' : ''; ?> 
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
                    <?php echo $clear_data ? 'checked' : ''; ?> 
                />
                <label for="toggle" class="toggle-label">
                    <span class="toggle-circle"></span>
                </label>
            </div>

        </div>

    </div>
</div>
