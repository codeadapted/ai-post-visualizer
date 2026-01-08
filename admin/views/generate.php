<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="template template-generate <?php echo $validation ? 'validated' : 'not-validated'; ?>" data-tab="generate">
  <div class="settings">

    <!-- Back to Posts button -->
    <div class="back-to-posts">
        <span></span>
        <?php esc_html_e( 'Back to Posts', 'ai-post-visualizer' ); ?>
    </div>

    <!-- Current Post Title (dynamically populated) -->
    <h2 class="current-post-title"></h2>

    <div class="settings-wrapper">

      <!-- Current Featured Image Section -->
      <div class="current-featured">
        <h3><?php esc_html_e( 'Current Featured Image', 'ai-post-visualizer' ); ?></h3>
        <div class="featured-img" style=""></div> <!-- Image will be dynamically set via JS -->
        <span class="revert-to-original"><?php esc_html_e( 'Revert to Original', 'ai-post-visualizer' ); ?></span>
      </div>

      <!-- Section for Generating New Images -->
      <h3><?php esc_html_e( 'Generate New Images', 'ai-post-visualizer' ); ?></h3>

      <!-- Keyword Search Input -->
      <div class="setting">
        <div class="label">
          <?php esc_html_e( 'Type in a series of words that best describe the desired image.', 'ai-post-visualizer' ); ?>
        </div>
        <div class="keyword-search">
          <input 
            type="text" 
            name="searchKeyword" 
            class="keyword-input" 
            placeholder="<?php esc_attr_e( 'Search Keywords', 'ai-post-visualizer' ); ?>" 
          />
          <div class="icon">
            <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/search.svg' ); ?>" alt="<?php esc_attr_e( 'Search Icon', 'ai-post-visualizer' ); ?>" />
          </div>
        </div>
      </div>

      <!-- Number of Images Input -->
      <div class="setting">
        <div class="label">
          <?php esc_html_e( 'Set number of images to be rendered at once. (Default is 1)', 'ai-post-visualizer' ); ?>
        </div>
        <input 
          type="number" 
          name="numOfImages" 
          class="number-input" 
          placeholder="<?php esc_attr_e( '1', 'ai-post-visualizer' ); ?>" 
          min="1"
          value="1"
        />
      </div>

      <!-- Resolution Dropdown -->
      <div class="setting">
        <div class="label">
          <?php esc_html_e( 'Set resolution of generated images. (Default is 256 x 256)', 'ai-post-visualizer' ); ?>
          <div class="tooltip">
            <span>?</span>
            <div class="tooltip-description">
              <?php esc_html_e( '256x256: $0.016 per image', 'ai-post-visualizer' ); ?><br>
              <?php esc_html_e( '512x512: $0.018 per image', 'ai-post-visualizer' ); ?><br>
              <?php esc_html_e( '1024x1024: $0.02 per image', 'ai-post-visualizer' ); ?>
            </div>
          </div>
        </div>
        <div class="resolution-select">
          <select name="resolution">
            <option value="256x256"><?php esc_html_e( '256 x 256', 'ai-post-visualizer' ); ?></option>
            <option value="512x512"><?php esc_html_e( '512 x 512', 'ai-post-visualizer' ); ?></option>
            <option value="1024x1024"><?php esc_html_e( '1024 x 1024', 'ai-post-visualizer' ); ?></option>
          </select>
        </div>
      </div>

      <!-- Cost Breakdown Section -->
      <div class="cost">
        <div class="text"><?php esc_html_e( 'Cost of rendering images:', 'ai-post-visualizer' ); ?></div>
        <div class="breakdown">
          <div class="num-images">
            <?php esc_html_e( 'Number of Images: ', 'ai-post-visualizer' ); ?><span>1</span>
          </div>
          <div class="cost-per-img">
            <?php esc_html_e( 'Cost per Image: ', 'ai-post-visualizer' ); ?><span>$0.016</span>
          </div>
          <div class="total">
              <?php esc_html_e( 'Total Cost: ', 'ai-post-visualizer' ); ?><span>$0.016</span>
          </div>
        </div>
      </div>

      <!-- Render Images Button -->
      <div class="render btn disabled">
          <span><?php esc_html_e( 'Render Images', 'ai-post-visualizer' ); ?></span>

          <!-- Prompt to Add API Key if Validation Fails -->
          <?php if( !$validation ) { ?>
            <div class="sign-up-text">
              <?php esc_html_e( 'Add your DALL·E API Key by going to ', 'ai-post-visualizer' ); ?>
              <div data-tab="settings"><?php esc_html_e( 'Settings.', 'ai-post-visualizer' ); ?></div>
            </div>
          <?php } ?>
      </div>

      <!-- Prompt to Add API Key if Validation Fails -->
      <?php if( !$validation ) { ?>
        <div class="sign-up-text mobile">
          <?php esc_html_e( 'Add your DALL·E API Key by going to Settings.', 'ai-post-visualizer' ); ?>
        </div>
      <?php } ?>

      <!-- Rendered Images Section -->
      <div class="rendered-images">
        <h3><?php esc_html_e( 'Rendered Images', 'ai-post-visualizer' ); ?></h3>
        <div class="rc-loader"><div></div><div></div><div></div><div></div></div> <!-- Loading animation -->
        <div class="images-wrapper"></div> <!-- Dynamically populated via JS -->
      </div>

    </div>
  </div>

  <!-- History Section -->
  <div class="history">
    <div class="title<?php echo !$history ? ' no-history' : ''; ?>">
      <div class="icon">
        <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/generation_history.svg' ); ?>" alt="<?php esc_attr_e( 'History Icon', 'ai-post-visualizer' ); ?>" />
      </div>
      <div class="text"><?php esc_html_e( 'Generation History', 'ai-post-visualizer' ); ?></div>
    </div>
    <div class="history-rows">
      <?php echo wp_kses( $history, $allowed_html ); ?> <!-- Populated with history data -->
    </div>
  </div>

</div>