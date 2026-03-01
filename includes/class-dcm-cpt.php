<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_CPT {

	const POST_TYPE = 'promo_block';

	const META_CTA_TEXT = '_dcm_cta_text';
	const META_CTA_URL  = '_dcm_cta_url';
	const META_PRIORITY = '_dcm_priority';
	const META_EXPIRY   = '_dcm_expiry_ymd';

	private $settings;

	public function __construct( DCM_Settings $settings ) {
		$this->settings = $settings;
	}

	public function register() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );
	}

	public function register_post_type() {
		register_post_type(
			self::POST_TYPE,
			array(
				'labels'       => array(
					'name'          => __( 'Promo Blocks', DCM_TEXTDOMAIN ),
					'singular_name' => __( 'Promo Block', DCM_TEXTDOMAIN ),
				),
				'public'       => false,
				'show_ui'      => true,
				'menu_icon'    => 'dashicons-megaphone',
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'has_archive'  => false,
				'show_in_rest' => true,
			)
		);
	}

	public function add_meta_box() {
		add_meta_box(
			'dcm_promo_details',
			__( 'Promo Details', DCM_TEXTDOMAIN ),
			array( $this, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'default'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'dcm_save_promo_meta', 'dcm_promo_nonce' );

		$cta_text = get_post_meta( $post->ID, self::META_CTA_TEXT, true );
		$cta_url  = get_post_meta( $post->ID, self::META_CTA_URL, true );
		$priority = get_post_meta( $post->ID, self::META_PRIORITY, true );
		$expiry   = get_post_meta( $post->ID, self::META_EXPIRY, true );
		?>
		<p>
			<label><strong><?php echo esc_html__( 'CTA Text', DCM_TEXTDOMAIN ); ?></strong></label><br>
			<input class="widefat" type="text" name="dcm_cta_text" value="<?php echo esc_attr( $cta_text ); ?>">
		</p>

		<p>
			<label><strong><?php echo esc_html__( 'CTA URL', DCM_TEXTDOMAIN ); ?></strong></label><br>
			<input class="widefat" type="url" name="dcm_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" placeholder="https://example.com">
		</p>

		<p>
			<label><strong><?php echo esc_html__( 'Display Priority', DCM_TEXTDOMAIN ); ?></strong></label><br>
			<input type="number" name="dcm_priority" value="<?php echo esc_attr( $priority ); ?>" min="0" step="1">
		</p>

		<p>
			<label><strong><?php echo esc_html__( 'Expiry Date (YYYYMMDD)', DCM_TEXTDOMAIN ); ?></strong></label><br>
			<input type="text" name="dcm_expiry" class="dcm-datepicker" value="<?php echo esc_attr( $expiry ); ?>" placeholder="20261231" autocomplete="off">
			<br><em><?php echo esc_html__( 'Empty = no expiry.', DCM_TEXTDOMAIN ); ?></em>
		</p>
		<?php
	}

	public function save_meta( $post_id ) {
		if ( get_post_type( $post_id ) !== self::POST_TYPE ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! isset( $_POST['dcm_promo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dcm_promo_nonce'] ) ), 'dcm_save_promo_meta' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$cta_text = isset( $_POST['dcm_cta_text'] ) ? sanitize_text_field( wp_unslash( $_POST['dcm_cta_text'] ) ) : '';
		$cta_url  = isset( $_POST['dcm_cta_url'] ) ? esc_url_raw( wp_unslash( $_POST['dcm_cta_url'] ) ) : '';
		$priority = isset( $_POST['dcm_priority'] ) ? absint( wp_unslash( $_POST['dcm_priority'] ) ) : 0;

		$expiry_raw = isset( $_POST['dcm_expiry'] ) ? sanitize_text_field( wp_unslash( $_POST['dcm_expiry'] ) ) : '';
		$expiry     = preg_replace( '/\D+/', '', (string) $expiry_raw );
		if ( ! preg_match( '/^\d{8}$/', $expiry ) ) {
			$expiry = '';
		}

		update_post_meta( $post_id, self::META_CTA_TEXT, $cta_text );
		update_post_meta( $post_id, self::META_CTA_URL, $cta_url );
		update_post_meta( $post_id, self::META_PRIORITY, $priority );

		if ( '' === $expiry ) {
			delete_post_meta( $post_id, self::META_EXPIRY );
		} else {
			update_post_meta( $post_id, self::META_EXPIRY, $expiry );
		}
	}
}