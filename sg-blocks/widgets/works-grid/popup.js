(function(){
'use strict';

window.sgWGInit = function(slider, nav, popup, works, cfg){
  if(!slider) return;

  var perView    = cfg.perView    || 3;
  var dotActive  = cfg.dotActive  || '#00c8ff';
  var dotInactive= cfg.dotInactive|| '#1e1e1e';
  var accent     = cfg.accent     || '#00c8ff';

  /* ── SLIDER ── */
  var track    = slider.querySelector('.sg-wg__track');
  var viewport = slider.querySelector('.sg-wg__viewport');
  var cards    = Array.prototype.slice.call(slider.querySelectorAll('.sg-wg__card'));
  var total    = cards.length;
  var pages    = Math.ceil(total / perView);
  var current  = 0;

  // Nav elements
  var btnPrev = nav ? nav.querySelector('.sg-wg__nav-arr--prev') : null;
  var btnNext = nav ? nav.querySelector('.sg-wg__nav-arr--next') : null;
  var dots    = nav ? Array.prototype.slice.call(nav.querySelectorAll('.sg-wg__nav-dot')) : [];

  function goTo(page, animate){
    if(page < 0) page = pages - 1;
    if(page >= pages) page = 0;
    current = page;

    var cardW  = viewport.offsetWidth / perView;
    var offset = -(current * perView * cardW);
    track.style.transition = animate===false ? 'none' : 'transform 0.52s cubic-bezier(0.4,0,0.2,1)';
    track.style.transform  = 'translateX('+offset+'px)';

    dots.forEach(function(d,i){
      var a = i===current;
      d.classList.toggle('active', a);
      d.style.background = a ? dotActive : dotInactive;
      d.style.color      = a ? '#000' : '#888';
    });

    if(btnPrev) btnPrev.style.opacity = current===0        ? '0.35' : '1';
    if(btnNext) btnNext.style.opacity = current===pages-1  ? '0.35' : '1';
  }

  if(btnPrev) btnPrev.addEventListener('click', function(){ goTo(current-1, true); });
  if(btnNext) btnNext.addEventListener('click', function(){ goTo(current+1, true); });
  dots.forEach(function(d){
    d.addEventListener('click', function(){ goTo(parseInt(d.dataset.page,10), true); });
  });

  // Touch swipe
  var tx=0;
  viewport.addEventListener('touchstart', function(e){ tx=e.changedTouches[0].screenX; },{passive:true});
  viewport.addEventListener('touchend',   function(e){
    var diff=tx-e.changedTouches[0].screenX;
    if(Math.abs(diff)>50) goTo(diff>0?current+1:current-1, true);
  });

  var rt;
  window.addEventListener('resize', function(){
    clearTimeout(rt);
    rt=setTimeout(function(){ goTo(current,false); },120);
  });

  goTo(0, false);

  /* ── POPUP ── */
  if(!popup) return;

  var pBox      = popup.querySelector('.sg-wg-popup__box');
  var pBd       = popup.querySelector('.sg-wg-popup__bd');
  var pClose    = popup.querySelector('.sg-wg-popup__close');
  var pGalImg   = popup.querySelector('.sg-wg-popup__gal-img');
  var pGalPrev  = popup.querySelector('.sg-wg-popup__gal-prev');
  var pGalNext  = popup.querySelector('.sg-wg-popup__gal-next');
  var pGalCount = popup.querySelector('.sg-wg-popup__gal-count');
  var pThumbs   = popup.querySelector('.sg-wg-popup__thumbs');
  var pGallery  = popup.querySelector('.sg-wg-popup__gallery');
  var pCat      = popup.querySelector('.sg-wg-popup__cat');
  var pTitle    = popup.querySelector('.sg-wg-popup__title');
  var pMeta     = popup.querySelector('.sg-wg-popup__meta');
  var pDesc     = popup.querySelector('.sg-wg-popup__desc');
  var pCta      = popup.querySelector('.sg-wg-popup__cta');

  var galImages=[], galCurrent=0;

  function galShow(i){
    if(!galImages.length) return;
    if(i<0) i=galImages.length-1;
    if(i>=galImages.length) i=0;
    galCurrent=i;
    pGalImg.style.opacity='0';
    setTimeout(function(){ pGalImg.src=galImages[i]; pGalImg.style.opacity='1'; },80);
    pGalCount.textContent=(i+1)+' / '+galImages.length;
    Array.prototype.slice.call(pThumbs.querySelectorAll('.sg-wg-popup__thumb')).forEach(function(t,ti){
      t.classList.toggle('sg-wg-popup__thumb--active', ti===i);
    });
    var multi=galImages.length>1;
    pGalPrev.style.display  = multi ? 'flex' : 'none';
    pGalNext.style.display  = multi ? 'flex' : 'none';
    pGalCount.style.display = multi ? 'block': 'none';
    pThumbs.style.display   = multi ? 'flex' : 'none';
  }

  function openPopup(work){
    if(!work) return;
    galImages=[];
    if(work.featImg) galImages.push(work.featImg);
    if(work.gallery&&Array.isArray(work.gallery)){
      work.gallery.forEach(function(g){ if(g&&g!==work.featImg) galImages.push(g); });
    }
    // Build thumbs
    pThumbs.innerHTML='';
    galImages.forEach(function(img,i){
      var t=document.createElement('button');
      t.className='sg-wg-popup__thumb'+(i===0?' sg-wg-popup__thumb--active':'');
      var im=document.createElement('img'); im.src=img;
      t.appendChild(im);
      t.addEventListener('click',function(){ galShow(i); });
      pThumbs.appendChild(t);
    });
    pGallery.style.display = galImages.length ? 'block':'none';
    if(galImages.length) galShow(0);

    pCat.textContent   = work.category||'';
    pCat.style.display = work.category ? '' : 'none';
    pTitle.textContent = work.title||'';

    var metaParts=[work.tags,work.type,work.price].filter(Boolean);
    pMeta.textContent   = metaParts.join(' · ');
    pMeta.style.display = metaParts.length ? '' : 'none';

    pDesc.textContent   = work.description||'';
    pDesc.style.display = work.description ? '' : 'none';

    pCta.style.display  = work.url ? 'inline-flex' : 'none';
    if(work.url) pCta.href = work.url;

    popup.classList.add('sg-wg-popup--open');
    document.body.style.overflow='hidden';
  }

  function closePopup(){
    popup.classList.remove('sg-wg-popup--open');
    document.body.style.overflow='';
  }

  if(pGalPrev) pGalPrev.addEventListener('click',function(e){e.stopPropagation();galShow(galCurrent-1);});
  if(pGalNext) pGalNext.addEventListener('click',function(e){e.stopPropagation();galShow(galCurrent+1);});

  document.addEventListener('keydown',function(e){
    if(!popup.classList.contains('sg-wg-popup--open')) return;
    if(e.key==='ArrowLeft')  galShow(galCurrent-1);
    if(e.key==='ArrowRight') galShow(galCurrent+1);
    if(e.key==='Escape')     closePopup();
  });

  cards.forEach(function(card){
    card.addEventListener('click',function(){
      openPopup(works[parseInt(card.dataset.workIndex,10)]);
    });
    card.addEventListener('keydown',function(e){
      if(e.key==='Enter'||e.key===' '){ e.preventDefault(); openPopup(works[parseInt(card.dataset.workIndex,10)]); }
    });
  });

  if(pClose) pClose.addEventListener('click', closePopup);
  if(pBd)    pBd.addEventListener('click', closePopup);
};
})();
