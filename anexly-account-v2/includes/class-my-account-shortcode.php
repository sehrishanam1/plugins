<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_MA_Shortcode {

    public function __construct() {
        add_shortcode( 'anexly_my_account', [ $this, 'render' ] );
        add_action( 'wp_ajax_anexly_logout', [ $this, 'handle_logout' ] );
    }

    public function render( $atts ) {

        /* ── Guest: just output the modal markup + auto-open trigger ── */
        if ( ! is_user_logged_in() ) {
            return $this->render_guest_view();
        }

        /* ── WooCommerce guard (only needed for logged-in dashboard) ── */
        if ( ! class_exists( 'WooCommerce' ) ) {
            return '<p><strong>Anexly My Account:</strong> WooCommerce is required.</p>';
        }

        $tab          = isset( $_GET['anx_tab'] ) ? sanitize_key( $_GET['anx_tab'] ) : 'dashboard';
        $allowed_tabs = [ 'dashboard', 'orders', 'account-settings' ];
        if ( ! in_array( $tab, $allowed_tabs, true ) ) {
            $tab = 'dashboard';
        }

        $page_url = get_permalink();

        ob_start(); ?>
        <div class="anx-wrapper anx-tab-<?php echo esc_attr( $tab ); ?>" id="anx-account">

            <!-- Mobile Header Bar -->
            <div class="anx-mobile-header" id="anx-mobile-header">
                <button class="anx-hamburger" id="anx-hamburger" aria-label="Open menu">
                    <?php
                    $tab_icons = [
                        'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none"><path d="M0 15.5V6.5C0 6.18333 0.0709998 5.88333 0.213 5.6C0.355 5.31667 0.550667 5.08333 0.8 4.9L6.8 0.4C7.15 0.133333 7.55 0 8 0C8.45 0 8.85 0.133333 9.2 0.4L15.2 4.9C15.45 5.08333 15.646 5.31667 15.788 5.6C15.93 5.88333 16.0007 6.18333 16 6.5V15.5C16 16.05 15.804 16.521 15.412 16.913C15.02 17.305 14.5493 17.5007 14 17.5H11C10.7167 17.5 10.4793 17.404 10.288 17.212C10.0967 17.02 10.0007 16.7827 10 16.5V11.5C10 11.2167 9.904 10.9793 9.712 10.788C9.52 10.5967 9.28267 10.5007 9 10.5H7C6.71667 10.5 6.47933 10.596 6.288 10.788C6.09667 10.98 6.00067 11.2173 6 11.5V16.5C6 16.7833 5.904 17.021 5.712 17.213C5.52 17.405 5.28267 17.5007 5 17.5H2C1.45 17.5 0.979333 17.3043 0.588 16.913C0.196666 16.5217 0.000666667 16.0507 0 15.5Z" fill="white"/></svg>',
                        'orders' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.4173 3.25C8.6973 2.66 9.3013 2.25 9.9993 2.25H13.9993C14.6973 2.25 15.3003 2.66 15.5813 3.25C16.2643 3.256 16.7973 3.287 17.2733 3.473C17.8415 3.69527 18.3357 4.07301 18.6993 4.563C19.0663 5.057 19.2393 5.69 19.4753 6.561L20.2173 9.283L20.4973 10.124L20.5213 10.154C21.4223 11.308 20.9933 13.024 20.1353 16.455C19.5893 18.638 19.3173 19.729 18.5033 20.365C17.6893 21 16.5643 21 14.3143 21H9.6843C7.4343 21 6.3093 21 5.4953 20.365C4.6813 19.729 4.4083 18.638 3.8633 16.455C3.0053 13.024 2.5763 11.308 3.4773 10.154L3.5013 10.124L3.7813 9.283L4.5233 6.561C4.7603 5.69 4.9333 5.056 5.2993 4.562C5.66303 4.07238 6.1572 3.695 6.7253 3.473C7.2013 3.287 7.7333 3.255 8.4173 3.25Z" fill="white"/></svg>',
                        'account-settings' => '<svg viewBox="0 0 24 24" width="18" height="18" fill="white"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.22-1.12.53-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.65 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.13.22.39.31.6.22l2.39-.96c.51.41 1.05.72 1.63.94l.36 2.54c.04.24.25.42.5.42h3.84c.25 0 .46-.18.5-.42l.36-2.54c.58-.22 1.12-.53 1.63-.94l2.39.96c.22.09.47 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"/></svg>',
                    ];
                    $tab_labels = [
                        'dashboard'        => 'Dashboard',
                        'orders'           => 'Orders',
                        'account-settings' => 'Account Settings',
                    ];
                    echo $tab_icons[ $tab ] ?? $tab_icons['dashboard'];
                    ?>
                    <span><?php echo esc_html( $tab_labels[ $tab ] ?? 'Dashboard' ); ?></span>
                    <svg class="anx-hamburger-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>
            </div>

            <!-- Overlay for drawer -->
            <div class="anx-overlay" id="anx-overlay"></div>

            <div class="anx-body">
                <aside class="anx-sidebar" id="anx-sidebar">
                    <nav class="anx-nav">
                        <a href="<?php echo esc_url( add_query_arg( 'anx_tab', 'dashboard', $page_url ) ); ?>"
                           class="anx-nav-item <?php echo $tab === 'dashboard' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="18" viewBox="0 0 16 18" fill="none">
                                <path d="M0 15.5V6.5C0 6.18333 0.0709998 5.88333 0.213 5.6C0.355 5.31667 0.550667 5.08333 0.8 4.9L6.8 0.4C7.15 0.133333 7.55 0 8 0C8.45 0 8.85 0.133333 9.2 0.4L15.2 4.9C15.45 5.08333 15.646 5.31667 15.788 5.6C15.93 5.88333 16.0007 6.18333 16 6.5V15.5C16 16.05 15.804 16.521 15.412 16.913C15.02 17.305 14.5493 17.5007 14 17.5H11C10.7167 17.5 10.4793 17.404 10.288 17.212C10.0967 17.02 10.0007 16.7827 10 16.5V11.5C10 11.2167 9.904 10.9793 9.712 10.788C9.52 10.5967 9.28267 10.5007 9 10.5H7C6.71667 10.5 6.47933 10.596 6.288 10.788C6.09667 10.98 6.00067 11.2173 6 11.5V16.5C6 16.7833 5.904 17.021 5.712 17.213C5.52 17.405 5.28267 17.5007 5 17.5H2C1.45 17.5 0.979333 17.3043 0.588 16.913C0.196666 16.5217 0.000666667 16.0507 0 15.5Z" fill="#111013"/>
                                </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="<?php echo esc_url( add_query_arg( 'anx_tab', 'orders', $page_url ) ); ?>"
                           class="anx-nav-item <?php echo $tab === 'orders' ? 'active' : ''; ?>">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
<path fill-rule="evenodd" clip-rule="evenodd" d="M8.4173 3.25C8.6973 2.66 9.3013 2.25 9.9993 2.25H13.9993C14.6973 2.25 15.3003 2.66 15.5813 3.25C16.2643 3.256 16.7973 3.287 17.2733 3.473C17.8415 3.69527 18.3357 4.07301 18.6993 4.563C19.0663 5.057 19.2393 5.69 19.4753 6.561L20.2173 9.283L20.4973 10.124L20.5213 10.154C21.4223 11.308 20.9933 13.024 20.1353 16.455C19.5893 18.638 19.3173 19.729 18.5033 20.365C17.6893 21 16.5643 21 14.3143 21H9.6843C7.4343 21 6.3093 21 5.4953 20.365C4.6813 19.729 4.4083 18.638 3.8633 16.455C3.0053 13.024 2.5763 11.308 3.4773 10.154L3.5013 10.124L3.7813 9.283L4.5233 6.561C4.7603 5.69 4.9333 5.056 5.2993 4.562C5.66303 4.07238 6.1572 3.695 6.7253 3.473C7.2013 3.287 7.7333 3.255 8.4173 3.25ZM8.4193 4.753C7.7573 4.76 7.4913 4.785 7.2713 4.871C6.96527 4.99068 6.69911 5.19411 6.5033 5.458C6.3273 5.695 6.2233 6.026 5.9333 7.093L5.3633 9.182C6.3833 9 7.7773 9 9.6833 9H14.3143C16.2213 9 17.6143 9 18.6343 9.18L18.0653 7.091C17.7753 6.024 17.6713 5.693 17.4953 5.456C17.2995 5.19211 17.0333 4.98868 16.7273 4.869C16.5073 4.783 16.2413 4.758 15.5793 4.751C15.4373 5.04985 15.2135 5.30232 14.9339 5.47914C14.6542 5.65596 14.3302 5.74987 13.9993 5.75H9.9993C9.66855 5.74996 9.34457 5.6562 9.06493 5.47956C8.78529 5.30293 8.56143 5.05166 8.4193 4.753Z" fill="#111013"/>
</svg>
                            <span>Orders</span>
                        </a>
                        <a href="<?php echo esc_url( add_query_arg( 'anx_tab', 'account-settings', $page_url ) ); ?>"
                           class="anx-nav-item <?php echo $tab === 'account-settings' ? 'active' : ''; ?>">
                            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.03 7.03 0 0 0-1.63-.94l-.36-2.54a.5.5 0 0 0-.5-.42h-3.84a.5.5 0 0 0-.5.42l-.36 2.54c-.58.22-1.12.53-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.65 8.84a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94l-2.03 1.58a.5.5 0 0 0-.12.64l1.92 3.32c.13.22.39.31.6.22l2.39-.96c.51.41 1.05.72 1.63.94l.36 2.54c.04.24.25.42.5.42h3.84c.25 0 .46-.18.5-.42l.36-2.54c.58-.22 1.12-.53 1.63-.94l2.39.96c.22.09.47 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8a3.5 3.5 0 0 1 0 7.5Z"/></svg>
                            <span>Account Settings</span>
                        </a>
                        <div class="anx-nav-divider"></div>
                        <a href="<?php echo esc_url( wp_logout_url( home_url() ) ); ?>" class="anx-nav-item anx-logout">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
<path d="M17 7L15.59 8.41L18.17 11H8V13H18.17L15.59 15.58L17 17L22 12M4 5H12V3H4C2.9 3 2 3.9 2 5V19C2 20.1 2.9 21 4 21H12V19H4V5Z" fill="#111013"/>
</svg>
                            <span>Log Out</span>
                        </a>
                    </nav>
                </aside>

                <main class="anx-content">
                    <?php
                    switch ( $tab ) {
                        case 'orders':
                            echo Anexly_MA_Orders::render();
                            break;
                        case 'account-settings':
                            echo Anexly_MA_Account_Settings::render();
                            break;
                        default:
                            echo Anexly_MA_Dashboard::render();
                    }
                    ?>
                </main>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Guest view: render the full auth popup modal markup
     * and immediately open it via an inline script.
     * No trigger button needed — popup opens automatically on page load.
     */
    private function render_guest_view() {
        $atts = [
            'trigger_text'  => '',
            'trigger_class' => 'wcap-hidden-auto-trigger',
        ];

        ob_start();
        // Suppress the trigger button via inline style on this instance
        ?>
        <style>#wcap-open-btn.wcap-hidden-auto-trigger { display:none !important; }</style>
        <?php
        if ( ! defined( 'ANXA_MODAL_PRINTED' ) ) { define( 'ANXA_MODAL_PRINTED', true ); }
        include ANXA_DIR . 'includes/modal.php';
        ?>
        <script>
        (function() {
            var params = new URLSearchParams(window.location.search);
            var wcapPanel = params.get('wcap_panel');  // e.g. "forgot"
            var wcapReset = params.get('wcap_reset');  // "1" for password reset
            var resetKey  = params.get('key')   || '';
            var resetLogin= params.get('login') || '';

            function openPopup() {
                var overlay = document.getElementById('wcap-overlay');
                if (!overlay) { setTimeout(openPopup, 100); return; }

                // Hide all panels first
                var panels = overlay.querySelectorAll('.wcap-panel');
                panels.forEach(function(p) { p.style.display = 'none'; });
                var tabs = overlay.querySelector('.wcap-tabs');

                if (wcapReset === '1' && resetKey && resetLogin) {
                    // Show reset password panel
                    var resetPanel = document.getElementById('wcap-panel-reset');
                    if (resetPanel) {
                        resetPanel.style.display = 'block';
                        if (tabs) tabs.style.display = 'none';
                        var keyField   = document.getElementById('wcap-reset-key');
                        var loginField = document.getElementById('wcap-reset-login');
                        if (keyField)   keyField.value   = decodeURIComponent(resetKey);
                        if (loginField) loginField.value = decodeURIComponent(resetLogin);
                    }
                } else if (wcapPanel === 'forgot') {
                    // Show forgot password panel
                    var forgotPanel = document.getElementById('wcap-panel-forgot');
                    if (forgotPanel) {
                        forgotPanel.style.display = 'block';
                        if (tabs) tabs.style.display = 'none';
                    }
                } else {
                    // Default: show login panel
                    var loginPanel = document.getElementById('wcap-panel-login');
                    if (loginPanel) loginPanel.style.display = 'block';
                    if (tabs) tabs.style.display = '';
                }

                overlay.classList.add('wcap-open');
                document.body.style.overflow = 'hidden';
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', openPopup);
            } else {
                openPopup();
            }
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function handle_logout() {
        if ( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'anexly_ma_nonce' ) ) {
            wp_logout();
            wp_send_json_success( [ 'redirect' => home_url() ] );
        }
        wp_send_json_error();
    }
}

new Anexly_MA_Shortcode();