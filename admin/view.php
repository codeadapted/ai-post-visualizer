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
$admin_url = apv()->plugin()->get_admin_url();

$validation = false;

// Get dalle api key option;
$dalle_api_key = get_option( 'apv_dalle_api_key' );

// Set validation
$validation = $dalle_api_key ? true : false;

?>
<div id="apv-admin-view">
	<?php include_once dirname( __FILE__ ) . '/views/header.php'; ?>
	<div class="content-area">
		<?php include_once dirname( __FILE__ ) . '/views/sidebar.php'; ?>
		<div class="main-content">
			<?php include_once dirname( __FILE__ ) . '/views/posts.php'; ?>
			<?php include_once dirname( __FILE__ ) . '/views/generate.php'; ?>
			<?php include_once dirname( __FILE__ ) . '/views/settings.php'; ?>
		</div>
	</div>
</div>