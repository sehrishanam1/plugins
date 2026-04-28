<?php
/**
 * Custom Meta Boxes – Position, Social Links, Custom Order.
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class TM_Meta_Boxes {

	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'register' ) );
		add_action( 'save_post_team_member', array( __CLASS__, 'save' ) );

		// Add "Order" column to list table
		add_filter( 'manage_team_member_posts_columns',       array( __CLASS__, 'add_columns' ) );
		add_action( 'manage_team_member_posts_custom_column', array( __CLASS__, 'render_columns' ), 10, 2 );
		add_filter( 'manage_edit-team_member_sortable_columns', array( __CLASS__, 'sortable_columns' ) );
	}

	// ── Register ─────────────────────────────────────────────────────────────

	public static function register() {
		add_meta_box(
			'tm_details',
			__( 'Member Details', 'team-members' ),
			array( __CLASS__, 'render_details_box' ),
			'team_member',
			'normal',
			'high'
		);
	}

	// ── Render ────────────────────────────────────────────────────────────────

	public static function render_details_box( $post ) {
		wp_nonce_field( 'tm_save_meta', 'tm_nonce' );

		$position      = get_post_meta( $post->ID, '_tm_position', true );
		$email         = get_post_meta( $post->ID, '_tm_email', true );
		$linkedin      = get_post_meta( $post->ID, '_tm_linkedin', true );
		$twitter       = get_post_meta( $post->ID, '_tm_twitter', true );
		$custom_order  = get_post_meta( $post->ID, '_tm_order', true );
		?>
		<style>
			.tm-meta-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
			.tm-meta-grid .full { grid-column:1/-1; }
			.tm-field label { display:block; font-weight:600; margin-bottom:4px; }
			.tm-field input[type=text],
			.tm-field input[type=email],
			.tm-field input[type=url],
			.tm-field input[type=number] { width:100%; }
			.tm-field .description { color:#888; font-size:12px; margin-top:4px; }
		</style>
		<div class="tm-meta-grid">

			<div class="tm-field full">
				<label for="tm_position"><?php esc_html_e( 'Position / Job Title', 'team-members' ); ?> <span style="color:red">*</span></label>
				<input type="text" id="tm_position" name="tm_position"
				       value="<?php echo esc_attr( $position ); ?>"
				       placeholder="e.g. Senior Developer" />
				<p class="description"><?php esc_html_e( 'Displayed beneath the member\'s name.', 'team-members' ); ?></p>
			</div>

			<div class="tm-field">
				<label for="tm_email"><?php esc_html_e( 'Email Address', 'team-members' ); ?></label>
				<input type="email" id="tm_email" name="tm_email"
				       value="<?php echo esc_attr( $email ); ?>" />
			</div>

			<div class="tm-field">
				<label for="tm_order"><?php esc_html_e( 'Custom Display Order', 'team-members' ); ?></label>
				<input type="number" id="tm_order" name="tm_order" min="0"
				       value="<?php echo esc_attr( $custom_order !== '' ? $custom_order : 0 ); ?>" />
				<p class="description"><?php esc_html_e( 'Lower number = displayed first.', 'team-members' ); ?></p>
			</div>

			<div class="tm-field">
				<label for="tm_linkedin"><?php esc_html_e( 'LinkedIn URL', 'team-members' ); ?></label>
				<input type="url" id="tm_linkedin" name="tm_linkedin"
				       value="<?php echo esc_attr( $linkedin ); ?>"
				       placeholder="https://linkedin.com/in/..." />
			</div>

			<div class="tm-field">
				<label for="tm_twitter"><?php esc_html_e( 'Twitter / X URL', 'team-members' ); ?></label>
				<input type="url" id="tm_twitter" name="tm_twitter"
				       value="<?php echo esc_attr( $twitter ); ?>"
				       placeholder="https://twitter.com/..." />
			</div>

		</div>
		<?php
	}

	// ── Save ──────────────────────────────────────────────────────────────────

	public static function save( $post_id ) {
		// Verify nonce
		if ( ! isset( $_POST['tm_nonce'] ) || ! wp_verify_nonce( $_POST['tm_nonce'], 'tm_save_meta' ) ) {
			return;
		}
		// Bail on autosave / bulk edit
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
		if ( ! current_user_can( 'edit_post', $post_id ) ) return;

		$fields = array(
			'_tm_position' => 'tm_position',
			'_tm_email'    => 'tm_email',
			'_tm_linkedin' => 'tm_linkedin',
			'_tm_twitter'  => 'tm_twitter',
			'_tm_order'    => 'tm_order',
		);

		foreach ( $fields as $meta_key => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {
				$value = sanitize_text_field( $_POST[ $post_key ] );
				if ( $meta_key === '_tm_email' ) {
					$value = sanitize_email( $_POST[ $post_key ] );
				} elseif ( in_array( $meta_key, array( '_tm_linkedin', '_tm_twitter' ), true ) ) {
					$value = esc_url_raw( $_POST[ $post_key ] );
				} elseif ( $meta_key === '_tm_order' ) {
					$value = absint( $_POST[ $post_key ] );
				}
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	// ── Admin Columns ─────────────────────────────────────────────────────────

	public static function add_columns( $columns ) {
		$new = array();
		foreach ( $columns as $key => $val ) {
			$new[ $key ] = $val;
			if ( $key === 'title' ) {
				$new['tm_position'] = __( 'Position', 'team-members' );
				$new['tm_order']    = __( 'Order', 'team-members' );
			}
		}
		return $new;
	}

	public static function render_columns( $column, $post_id ) {
		switch ( $column ) {
			case 'tm_position':
				echo esc_html( get_post_meta( $post_id, '_tm_position', true ) );
				break;
			case 'tm_order':
				echo esc_html( get_post_meta( $post_id, '_tm_order', true ) );
				break;
		}
	}

	public static function sortable_columns( $columns ) {
		$columns['tm_order'] = 'tm_order';
		return $columns;
	}
}

TM_Meta_Boxes::init();
