== Anexly Account ==
Version:     1.0.0
Requires WP: 5.8+
Requires PHP: 7.4+
Requires WooCommerce: 6.0+

== Description ==

Anexly Account merges two plugins into one:

1. WC Auth Popup  — Beautiful Login / Register / Forgot Password popup
   with real Google & Facebook OAuth. Supports Nextend Social Login,
   WooCommerce Social Login, and manual OAuth credentials.

2. Anexly My Account — Custom WooCommerce My Account dashboard with
   Dashboard, Orders (with expandable details + credentials), and
   Account Settings tabs.

== Shortcodes ==

[wc_auth_popup]
  Place anywhere to render a Login/Register trigger button.
  Options:
    trigger_text  — Button label (default: "Login / Register")
    trigger_class — Extra CSS class on the button

[anexly_my_account]
  Place on your My Account page to render the full dashboard.
  When the user is not logged in, the Anexly popup is shown instead
  of the plain WooCommerce login form.

== Admin Settings ==

Settings → Anexly Account

  • Custom Site URL — only needed for LocalWP Live Link / staging
  • Google Client ID / Secret
  • Facebook App ID / Secret

OAuth Callback URLs (register these in Google / Facebook console):
  Google:   https://yoursite.com/wp-json/wcap/v1/google/callback
  Facebook: https://yoursite.com/wp-json/wcap/v1/facebook/callback

== File Structure ==

  anexly-account/
  ├── anexly-account.php                  ← Main plugin bootstrap
  ├── includes/
  │   ├── class-auth-popup.php          ← Login/Register popup + OAuth
  │   ├── modal.php                     ← Popup HTML template
  │   ├── class-my-account-shortcode.php ← [anexly_my_account] shortcode
  │   ├── class-dashboard.php           ← Dashboard tab
  │   ├── class-orders.php              ← Orders tab
  │   └── class-account-settings.php   ← Account Settings tab
  ├── css/
  │   ├── wcap-style.css                ← Auth popup styles
  │   └── anx-style.css                 ← My Account styles
  └── js/
      ├── wcap-script.js                ← Auth popup logic
      └── anx-script.js                 ← My Account logic

== Changelog ==

1.0.0 — Initial merged release
  - Combined WC Auth Popup 1.4.0 and Anexly My Account 1.0.0
  - Unified admin settings page under Settings → Anexly Account
  - [anexly_my_account] now shows the branded auth popup instead of
    the plain WooCommerce login form when user is not logged in
  - Single enqueue call for all 4 assets (2 CSS + 2 JS)
  - Renamed internal class to Anexly_Suite_Auth_Popup to avoid conflicts
