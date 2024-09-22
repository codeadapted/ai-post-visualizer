<div class="template template-settings active <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="settings">
    <div class="settings">

        <h3><?php echo esc_html_e( 'DALL·E API Key Settings', 'ai-post-visualizer' ); ?></h3>
        <div class="setting">
            <div class="label">
                <?php
                printf(
                    esc_html__('Type in DALL·E API key. If you don\'t have an API key, login to your account %1$shere%2$s then go to %3$sthe API keys page%4$s.', 'ai-post-visualizer'),
                    '<a href="' . esc_url('https://platform.openai.com/') . '">', '</a>',
                    '<a href="' . esc_url('https://platform.openai.com/api-keys') . '">', '</a>'
                );
                ?>
            </div>
            <input type="password" name="dalleApiKey" class="dalle-api-key-input" placeholder="<?php echo esc_html_e( 'Insert DALL·E API Key', 'ai-post-visualizer' ); ?>" min="1" <?php echo $dalle_api_key ? 'value="' . $dalle_api_key . '"' : ''; ?> />
        </div>

    </div>
</div>