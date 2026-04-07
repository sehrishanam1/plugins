(function(){
'use strict';

window.sgTSInit = function(wrap, cfg){
  if(!wrap) return;

  var track      = wrap.querySelector('.sg-ts__track');
  var slides     = Array.prototype.slice.call(wrap.querySelectorAll('.sg-ts__slide'));
  var statPanels = Array.prototype.slice.call(wrap.querySelectorAll('.sg-ts__stats--slide'));
  var btnPrev    = wrap.querySelector('.sg-ts__nav-arr--prev');
  var btnNext    = wrap.querySelector('.sg-ts__nav-arr--next');
  var dots       = Array.prototype.slice.call(wrap.querySelectorAll('.sg-ts__nav-dot'));
  var total      = cfg.total || slides.length;
  var current    = 0;
  var timer      = null;

  function goTo(i, animate){
    if(i < 0) i = total - 1;
    if(i >= total) i = 0;
    current = i;

    // Slide track
    track.style.transition = animate===false ? 'none' : 'transform 0.5s cubic-bezier(0.4,0,0.2,1)';
    track.style.transform  = 'translateX(-'+(current * 100)+'%)';

    // Swap stats panels
    statPanels.forEach(function(panel){
      var pIdx = parseInt(panel.dataset.slide, 10);
      panel.style.display = pIdx === current ? 'flex' : 'none';
    });

    // Update dots
    dots.forEach(function(d, di){
      var a = di === current;
      d.classList.toggle('active', a);
      d.style.background = a ? cfg.dotActive   : cfg.dotInactive;
      d.style.color      = a ? '#000' : '#888';
    });

    // Arrow opacity
    if(btnPrev) btnPrev.style.opacity = current === 0       ? '0.35' : '1';
    if(btnNext) btnNext.style.opacity = current === total-1 ? '0.35' : '1';
  }

  if(btnPrev) btnPrev.addEventListener('click', function(){ stopAuto(); goTo(current-1, true); startAuto(); });
  if(btnNext) btnNext.addEventListener('click', function(){ stopAuto(); goTo(current+1, true); startAuto(); });

  dots.forEach(function(d){
    d.addEventListener('click', function(){
      stopAuto();
      goTo(parseInt(d.dataset.page, 10), true);
      startAuto();
    });
  });

  // Touch swipe
  var tx = 0;
  wrap.addEventListener('touchstart', function(e){ tx = e.changedTouches[0].screenX; }, {passive:true});
  wrap.addEventListener('touchend',   function(e){
    var diff = tx - e.changedTouches[0].screenX;
    if(Math.abs(diff) > 50){ stopAuto(); goTo(diff > 0 ? current+1 : current-1, true); startAuto(); }
  });

  function startAuto(){
    if(!cfg.autoPlay) return;
    stopAuto();
    timer = setInterval(function(){ goTo(current+1, true); }, cfg.autoPlayDelay || 5000);
  }
  function stopAuto(){
    if(timer){ clearInterval(timer); timer = null; }
  }

  wrap.addEventListener('mouseenter', stopAuto);
  wrap.addEventListener('mouseleave', startAuto);

  goTo(0, false);
  startAuto();
};
})();
