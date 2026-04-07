(function(){
  var el  = window.wp.element.createElement;
  var Fr  = window.wp.element.Fragment;
  var IC  = window.wp.blockEditor.InspectorControls;
  var MU  = window.wp.blockEditor.MediaUpload;
  var MUC = window.wp.blockEditor.MediaUploadCheck;
  var PB  = window.wp.components.PanelBody;
  var TC  = window.wp.components.TextControl;
  var TA  = window.wp.components.TextareaControl;
  var TOG = window.wp.components.ToggleControl;
  var RC  = window.wp.components.RangeControl;
  var CP  = window.wp.components.ColorPicker;
  var BTN = window.wp.components.Button;

  function parse(s){ try{ return JSON.parse(s)||[]; }catch(e){ return []; } }
  function safe(v){ return v!=null?String(v):''; }

  function defSlide(i){
    return {
      photo:'', photoId:0,
      name:'Sarah William',
      role:'Market Manager, Greenfields Farmers\' Market',
      quote:'"We used to juggle spreadsheets and calls. Now, with one dashboard for bookings, payments, and promotions, our market runs smoother — and I\'ve got my weekends back"',
      stats:[
        {number:'42', label:'Total Markets'},
        {number:'37', label:'Market Operators'},
        {number:'529', label:'Stallholders'},
        {number:'$149', label:'Average Booking'},
      ],
    };
  }

  window.wp.blocks.registerBlockType('sg-blocks/testimonials-slider',{
    title   : 'Testimonials Slider',
    icon    : 'format-quote',
    category: 'layout',
    keywords: ['testimonials','reviews','slider','quotes'],

    attributes:{
      badgeText      :{type:'string',  default:'Testimonials'},
      showBadge      :{type:'boolean', default:true},
      heading        :{type:'string',  default:'Businesses Speak for Themselves'},
      subheading     :{type:'string',  default:'Discover genuine stories from real people sharing their experiences and how they benefited by working with us.'},
      showSubheading :{type:'boolean', default:true},
      slides         :{type:'string',  default:'[]'},
      bgColor        :{type:'string',  default:'#0a0a0a'},
      cardBgColor    :{type:'string',  default:'#181818'},
      cardBorderColor:{type:'string',  default:'#2a2a2a'},
      statsBgColor   :{type:'string',  default:'#111111'},
      statsBorderColor:{type:'string', default:'#222222'},
      dotActiveColor :{type:'string',  default:'#00c8ff'},
      dotInactiveColor:{type:'string', default:'#1e1e1e'},
      accentColor    :{type:'string',  default:'#00c8ff'},
      innerWidth     :{type:'string',  default:'820px'},
      autoPlay       :{type:'boolean', default:false},
      autoPlayDelay  :{type:'number',  default:5000},
    },

    edit:function(props){
      var attr   = props.attributes;
      var set    = props.setAttributes;
      var slides = parse(attr.slides);

      if(slides.length===0){
        var seed = [defSlide(0), defSlide(1), defSlide(2)];
        set({slides:JSON.stringify(seed)});
        slides = seed;
      }

      /* helpers — always read from props.attributes.slides */
      function getSlides(){ return parse(props.attributes.slides); }

      function setSlide(i, k, v){
        var a=getSlides();
        if(!a[i]) a[i]={};
        a[i][k]=v;
        set({slides:JSON.stringify(a)});
      }

      function setStat(i, si, k, v){
        var a=getSlides();
        if(!a[i]) return;
        if(!a[i].stats) a[i].stats=[];
        if(!a[i].stats[si]) a[i].stats[si]={};
        a[i].stats[si][k]=v;
        set({slides:JSON.stringify(a)});
      }

      function addStat(i){
        var a=getSlides();
        if(!a[i]) return;
        if(!a[i].stats) a[i].stats=[];
        a[i].stats.push({number:'0', label:'Label'});
        set({slides:JSON.stringify(a)});
      }

      function rmStat(i, si){
        var a=getSlides();
        if(!a[i]||!a[i].stats) return;
        a[i].stats.splice(si,1);
        set({slides:JSON.stringify(a)});
      }

      function addSlide(){
        var a=getSlides();
        a.push(defSlide(a.length));
        set({slides:JSON.stringify(a)});
      }

      function rmSlide(i){
        var a=getSlides();
        a.splice(i,1);
        set({slides:JSON.stringify(a)});
      }

      function mvSlide(i,d){
        var a=getSlides(), j=i+d;
        if(j<0||j>=a.length) return;
        var t=a[i]; a[i]=a[j]; a[j]=t;
        set({slides:JSON.stringify(a)});
      }

      /* ── SIDEBAR ── */
      var sidebar = el(IC,{},

        /* Section header */
        el(PB,{title:'Section Header',initialOpen:true},
          el(TOG,{label:'Show Badge',checked:!!attr.showBadge,onChange:function(v){set({showBadge:v});}}),
          attr.showBadge && el(TC,{label:'Badge text',value:attr.badgeText||'',onChange:function(v){set({badgeText:v});}}),
          el(TC,{label:'Heading',value:attr.heading||'',onChange:function(v){set({heading:v});}}),
          el(TOG,{label:'Show Subheading',checked:!!attr.showSubheading,onChange:function(v){set({showSubheading:v});}}),
          attr.showSubheading && el(TA,{label:'Subheading',value:attr.subheading||'',rows:3,onChange:function(v){set({subheading:v});}})
        ),

        /* Slides repeater */
        el(PB,{title:'Slides ('+slides.length+')',initialOpen:true},
          slides.map(function(slide,i){
            if(!slide) return null;
            var stats = slide.stats||[];
            return el(PB,{
              key:'sl'+i,
              title:'Slide '+(i+1)+(slide.name?' — '+slide.name:''),
              initialOpen:false,
            },
              /* Move/Remove */
              el('div',{style:{display:'flex',gap:'4px',marginBottom:'10px'}},
                el(BTN,{onClick:function(){mvSlide(i,-1);},variant:'secondary',isSmall:true,disabled:i===0},'↑'),
                el(BTN,{onClick:function(){mvSlide(i,1);},variant:'secondary',isSmall:true,disabled:i===slides.length-1},'↓'),
                el(BTN,{onClick:function(){rmSlide(i);},variant:'secondary',isDestructive:true,isSmall:true},'✕ Remove')
              ),

              /* Photo upload */
              el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 4px'}},'Person photo'),
              el(MUC,{},
                el(MU,{
                  onSelect:function(m){
                    if(!m||!m.url) return;
                    var a=getSlides();
                    if(!a[i]) a[i]={};
                    a[i].photo   = safe(m.url);
                    a[i].photoId = m.id||0;
                    set({slides:JSON.stringify(a)});
                  },
                  allowedTypes:['image'], value:slide.photoId||0,
                  render:function(p){
                    return el('div',{style:{marginBottom:'12px'}},
                      slide.photo && el('div',{style:{display:'flex',alignItems:'center',gap:'10px',marginBottom:'8px'}},
                        el('img',{src:slide.photo,style:{width:'60px',height:'60px',objectFit:'cover',borderRadius:'50%',border:'2px dashed #444'}}),
                        el(BTN,{onClick:function(){
                          var a=getSlides();
                          if(!a[i]) return;
                          a[i].photo=''; a[i].photoId=0;
                          set({slides:JSON.stringify(a)});
                        },variant:'link',isDestructive:true,isSmall:true},'Remove')
                      ),
                      el(BTN,{onClick:p.open,variant:'secondary',isSmall:true},slide.photo?'Replace Photo':'+ Upload Photo')
                    );
                  }
                })
              ),

              el(TC,{label:'Name',value:slide.name||'',onChange:function(v){setSlide(i,'name',v);}}),
              el(TC,{label:'Role / Company',value:slide.role||'',onChange:function(v){setSlide(i,'role',v);}}),
              el(TA,{label:'Quote',value:slide.quote||'',rows:4,onChange:function(v){setSlide(i,'quote',v);}}),

              /* Stats */
              el('p',{style:{fontSize:'11px',fontWeight:'600',margin:'12px 0 8px',borderTop:'1px solid #333',paddingTop:'12px'}},'Stats (shown in bottom bar)'),
              stats.map(function(stat,si){
                if(!stat) return null;
                return el('div',{key:'st'+si,style:{background:'#1a1a1a',borderRadius:'6px',padding:'8px 10px',marginBottom:'8px'}},
                  el('div',{style:{display:'flex',justifyContent:'space-between',alignItems:'center',marginBottom:'6px'}},
                    el('span',{style:{fontSize:'11px',color:'#888'}},'Stat '+(si+1)),
                    el(BTN,{onClick:function(){rmStat(i,si);},variant:'link',isDestructive:true,isSmall:true},'✕')
                  ),
                  el(TC,{label:'Number / Value',value:stat.number||'',onChange:function(v){setStat(i,si,'number',v);}}),
                  el(TC,{label:'Label',value:stat.label||'',onChange:function(v){setStat(i,si,'label',v);}})
                );
              }),
              el(BTN,{onClick:function(){addStat(i);},variant:'secondary',isSmall:true,style:{width:'100%',justifyContent:'center',marginTop:'4px'}},'+ Add Stat')
            );
          }),
          el(BTN,{onClick:addSlide,variant:'primary',style:{width:'100%',justifyContent:'center',marginTop:'10px'}},'+ Add Slide')
        ),

        /* Style */
        el(PB,{title:'Colors & Style',initialOpen:false},
          el(TC,{label:'Inner max-width',value:attr.innerWidth||'820px',onChange:function(v){set({innerWidth:v});}}),
          el(TOG,{label:'Auto-play',checked:!!attr.autoPlay,onChange:function(v){set({autoPlay:v});}}),
          attr.autoPlay && el(RC,{label:'Auto-play delay (ms)',value:attr.autoPlayDelay||5000,min:1000,max:10000,step:500,onChange:function(v){set({autoPlayDelay:v});}}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Section background'),
          el(CP,{color:attr.bgColor||'#0a0a0a',onChange:function(v){set({bgColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Card background'),
          el(CP,{color:attr.cardBgColor||'#181818',onChange:function(v){set({cardBgColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Card border'),
          el(CP,{color:attr.cardBorderColor||'#2a2a2a',onChange:function(v){set({cardBorderColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Stats bar background'),
          el(CP,{color:attr.statsBgColor||'#111111',onChange:function(v){set({statsBgColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Stats border color'),
          el(CP,{color:attr.statsBorderColor||'#222222',onChange:function(v){set({statsBorderColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Active dot color'),
          el(CP,{color:attr.dotActiveColor||'#00c8ff',onChange:function(v){set({dotActiveColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Inactive dot color'),
          el(CP,{color:attr.dotInactiveColor||'#1e1e1e',onChange:function(v){set({dotInactiveColor:v});},enableAlpha:false})
        )
      );

      /* ── EDITOR PREVIEW ── */
      var preview    = slides[0] || {};
      var pStats     = preview.stats||[];
      var dotActive  = attr.dotActiveColor||'#00c8ff';
      var dotInactive= attr.dotInactiveColor||'#1e1e1e';
      var iw         = attr.innerWidth||'1100px';

      return el(Fr,{},
        sidebar,
        el('section',{className:'sg-ts',style:{background:attr.bgColor||'#0a0a0a'}},
          el('div',{className:'sg-ts__inner'},

            /* Header */
            el('div',{style:{maxWidth:iw,margin:'0 auto',padding:'0 40px',boxSizing:'border-box'}},
              attr.showBadge && attr.badgeText &&
              el('div',{className:'sg-ts__badge-wrap'}, el('span',{className:'sg-ts__badge'}, attr.badgeText)),
              attr.heading &&
              el('h2',{className:'sg-ts__heading'}, attr.heading),
              attr.showSubheading && attr.subheading &&
              el('p',{className:'sg-ts__subheading'}, attr.subheading)
            ),

            /* Slider box */
            slides.length > 0 &&
            el('div',{className:'sg-ts__slider',style:{'--sg-ts-width':iw}},
              el('div',{className:'sg-ts__slider-inner'},

                /* Card preview */
                el('div',{className:'sg-ts__track-wrap'},
                  el('div',{className:'sg-ts__track'},
                    el('div',{className:'sg-ts__slide'},
                      el('div',{className:'sg-ts__card',style:{background:attr.cardBgColor||'#181818',borderColor:attr.cardBorderColor||'#2a2a2a'}},
                        el('div',{className:'sg-ts__card-inner'},
                          el('div',{className:'sg-ts__photo-wrap'},
                            preview.photo
                            ? el('img',{className:'sg-ts__photo',src:preview.photo,alt:preview.name||''})
                            : el('div',{className:'sg-ts__photo-placeholder'},
                                el('svg',{width:'44',height:'44',viewBox:'0 0 24 24',fill:'none',stroke:'#444',strokeWidth:'1.5'},
                                  el('path',{d:'M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2'}),
                                  el('circle',{cx:'12',cy:'7',r:'4'})
                                )
                              )
                          ),
                          el('div',{className:'sg-ts__content'},
                            preview.quote && el('p',{className:'sg-ts__quote'}, preview.quote),
                            el('div',{className:'sg-ts__divider'}),
                            preview.name && el('p',{className:'sg-ts__name'}, preview.name),
                            preview.role && el('p',{className:'sg-ts__role'}, preview.role)
                          )
                        )
                      )
                    )
                  )
                ),

                /* Nav pill */
                slides.length > 1 &&
                el('div',{className:'sg-ts__nav-wrap'},
                  el('div',{className:'sg-ts__nav'},
                    el('button',{className:'sg-ts__nav-arr'},
                      el('svg',{width:'16',height:'16',viewBox:'0 0 24 24',fill:'none',stroke:'currentColor',strokeWidth:'2.5',strokeLinecap:'round',strokeLinejoin:'round'},
                        el('polyline',{points:'15 18 9 12 15 6'})
                      )
                    ),
                    el('div',{className:'sg-ts__nav-dots'},
                      slides.map(function(s,si){
                        return el('button',{
                          key:'nd'+si,
                          className:'sg-ts__nav-dot'+(si===0?' active':''),
                          style:{background:si===0?dotActive:dotInactive,color:si===0?'#000':'#888'}
                        }, String(si+1));
                      })
                    ),
                    el('button',{className:'sg-ts__nav-arr'},
                      el('svg',{width:'16',height:'16',viewBox:'0 0 24 24',fill:'none',stroke:'currentColor',strokeWidth:'2.5',strokeLinecap:'round',strokeLinejoin:'round'},
                        el('polyline',{points:'9 18 15 12 9 6'})
                      )
                    )
                  )
                )
              ),

              /* Stats bar — full width, outside card */
              pStats.length > 0 &&
              el('div',{className:'sg-ts__stats',style:{background:attr.statsBgColor||'#0a0a0a',borderTopColor:attr.statsBorderColor||'#222',display:'flex'}},
                pStats.map(function(s,si){
                  return el('div',{
                    key:'ps'+si,
                    className:'sg-ts__stat',
                    style:{borderColor:attr.statsBorderColor||'#222'}
                  },
                    el('span',{className:'sg-ts__stat-num'}, s.number||''),
                    el('span',{className:'sg-ts__stat-label'}, s.label||'')
                  );
                })
              )
            )
          )
        )
      );
    },

    save:function(){ return null; }
  });
})();
