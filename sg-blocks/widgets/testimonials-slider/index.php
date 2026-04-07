<?php
/**
 * Widget: Testimonials Slider
 */
if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'sg_testimonials_register' );

function sg_testimonials_register() {
    $url  = SG_BLOCKS_URL  . 'widgets/testimonials-slider/';
    $path = SG_BLOCKS_PATH . 'widgets/testimonials-slider/';

    $jv = file_exists($path.'block.js')  ? filemtime($path.'block.js')  : '1';
    $cv = file_exists($path.'style.css') ? filemtime($path.'style.css') : '1';
    $fv = file_exists($path.'front.js')  ? filemtime($path.'front.js')  : '1';

    wp_register_script('sg-test-editor',   $url.'block.js',  ['wp-blocks','wp-element','wp-components','wp-block-editor'], $jv, true);
    wp_register_script('sg-test-frontend', $url.'front.js',  [], $fv, true);
    wp_register_style( 'sg-test-style',    $url.'style.css', [], $cv);

    register_block_type('sg-blocks/testimonials-slider', [
        'editor_script'   => 'sg-test-editor',
        'editor_style'    => 'sg-test-style',
        'style'           => 'sg-test-style',
        'script'          => 'sg-test-frontend',
        'render_callback' => 'sg_testimonials_render',
        'attributes'      => [
            'badgeText'       => ['type'=>'string',  'default'=>'Testimonials'],
            'showBadge'       => ['type'=>'boolean', 'default'=>true],
            'heading'         => ['type'=>'string',  'default'=>'Businesses Speak for Themselves'],
            'subheading'      => ['type'=>'string',  'default'=>'Discover genuine stories from real people sharing their experiences and how they benefited by working with us.'],
            'showSubheading'  => ['type'=>'boolean', 'default'=>true],
            'slides'          => ['type'=>'string',  'default'=>'[]'],
            'bgColor'         => ['type'=>'string',  'default'=>'#0a0a0a'],
            'cardBgColor'     => ['type'=>'string',  'default'=>'#181818'],
            'cardBorderColor' => ['type'=>'string',  'default'=>'#2a2a2a'],
            'statsBgColor'    => ['type'=>'string',  'default'=>'#0a0a0a'],
            'statsBorderColor'=> ['type'=>'string',  'default'=>'#222222'],
            'dotActiveColor'  => ['type'=>'string',  'default'=>'#00c8ff'],
            'dotInactiveColor'=> ['type'=>'string',  'default'=>'#1e1e1e'],
            'innerWidth'      => ['type'=>'string',  'default'=>'1100px'],
            'autoPlay'        => ['type'=>'boolean', 'default'=>false],
            'autoPlayDelay'   => ['type'=>'number',  'default'=>5000],
        ],
    ]);
}

function sg_testimonials_render( $attr ) {
    $slides = [];
    if ( !empty($attr['slides']) ) {
        $d = json_decode($attr['slides'], true);
        if ( is_array($d) ) $slides = $d;
    }

    $uid          = 'sg-ts-'.uniqid();
    $total        = count($slides);
    $dot_active   = esc_attr($attr['dotActiveColor']);
    $dot_inactive = esc_attr($attr['dotInactiveColor']);
    $inner_width  = esc_attr($attr['innerWidth']);

    // Collect stats from first slide that has them (stats are global per design)
    // But we'll use per-slide stats and show current slide's stats
    ob_start(); ?>
    <section class="sg-ts" style="background:<?php echo esc_attr($attr['bgColor']); ?>">
      <div class="sg-ts__inner">

        <!-- Header (centered, no max-width constraint) -->
        <div style="max-width:<?php echo $inner_width; ?>;margin:0 auto;padding:0 40px;box-sizing:border-box;">
          <?php if (!empty($attr['showBadge']) && !empty($attr['badgeText'])): ?>
          <div class="sg-ts__badge-wrap"><span class="sg-ts__badge"><?php echo esc_html($attr['badgeText']); ?></span></div>
          <?php endif; ?>
          <?php if (!empty($attr['heading'])): ?>
          <h2 class="sg-ts__heading"><?php echo esc_html($attr['heading']); ?></h2>
          <?php endif; ?>
          <?php if (!empty($attr['showSubheading']) && !empty($attr['subheading'])): ?>
          <p class="sg-ts__subheading"><?php echo esc_html($attr['subheading']); ?></p>
          <?php endif; ?>
        </div>

        <?php if ($total > 0): ?>
        <!-- Bordered slider wrapper -->
        <div class="sg-ts__slider" id="<?php echo esc_attr($uid); ?>"
             style="--sg-ts-width:<?php echo $inner_width; ?>">

          <div class="sg-ts__slider-inner">
            <!-- Track -->
            <div class="sg-ts__track-wrap">
              <div class="sg-ts__track">
                <?php foreach ($slides as $i => $slide):
                  $photo = !empty($slide['photo']) ? $slide['photo'] : '';
                  $name  = !empty($slide['name'])  ? $slide['name']  : '';
                  $role  = !empty($slide['role'])  ? $slide['role']  : '';
                  $quote = !empty($slide['quote']) ? $slide['quote'] : '';
                ?>
                <div class="sg-ts__slide">
                  <div class="sg-ts__card" style="background:<?php echo esc_attr($attr['cardBgColor']); ?>;border-color:<?php echo esc_attr($attr['cardBorderColor']); ?>">
                    <div class="sg-ts__card-inner">
                      <div class="sg-ts__photo-wrap">
                        <?php if ($photo): ?>
                        <img class="sg-ts__photo" src="<?php echo esc_url($photo); ?>" alt="<?php echo esc_attr($name); ?>"/>
                        <?php else: ?>
                        <div class="sg-ts__photo-placeholder">
                          <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#444" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <?php endif; ?>
                      </div>
                      <div class="sg-ts__content">
                        <?php if ($quote): ?><p class="sg-ts__quote"><?php echo esc_html($quote); ?></p><div class="sg-ts__divider"></div><?php endif; ?>
                        <?php if ($name): ?><p class="sg-ts__name"><?php echo esc_html($name); ?></p><?php endif; ?>
                        <?php if ($role): ?><p class="sg-ts__role"><?php echo esc_html($role); ?></p><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>

            <!-- Nav pill -->
            <?php if ($total > 1): ?>
            <div class="sg-ts__nav-wrap">
              <div class="sg-ts__nav">
                <button class="sg-ts__nav-arr sg-ts__nav-arr--prev" aria-label="Previous">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <div class="sg-ts__nav-dots">
                  <?php for ($p=0; $p<$total; $p++): ?>
                  <button class="sg-ts__nav-dot<?php echo $p===0?' active':''; ?>"
                          data-page="<?php echo $p; ?>"
                          style="<?php echo $p===0?'background:'.$dot_active.';color:#000;':'background:'.$dot_inactive.';color:#888;'; ?>">
                    <?php echo $p+1; ?>
                  </button>
                  <?php endfor; ?>
                </div>
                <button class="sg-ts__nav-arr sg-ts__nav-arr--next" aria-label="Next">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
              </div>
            </div>
            <?php endif; ?>
          </div>

          <!-- Stats bar: full width, outside card, below nav -->
          <?php
          // Use first slide's stats for static display
          // On slide change JS will swap the stats
          $first_stats = !empty($slides[0]['stats']) ? $slides[0]['stats'] : [];
          ?>
          <?php if (!empty($first_stats)): ?>
          <div class="sg-ts__stats-outer">
            <?php foreach ($slides as $si => $slide):
              $sts = !empty($slide['stats']) ? $slide['stats'] : [];
              if (empty($sts)) continue;
            ?>
            <div class="sg-ts__stats sg-ts__stats--slide"
                 data-slide="<?php echo $si; ?>"
                 style="<?php echo $si===0?'':'display:none;'; ?>background:<?php echo esc_attr($attr['statsBgColor']); ?>">
              <?php foreach ($sts as $ssi => $stat):
                $snum   = !empty($stat['number']) ? $stat['number'] : '';
                $slabel = !empty($stat['label'])  ? $stat['label']  : '';
              ?>
              <div class="sg-ts__stat" style="border-color:<?php echo esc_attr($attr['statsBorderColor']); ?>">
                <span class="sg-ts__stat-num"><?php echo esc_html($snum); ?></span>
                <span class="sg-ts__stat-label"><?php echo esc_html($slabel); ?></span>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

        </div>
        <?php endif; ?>

      </div>
    </section>

    <script>
    (function(){
      var el  = document.getElementById('<?php echo esc_js($uid); ?>');
      var cfg = {
        total        : <?php echo $total; ?>,
        dotActive    : '<?php echo esc_js($dot_active); ?>',
        dotInactive  : '<?php echo esc_js($dot_inactive); ?>',
        autoPlay     : <?php echo !empty($attr['autoPlay']) ? 'true' : 'false'; ?>,
        autoPlayDelay: <?php echo intval($attr['autoPlayDelay']); ?>,
      };
      function run(){ if(window.sgTSInit) window.sgTSInit(el, cfg); }
      if(document.readyState==='loading') document.addEventListener('DOMContentLoaded', run); else run();
    })();
    </script>
    <?php
    return ob_get_clean();
}
