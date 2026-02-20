<?php
/**
 * Admin Settings Page.
 *
 * @package NuvoraReadingTime
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Nvrtp_Admin {

	private const OPTION_NAME = 'nvrtp_settings';
	private const PAGE_SLUG = 'nuvora-reading-time-progress-bar';
	private array $settings;

	public function __construct( array $settings ) {
		$this->settings = $settings;
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_reset' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function add_menu_page(): void {
		add_options_page(
			__( 'Nuvora Reading Time & Progress Bar', 'nuvora-reading-time-progress-bar' ),
			__( 'Reading Time', 'nuvora-reading-time-progress-bar' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	public function register_settings(): void {
		register_setting(
			'nvrtp_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		add_settings_section( 'nvrtp_speed', __( 'Reading Speed', 'nuvora-reading-time-progress-bar' ), null, self::PAGE_SLUG );
		add_settings_field( 'wpm', __( 'Words per minute', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_number' ), self::PAGE_SLUG, 'nvrtp_speed',
			array( 'id' => 'wpm', 'min' => 60, 'max' => 600, 'desc' => __( 'Average adult reads 200–250 wpm. Default: 238.', 'nuvora-reading-time-progress-bar' ) )
		);
		add_settings_field( 'ai_adjustment', __( 'AI-assisted adjustment', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_speed',
			array( 'id' => 'ai_adjustment', 'desc' => __( 'Reduce estimated time for posts with many headings, lists, and short paragraphs (readers scan structured content faster).', 'nuvora-reading-time-progress-bar' ) )
		);

		add_settings_section( 'nvrtp_badge', __( 'Reading Time Badge', 'nuvora-reading-time-progress-bar' ), null, self::PAGE_SLUG );
		add_settings_field( 'show_badge', __( 'Show badge', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_badge',
			array( 'id' => 'show_badge' )
		);
		add_settings_field( 'badge_position', __( 'Badge position', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_select' ), self::PAGE_SLUG, 'nvrtp_badge',
			array(
				'id'      => 'badge_position',
				'options' => array(
					'before' => __( 'Before content', 'nuvora-reading-time-progress-bar' ),
					'after'  => __( 'After content', 'nuvora-reading-time-progress-bar' ),
					'both'   => __( 'Both', 'nuvora-reading-time-progress-bar' ),
				),
			)
		);
		add_settings_field( 'badge_post_types', __( 'Show badge on', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_post_types' ), self::PAGE_SLUG, 'nvrtp_badge',
			array( 'id' => 'badge_post_types' )
		);
		add_settings_field( 'badge_label', __( 'Badge label', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_text' ), self::PAGE_SLUG, 'nvrtp_badge',
			array( 'id' => 'badge_label', 'desc' => __( 'Use {time} as placeholder for the minute count.', 'nuvora-reading-time-progress-bar' ) )
		);
		add_settings_field( 'badge_icon', __( 'Show clock icon', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_badge',
			array( 'id' => 'badge_icon' )
		);

		add_settings_section( 'nvrtp_progress', __( 'Scroll Progress Bar', 'nuvora-reading-time-progress-bar' ), null, self::PAGE_SLUG );
		add_settings_field( 'show_progress_bar', __( 'Show progress bar', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_progress',
			array( 'id' => 'show_progress_bar' )
		);
		add_settings_field( 'progress_position', __( 'Bar position', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_select' ), self::PAGE_SLUG, 'nvrtp_progress',
			array(
				'id'      => 'progress_position',
				'options' => array(
					'top'    => __( 'Top of viewport', 'nuvora-reading-time-progress-bar' ),
					'bottom' => __( 'Bottom of viewport', 'nuvora-reading-time-progress-bar' ),
				),
			)
		);
		add_settings_field( 'progress_post_types', __( 'Show progress bar on', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_post_types' ), self::PAGE_SLUG, 'nvrtp_progress',
			array( 'id' => 'progress_post_types' )
		);
		add_settings_field( 'progress_color', __( 'Progress bar color', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_color' ), self::PAGE_SLUG, 'nvrtp_progress',
			array( 'id' => 'progress_color' )
		);
		add_settings_field( 'progress_height', __( 'Bar height (px)', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_number' ), self::PAGE_SLUG, 'nvrtp_progress',
			array( 'id' => 'progress_height', 'min' => 1, 'max' => 10 )
		);
		add_settings_field( 'progress_show_tooltip', __( 'Show percentage tooltip', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_progress',
			array( 'id' => 'progress_show_tooltip', 'desc' => __( 'Display % read on hover.', 'nuvora-reading-time-progress-bar' ) )
		);

		add_settings_section( 'nvrtp_advanced', __( 'Advanced', 'nuvora-reading-time-progress-bar' ), null, self::PAGE_SLUG );
		add_settings_field( 'exclude_code_blocks', __( 'Exclude code blocks', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_advanced',
			array( 'id' => 'exclude_code_blocks', 'desc' => __( 'Exclude <code>&lt;pre&gt;</code> and <code>&lt;code&gt;</code> tags from word count.', 'nuvora-reading-time-progress-bar' ) )
		);
		add_settings_field( 'reduce_motion_respect', __( 'Respect reduce-motion', 'nuvora-reading-time-progress-bar' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'nvrtp_advanced',
			array( 'id' => 'reduce_motion_respect', 'desc' => __( 'Disable progress bar animation for users with prefers-reduced-motion enabled (accessibility).', 'nuvora-reading-time-progress-bar' ) )
		);
	}

	public function sanitize_settings( array $input ): array {
		$clean = array();

		$clean['wpm']             = max( 60, min( 600, absint( $input['wpm'] ?? 238 ) ) );
		$clean['cpm']             = max( 200, min( 2000, absint( $input['cpm'] ?? 1000 ) ) );
		$clean['progress_height'] = max( 1, min( 10, absint( $input['progress_height'] ?? 3 ) ) );

		$checkboxes = array(
			'ai_adjustment', 'show_badge', 'badge_icon',
			'show_progress_bar', 'progress_show_tooltip',
			'exclude_code_blocks', 'exclude_shortcodes', 'reduce_motion_respect',
		);
		foreach ( $checkboxes as $key ) {
			$clean[ $key ] = ! empty( $input[ $key ] );
		}

		$clean['badge_position']    = in_array( $input['badge_position'] ?? 'before', array( 'before', 'after', 'both' ), true )
			? $input['badge_position'] : 'before';
		$clean['progress_position'] = in_array( $input['progress_position'] ?? 'top', array( 'top', 'bottom' ), true )
			? $input['progress_position'] : 'top';

		$clean['badge_label']           = sanitize_text_field( $input['badge_label'] ?? '{time} min read' );
		$clean['badge_label_under_one'] = sanitize_text_field( $input['badge_label_under_one'] ?? 'Less than 1 min read' );

		$clean['progress_color']    = sanitize_hex_color( $input['progress_color'] ?? '#6366f1' ) ?: '#6366f1';
		$clean['progress_bg_color'] = sanitize_text_field( $input['progress_bg_color'] ?? 'rgba(99,102,241,0.15)' );

		$all_types = array_keys( get_post_types( array( 'public' => true ) ) );
		foreach ( array( 'badge_post_types', 'progress_post_types' ) as $key ) {
			$raw           = isset( $input[ $key ] ) ? (array) $input[ $key ] : array( 'post' );
			$clean[ $key ] = array_values( array_intersect( $raw, $all_types ) );
		}

		return $clean;
	}

	public function handle_reset(): void {
		if ( ! isset( $_POST['nvrtp_reset_settings'] ) ) {
			return;
		}
		if (
			! isset( $_POST['nvrtp_reset_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nvrtp_reset_nonce'] ) ), 'nvrtp_reset_settings' )
		) {
			wp_die( esc_html__( 'Security check failed.', 'nuvora-reading-time-progress-bar' ) );
		}
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'nuvora-reading-time-progress-bar' ) );
		}
		delete_option( self::OPTION_NAME );
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'      => self::PAGE_SLUG,
					'nvrtp-reset' => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap nvrtp-admin-wrap">
			<h1>
				<span class="nvrtp-admin-icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
					</svg>
				</span>
				<?php esc_html_e( 'Nuvora Reading Time', 'nuvora-reading-time-progress-bar' ); ?>
			</h1>

			<?php settings_errors( self::OPTION_NAME ); ?>

			<?php if ( isset( $_GET['nvrtp-reset'] ) && '1' === $_GET['nvrtp-reset'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong><?php esc_html_e( 'Settings reset successfully.', 'nuvora-reading-time-progress-bar' ); ?></strong>
						<?php esc_html_e( 'All settings have been restored to their defaults.', 'nuvora-reading-time-progress-bar' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<form method="post" action="options.php" novalidate>
				<?php
				settings_fields( 'nvrtp_settings_group' );
				do_settings_sections( self::PAGE_SLUG );
				?>
				<div class="nvrtp-form-actions">
					<?php submit_button( __( 'Save Settings', 'nuvora-reading-time-progress-bar' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ); ?>"
				class="nvrtp-reset-form"
				id="nvrtp-reset-form"
			>
				<?php wp_nonce_field( 'nvrtp_reset_settings', 'nvrtp_reset_nonce' ); ?>
				<div class="nvrtp-reset-section">
					<button
						type="submit"
						name="nvrtp_reset_settings"
						value="1"
						class="button button-secondary nvrtp-reset-btn"
						id="nvrtp-reset-btn"
					>
						<span aria-hidden="true">&#8634;</span>
						<?php esc_html_e( 'Reset All Settings to Defaults', 'nuvora-reading-time-progress-bar' ); ?>
					</button>
					<span class="nvrtp-reset-hint">
						<?php esc_html_e( 'This will restore all settings to their original defaults. Your posts are not affected.', 'nuvora-reading-time-progress-bar' ); ?>
					</span>
				</div>
			</form>

			<div class="nvrtp-admin-footer">
				<p>
					<?php
					printf(
						/* translators: %s: Author name */
						esc_html__( 'Built with care by %s · 8+ years in WordPress.', 'nuvora-reading-time-progress-bar' ),
						'<a href="https://sehrishanam.com" target="_blank" rel="noopener">Sehrish Anam</a>'
					);
					?>
				</p>
			</div>
		</div>
		<?php
	}

	// ── Field renderers ────────────────────────────────────────────────.

	public function field_checkbox( array $args ): void {
		$id      = esc_attr( $args['id'] );
		$value   = (bool) ( $this->settings[ $args['id'] ] ?? false );
		$desc    = $args['desc'] ?? '';
		$checked = checked( $value, true, false );
		echo '<label><input type="checkbox" id="' . $id . '" name="nvrtp_settings[' . $id . ']" value="1" ' . $checked . ' /> ' . wp_kses_post( $desc ) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function field_number( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = absint( $this->settings[ $args['id'] ] ?? 0 );
		$min   = (int) ( $args['min'] ?? 1 );
		$max   = (int) ( $args['max'] ?? 999 );
		$desc  = esc_html( $args['desc'] ?? '' );
		echo '<input type="number" id="' . $id . '" name="nvrtp_settings[' . $id . ']" value="' . $value . '" min="' . $min . '" max="' . $max . '" class="small-text" /> ' . $desc; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function field_text( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = esc_attr( $this->settings[ $args['id'] ] ?? '' );
		$desc  = esc_html( $args['desc'] ?? '' );
		echo '<input type="text" id="' . $id . '" name="nvrtp_settings[' . $id . ']" value="' . $value . '" class="regular-text" /> <span class="description">' . $desc . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function field_select( array $args ): void {
		$id      = esc_attr( $args['id'] );
		$current = $this->settings[ $args['id'] ] ?? '';
		$options = $args['options'] ?? array();
		echo '<select id="' . $id . '" name="nvrtp_settings[' . $id . ']">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $options as $val => $label ) {
			$sel = selected( $current, $val, false );
			echo '<option value="' . esc_attr( $val ) . '" ' . $sel . '>' . esc_html( $label ) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</select>';
	}

	public function field_color( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = esc_attr( $this->settings[ $args['id'] ] ?? '#6366f1' );
		echo '<input type="color" id="' . $id . '" name="nvrtp_settings[' . $id . ']" value="' . $value . '" class="nvrtp-color-field" />'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function field_post_types( array $args ): void {
		$id      = esc_attr( $args['id'] );
		$current = (array) ( $this->settings[ $args['id'] ] ?? array( 'post' ) );
		$types   = get_post_types( array( 'public' => true ), 'objects' );
		echo '<fieldset>';
		foreach ( $types as $type ) {
			$chk = checked( in_array( $type->name, $current, true ), true, false );
			echo '<label style="display:block;margin-bottom:4px"><input type="checkbox" name="nvrtp_settings[' . $id . '][]" value="' . esc_attr( $type->name ) . '" ' . $chk . ' /> ' . esc_html( $type->labels->singular_name ) . ' <code>(' . esc_html( $type->name ) . ')</code></label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		echo '</fieldset>';
	}

	public function enqueue_assets( string $hook ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'nvrtp-admin',
			NVRTP_PLUGIN_URL . 'admin/css/nvrtp-admin.css',
			array(),
			NVRTP_VERSION
		);
		wp_enqueue_script(
			'nvrtp-admin-js',
			NVRTP_PLUGIN_URL . 'admin/js/nvrtp-admin.js',
			array(),
			NVRTP_VERSION,
			true
		);
		wp_localize_script(
			'nvrtp-admin-js',
			'nvrtpAdminVars',
			array(
				'resetConfirm' => __( 'Reset all settings to defaults? This cannot be undone.', 'nuvora-reading-time-progress-bar' ),
			)
		);
	}
}
