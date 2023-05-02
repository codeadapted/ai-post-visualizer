<div class="sidebar">
    <div class="item posts" data-tab="posts">
        <div class="icon">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/posts.svg';?>" alt="Posts" title="Posts" />
        </div>
        <div class="name"><?php echo esc_html_e( 'Posts', 'ai-post-visualizer' ); ?></div>
    </div>
    <div class="item generate" data-tab="generate">
        <div class="icon">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/generate.svg';?>" alt="Generate" title="Generate" />
        </div>
        <div class="name"><?php echo esc_html_e( 'Generate', 'ai-post-visualizer' ); ?></div>
    </div>
    <div class="item settings active" data-tab="settings">
        <div class="icon">
            <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/settings.svg';?>" alt="Settings" title="Settings" />
        </div>
        <div class="name"><?php echo esc_html_e( 'Settings', 'ai-post-visualizer' ); ?></div>
    </div>
</div>