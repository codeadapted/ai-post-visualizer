<?php
// Admin View Options Page

if( !current_user_can( 'manage_options' ) ) {
	wp_die(__('You do not have sufficient permissions to access this page.'));
}

if( get_option( 'apv_clear_data' ) ) {
	$clear_data = true;
} else {
	$clear_data = false;
}

$posts = apv()->posts()->apv_get_posts();
$post_types = apv()->posts()->apv_get_post_types();
$history = apv()->posts()->apv_get_history();

?>
<div id="apv-admin-view">
	<div class="apv-header">
		<div class="content">
			<div class="logo">
				<img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/codeadapted_logo_no_text.svg';?>" alt="CodeAdpated" title="CodeAdpated" />
			</div>
			<h1><?php echo esc_html_e( 'AI Post Visualizer', 'ai-post-visualizer' ); ?></h1>
		</div>
		<img class="sizer" src="<?php echo plugin_dir_url( __FILE__ ) . 'img/header.png';?>"/>
	</div>
	<div class="content-area">
		<div class="sidebar">
			<div class="item posts active" data-tab="0">
				<div class="icon">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/posts.svg';?>" alt="Posts" title="Posts" />
				</div>
				<div class="name"><?php echo esc_html_e( 'Posts', 'ai-post-visualizer' ); ?></div>
			</div>
			<div class="item generate" data-tab="1">
				<div class="icon">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/generate.svg';?>" alt="Generate" title="Generate" />
				</div>
				<div class="name"><?php echo esc_html_e( 'Generate', 'ai-post-visualizer' ); ?></div>
			</div>
			<div class="item settings" data-tab="2">
				<div class="icon">
					<img src="<?php echo plugin_dir_url( __FILE__ ) . 'img/settings.svg';?>" alt="Settings" title="Settings" />
				</div>
				<div class="name"><?php echo esc_html_e( 'Settings', 'ai-post-visualizer' ); ?></div>
			</div>
		</div>
		<div class="main-content">
			<div class="template template-posts active" data-tab="0">
				<div class="search-sidebar">
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
			<div class="template template-generate" data-tab="1">
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
						<div class="render btn"><span><?php echo esc_html_e( 'Render Images', 'ai-post-visualizer' ); ?><span></div>
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
			<div class="template template-settings" data-tab="2">
				<div class="settings">
					<h3><?php echo esc_html_e( 'API Key Settings', 'ai-post-visualizer' ); ?></h3>
					<div class="setting">
						<div class="label"><?php echo esc_html_e( 'Type in AI Post Visualizer API key.  If you don’t have an API key please select a plan to the right to get started.', 'ai-post-visualizer' ); ?></div>
						<input type="text" name="apiKey" class="text-input" placeholder="<?php echo esc_html_e( 'Insert API Key', 'ai-post-visualizer' ); ?>" min="1" />
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
