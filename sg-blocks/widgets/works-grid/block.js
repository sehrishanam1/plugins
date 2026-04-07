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
  function safe(v){ return (v!=null)?String(v):''; }

  function defWork(i){
    return {
      featImg:'', featImgId:0, gallery:[],
      category:'Digital Marketing Agency',
      title:'Project '+(i+1),
      tags:'Performance Marketing · Enterprise Clients',
      type:'Full Website', price:'AED 14,999',
      url:'', description:'A brief description of this project.',
    };
  }

  window.wp.blocks.registerBlockType('sg-blocks/works-grid', {
    title   : 'Works Grid',
    icon    : 'portfolio',
    category: 'layout',
    keywords: ['works','portfolio','projects','slider','popup'],

    attributes: {
      showBadge        : {type:'boolean', default:true},
      badgeText        : {type:'string',  default:'Our Work'},
      headingLine1     : {type:'string',  default:'Built for'},
      headingAccent    : {type:'string',  default:'Premium'},
      headingLine2     : {type:'string',  default:'Service Businesses'},
      accentColor      : {type:'string',  default:'#00c8ff'},
      showSubheading   : {type:'boolean', default:true},
      subheading       : {type:'string',  default:'Every website we build is designed to position you as the best — not just the fastest to find.'},
      headingBgImage   : {type:'string',  default:''},
      headingBgImageId : {type:'integer', default:0},
      bgColor          : {type:'string',  default:'#080808'},
      cardBgColor      : {type:'string',  default:'#111111'},
      works            : {type:'string',  default:'[]'},
      cardsPerView     : {type:'number',  default:3},
      dotActiveColor   : {type:'string',  default:'#00c8ff'},
      dotInactiveColor : {type:'string',  default:'#1e1e1e'},
      showDots         : {type:'boolean', default:true},
      showArrows       : {type:'boolean', default:true},
      innerWidth       : {type:'string',  default:'1100px'},
    },

    edit: function(props){
      var attr = props.attributes;
      var set  = props.setAttributes;
      var works = parse(attr.works);

      if(works.length === 0){
        var seed = [defWork(0), defWork(1), defWork(2)];
        set({ works: JSON.stringify(seed) });
        works = seed;
      }

      function sw(i,k,v){
        // Always re-parse from latest attr to avoid stale closures
        var raw = props.attributes.works;
        var a = parse(raw);
        if(!a[i]) a[i] = {};
        a[i][k] = v;
        set({ works: JSON.stringify(a) });
      }
      function addWork(){
        var a = parse(props.attributes.works);
        a.push(defWork(a.length));
        set({ works: JSON.stringify(a) });
      }
      function rmWork(i){
        var a = parse(props.attributes.works);
        a.splice(i,1);
        set({ works: JSON.stringify(a) });
      }
      function mvWork(i,d){
        var a = parse(props.attributes.works), j = i+d;
        if(j<0||j>=a.length) return;
        var t=a[i]; a[i]=a[j]; a[j]=t;
        set({ works: JSON.stringify(a) });
      }
      function addGallery(i, items){
        var a = parse(props.attributes.works);
        if(!a[i]) return;
        var ex = a[i].gallery||[];
        (Array.isArray(items)?items:[items]).forEach(function(m){
          if(m&&m.url&&ex.indexOf(m.url)===-1) ex.push(m.url);
        });
        a[i].gallery = ex;
        set({ works: JSON.stringify(a) });
      }
      function rmGallery(i, url){
        var a = parse(props.attributes.works);
        if(!a[i]) return;
        a[i].gallery = (a[i].gallery||[]).filter(function(u){ return u!==url; });
        set({ works: JSON.stringify(a) });
      }

      /* ── SIDEBAR ── */
      var sidebar = el(IC, {},

        /* Section Header */
        el(PB, {title:'Section Header', initialOpen:true},
          el(TOG,{label:'Show Badge',checked:!!attr.showBadge,onChange:function(v){set({showBadge:v});}}),
          attr.showBadge && el(TC,{label:'Badge text',value:attr.badgeText||'',onChange:function(v){set({badgeText:v});}}),
          el(TC,{label:'Heading line 1',value:attr.headingLine1||'',onChange:function(v){set({headingLine1:v});}}),
          el(TC,{label:'Heading accent word',value:attr.headingAccent||'',onChange:function(v){set({headingAccent:v});}}),
          el(TC,{label:'Heading line 2',value:attr.headingLine2||'',onChange:function(v){set({headingLine2:v});}}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'8px 0 4px'}},'Accent color'),
          el(CP,{color:attr.accentColor||'#00c8ff',onChange:function(v){set({accentColor:v});},enableAlpha:false}),
          el(TOG,{label:'Show subheading',checked:!!attr.showSubheading,onChange:function(v){set({showSubheading:v});}}),
          attr.showSubheading && el(TA,{label:'Subheading',value:attr.subheading||'',rows:3,onChange:function(v){set({subheading:v});}})
        ),

        /* Layout & Colors */
        el(PB, {title:'Layout & Colors', initialOpen:false},
          el(RC,{label:'Cards visible at once',value:attr.cardsPerView||3,min:1,max:6,onChange:function(v){set({cardsPerView:v});}}),
          el(TC,{label:'Inner max-width',value:attr.innerWidth||'1100px',onChange:function(v){set({innerWidth:v});}}),
          el(TOG,{label:'Show dots',checked:!!attr.showDots,onChange:function(v){set({showDots:v});}}),
          el(TOG,{label:'Show arrows',checked:!!attr.showArrows,onChange:function(v){set({showArrows:v});}}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Section background color'),
          el(CP,{color:attr.bgColor||'#080808',onChange:function(v){set({bgColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Card background color'),
          el(CP,{color:attr.cardBgColor||'#111111',onChange:function(v){set({cardBgColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Active dot color'),
          el(CP,{color:attr.dotActiveColor||'#00c8ff',onChange:function(v){set({dotActiveColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Inactive dot color'),
          el(CP,{color:attr.dotInactiveColor||'#1e1e1e',onChange:function(v){set({dotInactiveColor:v});},enableAlpha:false}),
          el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px'}},'Heading section background image'),
          el(MUC, {},
            el(MU, {
              onSelect: function(m){ if(!m||!m.url) return; set({headingBgImage:safe(m.url), headingBgImageId:m.id||0}); },
              allowedTypes: ['image'],
              value: attr.headingBgImageId||0,
              render: function(p){
                return el('div', {},
                  attr.headingBgImage && el('img',{src:attr.headingBgImage,style:{width:'100%',maxHeight:'80px',objectFit:'cover',borderRadius:'6px',marginBottom:'6px'}}),
                  el('div',{style:{display:'flex',gap:'6px'}},
                    el(BTN,{onClick:p.open,variant:'secondary',isSmall:true}, attr.headingBgImage ? 'Replace Image' : 'Upload Image'),
                    attr.headingBgImage && el(BTN,{onClick:function(){set({headingBgImage:'',headingBgImageId:0});},variant:'link',isDestructive:true,isSmall:true},'Remove')
                  )
                );
              }
            })
          )
        ),

        /* Works Repeater */
        el(PB, {title:'Works ('+works.length+')', initialOpen:true},
          works.map(function(work, i){
            if(!work) return null;
            var gallery = work.gallery||[];
            return el(PB, {
              key: 'w'+i,
              title: (i+1)+'. '+(work.title||'Untitled'),
              initialOpen: false,
            },
              el('div',{style:{display:'flex',gap:'4px',marginBottom:'10px'}},
                el(BTN,{onClick:function(){mvWork(i,-1);},variant:'secondary',isSmall:true,disabled:i===0},'↑'),
                el(BTN,{onClick:function(){mvWork(i,1);},variant:'secondary',isSmall:true,disabled:i===works.length-1},'↓'),
                el(BTN,{onClick:function(){rmWork(i);},variant:'secondary',isDestructive:true,isSmall:true},'✕ Remove')
              ),

              el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 4px'}},'Featured image (shown in slider)'),
              el(MUC, {},
                el(MU, {
                  onSelect: function(m){
                    if(!m||!m.url) return;
                    var a = parse(props.attributes.works);
                    if(!a[i]) a[i] = {};
                    a[i].featImg   = safe(m.url);
                    a[i].featImgId = m.id || 0;
                    set({ works: JSON.stringify(a) });
                  },
                  allowedTypes:['image'],
                  value: work.featImgId || 0,
                  render: function(p){
                    return el('div',{style:{marginBottom:'12px'}},
                      work.featImg && el('img',{
                        src: work.featImg,
                        style:{width:'100%',maxHeight:'120px',objectFit:'cover',borderRadius:'6px',marginBottom:'6px',display:'block'}
                      }),
                      el('div',{style:{display:'flex',gap:'6px',flexWrap:'wrap'}},
                        el(BTN,{onClick:p.open,variant:'secondary',isSmall:true},
                          work.featImg ? 'Replace Featured Image' : '+ Upload Featured Image'
                        ),
                        work.featImg && el(BTN,{
                          onClick:function(){
                            var a=parse(props.attributes.works);
                            if(!a[i]) return;
                            a[i].featImg=''; a[i].featImgId=0;
                            set({works:JSON.stringify(a)});
                          },
                          variant:'link', isDestructive:true, isSmall:true
                        },'Remove')
                      )
                    );
                  }
                })
              ),

              el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 6px'}},'Gallery images (popup)'),
              gallery.length > 0 && el('div',{style:{display:'flex',flexWrap:'wrap',gap:'6px',marginBottom:'8px'}},
                gallery.map(function(imgUrl, gi){
                  return el('div',{key:'g'+gi,style:{position:'relative',width:'60px',height:'44px',borderRadius:'4px',overflow:'hidden',border:'1px solid #333'}},
                    el('img',{src:imgUrl,style:{width:'100%',height:'100%',objectFit:'cover',display:'block'}}),
                    el('button',{
                      onClick:function(){ rmGallery(i, imgUrl); },
                      style:{position:'absolute',top:'2px',right:'2px',width:'16px',height:'16px',borderRadius:'50%',border:'none',background:'rgba(0,0,0,0.75)',color:'#fff',fontSize:'10px',cursor:'pointer',display:'flex',alignItems:'center',justifyContent:'center',padding:'0',lineHeight:'1'}
                    },'×')
                  );
                })
              ),
              el(MUC, {},
                el(MU, {
                  onSelect: function(m){ if(!m) return; addGallery(i, Array.isArray(m)?m:[m]); },
                  allowedTypes:['image'], multiple:true, gallery:false,
                  render: function(p){
                    return el(BTN,{onClick:p.open,variant:'secondary',isSmall:true,style:{marginBottom:'12px'}},'+ Add Gallery Images');
                  }
                })
              ),

              el(TC,{label:'Category',   value:work.category||'',    onChange:function(v){sw(i,'category',v);}}),
              el(TC,{label:'Title',      value:work.title||'',       onChange:function(v){sw(i,'title',v);}}),
              el(TC,{label:'Tags',       value:work.tags||'',        onChange:function(v){sw(i,'tags',v);}}),
              el(TC,{label:'Work Type',  value:work.type||'',        onChange:function(v){sw(i,'type',v);}}),
              el(TC,{label:'Price',      value:work.price||'',       onChange:function(v){sw(i,'price',v);}}),
              el(TC,{label:'Project URL',value:work.url||'',         onChange:function(v){sw(i,'url',v);}}),
              el(TA,{label:'Description',value:work.description||'', rows:4, onChange:function(v){sw(i,'description',v);}})
            );
          }),
          el(BTN,{onClick:addWork,variant:'primary',style:{width:'100%',justifyContent:'center',marginTop:'10px'}},'+ Add Work')
        )
      );

      /* ── EDITOR PREVIEW ── */
      var cpv     = attr.cardsPerView||3;
      var preview = works.slice(0, cpv);
      var hBgStyle = {
        backgroundColor: attr.bgColor||'#080808',
        padding:'48px 40px 40px',
      };
      if(attr.headingBgImage){
        hBgStyle.backgroundImage    = 'url('+attr.headingBgImage+')';
        hBgStyle.backgroundSize     = 'cover';
        hBgStyle.backgroundPosition = 'center';
      }

      return el(Fr, {},
        sidebar,
        el('section', {className:'sg-wg', style:{background:attr.bgColor||'#080808'}},

          /* Heading section */
          el('div', {className:'sg-wg__heading-section', style: hBgStyle},
            el('div', {className:'sg-wg__inner', style:{maxWidth:attr.innerWidth||'1100px',margin:'0 auto'}},
              attr.showBadge && attr.badgeText &&
              el('div',{className:'sg-wg__badge-wrap'}, el('span',{className:'sg-wg__badge'}, attr.badgeText)),

              el('h2',{className:'sg-wg__heading'},
                (attr.headingLine1||attr.headingAccent) && el('span',{className:'sg-wg__h-row'},
                  attr.headingLine1 && el('span',{}, attr.headingLine1+' '),
                  attr.headingAccent && el('span',{style:{color:attr.accentColor||'#00c8ff'}}, attr.headingAccent)
                ),
                attr.headingLine2 && el('span',{className:'sg-wg__h-row'}, attr.headingLine2)
              ),

              attr.showSubheading && attr.subheading &&
              el('p',{className:'sg-wg__sub'}, attr.subheading)
            )
          ),

          /* Slider preview */
          el('div', {className:'sg-wg__slider-section'},
            el('div', {className:'sg-wg__inner', style:{maxWidth:attr.innerWidth||'1100px',margin:'0 auto'}},
              works.length === 0
              ? el('div',{style:{textAlign:'center',padding:'48px',color:'#444',border:'1px dashed #222',borderRadius:'12px',fontSize:'13px'}},'← Add works from the sidebar')
              : el('div',{style:{display:'flex',gap:'20px'}},
                  preview.map(function(w,i){
                    if(!w) return null;
                    var meta = [w.tags,w.type,w.price].filter(Boolean).join(' · ');
                    return el('div',{
                      key:'pv'+i,
                      className:'sg-wg__card',
                      style:{background:attr.cardBgColor||'#111',flex:'1',cursor:'default'}
                    },
                      el('div',{className:'sg-wg__card-img'},
                        w.featImg ? el('img',{src:w.featImg,alt:w.title||''}) : el('div',{className:'sg-wg__card-placeholder'})
                      ),
                      el('div',{className:'sg-wg__card-body'},
                        w.category && el('span',{className:'sg-wg__card-cat'}, w.category),
                        el('h3',{className:'sg-wg__card-title'}, w.title||''),
                        meta && el('p',{className:'sg-wg__card-meta'}, meta)
                      )
                    );
                  }),
                  works.length > cpv && el('div',{style:{display:'flex',alignItems:'center',justifyContent:'center',color:'#333',fontSize:'12px',border:'1px dashed #222',borderRadius:'12px',minHeight:'180px',padding:'20px',flex:'0 0 120px'}},
                    '+'+(works.length-cpv)+' more'
                  )
                ),

              /* Nav pill preview */
              el('div',{className:'sg-wg__nav-pill',style:{pointerEvents:'none',opacity:0.7}},
                el('button',{className:'sg-wg__nav-arr'},'<'),
                el('div',{className:'sg-wg__nav-dots'},
                  [1,2,3].map(function(n){
                    return el('button',{
                      key:n,
                      className:'sg-wg__nav-dot'+(n===1?' active':''),
                      style:{background:n===1?(attr.dotActiveColor||'#00c8ff'):(attr.dotInactiveColor||'#1e1e1e'), color:n===1?'#000':'#888'}
                    }, String(n));
                  })
                ),
                el('button',{className:'sg-wg__nav-arr'},'>')
              )
            )
          )
        )
      );
    },

    save: function(){ return null; }
  });
})();
