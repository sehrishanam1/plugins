<?php
/**
 * Widget: Works Grid
 * Manual repeater. Smooth slider. Click → popup with image gallery.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_works_grid_register' );

function sg_works_grid_register() {
    $widget_url  = SG_BLOCKS_URL  . 'widgets/works-grid/';
    $widget_path = SG_BLOCKS_PATH . 'widgets/works-grid/';

    $js_ver  = file_exists( $widget_path . 'block.js'  ) ? filemtime( $widget_path . 'block.js'  ) : '1.0';
    $css_ver = file_exists( $widget_path . 'style.css' ) ? filemtime( $widget_path . 'style.css' ) : '1.0';
    $fe_ver  = file_exists( $widget_path . 'popup.js'  ) ? filemtime( $widget_path . 'popup.js'  ) : '1.0';

    wp_register_script( 'sg-works-grid-editor',   $widget_url . 'block.js',  array('wp-blocks','wp-element','wp-components','wp-block-editor'), $js_ver,  true );
    wp_register_script( 'sg-works-grid-frontend', $widget_url . 'popup.js',  array(), $fe_ver,  true );
    wp_register_style(  'sg-works-grid-style',    $widget_url . 'style.css', array(), $css_ver );

    register_block_type( 'sg-blocks/works-grid', array(
        'editor_script'   => 'sg-works-grid-editor',
        'editor_style'    => 'sg-works-grid-style',
        'style'           => 'sg-works-grid-style',
        'script'          => 'sg-works-grid-frontend',
        'render_callback' => 'sg_works_grid_render',
        'attributes'      => array(
            'showBadge'        => array('type'=>'boolean','default'=>true),
            'badgeText'        => array('type'=>'string', 'default'=>'Our Work'),
            'headingLine1'     => array('type'=>'string', 'default'=>'Built for'),
            'headingAccent'    => array('type'=>'string', 'default'=>'Premium'),
            'headingLine2'     => array('type'=>'string', 'default'=>'Service Businesses'),
            'accentColor'      => array('type'=>'string', 'default'=>'#00c8ff'),
            'showSubheading'   => array('type'=>'boolean','default'=>true),
            'subheading'       => array('type'=>'string', 'default'=>'Every website we build is designed to position you as the best — not just the fastest to find.'),
            // Background
            'bgColor'          => array('type'=>'string', 'default'=>'#080808'),
            'headingBgImage'   => array('type'=>'string', 'default'=>''),
            'headingBgImageId' => array('type'=>'integer','default'=>0),
            // Works
            'works'            => array('type'=>'string', 'default'=>'[]'),
            'cardsPerView'     => array('type'=>'number', 'default'=>3),
            'cardBgColor'      => array('type'=>'string', 'default'=>'#111111'),
            'dotActiveColor'   => array('type'=>'string', 'default'=>'#00c8ff'),
            'dotInactiveColor' => array('type'=>'string', 'default'=>'#1e1e1e'),
            'showDots'         => array('type'=>'boolean','default'=>true),
            'showArrows'       => array('type'=>'boolean','default'=>true),
            'innerWidth'       => array('type'=>'string', 'default'=>'1100px'),
        ),
    ));
}

function sg_works_grid_render( $attr ) {
    $works = array();
    if ( !empty($attr['works']) ) {
        $d = json_decode( $attr['works'], true );
        if ( is_array($d) ) $works = $d;
    }
    if ( empty($works) ) return '';

    $uid          = 'sgwg-' . uniqid();
    $accent       = esc_attr( $attr['accentColor'] );
    $card_bg      = esc_attr( $attr['cardBgColor'] );
    $dot_active   = esc_attr( $attr['dotActiveColor'] );
    $dot_inactive = esc_attr( $attr['dotInactiveColor'] );
    $per_view     = max(1, intval($attr['cardsPerView']));
    $total        = count($works);
    $pages        = ceil($total / $per_view);

    // Heading section background
    $heading_bg_style = 'background-color:' . esc_attr($attr['bgColor']) . ';';
    if ( !empty($attr['headingBgImage']) ) {
        $heading_bg_style .= 'background-image:url(' . esc_url($attr['headingBgImage']) . ');background-size:cover;background-position:center;';
    }

    ob_start();
    ?>
    <section class="sg-wg" style="background:<?php echo esc_attr($attr['bgColor']); ?>">

      <!-- Heading area with its own background -->
      <div class="sg-wg__heading-section" style="<?php echo $heading_bg_style; ?>">
        <div class="sg-wg__inner" style="max-width:<?php echo esc_attr($attr['innerWidth']); ?>;margin:0 auto">

          <?php if ( !empty($attr['showBadge']) && !empty($attr['badgeText']) ) : ?>
          <div class="sg-wg__badge-wrap">
            <span class="sg-wg__badge"><?php echo esc_html($attr['badgeText']); ?></span>
          </div>
          <?php endif; ?>

          <h2 class="sg-wg__heading">
            <?php if ( $attr['headingLine1'] || $attr['headingAccent'] ) : ?>
            <span class="sg-wg__h-row">
              <?php if($attr['headingLine1']): ?><span><?php echo esc_html($attr['headingLine1']); ?> </span><?php endif; ?>
              <?php if($attr['headingAccent']): ?><span style="color:<?php echo $accent; ?>"><?php echo esc_html($attr['headingAccent']); ?></span><?php endif; ?>
            </span>
            <?php endif; ?>
            <?php if ($attr['headingLine2']): ?>
            <span class="sg-wg__h-row"><?php echo esc_html($attr['headingLine2']); ?></span>
            <?php endif; ?>
          </h2>

          <?php if ( !empty($attr['showSubheading']) && !empty($attr['subheading']) ) : ?>
          <p class="sg-wg__sub"><?php echo esc_html($attr['subheading']); ?></p>
          <?php endif; ?>

        </div>
      </div>

      <!-- Slider area -->
      <div class="sg-wg__slider-section">
        <div class="sg-wg__inner" style="max-width:<?php echo esc_attr($attr['innerWidth']); ?>;margin:0 auto">

          <div class="sg-wg__slider-wrap" id="<?php echo esc_attr($uid); ?>">
            <div class="sg-wg__viewport">
              <div class="sg-wg__track" style="--cpv:<?php echo $per_view; ?>">
                <?php foreach ( $works as $i => $w ) :
                  $feat_img   = !empty($w['featImg'])   ? $w['featImg']   : '';
                  $category   = !empty($w['category'])  ? $w['category']  : '';
                  $title      = !empty($w['title'])     ? $w['title']     : '';
                  $tags       = !empty($w['tags'])      ? $w['tags']      : '';
                  $type       = !empty($w['type'])      ? $w['type']      : '';
                  $price      = !empty($w['price'])     ? $w['price']     : '';
                  $meta_parts = array_filter(array($tags, $type, $price));
                  $meta_line  = implode(' · ', $meta_parts);
                ?>
                <div class="sg-wg__card" style="background:<?php echo $card_bg; ?>"
                     data-work-index="<?php echo $i; ?>"
                     role="button" tabindex="0" aria-label="View <?php echo esc_attr($title); ?>">
                  <div class="sg-wg__card-img">
                    <?php if($feat_img): ?>
                    <img src="<?php echo esc_url($feat_img); ?>" alt="<?php echo esc_attr($title); ?>" loading="lazy"/>
                    <?php else: ?>
                    <div class="sg-wg__card-placeholder"></div>
                    <?php endif; ?>
                    <div class="sg-wg__card-overlay">
                      <span class="sg-wg__view-btn" style="border-color:<?php echo $accent; ?>;color:<?php echo $accent; ?>">View Project</span>
                    </div>
                  </div>
                  <div class="sg-wg__card-body">
                    <?php if($category): ?><span class="sg-wg__card-cat"><?php echo esc_html($category); ?></span><?php endif; ?>
                    <?php if($title): ?><h3 class="sg-wg__card-title"><?php echo esc_html($title); ?></h3><?php endif; ?>
                    <?php if($meta_line): ?><p class="sg-wg__card-meta"><?php echo esc_html($meta_line); ?></p><?php endif; ?>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Nav pill: < 1 2 3 > -->
          <?php if ( $pages > 1 && (!empty($attr['showDots']) || !empty($attr['showArrows'])) ) : ?>
          <div class="sg-wg__nav-pill" id="<?php echo esc_attr($uid); ?>-nav">
            <?php if ( !empty($attr['showArrows']) ) : ?>
            <button class="sg-wg__nav-arr sg-wg__nav-arr--prev" aria-label="Previous">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
            </button>
            <?php endif; ?>

            <?php if ( !empty($attr['showDots']) ) : ?>
            <div class="sg-wg__nav-dots">
              <?php for ( $p = 0; $p < $pages; $p++ ) : ?>
              <button class="sg-wg__nav-dot<?php echo $p===0?' active':''; ?>"
                      data-page="<?php echo $p; ?>"
                      style="<?php echo $p===0 ? 'background:'.$dot_active.';color:#000;' : 'background:'.$dot_inactive.';color:#888;'; ?>">
                <?php echo $p + 1; ?>
              </button>
              <?php endfor; ?>
            </div>
            <?php endif; ?>

            <?php if ( !empty($attr['showArrows']) ) : ?>
            <button class="sg-wg__nav-arr sg-wg__nav-arr--next" aria-label="Next">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </button>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>

    </section>

    <!-- POPUP -->
    <div class="sg-wg-popup" id="<?php echo esc_attr($uid); ?>-popup" role="dialog" aria-modal="true">
      <div class="sg-wg-popup__bd"></div>
      <div class="sg-wg-popup__box">
        <button class="sg-wg-popup__close" aria-label="Close">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
        <div class="sg-wg-popup__gallery">
          <div class="sg-wg-popup__gal-main">
            <img class="sg-wg-popup__gal-img" src="" alt=""/>
            <button class="sg-wg-popup__gal-prev"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg></button>
            <button class="sg-wg-popup__gal-next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg></button>
            <span class="sg-wg-popup__gal-count"></span>
          </div>
          <div class="sg-wg-popup__thumbs"></div>
        </div>
        <div class="sg-wg-popup__info">
          <span class="sg-wg-popup__cat"></span>
          <h2 class="sg-wg-popup__title"></h2>
          <p class="sg-wg-popup__meta" style="color:<?php echo $accent; ?>"></p>
          <p class="sg-wg-popup__desc"></p>
          <a class="sg-wg-popup__cta" href="#" target="_blank" rel="noopener" style="background:<?php echo $accent; ?>;color:#000">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Visit Website
          </a>
        </div>
      </div>
    </div>

    <script>
    (function(){
      var slider = document.getElementById('<?php echo esc_js($uid); ?>');
      var nav    = document.getElementById('<?php echo esc_js($uid); ?>-nav');
      var popup  = document.getElementById('<?php echo esc_js($uid); ?>-popup');
      var works  = <?php echo wp_json_encode(array_values($works)); ?>;
      var cfg    = {
        perView     : <?php echo $per_view; ?>,
        dotActive   : '<?php echo esc_js($dot_active); ?>',
        dotInactive : '<?php echo esc_js($dot_inactive); ?>',
        accent      : '<?php echo esc_js($accent); ?>'
      };
      function run(){ if(window.sgWGInit) window.sgWGInit(slider,nav,popup,works,cfg); }
      if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',run); else run();
    })();
    </script>
    <?php
    return ob_get_clean();
}
