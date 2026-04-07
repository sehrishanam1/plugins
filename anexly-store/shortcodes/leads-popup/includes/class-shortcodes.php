<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Leads_Shortcodes {

    public function __construct() {
        add_shortcode( 'anexly_newsletter', [ $this, 'newsletter_form' ] );
        add_shortcode( 'anexly_popup',      [ $this, 'popup_trigger' ] );
        add_action( 'wp_enqueue_scripts',   [ $this, 'enqueue_assets' ] );
        // Popup is injected into footer automatically when shortcode is present OR always for logged-out users
        add_action( 'wp_footer',            [ $this, 'maybe_inject_popup' ] );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'anexly-leads',
            ALEADS_URL . 'assets/frontend.css',
            [],
            ALEADS_VERSION
        );
        wp_enqueue_script(
            'anexly-leads',
            ALEADS_URL . 'assets/frontend.js',
            [ 'jquery' ],
            ALEADS_VERSION,
            true
        );
        wp_localize_script( 'anexly-leads', 'AnexlyLeads', [
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonce'     => wp_create_nonce( 'anexly_subscribe_nonce' ),
            'isLoggedIn'=> is_user_logged_in() ? '1' : '0',
            'i18n'      => [
                'sending'   => __( 'Sending...', 'anexly-leads' ),
                'subscribe' => __( 'Sign Up', 'anexly-leads' ),
                'discount'  => __( 'Get My Discount', 'anexly-leads' ),
            ],
        ]);
    }

    // -------------------------------------------------------------------------
    // [anexly_newsletter] shortcode
    // -------------------------------------------------------------------------
    public function newsletter_form( $atts ): string {
        $atts = shortcode_atts( [
            'button_text' => __( 'Sign Up', 'anexly-leads' ),
            'placeholder' => __( 'Email address', 'anexly-leads' ),
            'show_consent' => 'yes',
        ], $atts, 'anexly_newsletter' );

        ob_start();
        ?>
        <div class="anexly-newsletter-wrap">
            <form class="anexly-form" data-source="newsletter" novalidate>
                <div class="anexly-input-row">
                    <input
                        type="email"
                        name="email"
                        class="anexly-email-input"
                        placeholder="<?php echo esc_attr( $atts['placeholder'] ); ?>"
                        required
                        autocomplete="email"
                    />
                    <button type="submit" class="anexly-btn-primary">
                        <?php echo esc_html( $atts['button_text'] ); ?>
                    </button>
                </div>
                <?php if ( $atts['show_consent'] === 'yes' ) : ?>
                <label class="anexly-consent">
                    <input type="checkbox" name="consent" required />
                    <span><?php _e( "I'm okay with getting emails and having that activity tracked to improve my experience.", 'anexly-leads' ); ?></span>
                </label>
                <?php endif; ?>
                <div class="anexly-msg" aria-live="polite"></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // [anexly_popup] shortcode — just marks this page to show popup
    // (popup HTML is injected via wp_footer)
    // -------------------------------------------------------------------------
    public function popup_trigger( $atts ): string {
        // Mark that shortcode was used — popup will render in footer
        add_filter( 'anexly_force_popup', '__return_true' );
        return ''; // No visible output
    }

    // -------------------------------------------------------------------------
    // Inject popup HTML into footer
    // Show if: shortcode is present, OR user is not logged in (random trigger via JS)
    // -------------------------------------------------------------------------
    public function maybe_inject_popup() {
        // Always inject for logged-out users (JS will decide timing)
        // Or if shortcode explicitly requested it
        if ( is_user_logged_in() && ! apply_filters( 'anexly_force_popup', false ) ) {
            return;
        }
        ?>
        <div id="anexly-popup-overlay" class="anexly-popup-overlay" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="anexly-popup-title">
            <div class="anexly-popup-box">
                <button class="anexly-popup-close" aria-label="<?php esc_attr_e( 'Close', 'anexly-leads' ); ?>">&#x2715;</button>

                <div class="anexly-popup-icon">
                    <!-- Shopping bag icon — matches screenshot exactly -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#e05240" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                    </svg>
                </div>

                <h2 id="anexly-popup-title"><?php _e( "Wait! Don't Leave Empty Handed", 'anexly-leads' ); ?></h2>
                <p class="anexly-popup-sub"><?php _e( 'Get 10% off your first order', 'anexly-leads' ); ?></p>

                <form class="anexly-form" data-source="popup" novalidate>
                    <div class="anexly-popup-input-wrap">
                        <svg class="anexly-mail-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M2 4l10 9 10-9"/></svg>
                        <input
                            type="email"
                            name="email"
                            class="anexly-email-input"
                            placeholder="<?php esc_attr_e( 'Enter your email address', 'anexly-leads' ); ?>"
                            required
                            autocomplete="email"
                        />
                    </div>
                    <button type="submit" class="anexly-btn-discount">
                        <?php _e( 'Get My Discount', 'anexly-leads' ); ?>
                    </button>
                    <div class="anexly-msg" aria-live="polite"></div>
                    <p class="anexly-unsub"><?php _e( 'Unsubscribe anytime', 'anexly-leads' ); ?></p>
                </form>
            </div>
        </div>
        <?php
    }
}

new Anexly_Leads_Shortcodes();