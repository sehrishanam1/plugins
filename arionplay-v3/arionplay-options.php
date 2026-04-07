<?php
/**
 * Plugin Name: ArionPlay Theme Options
 * Description: Professional theme options panel for ArionPlay homepage management
 * Version: 3.0.0
 * Author: ArionPlay
 */

if (!defined('ABSPATH')) exit;

define('AP_VER',  '3.0.0');
define('AP_URL',  plugin_dir_url(__FILE__));
define('AP_OPT',  'arionplay_homepage');

register_activation_hook(__FILE__,   'flush_rewrite_rules');
register_deactivation_hook(__FILE__, 'flush_rewrite_rules');

// ── Menu ──────────────────────────────────────────────────────────────────────
add_action('admin_menu', function () {
    add_menu_page(
        'ArionPlay Options',
        'ArionPlay',
        'manage_options',
        'arionplay',
        'ap_page',
        'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><polygon points="3,2 17,10 3,18" fill="#f0b429"/></svg>'),
        3
    );
});

// ── Assets ────────────────────────────────────────────────────────────────────
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_arionplay') return;
    wp_enqueue_media();
    wp_enqueue_style('ap-options',  AP_URL . 'admin.css',  [], AP_VER);
    wp_enqueue_script('ap-options', AP_URL . 'admin.js',   ['jquery'], AP_VER, true);
    wp_localize_script('ap-options', 'AP', [
        'ajax'  => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ap_nonce'),
    ]);
});

// ── AJAX Save ─────────────────────────────────────────────────────────────────
add_action('wp_ajax_ap_save', function () {
    check_ajax_referer('ap_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();
    parse_str($_POST['payload'] ?? '', $parsed);
    $incoming = ap_deep_sanitize($parsed['ap'] ?? []);
    $existing = get_option(AP_OPT, []);
    // Merge section-by-section so untouched sections aren't wiped
    $section  = sanitize_key($_POST['section'] ?? '');
    if ($section) {
        $existing[$section] = $incoming[$section] ?? [];
    } else {
        $existing = array_merge($existing, $incoming);
    }
    update_option(AP_OPT, $existing);
    wp_send_json_success(['time' => current_time('H:i:s')]);
});

// ── AJAX Reset Section ────────────────────────────────────────────────────────
add_action('wp_ajax_ap_reset_section', function () {
    check_ajax_referer('ap_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();
    $section = sanitize_key($_POST['section'] ?? '');
    $all = get_option(AP_OPT, []);
    unset($all[$section]);
    update_option(AP_OPT, $all);
    wp_send_json_success();
});

// ── AJAX Reset All ────────────────────────────────────────────────────────────
add_action('wp_ajax_ap_reset_all', function () {
    check_ajax_referer('ap_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error();
    delete_option(AP_OPT);
    wp_send_json_success();
});

function ap_deep_sanitize($v) {
    return is_array($v) ? array_map('ap_deep_sanitize', $v) : wp_kses_post($v);
}

function ap($key, $default = '') {
    static $cache = null;
    if ($cache === null) $cache = get_option(AP_OPT, []);
    $parts = explode('.', $key);
    $val   = $cache;
    foreach ($parts as $p) {
        if (!isset($val[$p])) return $default;
        $val = $val[$p];
    }
    return $val;
}

// ── REST API ──────────────────────────────────────────────────────────────────
add_action('rest_api_init', function () {
    register_rest_route('arionplay/v1', '/homepage', [
        'methods'             => 'GET',
        'callback'            => fn() => rest_ensure_response(get_option(AP_OPT, [])),
        'permission_callback' => '__return_true',
    ]);
    foreach (['seo','hero','features','games','how_to','promotions','app','support','regulation','faq','news','footer'] as $s) {
        register_rest_route('arionplay/v1', '/homepage/' . $s, [
            'methods'             => 'GET',
            'callback'            => function () use ($s) {
                $all = get_option(AP_OPT, []);
                return rest_ensure_response($all[$s] ?? []);
            },
            'permission_callback' => '__return_true',
        ]);
    }
});

// ═════════════════════════════════════════════════════════════════════════════
// PAGE RENDER
// ═════════════════════════════════════════════════════════════════════════════
function ap_page() {
    $d = get_option(AP_OPT, []);

    $nav = [
        'seo'        => ['🔍', 'SEO & Meta'],
        'hero'       => ['🏠', 'Hero Banner'],
        'features'   => ['⭐', 'Features'],
        'games'      => ['🎮', 'Games'],
        'how_to'     => ['📋', 'How To Register'],
        'promotions' => ['🎁', 'Promotions'],
        'app'        => ['📱', 'App Download'],
        'support'    => ['🎧', 'Customer Service'],
        'regulation' => ['🛡️', 'Regulation'],
        'faq'        => ['❓', 'FAQ'],
        'news'       => ['📰', 'Latest News'],
        'footer'     => ['🔻', 'Footer'],
    ];
    ?>
    <div id="ap-root">

        <!-- ── Top Bar ── -->
        <div id="ap-topbar">
            <div class="ap-topbar-brand">
                <svg width="18" height="18" viewBox="0 0 20 20"><polygon points="3,2 17,10 3,18" fill="#f0b429"/></svg>
                <span>ArionPlay <em>Theme Options</em></span>
            </div>
            <div class="ap-topbar-actions">
                <span id="ap-toast"></span>
                <button class="ap-btn ap-btn-ghost" id="btn-reset-section">Reset Section</button>
                <button class="ap-btn ap-btn-ghost" id="btn-reset-all">Reset All</button>
                <button class="ap-btn ap-btn-primary" id="btn-save">Save Changes</button>
            </div>
        </div>

        <!-- ── Body ── -->
        <div id="ap-body">

            <!-- Sidebar -->
            <nav id="ap-sidebar">
                <?php foreach ($nav as $key => [$icon, $label]): ?>
                <a class="ap-nav-item" data-section="<?= $key ?>" href="#">
                    <span class="ap-nav-icon"><?= $icon ?></span>
                    <span class="ap-nav-label"><?= $label ?></span>
                </a>
                <?php endforeach; ?>

                <div class="ap-sidebar-footer">
                    <div class="ap-api-badge">REST API Active</div>
                    <code class="ap-api-url">/wp-json/arionplay/v1/homepage</code>
                </div>
            </nav>

            <!-- Content -->
            <main id="ap-content">
            <form id="ap-form">

                <!-- ══ SEO ══════════════════════════════════════════════════ -->
                <?php ap_section('seo', 'SEO & Meta Tags', 'Control how Google and social platforms display your homepage.'); ?>
                    <?php ap_fieldset('Page Identity'); ?>
                        <?php ap_text('Meta Title',       'ap[seo][title]',       $d['seo']['title']       ?? 'ArionPlay – Your Premier Online Casino Experience'); ?>
                        <?php ap_textarea('Meta Description', 'ap[seo][description]', $d['seo']['description'] ?? '', 'Recommended: 150–160 characters'); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Social Sharing'); ?>
                        <?php ap_image('OG Image (1200×630px)', 'ap[seo][og_image]', $d['seo']['og_image'] ?? ''); ?>
                        <?php ap_text('Canonical URL', 'ap[seo][canonical]', $d['seo']['canonical'] ?? home_url('/'), 'url'); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Advanced'); ?>
                        <?php ap_text('Robots',      'ap[seo][robots]',      $d['seo']['robots']      ?? 'index, follow'); ?>
                        <?php ap_text('Schema Type', 'ap[seo][schema_type]', $d['seo']['schema_type'] ?? 'WebPage', '', 'e.g. WebPage, EntertainmentBusiness'); ?>
                    <?php ap_fieldset_end(); ?>
                <?php ap_section_end(); ?>

                <!-- ══ HERO ═════════════════════════════════════════════════ -->
                <?php ap_section('hero', 'Hero Banner', 'The full-width banner section visitors see first.'); ?>
                    <?php ap_fieldset('Headline & Copy'); ?>
                        <?php ap_text('Main Headline',    'ap[hero][headline]',    $d['hero']['headline']    ?? 'Welcome to ARIONPLAY – Your Premier Online Casino Experience'); ?>
                        <?php ap_textarea('Subheadline',  'ap[hero][subheadline]', $d['hero']['subheadline'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Media'); ?>
                        <?php ap_image('Background Image (1920×800px)', 'ap[hero][bg_image]',   $d['hero']['bg_image']   ?? ''); ?>
                        <?php ap_image('Side Art / Character PNG',       'ap[hero][side_image]', $d['hero']['side_image'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Primary CTA Button'); ?>
                        <?php ap_text('Label', 'ap[hero][cta1_label]', $d['hero']['cta1_label'] ?? 'PLAY NOW AT ARIONPLAY'); ?>
                        <?php ap_text('URL',   'ap[hero][cta1_url]',   $d['hero']['cta1_url']   ?? '#', 'url'); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Secondary CTA Button'); ?>
                        <?php ap_text('Label', 'ap[hero][cta2_label]', $d['hero']['cta2_label'] ?? 'REGISTER AT ARIONPLAY'); ?>
                        <?php ap_text('URL',   'ap[hero][cta2_url]',   $d['hero']['cta2_url']   ?? '#', 'url'); ?>
                    <?php ap_fieldset_end(); ?>
                <?php ap_section_end(); ?>

                <!-- ══ FEATURES ═════════════════════════════════════════════ -->
                <?php ap_section('features', 'Features', '5 feature highlight cards shown below the hero.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title',    'ap[features][title]',    $d['features']['title']    ?? 'The Power Behind the ARIONPLAY Experience'); ?>
                        <?php ap_textarea('Subtitle', 'ap[features][subtitle]', $d['features']['subtitle'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $fd = [
                        ['icon'=>'🔒','title'=>'Secure and Fast Login',           'desc'=>'State-of-the-art security keeping your identity and funds protected at all times.'],
                        ['icon'=>'✅','title'=>'Hassle-Free Register Process',    'desc'=>'Quick and user-friendly — sign up in just a few steps.'],
                        ['icon'=>'🎰','title'=>'Access to Premium Games',         'desc'=>'Full range of casino games updated regularly with the best international titles.'],
                        ['icon'=>'📱','title'=>'Mobile-Friendly App & Downloads', 'desc'=>'Smooth experience on any device — play on the go anytime.'],
                        ['icon'=>'🎧','title'=>'24/7 Customer Service',           'desc'=>'Always available — no waiting, no hassle, always here for you.'],
                    ];
                    for ($i = 0; $i < 5; $i++):
                        $f = $d['features']['items'][$i] ?? $fd[$i];
                        ap_fieldset('Feature ' . ($i + 1));
                        ap_inline([
                            ['Icon / Emoji', "ap[features][items][{$i}][icon]",  $f['icon']  ?? '', 'text', '', '80px'],
                            ['Title',        "ap[features][items][{$i}][title]", $f['title'] ?? ''],
                        ]);
                        ap_textarea('Description', "ap[features][items][{$i}][desc]", $f['desc'] ?? '');
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ GAMES ════════════════════════════════════════════════ -->
                <?php ap_section('games', 'Games', 'The 4 main game category blocks.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title', 'ap[games][title]', $d['games']['title'] ?? 'Most Played Games at ARIONPLAY'); ?>
                        <?php ap_textarea('Intro', 'ap[games][intro]', $d['games']['intro'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $gd = [
                        ['badge'=>'SLOTS',   'title'=>'Slots Games',    'desc'=>'Wide range of slots with stunning graphics, engaging features, and exclusive jackpots.'],
                        ['badge'=>'FISHING', 'title'=>'Fishing Games',  'desc'=>'Blend skill and chance into one addictive fishing experience.'],
                        ['badge'=>'LIVE',    'title'=>'Live Casino',    'desc'=>'Real dealers, real tables — the full casino experience from home.'],
                        ['badge'=>'SPORTS',  'title'=>'Sports Betting', 'desc'=>'Competitive odds across football, basketball, esports and more.'],
                    ];
                    for ($i = 0; $i < 4; $i++):
                        $g = $d['games']['items'][$i] ?? $gd[$i];
                        ap_fieldset($gd[$i]['title']);
                        ap_inline([
                            ['Badge',  "ap[games][items][{$i}][badge]", $g['badge'] ?? '', 'text', '', '120px'],
                            ['Title',  "ap[games][items][{$i}][title]", $g['title'] ?? ''],
                            ['CTA URL',"ap[games][items][{$i}][url]",   $g['url']   ?? '#', 'url'],
                        ]);
                        ap_image('Thumbnail (600×300px)', "ap[games][items][{$i}][image]", $g['image'] ?? '');
                        ap_textarea('Description', "ap[games][items][{$i}][desc]", $g['desc'] ?? '');
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ HOW TO ════════════════════════════════════════════════ -->
                <?php ap_section('how_to', 'How To Register', '5-step registration guide shown on the homepage.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title', 'ap[how_to][title]', $d['how_to']['title'] ?? 'How to Join ARIONPLAY Online Casino'); ?>
                        <?php ap_textarea('Intro Text', 'ap[how_to][intro]', $d['how_to']['intro'] ?? ''); ?>
                        <?php ap_image('Side Decorative Image', 'ap[how_to][side_image]', $d['how_to']['side_image'] ?? ''); ?>
                        <?php ap_inline([
                            ['CTA Label', 'ap[how_to][cta_label]', $d['how_to']['cta_label'] ?? 'JOIN NOW AT ARIONPLAY'],
                            ['CTA URL',   'ap[how_to][cta_url]',   $d['how_to']['cta_url']   ?? '#', 'url'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $sd = [
                        ['title'=>'Step 1: Visit the Official Platform','body'=>'Go to the official website or download the app for instant mobile access.'],
                        ['title'=>'Step 2: Click "Register"','body'=>'Find the Register button and click it to begin signup.'],
                        ['title'=>'Step 3: Fill In Your Information','body'=>'Enter your email, create a strong password and complete personal details.'],
                        ['title'=>'Step 4: Verify Your Account','body'=>'Check your email for a confirmation link and activate your account.'],
                        ['title'=>'Step 5: Log In and Start Playing','body'=>'Log in and enjoy games, fishing, live casino, and sports betting!'],
                    ];
                    for ($i = 0; $i < 5; $i++):
                        $s = $d['how_to']['steps'][$i] ?? $sd[$i];
                        ap_fieldset('Step ' . ($i + 1));
                        ap_text('Title',       "ap[how_to][steps][{$i}][title]", $s['title'] ?? '');
                        ap_textarea('Details', "ap[how_to][steps][{$i}][body]",  $s['body']  ?? '');
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ PROMOTIONS ════════════════════════════════════════════ -->
                <?php ap_section('promotions', 'Promotions', 'Bonus cards and promotional banners.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title', 'ap[promotions][title]', $d['promotions']['title'] ?? "Promotions & Bonuses You'll Love"); ?>
                        <?php ap_textarea('Intro', 'ap[promotions][intro]', $d['promotions']['intro'] ?? ''); ?>
                        <?php ap_inline([
                            ['See All Label', 'ap[promotions][cta_label]', $d['promotions']['cta_label'] ?? 'SEE ALL PROMOTIONS'],
                            ['See All URL',   'ap[promotions][cta_url]',   $d['promotions']['cta_url']   ?? '#', 'url'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php for ($i = 0; $i < 5; $i++):
                        $p = $d['promotions']['items'][$i] ?? [];
                        ap_fieldset('Promotion ' . ($i + 1));
                        ap_inline([
                            ['Title',   "ap[promotions][items][{$i}][title]",  $p['title']  ?? ''],
                            ['Amount',  "ap[promotions][items][{$i}][amount]", $p['amount'] ?? '', 'text', 'e.g. ₱999,999'],
                            ['Badge',   "ap[promotions][items][{$i}][badge]",  $p['badge']  ?? ''],
                        ]);
                        ap_image('Banner Image (400×200px)', "ap[promotions][items][{$i}][image]", $p['image'] ?? '');
                        ap_inline([
                            ['CTA Label', "ap[promotions][items][{$i}][cta_label]", $p['cta_label'] ?? 'CLAIM NOW'],
                            ['CTA URL',   "ap[promotions][items][{$i}][cta_url]",   $p['cta_url']   ?? '#', 'url'],
                        ]);
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ APP ══════════════════════════════════════════════════ -->
                <?php ap_section('app', 'App Download', 'The mobile app download section.'); ?>
                    <?php ap_fieldset('Content'); ?>
                        <?php ap_text('Title',       'ap[app][title]',       $d['app']['title']       ?? 'Download the ARIONPLAY App Now'); ?>
                        <?php ap_textarea('Description', 'ap[app][description]', $d['app']['description'] ?? ''); ?>
                        <?php ap_image('Phone Mockup Image', 'ap[app][mockup]', $d['app']['mockup'] ?? ''); ?>
                        <?php ap_inline([
                            ['CTA Label', 'ap[app][cta_label]', $d['app']['cta_label'] ?? 'DOWNLOAD ARIONPLAY MOBILE APP'],
                            ['CTA URL',   'ap[app][cta_url]',   $d['app']['cta_url']   ?? '#', 'url'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('App Store Links'); ?>
                        <?php ap_inline([
                            ['Google Play URL', 'ap[app][google_play_url]', $d['app']['google_play_url'] ?? '#', 'url'],
                            ['Apple Store URL', 'ap[app][apple_store_url]', $d['app']['apple_store_url'] ?? '#', 'url'],
                        ]); ?>
                        <?php ap_inline([
                            ['Google Play Badge', 'ap[app][google_play_badge]', $d['app']['google_play_badge'] ?? '', 'image'],
                            ['Apple Store Badge',  'ap[app][apple_store_badge]', $d['app']['apple_store_badge'] ?? '', 'image'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                <?php ap_section_end(); ?>

                <!-- ══ SUPPORT ══════════════════════════════════════════════ -->
                <?php ap_section('support', 'Customer Service', '3 support columns shown in the customer service section.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title',     'ap[support][title]', $d['support']['title'] ?? 'ARIONPLAY Customer Service – Always Here for You'); ?>
                        <?php ap_textarea('Intro', 'ap[support][intro]', $d['support']['intro'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $cd = [
                        ['icon'=>'💬','title'=>'24/7 Live Chat Support',          'desc'=>'Agents available round the clock for any questions.'],
                        ['icon'=>'🤝','title'=>'Fast and Helpful Email Assistance','desc'=>'Send your inquiry and get a response within the day.'],
                        ['icon'=>'📚','title'=>'In-Depth Help Center',            'desc'=>'Guides and FAQs for troubleshooting and learning about our platform.'],
                    ];
                    for ($i = 0; $i < 3; $i++):
                        $c = $d['support']['columns'][$i] ?? $cd[$i];
                        ap_fieldset('Support Column ' . ($i + 1));
                        ap_inline([
                            ['Icon',  "ap[support][columns][{$i}][icon]",  $c['icon']  ?? '', 'text', '', '80px'],
                            ['Title', "ap[support][columns][{$i}][title]", $c['title'] ?? ''],
                        ]);
                        ap_textarea('Description', "ap[support][columns][{$i}][desc]", $c['desc'] ?? '');
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ REGULATION ════════════════════════════════════════════ -->
                <?php ap_section('regulation', 'Regulation & Licenses', 'Compliance text and license authority logos.'); ?>
                    <?php ap_fieldset('Content'); ?>
                        <?php ap_text('Section Title', 'ap[regulation][title]', $d['regulation']['title'] ?? 'ARIONPLAY Regulation & Licenses'); ?>
                        <?php ap_textarea('Regulation Text', 'ap[regulation][text]', $d['regulation']['text'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('License Logos'); ?>
                        <?php for ($i = 0; $i < 4; $i++):
                            $l = $d['regulation']['licenses'][$i] ?? [];
                            ap_inline([
                                ['License Name', "ap[regulation][licenses][{$i}][name]", $l['name'] ?? '', 'text', '', '160px'],
                                ['Logo Image',   "ap[regulation][licenses][{$i}][logo]", $l['logo'] ?? '', 'image'],
                            ]);
                        endfor; ?>
                    <?php ap_fieldset_end(); ?>
                <?php ap_section_end(); ?>

                <!-- ══ FAQ ══════════════════════════════════════════════════ -->
                <?php ap_section('faq', 'FAQ', 'Frequently asked questions accordion.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title', 'ap[faq][title]', $d['faq']['title'] ?? 'Frequently Asked Questions'); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $faqd = [
                        ['q'=>'How can I create an account?',                  'a'=>'Click SIGNUP, fill in your details, and your account will be ready in no time.'],
                        ['q'=>'How do I log in?',                              'a'=>'Enter your username and password on the login page. Use password recovery if needed.'],
                        ['q'=>'Can I play without downloading anything?',      'a'=>'Yes, but we recommend downloading the app for the best experience.'],
                        ['q'=>'What types of games are available?',            'a'=>'Slots, fishing games, live dealers, and sports betting — regularly updated.'],
                        ['q'=>'How can I contact ARIONPLAY customer service?', 'a'=>'Via live chat, email, or our Help Center — available 24/7.'],
                    ];
                    for ($i = 0; $i < 5; $i++):
                        $f = $d['faq']['items'][$i] ?? $faqd[$i];
                        ap_fieldset('FAQ Item ' . ($i + 1));
                        ap_text('Question', "ap[faq][items][{$i}][q]", $f['q'] ?? '');
                        ap_textarea('Answer', "ap[faq][items][{$i}][a]", $f['a'] ?? '');
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ NEWS ═════════════════════════════════════════════════ -->
                <?php ap_section('news', 'Latest News', '3 news and update cards.'); ?>
                    <?php ap_fieldset('Section Header'); ?>
                        <?php ap_text('Title', 'ap[news][title]', $d['news']['title'] ?? 'Latest News and Updates'); ?>
                        <?php ap_textarea('Intro', 'ap[news][intro]', $d['news']['intro'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php
                    $nd = [
                        ['title'=>'Unlock Daily Rewards at ARIONPLAY!',  'excerpt'=>'Log in every day to unlock exciting daily rewards and bonuses.'],
                        ['title'=>'Slam Dunk Fun with Basketball Games!', 'excerpt'=>'Exciting basketball-themed slot games have arrived at ARIONPLAY.'],
                        ['title'=>'Gaming On the Go with ARIONPLAY!',    'excerpt'=>'Download the mobile app and take your favorite games anywhere.'],
                    ];
                    for ($i = 0; $i < 3; $i++):
                        $n = $d['news']['items'][$i] ?? $nd[$i];
                        ap_fieldset('News Card ' . ($i + 1));
                        ap_image('Thumbnail (400×250px)', "ap[news][items][{$i}][image]", $n['image'] ?? '');
                        ap_inline([
                            ['Title', "ap[news][items][{$i}][title]",   $n['title']   ?? ''],
                            ['Date',  "ap[news][items][{$i}][date]",    $n['date']    ?? '', 'date'],
                        ]);
                        ap_inline([
                            ['Excerpt',       "ap[news][items][{$i}][excerpt]", $n['excerpt'] ?? '', 'textarea'],
                            ['Read More URL', "ap[news][items][{$i}][url]",     $n['url']     ?? '#', 'url'],
                        ]);
                        ap_fieldset_end();
                    endfor; ?>
                <?php ap_section_end(); ?>

                <!-- ══ FOOTER ════════════════════════════════════════════════ -->
                <?php ap_section('footer', 'Footer', 'Join CTA banner, branding, links, social media.'); ?>
                    <?php ap_fieldset('Join CTA Banner'); ?>
                        <?php ap_text('Banner Title',       'ap[footer][join_title]',     $d['footer']['join_title']     ?? 'Join ARIONPLAY Today!'); ?>
                        <?php ap_textarea('Banner Description', 'ap[footer][join_desc]',  $d['footer']['join_desc']      ?? ''); ?>
                        <?php ap_inline([
                            ['CTA Label', 'ap[footer][join_cta_label]', $d['footer']['join_cta_label'] ?? 'JOIN NOW AT ARIONPLAY'],
                            ['CTA URL',   'ap[footer][join_cta_url]',   $d['footer']['join_cta_url']   ?? '#', 'url'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Brand'); ?>
                        <?php ap_image('Footer Logo', 'ap[footer][logo]', $d['footer']['logo'] ?? ''); ?>
                        <?php ap_textarea('Footer Tagline / Description', 'ap[footer][tagline]', $d['footer']['tagline'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Games Navigation Links'); ?>
                        <?php
                        $gl_labels = ['Slots','Live Casino','Fishing','Baccarat','Esports'];
                        for ($i = 0; $i < 5; $i++):
                            $gl = $d['footer']['game_links'][$i] ?? [];
                            ap_inline([
                                ['Label', "ap[footer][game_links][{$i}][label]", $gl['label'] ?? $gl_labels[$i], 'text', '', '160px'],
                                ['URL',   "ap[footer][game_links][{$i}][url]",   $gl['url']   ?? '#', 'url'],
                            ]);
                        endfor; ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Social Media'); ?>
                        <?php ap_inline([
                            ['Facebook',  'ap[footer][social_fb]', $d['footer']['social_fb'] ?? '', 'url'],
                            ['Instagram', 'ap[footer][social_ig]', $d['footer']['social_ig'] ?? '', 'url'],
                        ]); ?>
                        <?php ap_inline([
                            ['Telegram',   'ap[footer][social_tg]', $d['footer']['social_tg'] ?? '', 'url'],
                            ['Twitter / X','ap[footer][social_tw]', $d['footer']['social_tw'] ?? '', 'url'],
                            ['YouTube',    'ap[footer][social_yt]', $d['footer']['social_yt'] ?? '', 'url'],
                        ]); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Responsible Gaming'); ?>
                        <?php ap_text('Title',   'ap[footer][rg_title]',   $d['footer']['rg_title']   ?? 'Responsible Gaming'); ?>
                        <?php ap_textarea('Content', 'ap[footer][rg_content]', $d['footer']['rg_content'] ?? ''); ?>
                        <?php ap_image('Badge / Logo', 'ap[footer][rg_badge]', $d['footer']['rg_badge'] ?? ''); ?>
                    <?php ap_fieldset_end(); ?>
                    <?php ap_fieldset('Copyright'); ?>
                        <?php ap_text('Copyright Text', 'ap[footer][copyright]', $d['footer']['copyright'] ?? '© ' . date('Y') . ' ArionPlay. All rights reserved.'); ?>
                    <?php ap_fieldset_end(); ?>
                <?php ap_section_end(); ?>

            </form>
            </main><!-- /#ap-content -->

        </div><!-- /#ap-body -->
    </div><!-- /#ap-root -->
    <?php
}

// ── Field Helpers ─────────────────────────────────────────────────────────────
function ap_section($id, $title, $desc = '') {
    echo '<div class="ap-section" data-section="' . $id . '" style="display:none;">';
    echo '<div class="ap-section-head"><h2>' . esc_html($title) . '</h2>' . ($desc ? '<p>' . esc_html($desc) . '</p>' : '') . '</div>';
}
function ap_section_end() { echo '</div>'; }

function ap_fieldset($label) {
    echo '<div class="ap-fieldset"><div class="ap-fieldset-label">' . esc_html($label) . '</div><div class="ap-fieldset-body">';
}
function ap_fieldset_end() { echo '</div></div>'; }

function ap_text($label, $name, $value = '', $type = 'text', $hint = '', $width = '') {
    $id = 'f_' . md5($name);
    $style = $width ? ' style="max-width:' . $width . '"' : '';
    echo '<div class="ap-field"' . $style . '>';
    echo '<label for="' . $id . '">' . esc_html($label) . '</label>';
    echo '<input type="' . esc_attr($type) . '" id="' . $id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" placeholder="' . ($type === 'url' ? 'https://' : '') . '">';
    if ($hint) echo '<span class="ap-hint">' . esc_html($hint) . '</span>';
    echo '</div>';
}

function ap_textarea($label, $name, $value = '', $hint = '') {
    $id = 'f_' . md5($name);
    echo '<div class="ap-field">';
    echo '<label for="' . $id . '">' . esc_html($label) . '</label>';
    echo '<textarea id="' . $id . '" name="' . esc_attr($name) . '" rows="3">' . esc_textarea($value) . '</textarea>';
    if ($hint) echo '<span class="ap-hint">' . esc_html($hint) . '</span>';
    echo '</div>';
}

function ap_image($label, $name, $value = '') {
    $id = 'f_' . md5($name);
    echo '<div class="ap-field ap-field-image">';
    echo '<label>' . esc_html($label) . '</label>';
    echo '<div class="ap-img-row">';
    echo '<input type="url" id="' . $id . '" name="' . esc_attr($name) . '" value="' . esc_attr($value) . '" class="ap-img-url" placeholder="https://">';
    echo '<button type="button" class="ap-upload-btn" data-target="' . $id . '">Upload / Select</button>';
    if ($value) echo '<button type="button" class="ap-remove-btn" data-target="' . $id . '">Remove</button>';
    echo '</div>';
    echo '<div class="ap-img-preview-wrap">' . ($value ? '<img src="' . esc_url($value) . '" class="ap-img-preview">' : '') . '</div>';
    echo '</div>';
}

// Inline fields on one row
function ap_inline($fields) {
    echo '<div class="ap-inline">';
    foreach ($fields as [$label, $name, $value, $type, $hint, $width]) {
        $type  = $type  ?? 'text';
        $hint  = $hint  ?? '';
        $width = $width ?? '';
        if ($type === 'image') {
            ap_image($label, $name, $value);
        } elseif ($type === 'textarea') {
            ap_textarea($label, $name, $value, $hint);
        } else {
            ap_text($label, $name, $value, $type, $hint, $width);
        }
    }
    echo '</div>';
}
