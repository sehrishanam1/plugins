<?php
/**
 * Meta Box: Per-Post Reading Time Override.
 *
 * Allows editors to:
 *   1. Manually override the computed reading time.
 *   2. Disable the badge and/or progress bar for individual posts.
 *
 * @package ReadingTimeEstimator
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class RTE_Meta_Box
 */
class RTE_Meta_Box {

	/**
	 * Plugin settings.
	 *
	 * @var array
	 */
	private array $settings;

	/**
	 * Nonce action.
	 *
	 * @var string
	 */
	private const NONCE_ACTION = 'rte_meta_box_save';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	private const NONCE_FIELD = 'rte_meta_box_nonce';

	/**
	 * Constructor.
	 *
	 * @param array $settings Plugin settings.
	 */
	public function __construct( array $settings ) {
		$this->settings = $settings;
		add_action( 'add_meta_boxes', array( $this, 'register' ) );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Register the meta box on all configured post types.
	 */
	public function register(): void {
		$post_types = array_unique(
			array_merge(
				(array) ( $this->settings['badge_post_types'] ?? array( 'post' ) ),
				(array) ( $this->settings['progress_post_types'] ?? array( 'post' ) )
			)
		);

		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'rte-reading-time',
				__( 'Reading Time', 'reading-time-estimator' ),
				array( $this, 'render' ),
				$post_type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Render meta box HTML.
	 *
	 * @param \WP_Post $post Current post object.
	 */
	public function render( \WP_Post $post ): void {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD );

		$override         = get_post_meta( $post->ID, '_rte_reading_time_override', true );
		$disable_badge    = get_post_meta( $post->ID, '_rte_disable_badge', true );
		$disable_progress = get_post_meta( $post->ID, '_rte_disable_progress', true );

		// Show auto-calculated value as hint.
		$calc    = new RTE_Calculator( $this->settings );
		$result  = $calc->calculate( $post );
		$auto    = $result['minutes'] ?? 0;
		$words   = $result['words'] ?? 0;
		$adjusted = ! empty( $result['adjusted'] );
		?>
		<div class="rte-meta-box">

			<p class="rte-meta-box__auto-info">
				<?php if ( $auto > 0 ) : ?>
					<strong><?php esc_html_e( 'Auto-calculated:', 'reading-time-estimator' ); ?></strong>
					<?php
					printf(
						/* translators: 1: minutes, 2: word count */
						esc_html__( '%1$d min (%2$s words)', 'reading-time-estimator' ),
						(int) $auto,
						number_format_i18n( $words )
					);
					if ( $adjusted ) {
						echo ' <em>(' . esc_html__( 'AI-adjusted', 'reading-time-estimator' ) . ')</em>';
					}
					?>
				<?php else : ?>
					<em><?php esc_html_e( 'Save the post to see an estimate.', 'reading-time-estimator' ); ?></em>
				<?php endif; ?>
			</p>

			<p>
				<label for="rte_override">
					<?php esc_html_e( 'Override reading time (minutes):', 'reading-time-estimator' ); ?>
				</label>
				<input
					type="number"
					id="rte_override"
					name="rte_reading_time_override"
					value="<?php echo esc_attr( $override ); ?>"
					min="1"
					max="999"
					step="1"
					class="widefat"
					placeholder="<?php echo esc_attr( $auto > 0 ? $auto : '' ); ?>"
					aria-describedby="rte-override-hint"
				/>
				<span id="rte-override-hint" class="description">
					<?php esc_html_e( 'Leave empty to use the auto-calculated value.', 'reading-time-estimator' ); ?>
				</span>
			</p>

			<hr>

			<fieldset>
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Visibility options', 'reading-time-estimator' ); ?>
				</legend>

				<p>
					<label>
						<input
							type="checkbox"
							name="rte_disable_badge"
							value="1"
							<?php checked( '1', $disable_badge ); ?>
						/>
						<?php esc_html_e( 'Hide reading time badge', 'reading-time-estimator' ); ?>
					</label>
				</p>

				<p>
					<label>
						<input
							type="checkbox"
							name="rte_disable_progress"
							value="1"
							<?php checked( '1', $disable_progress ); ?>
						/>
						<?php esc_html_e( 'Hide scroll progress bar', 'reading-time-estimator' ); ?>
					</label>
				</p>
			</fieldset>

		</div>
		<?php
	}

	/**
	 * Save meta box data.
	 *
	 * @param int      $post_id Post ID.
	 * @param \WP_Post $post    Post object.
	 */
	public function save( int $post_id, \WP_Post $post ): void {
		// Nonce check.
		if (
			! isset( $_POST[ self::NONCE_FIELD ] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ), self::NONCE_ACTION )
		) {
			return;
		}

		// Bail on auto-save / revisions / bulk edit.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// Capability check.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// -- Override value.
		if ( isset( $_POST['rte_reading_time_override'] ) ) {
			$override = sanitize_text_field( wp_unslash( $_POST['rte_reading_time_override'] ) );
			if ( '' === $override ) {
				delete_post_meta( $post_id, '_rte_reading_time_override' );
			} else {
				update_post_meta( $post_id, '_rte_reading_time_override', absint( $override ) );
			}
		}

		// -- Badge disable flag.
		$disable_badge = isset( $_POST['rte_disable_badge'] ) && '1' === $_POST['rte_disable_badge'];
		if ( $disable_badge ) {
			update_post_meta( $post_id, '_rte_disable_badge', '1' );
		} else {
			delete_post_meta( $post_id, '_rte_disable_badge' );
		}

		// -- Progress bar disable flag.
		$disable_progress = isset( $_POST['rte_disable_progress'] ) && '1' === $_POST['rte_disable_progress'];
		if ( $disable_progress ) {
			update_post_meta( $post_id, '_rte_disable_progress', '1' );
		} else {
			delete_post_meta( $post_id, '_rte_disable_progress' );
		}
	}
}
