<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$wcap         = Anexly_Suite_Auth_Popup::instance();
$google_url   = $wcap->get_google_url();
$facebook_url = $wcap->get_facebook_url();
$google_ready   = ( $google_url   !== '#wcap-google-not-configured' );
$facebook_ready = ( $facebook_url !== '#wcap-facebook-not-configured' );

$google_svg = '<svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>';

$facebook_svg = '<svg viewBox="0 0 24 24" width="18" height="18" style="flex-shrink:0" fill="#1877F2"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.413c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z"/></svg>';
?>

<!-- ===== Trigger Button ===== -->
<button class="wcap-trigger-btn <?php echo esc_attr( $atts['trigger_class'] ); ?>" id="wcap-open-btn" aria-label="Open Login Popup">
    <?php echo esc_html( $atts['trigger_text'] ); ?>
</button>

<!-- ===== Overlay ===== -->
<div class="wcap-overlay" id="wcap-overlay" role="dialog" aria-modal="true" aria-label="Authentication">
    <div class="wcap-modal" id="wcap-modal">

        <button class="wcap-close" id="wcap-close" aria-label="Close">&times;</button>

        <!-- TABS -->
        <div class="wcap-tabs" role="tablist">
            <button class="wcap-tab active" data-tab="login" role="tab" aria-selected="true">Log In</button>
            <button class="wcap-tab" data-tab="signup" role="tab" aria-selected="false">Sign Up</button>
        </div>

        <!-- ============================================================
             LOGIN PANEL
        ============================================================ -->
        <div class="wcap-panel" id="wcap-panel-login">
            <form id="wcap-login-form" novalidate>

                <div class="wcap-field-wrap">
                    <input type="email" id="wcap-login-email" name="email" placeholder="Email" autocomplete="email" required />
                    <span class="wcap-field-msg" id="wcap-login-email-msg"></span>
                </div>

                <div class="wcap-field-wrap wcap-password-wrap">
                    <input type="password" id="wcap-login-password" name="password" placeholder="Password" autocomplete="current-password" required />
                    <button type="button" class="wcap-toggle-pw" aria-label="Toggle password">
                        <svg class="wcap-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="wcap-eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                    <span class="wcap-field-msg" id="wcap-login-password-msg"></span>
                </div>

                <div class="wcap-forgot-wrap">
                    <button type="button" class="wcap-link-btn" id="wcap-forgot-link">Forgot Password?</button>
                </div>

                <div class="wcap-server-msg" id="wcap-login-server-msg"></div>

                <button type="submit" class="wcap-submit-btn" id="wcap-login-submit">
                    <span class="wcap-btn-text">Login</span>
                    <span class="wcap-spinner" style="display:none"></span>
                </button>

                <div class="wcap-divider"><span>OR</span></div>

                <a href="<?php echo esc_attr( $google_url ); ?>"
                   class="wcap-social-btn wcap-google-btn<?php echo $google_ready ? '' : ' wcap-social-disabled'; ?>"
                   <?php echo ! $google_ready ? 'data-wcap-not-configured="1" title="Google login not configured"' : ''; ?>>
                    <?php echo $google_svg; ?>
                    Continue with Google
                </a>

                <a href="<?php echo esc_attr( $facebook_url ); ?>"
                   class="wcap-social-btn wcap-facebook-btn<?php echo $facebook_ready ? '' : ' wcap-social-disabled'; ?>"
                   <?php echo ! $facebook_ready ? 'data-wcap-not-configured="1" title="Facebook login not configured"' : ''; ?>>
                    <?php echo $facebook_svg; ?>
                    Continue with Facebook
                </a>


                <p class="wcap-switch-text">Don't have an account? <button type="button" class="wcap-link-btn wcap-switch" data-target="signup">Sign Up</button></p>
            </form>
        </div>

        <!-- ============================================================
             SIGN UP PANEL
        ============================================================ -->
        <div class="wcap-panel" id="wcap-panel-signup" style="display:none">
            <form id="wcap-signup-form" novalidate>

                <div class="wcap-field-wrap">
                    <input type="text" id="wcap-signup-name" name="full_name" placeholder="Full Name" autocomplete="name" required />
                    <span class="wcap-field-msg" id="wcap-signup-name-msg"></span>
                </div>

                <div class="wcap-field-wrap">
                    <input type="email" id="wcap-signup-email" name="email" placeholder="Email" autocomplete="email" required />
                    <span class="wcap-field-msg" id="wcap-signup-email-msg"></span>
                </div>

                <div class="wcap-field-wrap wcap-password-wrap">
                    <input type="password" id="wcap-signup-password" name="password" placeholder="Password" autocomplete="new-password" required />
                    <button type="button" class="wcap-toggle-pw" aria-label="Toggle password">
                        <svg class="wcap-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="wcap-eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                    <span class="wcap-field-msg" id="wcap-signup-password-msg"></span>
                </div>

                <div class="wcap-field-wrap wcap-password-wrap">
                    <input type="password" id="wcap-signup-confirm" name="confirm_password" placeholder="Confirm Password" autocomplete="new-password" required />
                    <button type="button" class="wcap-toggle-pw" aria-label="Toggle password">
                        <svg class="wcap-eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="wcap-eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                    <span class="wcap-field-msg" id="wcap-signup-confirm-msg"></span>
                </div>

                <div class="wcap-strength-wrap" id="wcap-strength-wrap" style="display:none">
                    <div class="wcap-strength-bar">
                        <div class="wcap-strength-fill" id="wcap-strength-fill"></div>
                    </div>
                    <span class="wcap-strength-label" id="wcap-strength-label"></span>
                </div>

                <div class="wcap-terms-wrap">
                    <label class="wcap-checkbox-label">
                        <input type="checkbox" id="wcap-terms" name="terms" />
                        <span class="wcap-custom-check"></span>
                        I agree to <a href="<?php echo esc_url( get_privacy_policy_url() ); ?>" target="_blank">Terms &amp; Conditions</a>
                    </label>
                    <span class="wcap-field-msg" id="wcap-terms-msg"></span>
                </div>

                <div class="wcap-server-msg" id="wcap-signup-server-msg"></div>

                <button type="submit" class="wcap-submit-btn" id="wcap-signup-submit">
                    <span class="wcap-btn-text">Create Account</span>
                    <span class="wcap-spinner" style="display:none"></span>
                </button>

                <div class="wcap-divider"><span>OR</span></div>

                <a href="<?php echo esc_attr( $google_url ); ?>"
                   class="wcap-social-btn wcap-google-btn<?php echo $google_ready ? '' : ' wcap-social-disabled'; ?>"
                   <?php echo ! $google_ready ? 'data-wcap-not-configured="1"' : ''; ?>>
                    <?php echo $google_svg; ?>
                    Continue with Google
                </a>

                <a href="<?php echo esc_attr( $facebook_url ); ?>"
                   class="wcap-social-btn wcap-facebook-btn<?php echo $facebook_ready ? '' : ' wcap-social-disabled'; ?>"
                   <?php echo ! $facebook_ready ? 'data-wcap-not-configured="1"' : ''; ?>>
                    <?php echo $facebook_svg; ?>
                    Continue with Facebook
                </a>

                <div class="wcap-guest-wrap">
                    <button type="button" id="wcap-guest-btn" class="wcap-close">Continue as Guest</button>
                </div>

                <p class="wcap-switch-text">Already have an account? <button type="button" class="wcap-link-btn wcap-switch" data-target="login">Log In</button></p>
            </form>
        </div>

        <!-- ============================================================
             FORGOT PASSWORD PANEL
        ============================================================ -->
        <div class="wcap-panel" id="wcap-panel-forgot" style="display:none">
            <div class="wcap-forgot-header">
                <button class="wcap-close" id="wcap-forgot-close" aria-label="Close">&times;</button>
                <h2 class="wcap-forgot-title">Reset your Password</h2>
                <p class="wcap-forgot-sub">Enter your Email and we'll send you a Reset Link</p>
            </div>
            <form id="wcap-forgot-form" novalidate>
                <div class="wcap-field-wrap">
                    <input type="email" id="wcap-forgot-email" name="email" placeholder="Email" autocomplete="email" required />
                    <span class="wcap-field-msg" id="wcap-forgot-email-msg"></span>
                </div>
                <div class="wcap-server-msg" id="wcap-forgot-server-msg"></div>
                <button type="submit" class="wcap-submit-btn" id="wcap-forgot-submit">
                    <span class="wcap-btn-text">Send Reset Link</span>
                    <span class="wcap-spinner" style="display:none"></span>
                </button>
                <div class="wcap-forgot-footer">
                    Don't have an account?
                    &nbsp;
                    <button type="button" class="wcap-link-btn wcap-switch" data-target="signup">Sign Up</button>
                </div>
            </form>
        </div>


    </div><!-- /.wcap-modal -->
</div><!-- /.wcap-overlay -->