<?php
/**
 * Bundle Widget — front-end template
 * Variables available from shortcode:
 *   $widget_id, $js_products, $discount_pct, $social_name,
 *   $social_item, $show_timer, $timer_iso, $nonce, $currency
 */
defined( 'ABSPATH' ) || exit;

$CHECK_SVG = '<svg viewBox="0 0 13 10" fill="none"><path d="M1.5 5L5 8.5L11.5 1.5" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
?>

<div id="<?= esc_attr( $widget_id ) ?>" class="abw-wrap">
  <div class="abw-card">

    <!-- ── HEADER ─────────────────────────────────────────── -->
    <div class="abw-head">
      <span class="abw-title">See what can you buy</span>
      <div class="abw-badges">
        <?php if ( $show_timer ) : ?>
          <span class="abw-limited">Limited time offer</span>
          <span class="abw-timer" data-end="<?= esc_attr( $timer_iso ) ?>">--:--</span>
        <?php endif; ?>
      </div>
    </div>

    <!-- ── PRODUCT LIST ───────────────────────────────────── -->
    <div class="abw-list">
      <?php foreach ( $js_products as $p ) : ?>
        <?php $checked = ! empty( $p['checked'] ); ?>
        <div class="abw-row<?= $checked ? ' abw-checked' : '' ?>" data-wc-id="<?= esc_attr( $p['wc_id'] ) ?>" data-price="<?= esc_attr( $p['price'] ) ?>">
          <div class="abw-chk<?= $checked ? ' on' : '' ?>">
            <?php if ( $checked ) echo $CHECK_SVG; ?>
          </div>
          <div class="abw-logo">
            <?php if ( ! empty( $p['img'] ) ) : ?>
              <img src="<?= esc_url( $p['img'] ) ?>" alt="<?= esc_attr( $p['label'] ) ?>">
            <?php else : ?>
              <span class="abw-logo-letter"><?= esc_html( strtoupper( substr( $p['label'], 0, 1 ) ) ) ?></span>
            <?php endif; ?>
          </div>
          <div class="abw-info">
            <div class="abw-name"><?= esc_html( $p['label'] ) ?></div>
            <div class="abw-badge">Premium</div>
          </div>
          <div class="abw-price"><?= esc_html( $currency . number_format( $p['price'], 2 ) ) ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- ── TOTALS ─────────────────────────────────────────── -->
    <div class="abw-totals">
      <div class="abw-trow">
        <span>Subtotal</span>
        <span class="abw-sub"><?= esc_html( $currency . '0.00' ) ?></span>
      </div>
      <div class="abw-trow abw-disc-row" style="display:none">
        <span>Discount</span>
        <span class="abw-dval" style="color:#2ecc71;font-weight:600">-<?= esc_html( $currency ) ?>0.00</span>
      </div>
      <div class="abw-trow abw-total-row">
        <span>Total</span>
        <span class="abw-tot"><?= esc_html( $currency . '0.00' ) ?></span>
      </div>
    </div>

    <!-- ── FOOTER ─────────────────────────────────────────── -->
    <div class="abw-foot">
      <div class="abw-social">
        <div class="abw-sp-icon">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="white">
            <path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/>
          </svg>
        </div>
        <span>
          <?= esc_html( $social_name ) ?>, just purchased
          <strong><?= esc_html( $social_item ) ?></strong>
        </span>
      </div>
      <button class="abw-btn" id="<?= esc_attr( $widget_id ) ?>-btn">Add to a Bundle Cart</button>
    </div>

    <div class="abw-msg" id="<?= esc_attr( $widget_id ) ?>-msg"></div>

  </div>
</div>

<script>
(function(){
  var WID          = <?= wp_json_encode( $widget_id ) ?>;
  var PRODUCTS     = <?= wp_json_encode( $js_products ) ?>;
  var DISCOUNT_PCT = <?= (int) $discount_pct ?>;
  var NONCE        = <?= wp_json_encode( $nonce ) ?>;
  var CURRENCY     = <?= wp_json_encode( $currency ) ?>;

  var CHECK_SVG = '<svg viewBox="0 0 13 10" fill="none"><path d="M1.5 5L5 8.5L11.5 1.5" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>';

  var wrap = document.getElementById(WID);
  if (!wrap) return;

  /* ── State: build from PHP-rendered rows (respects default_on) ── */
  var state = [];
  wrap.querySelectorAll('.abw-row').forEach(function(row) {
    state.push({
      wc_id:   parseInt(row.dataset.wcId, 10),
      price:   parseFloat(row.dataset.price),
      checked: row.classList.contains('abw-checked'),
      el:      row
    });
  });

  /* ── Toggle on click ─────────────────────────────────────────── */
  state.forEach(function(p) {
    p.el.addEventListener('click', function() {
      p.checked = !p.checked;
      var chk = p.el.querySelector('.abw-chk');
      chk.innerHTML  = p.checked ? CHECK_SVG : '';
      chk.className  = 'abw-chk' + (p.checked ? ' on' : '');
      updateTotals();
    });
  });

  /* ── Totals ──────────────────────────────────────────────────── */
  function updateTotals() {
    var sel  = state.filter(function(p){ return p.checked; });
    var sub  = sel.reduce(function(s,p){ return s + p.price; }, 0);
    var disc = sel.length >= 3 ? sub * DISCOUNT_PCT / 100 : 0;
    var tot  = sub - disc;

    wrap.querySelector('.abw-sub').textContent  = CURRENCY + sub.toFixed(2);
    wrap.querySelector('.abw-dval').textContent = '-' + CURRENCY + disc.toFixed(2);
    wrap.querySelector('.abw-tot').textContent  = CURRENCY + tot.toFixed(2);
    wrap.querySelector('.abw-disc-row').style.display = disc > 0 ? 'flex' : 'none';
  }

  /* ── Timer ───────────────────────────────────────────────────── */
  var timerEl = wrap.querySelector('.abw-timer');
  if (timerEl) {
    var endTime = new Date(timerEl.dataset.end).getTime();
    (function tick(){
      var diff = endTime - Date.now();
      if (diff <= 0) { timerEl.textContent = 'Expired'; return; }
      var d  = Math.floor(diff / 86400000);
      var hh = String(Math.floor((diff % 86400000) / 3600000)).padStart(2,'0');
      var mm = String(Math.floor((diff % 3600000)  /   60000)).padStart(2,'0');
      timerEl.textContent = d + 'd ' + hh + ':' + mm;
      setTimeout(tick, 1000);
    })();
  }

  /* ── Add to Cart ─────────────────────────────────────────────── */
  document.getElementById(WID + '-btn').addEventListener('click', function() {
    var btn = this;
    var msg = document.getElementById(WID + '-msg');
    var sel = state.filter(function(p){ return p.checked; });

    if (sel.length < 3) {
      msg.textContent = 'Please select at least 3 products to create a bundle.';
      msg.className   = 'abw-msg abw-err';
      return;
    }

    btn.disabled    = true;
    btn.textContent = 'Adding…';
    msg.textContent = '';

    var data = new FormData();
    data.append('action', 'abw_add_to_cart');
    data.append('nonce',  NONCE);
    sel.forEach(function(p, i){
      data.append('selected['+i+'][wc_id]',        p.wc_id);
      data.append('selected['+i+'][price]',        p.price);
      data.append('selected['+i+'][variation_id]', p.variation_id || 0);
      var variation = p.variation || {};
      Object.keys(variation).forEach(function(k){
        data.append('selected['+i+'][variation]['+k+']', variation[k]);
      });
    });

    fetch(ABW.ajax_url, { method: 'POST', body: data })
      .then(function(r){ return r.json(); })
      .then(function(res) {
        if (res.success) {
          msg.innerHTML = res.data.message;
          msg.className = 'abw-msg abw-ok';
          setTimeout(function(){ window.location.href = res.data.cart_url; }, 900);
        } else {
          msg.textContent = res.data.message || 'Something went wrong.';
          msg.className   = 'abw-msg abw-err';
          btn.disabled    = false;
          btn.textContent = 'Add to a Bundle Cart';
        }
      })
      .catch(function(){
        msg.textContent = 'Request failed. Please try again.';
        msg.className   = 'abw-msg abw-err';
        btn.disabled    = false;
        btn.textContent = 'Add to a Bundle Cart';
      });
  });

  /* ── Init totals ─────────────────────────────────────────────── */
  updateTotals();
})();
</script>
