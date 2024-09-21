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

if( isset( $_GET[ 'apv_api_key' ] ) ) {
	$apv_api_key = $_GET[ 'apv_api_key' ];
	update_option( 'apv_api_key', $apv_api_key );
}

// delete_option( 'apv_api_key' );
$dalle_api_key = get_option( 'apv_dalle_api_key' );
$api_key = get_option( 'apv_api_key' );

if( $api_key ) {

	if( get_transient( 'apv_key_validation' ) && get_transient( 'apv_key_validation' ) == 'valid' ) {

		$validation = true;

	} else {

		// Set expiration for transient to the beginning of the next day
		$now = time();
		$tomorrow = strtotime( 'tomorrow' );
		$expiration = $tomorrow - $now;

		// Send api key to validator app and retrieve sub status
		$validation = apv()->api_keys()->apv_subscription_validation();
		$validation = json_decode( $validation );

		// If sub is active set validation var to true, else to false
		if( $validation->status === 'active' ) {
			$validation = true;
			set_transient( 'apv_key_validation', 'valid', $expiration );
		} else {
			$validation = false;
			set_transient( 'apv_key_validation', 'invalid', $expiration );
		}

	}
} else if ( $dalle_api_key ) {

	$validation = true;

}

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
<?php if( isset( $_GET[ 'apv_api_key' ] ) ) { ?>
	<script>
		window.history.replaceState( {}, '', '<?php echo esc_url( $admin_url ); ?>' );
	</script>
<?php } ?>