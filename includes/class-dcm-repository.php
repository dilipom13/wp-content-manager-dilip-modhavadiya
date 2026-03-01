<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Repository {

	private $settings;

	public function __construct( DCM_Settings $settings ) {
		$this->settings = $settings;
	}

	private function cache_key() {
		return 'dcm_promos_' . (int) $this->settings->max_promos();
	}

	public function flush_cache() {
		delete_transient( $this->cache_key() );
	}

    public function get_active_promos() {

		if ( ! $this->settings->is_enabled() ) {
			return array();
		}

		$key = $this->cache_key();
		error_log( 'DCM: Cache key = ' . $key );

		$cached = get_transient( $key );

		if ( is_array( $cached ) ) {
			error_log( 'DCM: Returned from CACHE (count=' . count( $cached ) . ')' );
			return $cached;
		}

		error_log( 'DCM: Returned from DATABASE' );

		$today = wp_date( 'Ymd' );
		$limit = $this->settings->max_promos();

		$q = new WP_Query(
			array(
				'post_type'      => DCM_CPT::POST_TYPE,
				'post_status'    => 'publish',
				'posts_per_page' => $limit,
				'orderby'        => 'meta_value_num',
				'meta_key'       => DCM_CPT::META_PRIORITY,
				'order'          => 'ASC',
				'no_found_rows'  => true,
				'meta_query'     => array(
					'relation' => 'OR',
					array(
						'key'     => DCM_CPT::META_EXPIRY,
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => DCM_CPT::META_EXPIRY,
						'value'   => $today,
						'compare' => '>=',
						'type'    => 'NUMERIC',
					),
				),
			)
		);

		$items = array();

		foreach ( $q->posts as $post ) {
			$post_id = (int) $post->ID;

			$image = '';
			if ( has_post_thumbnail( $post_id ) ) {
				$image = (string) get_the_post_thumbnail_url( $post_id, 'large' );
			}

			$items[] = array(
				'id'       => $post_id,
				'title'    => get_the_title( $post_id ),
				'content'  => apply_filters( 'the_content', $post->post_content ),
				'image'    => $image,
				'cta_text' => (string) get_post_meta( $post_id, DCM_CPT::META_CTA_TEXT, true ),
				'cta_url'  => (string) get_post_meta( $post_id, DCM_CPT::META_CTA_URL, true ),
				'priority' => (int) get_post_meta( $post_id, DCM_CPT::META_PRIORITY, true ),
				'expiry'   => (string) get_post_meta( $post_id, DCM_CPT::META_EXPIRY, true ),
			);
		}

		$saved = set_transient( $key, $items, $this->settings->ttl_seconds() );
		error_log( 'DCM: set_transient result = ' . ( $saved ? 'true' : 'false' ) );
		error_log( 'DCM: items count saved = ' . count( $items ) );
		return $items;
	}
}