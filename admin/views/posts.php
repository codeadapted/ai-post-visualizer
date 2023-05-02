<div class="template template-posts <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="posts">
    <div class="search-sidebar">
        <div class="filter-menu"><div></div></div>
        <div class="search-bar">
            <input name="searchPosts" class="search-input" placeholder="<?php echo esc_html_e( 'Search Posts', 'ai-post-visualizer' ); ?>" />
            <div class="icon">
                <img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/search.svg';?>" />
            </div>
        </div>
        <div class="accordions">
            <div class="accordion post-types active">
                <div class="title"><?php echo esc_html_e( 'Post Types', 'ai-post-visualizer' ); ?></div>
                <div class="types">
                    <div class="types-wrapper">
                        <div class="type-block active" data-type="any">All</div>
                        <?php echo $post_types; ?>
                    </div>
                </div>
            </div>
            <div class="accordion sort active">
                <div class="title"><?php echo esc_html_e( 'Alphabetical Order', 'ai-post-visualizer' ); ?></div>
                <div class="types">
                    <div class="types-wrapper">
                        <div class="type-block" data-alphabetical="ASC">Ascending</div>
                        <div class="type-block" data-alphabetical="DESC">Descending</div>
                    </div>
                </div>
            </div>
            <div class="accordion sort active">
                <div class="title"><?php echo esc_html_e( 'Date', 'ai-post-visualizer' ); ?></div>
                <div class="types">
                    <div class="types-wrapper">
                        <div class="type-block" data-date="DESC">Newest first</div>
                        <div class="type-block" data-date="ASC">Oldest first</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="posts-section">
        <div class="posts-wrapper">
            <?php echo $posts['content']; ?>
        </div>
        <div class="load-more <?php echo $posts['total_posts'] <= 18 ? 'hidden' : ''; ?>">
            <div class="load-more-text"><?php echo esc_html_e( 'Load More', 'ai-post-visualizer' ); ?></div>
            <div class="rc-loader"><div></div><div></div><div></div><div></div></div>
        </div>
    </div>
</div>