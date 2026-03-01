<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Plugin {

	private static $instance = null;
	
	/* DCM_Settings */
	public $settings;

	/* DCM_Repository */
	public $repo;

	/* DCM_Assets */
	public $assets;

	private function __construct() {}

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->boot();
		}
		return self::$instance;
	}

	private function boot() {
		
		$this->settings = new DCM_Settings();
		$this->repo     = new DCM_Repository( $this->settings );
		$this->assets   = new DCM_Assets( $this->settings );

		( new DCM_CPT( $this->settings ) )->register();
		( new DCM_Rest( $this->settings, $this->repo ) )->register();
		( new DCM_Shortcode( $this->settings, $this->repo, $this->assets ) )->register();
		
		/*Added Ajax Register*/
		( new DCM_Ajax( $this->settings, $this->repo ) )->register();

		$this->assets->register();
		$this->settings->register();

		// Cache invalidation on changes (bonus)
		add_action( 'save_post_' . DCM_CPT::POST_TYPE, array( $this->repo, 'flush_cache' ) );
		add_action( 'deleted_post', array( $this->repo, 'flush_cache' ) );
	}
}