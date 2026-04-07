<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_MA_Account_Settings {

    public static function render() {
        if ( ! is_user_logged_in() ) return '';

        $user    = wp_get_current_user();
        $message = '';
        $error   = '';

        if ( isset($_POST['anx_update_profile']) && wp_verify_nonce($_POST['anx_profile_nonce'], 'anx_update_profile') ) {
            $full_name = sanitize_text_field( $_POST['anx_full_name'] ?? '' );
            $email     = sanitize_email( $_POST['anx_email'] ?? '' );

            if ( $email && ! is_email($email) ) {
                $error = 'Please enter a valid email address.';
            } elseif ( $email && $email !== $user->user_email && email_exists($email) ) {
                $error = 'This email address is already in use.';
            } else {
                $parts = explode(' ', $full_name, 2);
                wp_update_user([
                    'ID'           => $user->ID,
                    'first_name'   => $parts[0] ?? '',
                    'last_name'    => $parts[1] ?? '',
                    'user_email'   => $email ?: $user->user_email,
                    'display_name' => $full_name ?: $user->display_name,
                ]);
                $user    = wp_get_current_user();
                $message = 'Profile updated successfully.';
            }
        }

        if ( isset($_POST['anx_update_password']) && wp_verify_nonce($_POST['anx_password_nonce'], 'anx_update_password') ) {
            $current  = $_POST['anx_current_password'] ?? '';
            $new_pass = $_POST['anx_new_password'] ?? '';
            $confirm  = $_POST['anx_confirm_password'] ?? '';

            if ( ! wp_check_password($current, $user->user_pass, $user->ID) ) {
                $error = 'Current password is incorrect.';
            } elseif ( strlen($new_pass) < 8 ) {
                $error = 'New password must be at least 8 characters.';
            } elseif ( $new_pass !== $confirm ) {
                $error = 'Passwords do not match.';
            } else {
                wp_set_password( $new_pass, $user->ID );
                $message = 'Password updated successfully. Please log in again.';
                wp_logout();
                wp_redirect( wc_get_page_permalink('myaccount') );
                exit;
            }
        }

        $display_name = trim( $user->first_name . ' ' . $user->last_name ) ?: $user->display_name;

        ob_start(); ?>
        <div class="anx-account-settings">
            <div class="anx-welcome">
                <h1>Account Settings</h1>
                <p>Manage your account information and security preferences.</p>
            </div>

            <?php if ( $message ) : ?>
                <div class="anx-notice anx-notice-success"><?php echo esc_html($message); ?></div>
            <?php endif; ?>
            <?php if ( $error ) : ?>
                <div class="anx-notice anx-notice-error"><?php echo esc_html($error); ?></div>
            <?php endif; ?>

            <div class="anx-settings-card">
                <h2 class="anx-settings-section-title">Profile Information</h2>
                <p class="anx-settings-section-desc">Update your personal details.</p>
                <form method="post" class="anx-form">
                    <?php wp_nonce_field('anx_update_profile', 'anx_profile_nonce'); ?>
                    <div class="anx-form-group">
                        <input type="text" name="anx_full_name" class="anx-input" placeholder="Full Name" value="<?php echo esc_attr($display_name); ?>">
                    </div>
                    <div class="anx-form-group">
                        <input type="email" name="anx_email" class="anx-input" placeholder="Email Address" value="<?php echo esc_attr($user->user_email); ?>">
                    </div>
                    <div class="anx-form-submit">
                        <button type="submit" name="anx_update_profile" class="anx-btn anx-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="anx-settings-card">
                <h2 class="anx-settings-section-title anx-settings-lock-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M6 8C6 6.4087 6.63214 4.88258 7.75736 3.75736C8.88258 2.63214 10.4087 2 12 2C13.5913 2 15.1174 2.63214 16.2426 3.75736C17.3679 4.88258 18 6.4087 18 8H19C19.5304 8 20.0391 8.21071 20.4142 8.58579C20.7893 8.96086 21 9.46957 21 10V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V10C3 9.46957 3.21071 8.96086 3.58579 8.58579C3.96086 8.21071 4.46957 8 5 8H6ZM12 4C13.0609 4 14.0783 4.42143 14.8284 5.17157C15.5786 5.92172 16 6.93913 16 8H8C8 6.93913 8.42143 5.92172 9.17157 5.17157C9.92172 4.42143 10.9391 4 12 4ZM14 14C14 14.3511 13.9076 14.6959 13.732 15C13.5565 15.304 13.304 15.5565 13 15.732V17C13 17.2652 12.8946 17.5196 12.7071 17.7071C12.5196 17.8946 12.2652 18 12 18C11.7348 18 11.4804 17.8946 11.2929 17.7071C11.1054 17.5196 11 17.2652 11 17V15.732C10.6187 15.5119 10.3208 15.1721 10.1523 14.7653C9.98384 14.3586 9.95429 13.9076 10.0682 13.4824C10.1822 13.0571 10.4333 12.6813 10.7825 12.4133C11.1318 12.1453 11.5597 12 12 12C12.5304 12 13.0391 12.2107 13.4142 12.5858C13.7893 12.9609 14 13.4696 14 14Z" fill="#111013"/>
                </svg>
                    <span>Change Password</span>
                </h2>
                <p class="anx-settings-section-desc">Update your password to keep your account secure.</p>
                <form method="post" class="anx-form">
                    <?php wp_nonce_field('anx_update_password', 'anx_password_nonce'); ?>
                    <div class="anx-form-group anx-password-field">
                        <input type="password" name="anx_current_password" id="anx_current_password_field" class="anx-input" placeholder="Current Password">
                        <button type="button" class="anx-eye-btn" data-target="anx_current_password_field">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z" stroke="currentColor" stroke-width="1.7"/><path d="m4 20 16-16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg>
                        </button>
                    </div>
                    <div class="anx-form-group anx-password-field">
                        <input type="password" name="anx_new_password" class="anx-input" placeholder="New Password" id="anx_new_password_field">
                        <button type="button" class="anx-eye-btn" data-target="anx_new_password_field">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z" stroke="currentColor" stroke-width="1.7"/><path d="m4 20 16-16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg>
                        </button>
                    </div>
                    <p class="anx-hint">Minimum 8 characters</p>
                    <div class="anx-password-strength-bar"><div class="anx-strength-fill"></div></div>
                    <div class="anx-form-group anx-password-field">
                        <input type="password" name="anx_confirm_password" id="anx_confirm_password_field" class="anx-input" placeholder="Confirm New Password">
                        <button type="button" class="anx-eye-btn" data-target="anx_confirm_password_field">
                            <svg viewBox="0 0 24 24" fill="none"><path d="M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z" stroke="currentColor" stroke-width="1.7"/><path d="m4 20 16-16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="12" cy="12" r="2.8" stroke="currentColor" stroke-width="1.7"/></svg>
                        </button>
                    </div>
                    <div class="anx-form-submit">
                        <button type="submit" name="anx_update_password" class="anx-btn anx-btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}