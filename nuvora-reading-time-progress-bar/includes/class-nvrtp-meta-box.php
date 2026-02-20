<?php
/**
 * Meta Box: Per-Post Reading Time Override.
 *
 * Allows editors to:
 *   1. Manually override the computed reading time.
 *   2. Disable the badge and/or progress bar for individual posts.
 *
 * @package NuvoraReadingTime
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Nvrtp_Meta_Box
 */
class Nvrtp_Meta_Box {

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
	private const NONCE_ACTION = 'nvrtp_meta_box_save';

	/**
	 * Nonce field name.
	 *
	 * @var string
	 */
	private const NONCE_FIELD = 'nvrtp_meta_box_nonce';

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
				'nvrtp-reading-time',
				__( 'Reading Time', 'nuvora-reading-time-progress-bar' ),
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

		$override         = get_post_meta( $post->ID, '_nvrtp_reading_time_override', true );
		$disable_badge    = get_post_meta( $post->ID, '_nvrtp_disable_badge', true );
		$disable_progress = get_post_meta( $post->ID, '_nvrtp_disable_progress', true );

		// Show auto-calculated value as hint.
		$calc    = new Nvrtp_Calculator( $this->settings );
		$result  = $calc->calculate( $post );
		$auto    = $result['minutes'] ?? 0;
		$words   = $result['words'] ?? 0;
		$adjusted = ! empty( $result['adjusted'] );
		?>
		<div class="nvrtp-meta-box">

			<p class="nvrtp-meta-box__auto-info">
				<?php if ( $auto > 0 ) : ?>
					<strong><?php esc_html_e( 'Auto-calculated:', 'nuvora-reading-time-progress-bar' ); ?></strong>
					<?php
					printf(
						/* translators: 1: minutes, 2: word count */
						esc_html__( '%1$d min (%2$s words)', 'nuvora-reading-time-progress-bar' ),
						(int) $auto,
						esc_html( number_format_i18n( $words ) )
					);
					if ( $adjusted ) {
						echo ' <em>(' . esc_html__( 'AI-adjusted', 'nuvora-reading-time-progress-bar' ) . ')</em>';
					}
					?>
				<?php else : ?>
					<em><?php esc_html_e( 'Save the post to see an estimate.', 'nuvora-reading-time-progress-bar' ); ?></em>
				<?php endif; ?>
			</p>

			<p>
				<label for="nvrtp_override">
					<?php esc_html_e( 'Override reading time (minutes):', 'nuvora-reading-time-progress-bar' ); ?>
				</label>
				<input
					type="number"
					id="nvrtp_override"
					name="nvrtp_reading_time_override"
					value="<?php echo esc_attr( $override ); ?>"
					min="1"
					max="999"
					step="1"
					class="widefat"
					placeholder="<?php echo esc_attr( $auto > 0 ? $auto : '' ); ?>"
					aria-describedby="nvrtp-override-hint"
				/>
				<span id="nvrtp-override-hint" class="description">
					<?php esc_html_e( 'Leave empty to use the auto-calculated value.', 'nuvora-reading-time-progress-bar' ); ?>
				</span>
			</p>

			<hr>

			<fieldset>
				<legend class="screen-reader-text">
					<?php esc_html_e( 'Visibility options', 'nuvora-reading-time-progress-bar' ); ?>
				</legend>

				<p>
					<label>
						<input
							type="checkbox"
							name="nvrtp_disable_badge"
							value="1"
							<?php checked( '1', $disable_badge ); ?>
						/>
						<?php esc_html_e( 'Hide reading time badge', 'nuvora-reading-time-progress-bar' ); ?>
					</label>
				</p>

				<p>
					<label>
						<input
							type="checkbox"
							name="nvrtp_disable_progress"
							value="1"
							<?php checked( '1', $disable_progress ); ?>
						/>
						<?php esc_html_e( 'Hide scroll progress bar', 'nuvora-reading-time-progress-bar' ); ?>
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
		if ( isset( $_POST['nvrtp_reading_time_override'] ) ) {
			$override = sanitize_text_field( wp_unslash( $_POST['nvrtp_reading_time_override'] ) );
			if ( '' === $override ) {
				delete_post_meta( $post_id, '_nvrtp_reading_time_override' );
			} else {
				update_post_meta( $post_id, '_nvrtp_reading_time_override', absint( $override ) );
			}
		}

		// -- Badge disable flag.
		$disable_badge = isset( $_POST['nvrtp_disable_badge'] ) && '1' === $_POST['nvrtp_disable_badge'];
		if ( $disable_badge ) {
			update_post_meta( $post_id, '_nvrtp_disable_badge', '1' );
		} else {
			delete_post_meta( $post_id, '_nvrtp_disable_badge' );
		}

		// -- Progress bar disable flag.
		$disable_progress = isset( $_POST['nvrtp_disable_progress'] ) && '1' === $_POST['nvrtp_disable_progress'];
		if ( $disable_progress ) {
			update_post_meta( $post_id, '_nvrtp_disable_progress', '1' );
		} else {
			delete_post_meta( $post_id, '_nvrtp_disable_progress' );
		}
	}
}
