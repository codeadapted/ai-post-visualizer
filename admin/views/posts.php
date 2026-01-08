<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="template template-posts <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="posts" data-current-page="1">
  <div class="posts-section">

    <!-- Section header for filtering posts -->
    <h3><?php esc_html_e( 'Filter Posts', 'ai-post-visualizer' ); ?></h3>

    <!-- Filters section -->
    <div class="filters">

      <!-- Search Bar for filtering posts by title -->
      <div class="search-bar">
        <input 
          name="searchPosts" 
          class="search-input" 
          aria-label="<?php esc_attr_e( 'Search posts', 'ai-post-visualizer' ); ?>"
          placeholder="<?php esc_attr_e( 'Search Posts', 'ai-post-visualizer' ); ?>" 
        />
        <div class="icon">
          <!-- Search icon -->
          <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/search.svg' ); ?>" alt="<?php esc_attr_e( 'Search icon', 'ai-post-visualizer' ); ?>" />
        </div>
      </div>

      <!-- Dropdown filters for Post Types, Alphabetical Sorting, and Date Sorting -->
      <div class="dropdowns">

          <!-- Post Types dropdown filter -->
          <div class="dropdown post-types">
            <div class="title"><?php esc_html_e( 'Post Types', 'ai-post-visualizer' ); ?></div>
            <div class="types">
              <div class="types-wrapper">
                <div class="type-block active" data-type="any"><?php esc_html_e( 'All', 'ai-post-visualizer' ); ?></div>

                <!-- Post types are echoed dynamically here -->
                <?php echo wp_kses( $post_types, $allowed_html ); ?>

              </div>
            </div>
          </div>

          <!-- Alphabetical sorting dropdown -->
          <div class="dropdown sort">
            <div class="title"><?php esc_html_e( 'Alphabetical Order', 'ai-post-visualizer' ); ?></div>
            <div class="types">
              <div class="types-wrapper">
                <div class="type-block" data-alphabetical="ASC"><?php esc_html_e( 'Ascending', 'ai-post-visualizer' ); ?></div>
                <div class="type-block" data-alphabetical="DESC"><?php esc_html_e( 'Descending', 'ai-post-visualizer' ); ?></div>
              </div>
            </div>
          </div>

          <!-- Date sorting dropdown -->
          <div class="dropdown sort">
            <div class="title"><?php esc_html_e( 'Date', 'ai-post-visualizer' ); ?></div>
            <div class="types">
              <div class="types-wrapper">
                <div class="type-block" data-date="DESC"><?php esc_html_e( 'Newest first', 'ai-post-visualizer' ); ?></div>
                <div class="type-block" data-date="ASC"><?php esc_html_e( 'Oldest first', 'ai-post-visualizer' ); ?></div>
              </div>
            </div>
          </div>

          <!-- Filter reset -->
          <a class="filter-reset" role="button" tabindex="0"><?php esc_html_e( 'Reset Filters', 'ai-post-visualizer' ); ?></a>

      </div>
    </div>

    <!-- Posts list wrapper where filtered posts will be displayed -->
    <div class="posts-wrapper">
      <?php echo wp_kses( $posts['content'], $allowed_html ); ?>
    </div>

    <!-- Load more button (hidden if total posts <= 18) -->
    <div class="load-more <?php echo $posts['total_posts'] <= 18 ? 'hidden' : ''; ?>">
      <div class="load-more-text"><?php esc_html_e( 'Load More', 'ai-post-visualizer' ); ?></div>

      <!-- Loading animation (rotating circles) -->
      <div class="rc-loader">
        <div></div><div></div><div></div><div></div>
      </div>

    </div>

  </div>
</div>