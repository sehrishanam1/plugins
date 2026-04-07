<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class ACP_Markup {

	public static function init() {
		add_action( 'wp_footer', [ __CLASS__, 'render_popup_shell' ] );
		// Note: We do NOT render a duplicate cart button here.
		// The plugin intercepts your theme's existing cart button (data-js="open-mini-cart").
	}

	public static function render_popup_shell() {
		?>
		<!-- Anexly Cart Popup -->
		<div id="acp-overlay"></div>
		<div id="acp-popup" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Shopping Cart', 'anexly-cart-popup' ); ?>">
			<div class="acp-drag-handle"></div>

			<div class="acp-header">
				<h2 class="acp-title">
					<?php esc_html_e( 'Your Cart', 'anexly-cart-popup' ); ?>
					<span class="acp-count-badge" id="acp-count-badge">0</span>
				</h2>
				<button class="acp-close" id="acp-close" aria-label="<?php esc_attr_e( 'Close cart', 'anexly-cart-popup' ); ?>">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none">
						<path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/>
					</svg>
				</button>
			</div>

			<div class="acp-scroll" id="acp-scroll">
				<div id="acp-items-wrap"></div>

				<div class="acp-discount-box" id="acp-discount-box">
					<div class="acp-discount-title" id="acp-discount-title"><?php esc_html_e( 'Discount Progress', 'anexly-cart-popup' ); ?></div>
					<div class="acp-progress-labels" id="acp-progress-labels"></div>
					<div class="acp-bar-wrap">
						<div class="acp-bar-fill" id="acp-bar-fill"></div>
					</div>
					<div class="acp-discount-status" id="acp-discount-status"></div>
				</div>

				<div class="acp-section-title"><?php esc_html_e( 'Recommended for You', 'anexly-cart-popup' ); ?></div>
				<div class="acp-rec-list" id="acp-rec-list"></div>

				<div class="acp-summary">
					<div class="acp-summary-row">
						<span class="acp-summary-label"><?php esc_html_e( 'Subtotal', 'anexly-cart-popup' ); ?></span>
						<span class="acp-summary-val" id="acp-subtotal">—</span>
					</div>
					<div class="acp-summary-row" id="acp-discount-row">
						<span class="acp-summary-label"><?php esc_html_e( 'Discount', 'anexly-cart-popup' ); ?></span>
						<span class="acp-summary-val acp-green" id="acp-discount">—</span>
					</div>
					<div class="acp-summary-total">
						<span class="acp-total-label"><?php esc_html_e( 'Final Total', 'anexly-cart-popup' ); ?></span>
						<span class="acp-total-val" id="acp-total">—</span>
					</div>
				</div>

				<div class="acp-footer-btns">
					<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="acp-btn-secondary"><?php esc_html_e( 'View Cart', 'anexly-cart-popup' ); ?></a>
					<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="acp-btn-primary"><?php esc_html_e( 'Checkout', 'anexly-cart-popup' ); ?></a>
				</div>
			</div>
		</div>
		<!-- /Anexly Cart Popup -->
		<?php
	}
}