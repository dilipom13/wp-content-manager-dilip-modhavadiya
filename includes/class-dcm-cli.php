<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {

	class DCM_CLI_Command {

		public function cache( $args ) {

			if ( empty( $args ) || $args[0] !== 'clear' ) {
				WP_CLI::error( 'Usage: wp dcm cache clear' );
			}

			global $wpdb;

			$wpdb->query(
				"DELETE FROM {$wpdb->options} 
				 WHERE option_name LIKE '_transient_dcm_promos_%'
				 OR option_name LIKE '_transient_timeout_dcm_promos_%'"
			);

			WP_CLI::success( 'DCM cache cleared successfully.' );
		}
	}

	WP_CLI::add_command( 'dcm', 'DCM_CLI_Command' );
}