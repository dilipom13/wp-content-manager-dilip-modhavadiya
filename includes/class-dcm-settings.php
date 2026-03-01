<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class DCM_Settings {

	const OPTION = 'dcm_settings';

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu() {
		add_options_page(
			__( 'Dynamic Content', DCM_TEXTDOMAIN ),
			__( 'Dynamic Content', DCM_TEXTDOMAIN ),
			'manage_options',
			'dcm-dynamic-content',
			array( $this, 'render_page' )
		);
	}

	public function register_settings() {
		register_setting( 'dcm_settings_group', self::OPTION, array( $this, 'sanitize' ) );

		add_settings_section(
			'dcm_main',
			__( 'Promo Configuration', DCM_TEXTDOMAIN ),
			function () {
				echo '<p>' . esc_html__( 'Control how promo blocks display on the front-end.', DCM_TEXTDOMAIN ) . '</p>';
			},
			'dcm-dynamic-content'
		);

		$this->add_toggle_field( 'enabled', __( 'Enable/Disable', DCM_TEXTDOMAIN ) );
		$this->add_number_field( 'max_promos', __( 'Maximum promo blocks', DCM_TEXTDOMAIN ), 1, 50 );
		$this->add_number_field( 'cache_ttl', __( 'Cache TTL (minutes)', DCM_TEXTDOMAIN ), 1, 1440 );
		$this->add_toggle_field( 'ajax_enabled', __( 'Enable AJAX loading', DCM_TEXTDOMAIN ) );
	}

	private function add_toggle_field( $key, $label ) {
		add_settings_field(
			'dcm_' . $key,
			$label,
			function () use ( $key ) {
				$opt   = $this->get_all();
				$value = ! empty( $opt[ $key ] ) ? 1 : 0;
				?>
				<label class="switch">
					<input type="checkbox" name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( 1, $value ); ?>>
					<span class="slider"></span>
				</label>
				<?php
			},
			'dcm-dynamic-content',
			'dcm_main'
		);
	}

	private function add_number_field( $key, $label, $min, $max ) {
		add_settings_field(
			'dcm_' . $key,
			$label,
			function () use ( $key, $min, $max ) {
				$opt   = $this->get_all();
				$value = isset( $opt[ $key ] ) ? (int) $opt[ $key ] : 0;
				?>
				<input type="number" class="small-text"
					min="<?php echo esc_attr( $min ); ?>"
					max="<?php echo esc_attr( $max ); ?>"
					step="1"
					name="<?php echo esc_attr( self::OPTION ); ?>[<?php echo esc_attr( $key ); ?>]"
					value="<?php echo esc_attr( $value ); ?>"
				>
				<?php
			},
			'dcm-dynamic-content',
			'dcm_main'
		);
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html__( 'Dynamic Content', DCM_TEXTDOMAIN ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'dcm_settings_group' );
				do_settings_sections( 'dcm-dynamic-content' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function sanitize( $input ) {
		$out = array();

		$out['enabled']      = ! empty( $input['enabled'] ) ? 1 : 0;
		$out['ajax_enabled'] = ! empty( $input['ajax_enabled'] ) ? 1 : 0;

		$max               = isset( $input['max_promos'] ) ? absint( $input['max_promos'] ) : 3;
		$out['max_promos'] = max( 1, min( 50, $max ) );

		$ttl              = isset( $input['cache_ttl'] ) ? absint( $input['cache_ttl'] ) : 10;
		$out['cache_ttl'] = max( 1, min( 1440, $ttl ) );

		return $out;
	}

	public function get_all() {
		$defaults = array(
			'enabled'      => 1,
			'max_promos'   => 3,
			'cache_ttl'    => 10,
			'ajax_enabled' => 0,
		);

		$opt = get_option( self::OPTION, array() );
		if ( ! is_array( $opt ) ) {
			$opt = array();
		}

		return array_merge( $defaults, $opt );
	}

	public function is_enabled() {
		$opt = $this->get_all();
		return ! empty( $opt['enabled'] );
	}

	public function ajax_enabled() {
		$opt = $this->get_all();
		return ! empty( $opt['ajax_enabled'] );
	}

	public function max_promos() {
		$opt = $this->get_all();
		return (int) $opt['max_promos'];
	}

	public function ttl_seconds() {
		$opt = $this->get_all();
		return (int) $opt['cache_ttl'] * MINUTE_IN_SECONDS;
	}
}