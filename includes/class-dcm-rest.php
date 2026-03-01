<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Rest {

	private $settings;
	private $repo;

	public function __construct( DCM_Settings $settings, DCM_Repository $repo ) {
		$this->settings = $settings;
		$this->repo     = $repo;
	}

	public function register() {
		add_action( 'rest_api_init', array( $this, 'routes' ) );
	}

	public function routes() {
		register_rest_route(
			'dcm/v1',
			'/promos',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_promos' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	public function get_promos( WP_REST_Request $request ) {
		if ( ! $this->settings->is_enabled() ) {
			return new WP_REST_Response( array( 'items' => array() ), 200 );
		}

		return new WP_REST_Response(
			array( 'items' => $this->repo->get_active_promos() ),
			200
		);
	}
}