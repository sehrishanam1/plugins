=== Reading Time Estimator & Progress Bar ===
Contributors: sehrishanam
Tags: reading time, progress bar, reading progress, read time, accessibility
Requires at least: 5.9
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A minimalist, accessible reading time badge + scroll progress bar. AI-assisted time adjustment, per-post overrides, zero tracking.

== Description ==

**Reading Time Estimator & Progress Bar** is a lightweight, developer-friendly plugin that adds two things readers love:

1. **A reading time badge** — shown before/after your post content.
2. **A scroll progress bar** — a sleek bar at the top (or bottom) of the screen that fills as the reader scrolls.

= ✨ What makes it different? =

**AI-assisted reading time adjustment**
Posts with lots of headings, bullet lists, and short paragraphs are scanned faster by readers. The plugin detects structure in your content and intelligently reduces the estimate — labelled "AI-adjusted" so readers know.

**Per-post overrides via meta box**
Override the calculated reading time for any post. Hide the badge or progress bar per-post, right from the editor.

**Accessibility-first design**
- Semantic `role="progressbar"` with live `aria-valuenow` updates
- Meaningful `aria-label` on every element
- Keyboard-focusable progress bar
- Fully respects `prefers-reduced-motion`
- Screen-reader-friendly badge with word count in ARIA label

**Minimal & performant**
- No jQuery dependency (vanilla JS)
- No external assets, fonts, or CDN calls
- No database writes beyond settings
- No user tracking whatsoever
- ~1.5 KB JS / ~1.2 KB CSS (gzipped)

**Developer-friendly**
- Template tags: `rte_the_reading_time()`, `rte_get_reading_time()`
- Filters: `rte_badge_html`, `rte_show_badge`, `rte_ai_adjustment_factor`
- CJK (Chinese/Japanese/Korean) content support
- Per-post type configuration

= 📐 Settings =
- Custom WPM (words per minute) — default 238
- Enable/disable AI-assisted adjustment
- Badge position: before content, after content, or both
- Choose which post types show the badge and/or progress bar
- Customise badge label text (use `{time}` placeholder)
- Pick progress bar color, height (1–10 px), position (top/bottom)
- Optional percentage tooltip on hover
- Respect `prefers-reduced-motion` toggle

= 🔧 Template Tags =

Use in your theme:

`<?php rte_the_reading_time(); // echoes "5 min read" ?>`

`<?php $data = rte_get_reading_time(); echo $data['minutes']; ?>`

= 🪝 Filters =

**Customise badge HTML:**
`add_filter( 'rte_badge_html', function( $html, $result ) { return $html; }, 10, 2 );`

**Disable badge conditionally:**
`add_filter( 'rte_show_badge', '__return_false' );`

**Tweak AI adjustment factor (0.75–1.0):**
`add_filter( 'rte_ai_adjustment_factor', function( $factor, $html ) { return 0.85; }, 10, 2 );`

== Installation ==

1. Upload the `reading-time-estimator` folder to `/wp-content/plugins/`
2. Activate the plugin through **Plugins** in WordPress admin
3. Go to **Settings → Reading Time** to configure

== Frequently Asked Questions ==

= Does this plugin use any external services? =
No. Everything runs locally on your server. No API calls, no tracking.

= Does it work with the Block Editor (Gutenberg)? =
Yes, fully compatible with both the Block Editor and Classic Editor.

= Can I use it on Pages, not just Posts? =
Yes. In Settings, check "Page" under "Show badge on" and "Show progress bar on".

= Does it support WooCommerce products or custom post types? =
Yes. Any public post type will appear in the settings checkboxes.

= What does "AI-assisted adjustment" mean? =
The plugin analyses your post's structure (heading density, list ratio, average paragraph length). If it detects highly structured, scannable content, it applies a small reduction factor (up to 25% faster). No external AI service is used — it's a smart local heuristic.

= How can I override the reading time for a specific post? =
Open the post in the editor. Find the **Reading Time** meta box in the sidebar. Enter a number (in minutes) to override the auto-calculated value, or leave it blank to use auto.

= Can I hide the badge/progress bar for one post only? =
Yes. Use the checkboxes in the **Reading Time** meta box.

== Screenshots ==

1. Front-end: Reading time badge before post content.
2. Front-end: Scroll progress bar at top of screen (75% read).
3. Post editor: Reading Time meta box with override and disable options.
4. Settings page: Full settings panel.

== Changelog ==

= 1.0.0 =
* Initial release.
* Reading time badge with AI-assisted adjustment.
* Scroll progress bar with ARIA support.
* Per-post meta box overrides.
* CJK content support.
* Template tags and developer filters.

== Upgrade Notice ==

= 1.0.0 =
Initial release. No upgrade steps needed.
