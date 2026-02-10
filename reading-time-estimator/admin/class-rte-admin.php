<?php
/**
 * Admin Settings Page.
 *
 * Registers and renders the plugin settings page under Settings > Reading Time.
 * Uses the Settings API for secure, sanitized form handling.
 *
 * @package ReadingTimeEstimator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RTE_Admin
 */
class RTE_Admin {

	/**
	 * Option name in wp_options.
	 *
	 * @var string
	 */
	private const OPTION_NAME = 'rte_settings';

	/**
	 * Settings page slug.
	 *
	 * @var string
	 */
	private const PAGE_SLUG = 'reading-time-estimator';

	/**
	 * Current settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Constructor.
	 *
	 * @param array $settings Current plugin settings.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_reset' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Add settings page under Settings menu.
	 */
	public function add_menu_page(): void {
		add_options_page(
			__( 'Reading Time Estimator', 'reading-time-estimator' ),
			__( 'Reading Time', 'reading-time-estimator' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Register settings, sections, and fields with the Settings API.
	 */
	public function register_settings(): void {
		register_setting(
			'rte_settings_group',
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(),
			)
		);

		// ── Section: Reading Speed ──────────────────────────────────────.
		add_settings_section( 'rte_speed', __( 'Reading Speed', 'reading-time-estimator' ), null, self::PAGE_SLUG );

		add_settings_field( 'wpm', __( 'Words per minute', 'reading-time-estimator' ),
			array( $this, 'field_number' ), self::PAGE_SLUG, 'rte_speed',
			array( 'id' => 'wpm', 'min' => 60, 'max' => 600, 'desc' => __( 'Average adult reads 200–250 wpm. Default: 238.', 'reading-time-estimator' ) )
		);

		add_settings_field( 'ai_adjustment', __( 'AI-assisted adjustment', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_speed',
			array( 'id' => 'ai_adjustment', 'desc' => __( 'Reduce estimated time for posts with many headings, lists, and short paragraphs (readers scan structured content faster).', 'reading-time-estimator' ) )
		);

		// ── Section: Badge ──────────────────────────────────────────────.
		add_settings_section( 'rte_badge', __( 'Reading Time Badge', 'reading-time-estimator' ), null, self::PAGE_SLUG );

		add_settings_field( 'show_badge', __( 'Show badge', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_badge',
			array( 'id' => 'show_badge' )
		);

		add_settings_field( 'badge_position', __( 'Badge position', 'reading-time-estimator' ),
			array( $this, 'field_select' ), self::PAGE_SLUG, 'rte_badge',
			array(
				'id'      => 'badge_position',
				'options' => array(
					'before' => __( 'Before content', 'reading-time-estimator' ),
					'after'  => __( 'After content', 'reading-time-estimator' ),
					'both'   => __( 'Both', 'reading-time-estimator' ),
				),
			)
		);

		add_settings_field( 'badge_post_types', __( 'Show badge on', 'reading-time-estimator' ),
			array( $this, 'field_post_types' ), self::PAGE_SLUG, 'rte_badge',
			array( 'id' => 'badge_post_types' )
		);

		add_settings_field( 'badge_label', __( 'Badge label', 'reading-time-estimator' ),
			array( $this, 'field_text' ), self::PAGE_SLUG, 'rte_badge',
			array( 'id' => 'badge_label', 'desc' => __( 'Use {time} as placeholder for the minute count.', 'reading-time-estimator' ) )
		);

		add_settings_field( 'badge_icon', __( 'Show clock icon', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_badge',
			array( 'id' => 'badge_icon' )
		);

		// ── Section: Progress Bar ───────────────────────────────────────.
		add_settings_section( 'rte_progress', __( 'Scroll Progress Bar', 'reading-time-estimator' ), null, self::PAGE_SLUG );

		add_settings_field( 'show_progress_bar', __( 'Show progress bar', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_progress',
			array( 'id' => 'show_progress_bar' )
		);

		add_settings_field( 'progress_position', __( 'Bar position', 'reading-time-estimator' ),
			array( $this, 'field_select' ), self::PAGE_SLUG, 'rte_progress',
			array(
				'id'      => 'progress_position',
				'options' => array(
					'top'    => __( 'Top of viewport', 'reading-time-estimator' ),
					'bottom' => __( 'Bottom of viewport', 'reading-time-estimator' ),
				),
			)
		);

		add_settings_field( 'progress_post_types', __( 'Show progress bar on', 'reading-time-estimator' ),
			array( $this, 'field_post_types' ), self::PAGE_SLUG, 'rte_progress',
			array( 'id' => 'progress_post_types' )
		);

		add_settings_field( 'progress_color', __( 'Progress bar color', 'reading-time-estimator' ),
			array( $this, 'field_color' ), self::PAGE_SLUG, 'rte_progress',
			array( 'id' => 'progress_color' )
		);

		add_settings_field( 'progress_height', __( 'Bar height (px)', 'reading-time-estimator' ),
			array( $this, 'field_number' ), self::PAGE_SLUG, 'rte_progress',
			array( 'id' => 'progress_height', 'min' => 1, 'max' => 10 )
		);

		add_settings_field( 'progress_show_tooltip', __( 'Show percentage tooltip', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_progress',
			array( 'id' => 'progress_show_tooltip', 'desc' => __( 'Display % read on hover.', 'reading-time-estimator' ) )
		);

		// ── Section: Advanced ───────────────────────────────────────────.
		add_settings_section( 'rte_advanced', __( 'Advanced', 'reading-time-estimator' ), null, self::PAGE_SLUG );

		add_settings_field( 'exclude_code_blocks', __( 'Exclude code blocks', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_advanced',
			array( 'id' => 'exclude_code_blocks', 'desc' => __( 'Exclude <code>&lt;pre&gt;</code> and <code>&lt;code&gt;</code> tags from word count.', 'reading-time-estimator' ) )
		);

		add_settings_field( 'reduce_motion_respect', __( 'Respect reduce-motion', 'reading-time-estimator' ),
			array( $this, 'field_checkbox' ), self::PAGE_SLUG, 'rte_advanced',
			array( 'id' => 'reduce_motion_respect', 'desc' => __( 'Disable progress bar animation for users with prefers-reduced-motion enabled (accessibility).', 'reading-time-estimator' ) )
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @param array $input Raw POST input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( array $input ): array {
		$clean = array();

		// Integers.
		$clean['wpm']             = max( 60, min( 600, absint( $input['wpm'] ?? 238 ) ) );
		$clean['cpm']             = max( 200, min( 2000, absint( $input['cpm'] ?? 1000 ) ) );
		$clean['progress_height'] = max( 1, min( 10, absint( $input['progress_height'] ?? 3 ) ) );

		// Checkboxes.
		$checkboxes = array(
			'ai_adjustment', 'show_badge', 'badge_icon',
			'show_progress_bar', 'progress_show_tooltip',
			'exclude_code_blocks', 'exclude_shortcodes', 'reduce_motion_respect',
		);
		foreach ( $checkboxes as $key ) {
			$clean[ $key ] = ! empty( $input[ $key ] );
		}

		// Selects.
		$clean['badge_position']    = in_array( $input['badge_position'] ?? 'before', array( 'before', 'after', 'both' ), true )
			? $input['badge_position']
			: 'before';
		$clean['progress_position'] = in_array( $input['progress_position'] ?? 'top', array( 'top', 'bottom' ), true )
			? $input['progress_position']
			: 'top';

		// Text / label.
		$clean['badge_label']       = sanitize_text_field( $input['badge_label'] ?? '{time} min read' );
		$clean['badge_label_under_one'] = sanitize_text_field( $input['badge_label_under_one'] ?? 'Less than 1 min read' );

		// Colors.
		$clean['progress_color']    = sanitize_hex_color( $input['progress_color'] ?? '#6366f1' ) ?: '#6366f1';
		$clean['progress_bg_color'] = sanitize_text_field( $input['progress_bg_color'] ?? 'rgba(99,102,241,0.15)' );

		// Post type arrays.
		$all_types = array_keys( get_post_types( array( 'public' => true ) ) );
		foreach ( array( 'badge_post_types', 'progress_post_types' ) as $key ) {
			$raw = isset( $input[ $key ] ) ? (array) $input[ $key ] : array( 'post' );
			$clean[ $key ] = array_values( array_intersect( $raw, $all_types ) );
		}

		return $clean;
	}

	/**
	 * Handle reset form submission.
	 * Runs on admin_init so redirect happens before any output.
	 */
	public function handle_reset(): void {
		if ( ! isset( $_POST['rte_reset_settings'] ) ) {
			return;
		}

		// Verify nonce.
		if (
			! isset( $_POST['rte_reset_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rte_reset_nonce'] ) ), 'rte_reset_settings' )
		) {
			wp_die( esc_html__( 'Security check failed.', 'reading-time-estimator' ) );
		}

		// Capability check.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to do this.', 'reading-time-estimator' ) );
		}

		// Delete the option — plugin will fall back to defaults automatically.
		delete_option( self::OPTION_NAME );

		// Redirect back with a success flag.
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'        => self::PAGE_SLUG,
					'rte-reset'   => '1',
				),
				admin_url( 'options-general.php' )
			)
		);
		exit;
	}

	/**
	 * Render the settings page.
	 */
	public function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap rte-admin-wrap">
			<h1>
				<span class="rte-admin-icon" aria-hidden="true">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
						<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
					</svg>
				</span>
				<?php esc_html_e( 'Reading Time Estimator', 'reading-time-estimator' ); ?>
			</h1>

			<?php settings_errors( self::OPTION_NAME ); ?>

			<?php // Show success notice after reset. ?>
			<?php if ( isset( $_GET['rte-reset'] ) && '1' === $_GET['rte-reset'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
				<div class="notice notice-success is-dismissible">
					<p>
						<strong><?php esc_html_e( 'Settings reset successfully.', 'reading-time-estimator' ); ?></strong>
						<?php esc_html_e( 'All settings have been restored to their defaults.', 'reading-time-estimator' ); ?>
					</p>
				</div>
			<?php endif; ?>

			<?php // Main settings form. ?>
			<form method="post" action="options.php" novalidate>
				<?php
				settings_fields( 'rte_settings_group' );
				do_settings_sections( self::PAGE_SLUG );
				?>
				<div class="rte-form-actions">
					<?php submit_button( __( 'Save Settings', 'reading-time-estimator' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<?php // Separate reset form — must be outside the settings form. ?>
			<form
				method="post"
				action="<?php echo esc_url( admin_url( 'options-general.php?page=' . self::PAGE_SLUG ) ); ?>"
				class="rte-reset-form"
				id="rte-reset-form"
			>
				<?php wp_nonce_field( 'rte_reset_settings', 'rte_reset_nonce' ); ?>
				<div class="rte-reset-section">
					<button
						type="submit"
						name="rte_reset_settings"
						value="1"
						class="button button-secondary rte-reset-btn"
						id="rte-reset-btn"
					>
						<span aria-hidden="true">&#8634;</span>
						<?php esc_html_e( 'Reset All Settings to Defaults', 'reading-time-estimator' ); ?>
					</button>
					<span class="rte-reset-hint">
						<?php esc_html_e( 'This will restore all settings to their original defaults. Your posts are not affected.', 'reading-time-estimator' ); ?>
					</span>
				</div>
			</form>

			<div class="rte-admin-footer">
				<p>
					<?php
					printf(
						/* translators: %s: Author name */
						esc_html__( 'Built with care by %s · 8+ years in WordPress.', 'reading-time-estimator' ),
						'<a href="https://sehrishanam.com" target="_blank" rel="noopener">Sehrish Anam</a>'
					);
					?>
				</p>
			</div>
		</div>

		<script>
		( function () {
			var btn = document.getElementById( 'rte-reset-btn' );
			if ( ! btn ) return;
			btn.addEventListener( 'click', function ( e ) {
				if ( ! window.confirm( '<?php echo esc_js( __( 'Reset all settings to defaults? This cannot be undone.', 'reading-time-estimator' ) ); ?>' ) ) {
					e.preventDefault();
				}
			} );
		} )();
		</script>
		<?php
	}

	// ── Field renderers ────────────────────────────────────────────────.

	/** @param array $args Field args. */
	public function field_checkbox( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = (bool) ( $this->settings[ $args['id'] ] ?? false );
		$desc  = $args['desc'] ?? '';
		printf(
			'<label><input type="checkbox" id="%1$s" name="rte_settings[%1$s]" value="1" %2$s /> %3$s</label>',
			$id,
			checked( $value, true, false ),
			wp_kses_post( $desc )
		);
	}

	/** @param array $args Field args. */
	public function field_number( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = absint( $this->settings[ $args['id'] ] ?? 0 );
		$min   = $args['min'] ?? 1;
		$max   = $args['max'] ?? 999;
		$desc  = $args['desc'] ?? '';
		printf(
			'<input type="number" id="%1$s" name="rte_settings[%1$s]" value="%2$d" min="%3$d" max="%4$d" class="small-text" /> %5$s',
			$id,
			$value,
			(int) $min,
			(int) $max,
			esc_html( $desc )
		);
	}

	/** @param array $args Field args. */
	public function field_text( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = esc_attr( $this->settings[ $args['id'] ] ?? '' );
		$desc  = $args['desc'] ?? '';
		printf(
			'<input type="text" id="%1$s" name="rte_settings[%1$s]" value="%2$s" class="regular-text" /> <span class="description">%3$s</span>',
			$id,
			$value,
			esc_html( $desc )
		);
	}

	/** @param array $args Field args. */
	public function field_select( array $args ): void {
		$id      = esc_attr( $args['id'] );
		$current = $this->settings[ $args['id'] ] ?? '';
		$options = $args['options'] ?? array();

		echo "<select id=\"{$id}\" name=\"rte_settings[{$id}]\">";
		foreach ( $options as $val => $label ) {
			printf(
				'<option value="%s" %s>%s</option>',
				esc_attr( $val ),
				selected( $current, $val, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/** @param array $args Field args. */
	public function field_color( array $args ): void {
		$id    = esc_attr( $args['id'] );
		$value = esc_attr( $this->settings[ $args['id'] ] ?? '#6366f1' );
		printf(
			'<input type="color" id="%1$s" name="rte_settings[%1$s]" value="%2$s" class="rte-color-field" />',
			$id,
			$value
		);
	}

	/** @param array $args Field args. */
	public function field_post_types( array $args ): void {
		$id       = esc_attr( $args['id'] );
		$current  = (array) ( $this->settings[ $args['id'] ] ?? array( 'post' ) );
		$types    = get_post_types( array( 'public' => true ), 'objects' );

		echo '<fieldset>';
		foreach ( $types as $type ) {
			printf(
				'<label style="display:block;margin-bottom:4px"><input type="checkbox" name="rte_settings[%1$s][]" value="%2$s" %3$s /> %4$s <code>(%2$s)</code></label>',
				$id,
				esc_attr( $type->name ),
				checked( in_array( $type->name, $current, true ), true, false ),
				esc_html( $type->labels->singular_name )
			);
		}
		echo '</fieldset>';
	}

	/**
	 * Enqueue admin CSS (minimal).
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'rte-admin',
			RTE_PLUGIN_URL . 'admin/css/rte-admin.css',
			array(),
			RTE_VERSION
		);
	}
}
