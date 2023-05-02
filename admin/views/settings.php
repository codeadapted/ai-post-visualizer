<div class="template template-settings active <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="settings">
    <div class="settings">

        <?php if( $api_key ) { ?>
            <h3><?php echo esc_html_e( 'API Key Settings', 'ai-post-visualizer' ); ?></h3>
            <div class="setting">
                <div class="label"><?php echo esc_html_e( 'Type in AI Post Visualizer API key.  If you don’t have an API key please select a plan to the right to get started.', 'ai-post-visualizer' ); ?></div>
                <input type="text" name="apiKey" class="text-input" placeholder="<?php echo esc_html_e( 'Insert API Key', 'ai-post-visualizer' ); ?>" min="1" <?php echo $api_key ? 'value="' . $api_key . '"' : ''; ?> />
            </div>
        <?php } else { ?>
            <h3><?php echo esc_html_e( 'Select a Plan', 'ai-post-visualizer' ); ?></h3>
            <div class="setting">
                <div class="label"><?php echo esc_html_e( 'Please select the plan that best fits your needs based on the features and pricing.', 'ai-post-visualizer' ); ?></div>
            </div>
            <div class="plans">
                <div class="plan starter">
                    <h4><?php echo esc_html_e( 'Starter', 'ai-post-visualizer' ); ?></h4>
                    <div class="plan-description"><?php echo esc_html_e( 'Perfect for individuals or small businesses who need a limited number of high-quality, unique images each month.', 'ai-post-visualizer' ); ?></div>
                    <ul>
                        <li><?php echo esc_html_e( 'Full access to our plugin', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Up to 100 image generations per month', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Clean, simple-to-use user interface', 'ai-post-visualizer' ); ?></li>
                    </ul>
                    <div class="plan-price">
                        <div><?php echo esc_html_e( '$10', 'ai-post-visualizer' ); ?></div>
                        <span><?php echo esc_html_e( 'per month', 'ai-post-visualizer' ); ?></span>
                    </div>
                    <div class="select-plan" data-tier="starter"><?php echo esc_html_e( 'Select Plan', 'ai-post-visualizer' ); ?></div>
                </div>
                <div class="plan pro">
                    <h4><?php echo esc_html_e( 'Pro', 'ai-post-visualizer' ); ?></h4>
                    <div class="plan-description"><?php echo esc_html_e( 'For those who need more images every month. Take your image creation to the next level and create more content, faster.', 'ai-post-visualizer' ); ?></div>
                    <ul>
                        <li><?php echo esc_html_e( 'Everything from the Starter plan', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Up to 250 image generations per month', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Prompt Helper', 'ai-post-visualizer' ); ?></li>
                    </ul>
                    <div class="plan-price">
                        <div><?php echo esc_html_e( '$15', 'ai-post-visualizer' ); ?></div>
                        <span><?php echo esc_html_e( 'per month', 'ai-post-visualizer' ); ?></span>
                    </div>
                    <div class="select-plan" data-tier="pro"><?php echo esc_html_e( 'Select Plan', 'ai-post-visualizer' ); ?></div>
                </div>
                <div class="plan enterprise">
                    <h4><?php echo esc_html_e( 'Enterprise', 'ai-post-visualizer' ); ?></h4>
                    <div class="plan-description"><?php echo esc_html_e( 'Catered towards content-rich websites and large-scale enterprise that require a high volume of AI-generated images.', 'ai-post-visualizer' ); ?></div>
                    <ul>
                        <li><?php echo esc_html_e( 'Everything from the Pro plan', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Up to 500 image generations per month', 'ai-post-visualizer' ); ?></li>
                        <li><?php echo esc_html_e( 'Image Editor', 'ai-post-visualizer' ); ?></li>
                    </ul>
                    <div class="plan-price">
                        <div><?php echo esc_html_e( '$25', 'ai-post-visualizer' ); ?></div>
                        <span><?php echo esc_html_e( 'per month', 'ai-post-visualizer' ); ?></span>
                    </div>
                    <div class="select-plan" data-tier="enterprise"><?php echo esc_html_e( 'Select Plan', 'ai-post-visualizer' ); ?></div>
                </div>
            </div>
        <?php } ?>

    </div>
</div>