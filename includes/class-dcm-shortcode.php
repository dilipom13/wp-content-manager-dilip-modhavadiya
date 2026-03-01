<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Shortcode {

	private $settings;
	private $repo;
	private $assets;

	public function __construct( DCM_Settings $settings, DCM_Repository $repo, DCM_Assets $assets ) {
		$this->settings = $settings;
		$this->repo     = $repo;
		$this->assets   = $assets;
	}

	public function register() {
		add_shortcode( 'dynamic_promo', array( $this, 'render' ) );
	}

	public function render() {
		if ( ! $this->settings->is_enabled() ) {
			return '';
		}

		if ( $this->settings->ajax_enabled() ) {
			return '<div class="dcm-promos" data-dcm-promos="1"><p>' . esc_html__( 'Loading promos...', DCM_TEXTDOMAIN ) . '</p></div>';
		}

		$promos = $this->repo->get_active_promos();
		return $this->render_html( $promos );
	}

	private function render_html( $promos ) {
		if ( empty( $promos ) ) {
			return '';
		}

		ob_start();
		?>
		<div class="dcm-promos">
			<?php foreach ( $promos as $p ) : ?>
				<div class="dcm-promo">
					<?php if ( ! empty( $p['image'] ) ) : ?>
						<img src="<?php echo esc_url( $p['image'] ); ?>" loading="lazy" alt="<?php echo esc_attr( $p['title'] ); ?>">
					<?php endif; ?>

					<h3><?php echo esc_html( $p['title'] ); ?></h3>

					<div class="dcm-promo__content">
						<?php echo wp_kses_post( $p['content'] ); ?>
					</div>

					<?php if ( ! empty( $p['cta_url'] ) && ! empty( $p['cta_text'] ) ) : ?>
						<p><a class="dcm-promo__btn" target="_blank" href="<?php echo esc_url( $p['cta_url'] ); ?>"><?php echo esc_html( $p['cta_text'] ); ?></a></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return (string) ob_get_clean();
	}
}