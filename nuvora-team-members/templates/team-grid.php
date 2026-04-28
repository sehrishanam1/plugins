<?php
/**
 * Template: Team Members Grid
 *
 * Variables available:
 *   $members      – WP_Post[]
 *   $columns      – int (1-4)
 *   $show_desc    – bool  (default true)
 *   $show_social  – bool  (default true)
 *
 * @package TeamMembers
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Defaults when called from shortcode (variables not yet set).
if ( ! isset( $columns ) )     $columns     = 3;
if ( ! isset( $show_desc ) )   $show_desc   = true;
if ( ! isset( $show_social ) ) $show_social = true;
?>

<div class="tm-grid tm-grid--cols-<?php echo esc_attr( $columns ); ?>">

	<?php foreach ( $members as $member ) :
		$position    = get_post_meta( $member->ID, '_tm_position', true );
		$email       = get_post_meta( $member->ID, '_tm_email', true );
		$linkedin    = get_post_meta( $member->ID, '_tm_linkedin', true );
		$twitter     = get_post_meta( $member->ID, '_tm_twitter', true );
		$description = $member->post_excerpt ?: wp_trim_words( $member->post_content, 25, '…' );
		$image_url   = get_the_post_thumbnail_url( $member->ID, 'medium_large' );
		if ( ! $image_url ) {
			$image_url = TM_PLUGIN_URL . 'assets/img/placeholder.svg';
		}
	?>

	<div class="tm-card" data-id="<?php echo esc_attr( $member->ID ); ?>">

		<!-- ── Image + Overlay ────────────────────────────────── -->
		<div class="tm-card__image-wrap">
			<img
				src="<?php echo esc_url( $image_url ); ?>"
				alt="<?php echo esc_attr( $member->post_title ); ?>"
				class="tm-card__image"
				loading="lazy"
			/>
			<div class="tm-card__overlay" aria-hidden="true">
				<?php if ( $show_social && ( $linkedin || $twitter || $email ) ) : ?>
				<div class="tm-card__social">
					<?php if ( $linkedin ) : ?>
					<a href="<?php echo esc_url( $linkedin ); ?>" class="tm-social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'LinkedIn', 'team-members' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0H5C2.24 0 0 2.24 0 5v14c0 2.76 2.24 5 5 5h14c2.76 0 5-2.24 5-5V5c0-2.76-2.24-5-5-5zM8 19H5V8h3v11zM6.5 6.73c-.97 0-1.75-.79-1.75-1.76 0-.97.78-1.76 1.75-1.76s1.75.79 1.75 1.76c0 .97-.78 1.76-1.75 1.76zM20 19h-3v-5.6c0-1.34-.03-3.07-1.87-3.07-1.87 0-2.16 1.46-2.16 2.97V19h-3V8h2.89v1.5h.04c.4-.76 1.38-1.56 2.84-1.56 3.04 0 3.6 2 3.6 4.59V19z"/></svg>
					</a>
					<?php endif; ?>
					<?php if ( $twitter ) : ?>
					<a href="<?php echo esc_url( $twitter ); ?>" class="tm-social-link" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Twitter', 'team-members' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/></svg>
					</a>
					<?php endif; ?>
					<?php if ( $email ) : ?>
					<a href="mailto:<?php echo esc_attr( $email ); ?>" class="tm-social-link" aria-label="<?php esc_attr_e( 'Email', 'team-members' ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- ── Card Body ──────────────────────────────────────── -->
		<div class="tm-card__body">
			<h3 class="tm-card__name"><?php echo esc_html( $member->post_title ); ?></h3>

			<?php if ( $position ) : ?>
			<p class="tm-card__position"><?php echo esc_html( $position ); ?></p>
			<?php endif; ?>

			<?php if ( $show_desc && $description ) : ?>
			<div class="tm-card__excerpt">
				<p><?php echo esc_html( wp_trim_words( $description, 18, '…' ) ); ?></p>
			</div>

			<?php if ( strlen( $description ) > 90 ) : ?>
			<button class="tm-card__toggle" aria-expanded="false" data-member-id="<?php echo esc_attr( $member->ID ); ?>">
				<?php esc_html_e( 'Read more', 'team-members' ); ?>
			</button>
			<div class="tm-card__full-bio" id="tm-bio-<?php echo esc_attr( $member->ID ); ?>" hidden>
				<p><?php echo esc_html( $description ); ?></p>
			</div>
			<?php endif; ?>
			<?php endif; ?>
		</div>

	</div><!-- .tm-card -->

	<?php endforeach; ?>

</div><!-- .tm-grid -->
