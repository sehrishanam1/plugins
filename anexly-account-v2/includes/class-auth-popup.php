<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Handles Login / Register / Forgot-Password popup
 * and Google + Facebook OAuth flows.
 *
 * Shortcode: [wc_auth_popup]
 */
final class Anexly_Suite_Auth_Popup {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'wc_auth_popup',    [ $this, 'render_shortcode' ] );
        add_action( 'wp_enqueue_scripts',  [ $this, 'enqueue_assets' ] );
        add_action( 'admin_menu',          [ $this, 'admin_menu' ] );
        add_action( 'admin_init',          [ $this, 'register_settings' ] );

        // REST API callbacks for Google & Facebook OAuth
        add_action( 'rest_api_init', [ $this, 'register_rest_routes' ] );

        // Inject modal HTML into wp_footer for guests so the popup is
        // available on EVERY page, not just where [wc_auth_popup] is placed.
        add_action( 'wp_footer', [ $this, 'print_global_modal' ] );

        // AJAX handlers
        add_action( 'wp_ajax_nopriv_wcap_login',           [ $this, 'ajax_login' ] );
        add_action( 'wp_ajax_nopriv_wcap_register',        [ $this, 'ajax_register' ] );
        add_action( 'wp_ajax_nopriv_wcap_forgot_password', [ $this, 'ajax_forgot_password' ] );
        add_action( 'wp_ajax_wcap_login',                  [ $this, 'ajax_login' ] );
        add_action( 'wp_ajax_wcap_register',               [ $this, 'ajax_register' ] );
        add_action( 'wp_ajax_wcap_forgot_password',        [ $this, 'ajax_forgot_password' ] );

        // Reset password is handled natively by WordPress/WooCommerce.
        // No interception or URL overrides needed.
    }

    /* ================================================================
       REST API ROUTES — Google & Facebook OAuth Callbacks
    ================================================================ */
    public function register_rest_routes() {
        register_rest_route( 'wcap/v1', '/google/callback', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_google_callback' ],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( 'wcap/v1', '/facebook/callback', [
            'methods'             => 'GET',
            'callback'            => [ $this, 'handle_facebook_callback' ],
            'permission_callback' => '__return_true',
        ] );
    }

    /* ================================================================
       GOOGLE OAUTH CALLBACK
    ================================================================ */
    public function handle_google_callback( WP_REST_Request $request ) {
        $code  = sanitize_text_field( $request->get_param( 'code' ) );
        $state = sanitize_text_field( $request->get_param( 'state' ) );
        $error = $request->get_param( 'error' );

        $redirect_url = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/' );

        if ( ! empty( $state ) && ! wp_verify_nonce( $state, 'wcap_google_state' ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_invalid_state', home_url( '/' ) ) );
            exit;
        }

        if ( $error ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_cancelled', home_url( '/' ) ) );
            exit;
        }

        if ( empty( $code ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_no_code', home_url( '/' ) ) );
            exit;
        }

        $client_id     = get_option( 'wcap_google_client_id', '' );
        $client_secret = get_option( 'wcap_google_client_secret', '' );
        $callback_uri  = $this->get_callback_url( 'google' );

        $token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', [
            'body' => [
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $callback_uri,
                'grant_type'    => 'authorization_code',
            ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $token_response ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_token_failed', home_url( '/' ) ) );
            exit;
        }

        $token_body   = json_decode( wp_remote_retrieve_body( $token_response ), true );
        $access_token = $token_body['access_token'] ?? '';

        if ( empty( $access_token ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_no_token', home_url( '/' ) ) );
            exit;
        }

        $user_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', [
            'headers' => [ 'Authorization' => 'Bearer ' . $access_token ],
            'timeout' => 15,
        ] );

        if ( is_wp_error( $user_response ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_userinfo_failed', home_url( '/' ) ) );
            exit;
        }

        $google_user = json_decode( wp_remote_retrieve_body( $user_response ), true );
        $email       = sanitize_email( $google_user['email'] ?? '' );
        $name        = sanitize_text_field( $google_user['name'] ?? '' );
        $google_id   = sanitize_text_field( $google_user['id'] ?? '' );

        if ( empty( $email ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_no_email', home_url( '/' ) ) );
            exit;
        }

        $user_id = $this->login_or_register_social_user( $email, $name, 'google', $google_id );

        if ( is_wp_error( $user_id ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'google_register_failed', home_url( '/' ) ) );
            exit;
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        wp_redirect( $redirect_url );
        exit;
    }

    /* ================================================================
       FACEBOOK OAUTH CALLBACK
    ================================================================ */
    public function handle_facebook_callback( WP_REST_Request $request ) {
        $code  = sanitize_text_field( $request->get_param( 'code' ) );
        $error = $request->get_param( 'error' );

        $redirect_url = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/' );

        if ( $error ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_cancelled', home_url( '/' ) ) );
            exit;
        }

        if ( empty( $code ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_no_code', home_url( '/' ) ) );
            exit;
        }

        $app_id       = get_option( 'wcap_facebook_app_id', '' );
        $app_secret   = get_option( 'wcap_facebook_app_secret', '' );
        $callback_uri = $this->get_callback_url( 'facebook' );

        $token_url = add_query_arg( [
            'client_id'     => $app_id,
            'client_secret' => $app_secret,
            'redirect_uri'  => $callback_uri,
            'code'          => $code,
        ], 'https://graph.facebook.com/v19.0/oauth/access_token' );

        $token_response = wp_remote_get( $token_url, [ 'timeout' => 15 ] );

        if ( is_wp_error( $token_response ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_token_failed', home_url( '/' ) ) );
            exit;
        }

        $token_body   = json_decode( wp_remote_retrieve_body( $token_response ), true );
        $access_token = $token_body['access_token'] ?? '';

        if ( empty( $access_token ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_no_token', home_url( '/' ) ) );
            exit;
        }

        $user_url = add_query_arg( [
            'fields'       => 'id,name,email',
            'access_token' => $access_token,
        ], 'https://graph.facebook.com/me' );

        $user_response = wp_remote_get( $user_url, [ 'timeout' => 15 ] );

        if ( is_wp_error( $user_response ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_userinfo_failed', home_url( '/' ) ) );
            exit;
        }

        $fb_user = json_decode( wp_remote_retrieve_body( $user_response ), true );
        $email   = sanitize_email( $fb_user['email'] ?? '' );
        $name    = sanitize_text_field( $fb_user['name'] ?? '' );
        $fb_id   = sanitize_text_field( $fb_user['id'] ?? '' );

        if ( empty( $email ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_no_email', home_url( '/' ) ) );
            exit;
        }

        $user_id = $this->login_or_register_social_user( $email, $name, 'facebook', $fb_id );

        if ( is_wp_error( $user_id ) ) {
            wp_redirect( add_query_arg( 'wcap_error', 'facebook_register_failed', home_url( '/' ) ) );
            exit;
        }

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        wp_redirect( $redirect_url );
        exit;
    }

    /* ================================================================
       SOCIAL LOGIN / REGISTER HELPER
    ================================================================ */
    private function login_or_register_social_user( $email, $name, $provider, $provider_id ) {
        $existing = get_user_by( 'email', $email );

        if ( $existing ) {
            update_user_meta( $existing->ID, "wcap_{$provider}_id", $provider_id );
            return $existing->ID;
        }

        $username = sanitize_user( strstr( $email, '@', true ) . rand( 100, 999 ) );
        while ( username_exists( $username ) ) {
            $username .= rand( 1, 9 );
        }

        $random_password = wp_generate_password( 16, true );
        $user_id         = wp_create_user( $username, $random_password, $email );

        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        wp_update_user( [ 'ID' => $user_id, 'display_name' => $name ] );
        update_user_meta( $user_id, "wcap_{$provider}_id", $provider_id );
        update_user_meta( $user_id, 'wcap_social_provider', $provider );

        if ( function_exists( 'WC' ) ) {
            update_user_meta( $user_id, 'billing_email', $email );
            $parts = explode( ' ', $name, 2 );
            update_user_meta( $user_id, 'billing_first_name', $parts[0] );
            update_user_meta( $user_id, 'billing_last_name',  $parts[1] ?? '' );
        }

        wp_mail( $email,
            'Welcome to ' . get_bloginfo( 'name' ),
            "Hi {$name},\n\nYour account has been created via {$provider} login.\n\nYou can now login anytime using your {$provider} account.\n\n" . get_bloginfo( 'name' )
        );

        return $user_id;
    }

    /* ================================================================
       SOCIAL LOGIN URL BUILDERS
    ================================================================ */
    public function get_google_url() {
        $redirect = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/' );

        if ( class_exists( 'NextendSocialLogin' ) || class_exists( 'NextendSocialLoginPro' ) ) {
            return add_query_arg( [
                'loginSocial' => 'google',
                'redirect'    => urlencode( $redirect ),
            ], site_url( '/' ) );
        }

        if ( function_exists( 'wc_social_login' ) ) {
            $providers = wc_social_login()->get_providers();
            if ( isset( $providers['google'] ) ) {
                return $providers['google']->get_login_url();
            }
        }

        $client_id = get_option( 'wcap_google_client_id', '' );
        if ( ! empty( $client_id ) ) {
            $params = [
                'client_id'     => $client_id,
                'redirect_uri'  => $this->get_callback_url( 'google' ),
                'response_type' => 'code',
                'scope'         => 'openid email profile',
                'state'         => wp_create_nonce( 'wcap_google_state' ),
                'access_type'   => 'online',
                'prompt'        => 'select_account',
            ];
            return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
        }

        return '#wcap-google-not-configured';
    }

    public function get_facebook_url() {
        $redirect = function_exists( 'wc_get_page_permalink' )
            ? wc_get_page_permalink( 'myaccount' )
            : home_url( '/' );

        if ( class_exists( 'NextendSocialLogin' ) || class_exists( 'NextendSocialLoginPro' ) ) {
            return add_query_arg( [
                'loginSocial' => 'facebook',
                'redirect'    => urlencode( $redirect ),
            ], site_url( '/' ) );
        }

        if ( function_exists( 'wc_social_login' ) ) {
            $providers = wc_social_login()->get_providers();
            if ( isset( $providers['facebook'] ) ) {
                return $providers['facebook']->get_login_url();
            }
        }

        $app_id = get_option( 'wcap_facebook_app_id', '' );
        if ( ! empty( $app_id ) ) {
            $params = [
                'client_id'     => $app_id,
                'redirect_uri'  => $this->get_callback_url( 'facebook' ),
                'response_type' => 'code',
                'scope'         => 'public_profile,email',
                'state'         => wp_create_nonce( 'wcap_facebook_state' ),
            ];
            return 'https://www.facebook.com/v19.0/dialog/oauth?' . http_build_query( $params );
        }

        return '#wcap-facebook-not-configured';
    }

    public function get_social_status() {
        if ( class_exists( 'NextendSocialLogin' ) || class_exists( 'NextendSocialLoginPro' ) ) {
            return [ 'plugin' => 'nextend', 'label' => 'Nextend Social Login' ];
        }
        if ( function_exists( 'wc_social_login' ) ) {
            return [ 'plugin' => 'wc_social_login', 'label' => 'WooCommerce Social Login' ];
        }
        $gid = get_option( 'wcap_google_client_id', '' );
        $fid = get_option( 'wcap_facebook_app_id', '' );
        if ( $gid || $fid ) {
            return [ 'plugin' => 'manual', 'label' => 'Manual OAuth' ];
        }
        return [ 'plugin' => 'none', 'label' => 'Not configured' ];
    }

    /* ================================================================
       ASSETS
    ================================================================ */
    public function enqueue_assets() {
        // Auth Popup styles & script
        wp_enqueue_style( 'ansx-popup-style', ANXA_URL . 'css/wcap-style.css', [], ANXA_VERSION );
        wp_enqueue_script( 'ansx-popup-script', ANXA_URL . 'js/wcap-script.js', [ 'jquery' ], ANXA_VERSION, true );

        wp_localize_script( 'ansx-popup-script', 'wcap_ajax', [
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'nonce'         => wp_create_nonce( 'wcap_nonce' ),
            'redirect'      => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ),
            'logged_in'     => is_user_logged_in() ? '1' : '0',
            'google_url'    => $this->get_google_url(),
            'facebook_url'  => $this->get_facebook_url(),
            'social_plugin' => $this->get_social_status()['plugin'],
        ] );

        // My Account styles & script
        wp_enqueue_style( 'ansx-account-style', ANXA_URL . 'css/anx-style.css', [], ANXA_VERSION );
        wp_enqueue_script( 'ansx-account-script', ANXA_URL . 'js/anx-script.js', [ 'jquery' ], ANXA_VERSION, true );

        wp_localize_script( 'ansx-account-script', 'AnexlyMA', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'anexly_ma_nonce' ),
        ] );
    }

    /* ================================================================
       GLOBAL MODAL — printed in wp_footer on every front-end page
       Allows the popup to be triggered from any element (e.g. the
       wishlist / my-account icon-button) even without the shortcode.
       Skipped for logged-in users and if modal was already printed
       by [wc_auth_popup] or [anexly_my_account] shortcodes.
    ================================================================ */
    public function print_global_modal() {
        if ( is_user_logged_in() ) return;

        // Don't double-print if a shortcode already rendered the modal
        if ( defined( 'ANXA_MODAL_PRINTED' ) ) return;
        define( 'ANXA_MODAL_PRINTED', true );

        $atts = [
            'trigger_text'  => '',
            'trigger_class' => 'wcap-global-hidden-trigger',
        ];
        // Trigger button is hidden; modal is present in DOM for JS to open
        echo '<style>.wcap-global-hidden-trigger{display:none!important}</style>';
        include ANXA_DIR . 'includes/modal.php';
    }

    /* ================================================================
       AUTH POPUP SHORTCODE
    ================================================================ */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts( [
            'trigger_text'  => 'Login / Register',
            'trigger_class' => '',
        ], $atts, 'wc_auth_popup' );

        if ( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $acc  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' );
            return '<div class="wcap-logged-in">Hello, <strong>' . esc_html( $user->display_name ) . '</strong>! '
                . '<a href="' . esc_url( $acc ) . '">My Account</a> | '
                . '<a href="' . esc_url( wp_logout_url( home_url( '/' ) ) ) . '">Logout</a></div>';
        }

        ob_start();
        include ANXA_DIR . 'includes/modal.php';
        return ob_get_clean();
    }

    /* ================================================================
       ADMIN SETTINGS — unified settings page
    ================================================================ */
    public function admin_menu() {
        add_menu_page(
            'Anexly Account Settings',
            'Anexly Account',
            'manage_options',
            'anexly-account',
            [ $this, 'settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'anxa_settings', 'wcap_site_url',             [ 'sanitize_callback' => 'esc_url_raw' ] );
        register_setting( 'anxa_settings', 'wcap_google_client_id',     [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'anxa_settings', 'wcap_google_client_secret', [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'anxa_settings', 'wcap_facebook_app_id',      [ 'sanitize_callback' => 'sanitize_text_field' ] );
        register_setting( 'anxa_settings', 'wcap_facebook_app_secret',  [ 'sanitize_callback' => 'sanitize_text_field' ] );
    }

    private function get_base_url() {
        $custom = get_option( 'wcap_site_url', '' );
        return ! empty( $custom ) ? trailingslashit( rtrim( $custom, '/' ) ) : trailingslashit( home_url() );
    }

    private function get_callback_url( $provider ) {
        return $this->get_base_url() . 'wp-json/wcap/v1/' . $provider . '/callback';
    }

    public function settings_page() {
        $social = $this->get_social_status();
        ?>
        <div class="wrap">
            <h1>Anexly Account — Settings</h1>

            <?php if ( $social['plugin'] !== 'none' && $social['plugin'] !== 'manual' ) : ?>
                <div class="notice notice-success inline">
                    <p>✅ <strong><?php echo esc_html( $social['label'] ); ?></strong> detected — Google &amp; Facebook login URLs are auto-generated!</p>
                </div>
            <?php endif; ?>

            <h2>📋 Shortcodes</h2>
            <table class="form-table">
                <tr>
                    <th>Auth Popup</th>
                    <td><code>[wc_auth_popup]</code> — Login / Register / Forgot Password popup with social login</td>
                </tr>
                <tr>
                    <th>My Account Dashboard</th>
                    <td><code>[anexly_my_account]</code> — Full account dashboard with Orders &amp; Settings tabs</td>
                </tr>
            </table>

            <h2>📋 OAuth Callback URLs</h2>
            <table class="form-table">
                <tr>
                    <th>Google Redirect URI</th>
                    <td><code><?php echo esc_html( $this->get_callback_url( 'google' ) ); ?></code></td>
                </tr>
                <tr>
                    <th>Facebook Redirect URI</th>
                    <td><code><?php echo esc_html( $this->get_callback_url( 'facebook' ) ); ?></code></td>
                </tr>
            </table>

            <form method="post" action="options.php">
                <?php settings_fields( 'anxa_settings' ); ?>

                <h2>🌐 Site URL (Live Link / Production)</h2>
                <table class="form-table">
                    <tr>
                        <th>Custom Site URL</th>
                        <td>
                            <input type="url" name="wcap_site_url" value="<?php echo esc_attr( get_option( 'wcap_site_url' ) ); ?>" class="regular-text" placeholder="https://yoursite.com" />
                            <p class="description">Set this to your live / staging URL only if it differs from WordPress home URL (e.g. LocalWP Live Link).</p>
                        </td>
                    </tr>
                </table>

                <h2>🔵 Google OAuth</h2>
                <table class="form-table">
                    <tr>
                        <th>Google Client ID</th>
                        <td><input type="text" name="wcap_google_client_id" value="<?php echo esc_attr( get_option( 'wcap_google_client_id' ) ); ?>" class="regular-text" placeholder="xxxx.apps.googleusercontent.com" /></td>
                    </tr>
                    <tr>
                        <th>Google Client Secret</th>
                        <td><input type="password" name="wcap_google_client_secret" value="<?php echo esc_attr( get_option( 'wcap_google_client_secret' ) ); ?>" class="regular-text" /></td>
                    </tr>
                </table>

                <h2>🔷 Facebook OAuth</h2>
                <table class="form-table">
                    <tr>
                        <th>Facebook App ID</th>
                        <td><input type="text" name="wcap_facebook_app_id" value="<?php echo esc_attr( get_option( 'wcap_facebook_app_id' ) ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th>Facebook App Secret</th>
                        <td><input type="password" name="wcap_facebook_app_secret" value="<?php echo esc_attr( get_option( 'wcap_facebook_app_secret' ) ); ?>" class="regular-text" /></td>
                    </tr>
                </table>

                <?php submit_button( 'Save Settings' ); ?>
            </form>
        </div>
        <?php
    }

    /* ================================================================
       AJAX — Login
    ================================================================ */
    public function ajax_login() {
        check_ajax_referer( 'wcap_nonce', 'nonce' );
        $email    = sanitize_email( $_POST['email'] ?? '' );
        $password = $_POST['password'] ?? '';
        if ( empty( $email ) || empty( $password ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in all fields.' ] );
        }
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            wp_send_json_error( [ 'message' => 'No account found with this email.' ] );
        }
        $result = wp_signon( [ 'user_login' => $user->user_login, 'user_password' => $password, 'remember' => true ], is_ssl() );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( [ 'message' => 'Incorrect password. Please try again.' ] );
        }
        wp_send_json_success( [
            'message'  => 'Login successful! Redirecting…',
            'redirect' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ),
        ] );
    }

    /* ================================================================
       AJAX — Register
    ================================================================ */
    public function ajax_register() {
        check_ajax_referer( 'wcap_nonce', 'nonce' );
        $full_name = sanitize_text_field( $_POST['full_name'] ?? '' );
        $email     = sanitize_email( $_POST['email'] ?? '' );
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $terms     = ! empty( $_POST['terms'] );

        if ( empty( $full_name ) || empty( $email ) || empty( $password ) || empty( $confirm ) ) {
            wp_send_json_error( [ 'message' => 'Please fill in all fields.' ] );
        }
        if ( ! is_email( $email ) )       wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
        if ( $password !== $confirm )      wp_send_json_error( [ 'message' => 'Passwords do not match.' ] );
        if ( strlen( $password ) < 8 )     wp_send_json_error( [ 'message' => 'Password must be at least 8 characters.' ] );
        if ( ! $terms )                    wp_send_json_error( [ 'message' => 'You must agree to the Terms & Conditions.' ] );
        if ( email_exists( $email ) )      wp_send_json_error( [ 'message' => 'An account with this email already exists.' ] );

        $username = sanitize_user( strstr( $email, '@', true ) . rand( 100, 999 ) );
        while ( username_exists( $username ) ) { $username .= rand( 1, 9 ); }

        $user_id = wp_create_user( $username, $password, $email );
        if ( is_wp_error( $user_id ) ) { wp_send_json_error( [ 'message' => $user_id->get_error_message() ] ); }

        wp_update_user( [ 'ID' => $user_id, 'display_name' => $full_name ] );
        if ( function_exists( 'WC' ) ) {
            update_user_meta( $user_id, 'billing_email', $email );
            $p = explode( ' ', $full_name, 2 );
            update_user_meta( $user_id, 'billing_first_name', $p[0] );
            update_user_meta( $user_id, 'billing_last_name',  $p[1] ?? '' );
        }
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id, true );
        wp_new_user_notification( $user_id, null, 'user' );

        wp_send_json_success( [
            'message'  => 'Account created! Redirecting…',
            'redirect' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/' ),
        ] );
    }

    /* ================================================================
       AJAX — Forgot Password
    ================================================================ */
    public function ajax_forgot_password() {
        check_ajax_referer( 'wcap_nonce', 'nonce' );

        $email = sanitize_email( $_POST['email'] ?? '' );
        if ( empty( $email ) || ! is_email( $email ) ) {
            wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
        }

        // Look up user by email
        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            wp_send_json_error( [ 'message' => 'DEBUG: No user found for email: ' . $email ] );
        }

        // Generate reset key
        $key = get_password_reset_key( $user );
        if ( is_wp_error( $key ) ) {
            wp_send_json_error( [ 'message' => 'DEBUG: Key error: ' . $key->get_error_message() ] );
        }

        // Build the native WP reset URL
        $reset_url = network_site_url( 'wp-login.php?action=rp&key=' . rawurlencode( $key ) . '&login=' . rawurlencode( $user->user_login ), 'login' );

        // Send email manually so we can confirm it fires
        $subject = 'Reset your password - ' . get_bloginfo( 'name' );
        $message = "Hi " . $user->display_name . ",

Click the link below to reset your password:

" . $reset_url . "

If you did not request this, ignore this email.

" . get_bloginfo( 'name' );
        $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];

        $sent = wp_mail( $user->user_email, $subject, $message, $headers );

        if ( $sent ) {
            wp_send_json_success( [ 'message' => 'Password reset link sent! Check your email.' ] );
        } else {
            wp_send_json_error( [ 'message' => 'DEBUG: wp_mail() returned false — email not sent. Check your SMTP/mail config.' ] );
        }
    }

}