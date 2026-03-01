<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Assets {

	private $settings;

	public function __construct( DCM_Settings $settings ) {
		$this->settings = $settings;
	}

	public function register() {
		// Frontend
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

		// Admin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	public function enqueue() {

		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		if ( ! has_shortcode( (string) $post->post_content, 'dynamic_promo' ) ) {
			return;
		}

		wp_enqueue_style(
			'dcm-promos',
			DCM_PLUGIN_URL . 'assets/css/promos.css',
			array(),
			filemtime( DCM_PLUGIN_DIR . 'assets/css/promos.css' )
		);

		wp_enqueue_script(
			'dcm-promos',
			DCM_PLUGIN_URL . 'assets/js/promos.js',
			array(),
			filemtime( DCM_PLUGIN_DIR . 'assets/js/promos.js' ),
			true
		);

		wp_localize_script(
		'dcm-promos',
		'dcmData',
		array(
			'ajaxEnabled' => $this->settings->ajax_enabled() ? 1 : 0,
			'ajaxUrl'     => esc_url_raw( admin_url( 'admin-ajax.php' ) ),
			'action'      => 'dcm_get_promos',
			'nonce'       => wp_create_nonce( 'dcm_promos_nonce' ),
			'restUrl'     => esc_url_raw( rest_url( 'dcm/v1/promos' ) ),
		)
		);

	}

	public function enqueue_admin( $hook ) {

	global $post_type;

	// Load CSS on settings page
	if ( $hook === 'settings_page_dcm-dynamic-content' ) {

		wp_enqueue_style(
			'dcm-admin-promos',
			DCM_PLUGIN_URL . 'assets/css/admin-promos.css',
			array(),
			filemtime( DCM_PLUGIN_DIR . 'assets/css/admin-promos.css' )
		);
	}

	// Load datepicker only on promo CPT edit screen
	if ( DCM_CPT::POST_TYPE === $post_type && 
		( 'post.php' === $hook || 'post-new.php' === $hook ) ) {

		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_style(
			'jquery-ui-css',
			'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css',
			array(),
			'1.13.2'
		);

		wp_add_inline_script(
			'jquery-ui-datepicker',
			"
			jQuery(document).ready(function($){
				$('.dcm-datepicker').datepicker({
					dateFormat: 'yymmdd',
					changeMonth: true,
					changeYear: true
				});
			});
			"
		);
	}
}
}