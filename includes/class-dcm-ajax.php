<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Ajax {

	private $settings;
	private $repo;

	public function __construct( DCM_Settings $settings, DCM_Repository $repo ) {
		$this->settings = $settings;
		$this->repo     = $repo;
	}

	public function register() {
		add_action( 'wp_ajax_dcm_get_promos', array( $this, 'get_promos' ) );
		add_action( 'wp_ajax_nopriv_dcm_get_promos', array( $this, 'get_promos' ) );
	}

	public function get_promos() {
		
		check_ajax_referer( 'dcm_promos_nonce', 'nonce' );
		$promos = array();

		if ( $this->settings->is_enabled() ) {
			$promos = $this->repo->get_active_promos();
		}

		ob_start();

		if ( ! empty( $promos ) ) :
			foreach ( $promos as $p ) :

				$title   = isset( $p['title'] ) ? (string) $p['title'] : '';
				$content = isset( $p['content'] ) ? (string) $p['content'] : '';
				$image   = isset( $p['image'] ) ? (string) $p['image'] : '';
				$cta_url = isset( $p['cta_url'] ) ? (string) $p['cta_url'] : '';
				$cta_txt = isset( $p['cta_text'] ) ? (string) $p['cta_text'] : '';
				?>
				<div class="dcm-promo">
					<?php if ( '' !== $image ) : ?>
						<img
							src="<?php echo esc_url( $image ); ?>"
							loading="lazy"
							alt="<?php echo esc_attr( wp_strip_all_tags( $title ) ); ?>"
						/>
					<?php endif; ?>

					<?php if ( '' !== $title ) : ?>
						<h3><?php echo esc_html( $title ); ?></h3>
					<?php endif; ?>

					<?php if ( '' !== $content ) : ?>
						<div class="dcm-promo__content">
							<?php echo wp_kses_post( $content ); ?>
						</div>
					<?php endif; ?>

					<?php if ( '' !== $cta_url && '' !== $cta_txt ) : ?>
						<p>
							<a
								class="dcm-promo__btn"
								target="_blank"
								rel="noopener noreferrer"
								href="<?php echo esc_url( $cta_url ); ?>"
							>
								<?php echo esc_html( $cta_txt ); ?>
							</a>
						</p>
					<?php endif; ?>
				</div>
				<?php
			endforeach;
		endif;

		$html = ob_get_clean();

		wp_send_json_success(
			array(
				'html'  => $html,
				'count' => count( $promos ),
			)
		);
	}
}