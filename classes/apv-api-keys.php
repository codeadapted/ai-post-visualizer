<?php

class APV_API_KEYS {


	/**
	 * __construct
	 *
	 * @param   void
	 * @return  void
	 */
	public function __construct() {

		if ( is_admin() ) {
			add_action( 'wp_ajax_apv_plans_routing', array( $this, 'apv_plans_routing' ) );
		}

	}

	/**
	 * apv_plans_routing
	 *
	 * Send user to Stripe checkout page with params
	 *
	 * @param   void
	 * @return  void
	 */
	public function apv_plans_routing() {

        $return_url = $_GET['return_url'];

        $tier = $_GET['tier'];

		$url = 'https://apv-key-validator.herokuapp.com/plans';

		$fields = array(
			'tier' => $tier,
            'returnUrl' => $return_url
		);

        $data = $this->apv_app_call( $url, $fields );

        if( $data ) {
            wp_send_json( $data );
        } else {
            wp_send_json( 'An error occurred' );
        }

	}

    /**
	 * apv_subscription_validation
	 *
	 * Check if user's API Key is valid
	 *
	 * @param   void
	 * @return  void
	 */
	public function apv_subscription_validation() {

        $api_key = get_option( 'apv_api_key' );

		$url = 'https://apv-key-validator.herokuapp.com/api-keys/verify';

		$fields = array(
			'apiKey' => $api_key
		);

        $data = $this->apv_app_call( $url, $fields );

        if( $data ) {
            return $data;
        } else {
            return false;
        }

	}

    /**
	 * apv_plans_routing
	 *
	 * Send user to Stripe checkout page with params
	 *
     * @param   String $url Url for validator app.
     * @param   Array $fields Fields to pass to validator app.
	 * @return  void
	 */
	public function apv_app_call( $url, $fields ) {

		$curl = curl_init();

		$headers = array(
			'Content-Type: application/json'
		);

		$json_string = json_encode( $fields );

		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_POST, TRUE );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $json_string );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true  );

		$data = curl_exec( $curl );

		curl_close( $curl );

		return $data;

	}

}
