<div class="template template-generate <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="generate">
    <div class="settings">
        <div class="back-to-posts">
            <span></span>
            <?php echo esc_html_e( 'Back to Posts', 'ai-post-visualizer' ); ?>
        </div>
        <h2 class="current-post-title"></h2>
        <div class="settings-wrapper">
            <div class="current-featured">
                <h3><?php echo esc_html_e( 'Current Featured Image', 'ai-post-visualizer' ); ?></h3>
                <div class="featured-img" style=""></div>
                <span class="revert-to-original"><?php echo esc_html_e( 'Revert to Original', 'ai-post-visualizer' ); ?></span>
            </div>
            <h3><?php echo esc_html_e( 'Generate New Images', 'ai-post-visualizer' ); ?></h3>
            <div class="setting">
                <div class="label"><?php echo esc_html_e( 'Type in a series of words that best describe the desired image.', 'ai-post-visualizer' ); ?></div>
                <div class="keyword-search">
                    <input type="text" name="searchKeyword" class="keyword-input" placeholder="<?php echo esc_html_e( 'Search Keywords', 'ai-post-visualizer' ); ?>" />
                    <div class="icon">
                        <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/search.svg';?>" />
                    </div>
                </div>
            </div>
            <div class="setting">
                <div class="label"><?php echo esc_html_e( 'Set number of images to be rendered at once. (Default is 1)', 'ai-post-visualizer' ); ?></div>
                <input type="number" name="numOfImages" class="number-input" placeholder="<?php echo esc_html_e( '1', 'ai-post-visualizer' ); ?>" min="1" />
            </div>
            <!-- <div class="setting">
                <div class="label">
                    <?php //echo esc_html_e( 'Set aspect ratio of generated images. (Default is 1:1)', 'ai-post-visualizer' ); ?>
                </div>
                <div class="aspect-ratio-input" />
                    <div class="radio-btn" data-aspect="1:1"><?php //echo esc_html_e( 'Square (1:1)', 'ai-post-visualizer' ); ?></div>
                    <div class="radio-btn" data-aspect="3:2"><?php //echo esc_html_e( 'Landscape (3:2)', 'ai-post-visualizer' ); ?></div>
                    <div class="radio-btn" data-aspect="2:3"><?php //echo esc_html_e( 'Portrait (2:3)', 'ai-post-visualizer' ); ?></div>
                </div>
            </div> -->
            <div class="setting">
                <div class="label">
                    <?php echo esc_html_e( 'Set resolution of generated images. (Default is 256 x 256)', 'ai-post-visualizer' ); ?>
                    <div class="tooltip">
                        <span>?</span>
                        <div class="tooltip-description">
                            <?php echo esc_html_e( '256×256: $0.016 per image', 'ai-post-visualizer' ); ?><br>
                            <?php echo esc_html_e( '512×512: $0.018 per image', 'ai-post-visualizer' ); ?><br>
                            <?php echo esc_html_e( '1024×1024: $0.02 per image', 'ai-post-visualizer' ); ?>
                        </div>
                    </div>
                </div>
                <div class="resolution-select">
                    <select name="resolution"/>
                        <option value="256x256"><?php echo esc_html_e( '256 x 256', 'ai-post-visualizer' ); ?></option>
                        <option value="512x512"><?php echo esc_html_e( '512 x 512', 'ai-post-visualizer' ); ?></option>
                        <option value="1024x1024"><?php echo esc_html_e( '1024 x 1024', 'ai-post-visualizer' ); ?></option>
                    </select>
                </div>
            </div>
            <div class="cost">
                <div class="text"><?php echo esc_html_e( 'Cost of rendering images:', 'ai-post-visualizer' ); ?></div>
                <div class="breakdown">
                    <div class="num-images"><?php echo esc_html_e( 'Number of Images: ', 'ai-post-visualizer' ); ?><span>1</span></div>
                    <div class="cost-per-img"><?php echo esc_html_e( 'Cost per Image: ', 'ai-post-visualizer' ); ?><span>$0.016</span></div>
                    <div class="total"><?php echo esc_html_e( 'Total Cost: ', 'ai-post-visualizer' ); ?><span>$0.016</span></div>
                </div>
            </div>
            <div class="render btn">
                <span><?php echo esc_html_e( 'Render Images', 'ai-post-visualizer' ); ?></span>
                <?php if( !$validation ) { ?>
                    <div class="sign-up-text">
                        <?php echo esc_html_e( 'Sign up for a plan to start rendering images by going to ', 'ai-post-visualizer' ); ?>
                        <div data-tab="settings"><?php echo esc_html_e( 'Settings.', 'ai-post-visualizer' ); ?></div>
                    </div>
                <?php } ?>
            </div>
            <div class="rendered-images">
                <h3><?php echo esc_html_e( 'Rendered Images', 'ai-post-visualizer' ); ?></h3>
                <div class="rc-loader"><div></div><div></div><div></div><div></div></div>
                <div class="images-wrapper"></div>
            </div>
        </div>
    </div>
    <div class="history">
        <div class="title">
            <div class="icon">
                <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/generation_history.svg';?>" />
            </div>
            <div class="text"><?php echo esc_html_e( 'Generation History', 'ai-post-visualizer' ); ?></div>
        </div>
        <div class="history-rows">
            <?php echo $history; ?>
        </div>
    </div>
</div>