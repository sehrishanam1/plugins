( function () {

    var el       = window.wp.element.createElement;
    var Fragment = window.wp.element.Fragment;

    var InspectorControls = window.wp.blockEditor.InspectorControls;
    var MediaUpload       = window.wp.blockEditor.MediaUpload;
    var MediaUploadCheck  = window.wp.blockEditor.MediaUploadCheck;

    var PanelBody       = window.wp.components.PanelBody;
    var TextControl     = window.wp.components.TextControl;
    var TextareaControl = window.wp.components.TextareaControl;
    var ToggleControl   = window.wp.components.ToggleControl;
    var ColorPicker     = window.wp.components.ColorPicker;
    var Button          = window.wp.components.Button;

    /* ── Helpers ── */
    function parseFaqs( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; } catch(e) { return []; }
    }

    function defaultFaqs() {
        return [
            {
                question    : 'How long does it actually take?',
                answer      : '2 weeks for a Landing Page. 6 weeks for a Full Website. This timeline starts once we receive your content brief. We\'ve never missed a deadline without warning a client first.',
                openByDefault: true,
            },
            { question: 'Do I need to provide content and photos?',     answer: '', openByDefault: false },
            { question: 'Will I be able to edit the website myself?',    answer: '', openByDefault: false },
            { question: "What if I don't like the design?",             answer: '', openByDefault: false },
            { question: 'Do you work with businesses outside Dubai?',    answer: '', openByDefault: false },
            { question: 'What happens after the website launches?',      answer: '', openByDefault: false },
        ];
    }

    /* ── Chevron SVG ── */
    function chevronSvg( rotated ) {
        return el('svg',{
            width:'20', height:'20', viewBox:'0 0 24 24',
            fill:'none', stroke:'currentColor', strokeWidth:'2',
            strokeLinecap:'round', strokeLinejoin:'round',
            style:{ display:'block', transition:'transform 0.28s', transform: rotated ? 'rotate(180deg)' : 'rotate(0deg)' }
        }, el('polyline',{ points:'6 9 12 15 18 9' }));
    }

    /* ═══════════════════════════
       Register block
    ═══════════════════════════ */
    window.wp.blocks.registerBlockType( 'sg-blocks/faq-section', {
        title    : 'FAQ Section',
        icon     : 'editor-help',
        category : 'layout',
        keywords : [ 'faq', 'accordion', 'questions', 'answers' ],

        attributes: {
            showBadge        : { type:'boolean', default:true },
            badgeText        : { type:'string',  default:'FAQs' },
            heading          : { type:'string',  default:'Frequently Asked Questions' },
            subheading       : { type:'string',  default:'Questions We Get Asked Before They Hit "Submit"' },
            showSubheading   : { type:'boolean', default:true },
            bgColor          : { type:'string',  default:'#0a0a0a' },
            bgImage          : { type:'string',  default:'' },
            bgImageId        : { type:'integer', default:0 },
            bgOverlayColor   : { type:'string',  default:'rgba(10,10,10,0.82)' },
            bgOverlayEnable  : { type:'boolean', default:true },
            headingColor     : { type:'string',  default:'#ffffff' },
            subheadingColor  : { type:'string',  default:'#888888' },
            badgeBorderColor : { type:'string',  default:'#333333' },
            badgeTextColor   : { type:'string',  default:'#aaaaaa' },
            itemBg           : { type:'string',  default:'#141414' },
            itemBorderColor  : { type:'string',  default:'#222222' },
            questionColor    : { type:'string',  default:'#ffffff' },
            answerColor      : { type:'string',  default:'#888888' },
            iconColor        : { type:'string',  default:'#666666' },
            iconActiveColor  : { type:'string',  default:'#ffffff' },
            innerWidth       : { type:'string',  default:'680px' },
            faqs             : { type:'string',  default:'[]' },
        },

        edit: function ( props ) {
            var attr = props.attributes;
            var set  = props.setAttributes;
            var faqs = parseFaqs( attr.faqs );

            /* Seed defaults */
            if ( faqs.length === 0 ) {
                var seed = defaultFaqs();
                set({ faqs: JSON.stringify( seed ) });
                faqs = seed;
            }

            /* Track which item is open in editor preview */
            var openIndex = faqs.findIndex(function(f){ return f.openByDefault; });
            if ( openIndex === -1 ) openIndex = 0;

            /* ── FAQ helpers ── */
            function setFaq( i, key, val ) {
                var arr = parseFaqs( attr.faqs );
                if ( ! arr[i] ) arr[i] = {};
                arr[i][key] = val;
                set({ faqs: JSON.stringify(arr) });
            }
            function addFaq() {
                var arr = parseFaqs( attr.faqs );
                arr.push({ question:'New Question', answer:'', openByDefault:false });
                set({ faqs: JSON.stringify(arr) });
            }
            function removeFaq( i ) {
                var arr = parseFaqs( attr.faqs );
                arr.splice(i,1);
                set({ faqs: JSON.stringify(arr) });
            }
            function moveFaq( i, dir ) {
                var arr = parseFaqs( attr.faqs );
                var sw  = i + dir;
                if ( sw < 0 || sw >= arr.length ) return;
                var tmp = arr[i]; arr[i] = arr[sw]; arr[sw] = tmp;
                set({ faqs: JSON.stringify(arr) });
            }

            /* ═══════════════ SIDEBAR ═══════════════ */
            var sidebar = el( InspectorControls, {},

                /* Header */
                el( PanelBody, { title:'Section Header', initialOpen:true },
                    el( ToggleControl, { label:'Show Badge', checked:!!attr.showBadge, onChange:function(v){ set({showBadge:v}); } }),
                    attr.showBadge && el( TextControl, { label:'Badge text', value:attr.badgeText||'', onChange:function(v){ set({badgeText:v}); } }),
                    el( TextControl, { label:'Heading', value:attr.heading||'', onChange:function(v){ set({heading:v}); } }),
                    el( ToggleControl, { label:'Show Subheading', checked:!!attr.showSubheading, onChange:function(v){ set({showSubheading:v}); } }),
                    attr.showSubheading && el( TextControl, { label:'Subheading', value:attr.subheading||'', onChange:function(v){ set({subheading:v}); } })
                ),

                /* Background */
                el( PanelBody, { title:'Background', initialOpen:false },
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 4px'}}, 'Background color'),
                    el( ColorPicker, { color:attr.bgColor||'#0a0a0a', onChange:function(v){ set({bgColor:v}); }, enableAlpha:false }),

                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'14px 0 6px'}}, 'Background image'),
                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect: function(m){
                                if(!m||!m.url) return;
                                set({ bgImage: m.url, bgImageId: m.id||0 });
                            },
                            allowedTypes: ['image'],
                            value: attr.bgImageId || 0,
                            render: function(p){
                                return el('div',{},
                                    attr.bgImage && el('div',{style:{marginBottom:'8px'}},
                                        el('img',{ src:attr.bgImage, style:{ width:'100%', borderRadius:'6px', opacity:0.75, maxHeight:'120px', objectFit:'cover' } }),
                                        el( Button, {
                                            onClick:function(){ set({ bgImage:'', bgImageId:0 }); },
                                            variant:'link', isDestructive:true, isSmall:true,
                                            style:{marginTop:'4px'}
                                        }, 'Remove image')
                                    ),
                                    el( Button, { onClick:p.open, variant:'secondary', isSmall:true },
                                        attr.bgImage ? 'Replace image' : 'Upload image'
                                    )
                                );
                            }
                        })
                    ),

                    attr.bgImage && el( Fragment, { key:'overlay-fields' },
                        el( ToggleControl, {
                            label:'Enable overlay', checked:!!attr.bgOverlayEnable,
                            onChange:function(v){ set({bgOverlayEnable:v}); }
                        }),
                        attr.bgOverlayEnable && el( Fragment, { key:'overlay-color' },
                            el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'8px 0 4px'}}, 'Overlay color (use alpha slider)'),
                            el( ColorPicker, { color:attr.bgOverlayColor||'rgba(10,10,10,0.82)', onChange:function(v){ set({bgOverlayColor:v}); }, enableAlpha:true })
                        )
                    ),

                    el( TextControl, { label:'Inner max-width (e.g. 680px)', value:attr.innerWidth||'680px', onChange:function(v){ set({innerWidth:v}); } })
                ),

                /* Typography / Colors */
                el( PanelBody, { title:'Colors', initialOpen:false },
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 4px'}}, 'Heading color'),
                    el( ColorPicker, { color:attr.headingColor||'#ffffff', onChange:function(v){ set({headingColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Subheading color'),
                    el( ColorPicker, { color:attr.subheadingColor||'#888888', onChange:function(v){ set({subheadingColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Badge border color'),
                    el( ColorPicker, { color:attr.badgeBorderColor||'#333333', onChange:function(v){ set({badgeBorderColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Badge text color'),
                    el( ColorPicker, { color:attr.badgeTextColor||'#aaaaaa', onChange:function(v){ set({badgeTextColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Item background'),
                    el( ColorPicker, { color:attr.itemBg||'#141414', onChange:function(v){ set({itemBg:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Item border color'),
                    el( ColorPicker, { color:attr.itemBorderColor||'#222222', onChange:function(v){ set({itemBorderColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Question text color'),
                    el( ColorPicker, { color:attr.questionColor||'#ffffff', onChange:function(v){ set({questionColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Answer text color'),
                    el( ColorPicker, { color:attr.answerColor||'#888888', onChange:function(v){ set({answerColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Icon color (closed)'),
                    el( ColorPicker, { color:attr.iconColor||'#666666', onChange:function(v){ set({iconColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Icon color (open)'),
                    el( ColorPicker, { color:attr.iconActiveColor||'#ffffff', onChange:function(v){ set({iconActiveColor:v}); }, enableAlpha:false })
                ),

                /* FAQs */
                el( PanelBody, { title:'FAQ Items (' + faqs.length + ')', initialOpen:true },

                    faqs.map(function(faq,i){
                        var label = faq.question ? ( faq.question.length > 40 ? faq.question.slice(0,40)+'…' : faq.question ) : 'Question '+(i+1);
                        return el( PanelBody, {
                            key:'faq-'+i,
                            title: label,
                            initialOpen: false,
                        },
                            el('div',{style:{display:'flex',gap:'6px',marginBottom:'10px'}},
                                el(Button,{onClick:function(){moveFaq(i,-1);},variant:'secondary',isSmall:true,disabled:i===0},'↑ Up'),
                                el(Button,{onClick:function(){moveFaq(i,1);},variant:'secondary',isSmall:true,disabled:i===faqs.length-1},'↓ Down'),
                                el(Button,{onClick:function(){removeFaq(i);},variant:'secondary',isDestructive:true,isSmall:true},'✕ Remove')
                            ),
                            el( TextControl, {
                                label:'Question',
                                value:faq.question||'',
                                onChange:function(v){ setFaq(i,'question',v); }
                            }),
                            el( TextareaControl, {
                                label:'Answer',
                                value:faq.answer||'',
                                rows:4,
                                onChange:function(v){ setFaq(i,'answer',v); }
                            }),
                            el( ToggleControl, {
                                label:'Open by default',
                                checked:!!faq.openByDefault,
                                onChange:function(v){ setFaq(i,'openByDefault',v); }
                            })
                        );
                    }),

                    el( Button, {
                        onClick:addFaq,
                        variant:'primary',
                        style:{width:'100%',justifyContent:'center',marginTop:'10px'}
                    }, '+ Add FAQ Item')
                )
            ); /* end sidebar */

            /* ═══════════════ EDITOR PREVIEW ═══════════════ */
            var sectionStyle = { backgroundColor: attr.bgColor||'#0a0a0a', position:'relative', overflow:'hidden' };
            if ( attr.bgImage ) {
                sectionStyle.backgroundImage    = 'url(' + attr.bgImage + ')';
                sectionStyle.backgroundSize     = 'cover';
                sectionStyle.backgroundPosition = 'center center';
                sectionStyle.backgroundRepeat   = 'no-repeat';
            }

            return el( Fragment, {},
                sidebar,

                el('section',{ className:'sg-faq', style:sectionStyle },

                    /* Overlay */
                    attr.bgImage && attr.bgOverlayEnable && el('div',{
                        className:'sg-faq__overlay',
                        style:{ position:'absolute', inset:0, zIndex:1, background: attr.bgOverlayColor||'rgba(10,10,10,0.82)', pointerEvents:'none' }
                    }),

                    el('div',{ className:'sg-faq__inner', style:{ maxWidth: attr.innerWidth||'680px', margin:'0 auto', position:'relative', zIndex:2 } },

                        /* Badge */
                        attr.showBadge && attr.badgeText &&
                        el('div',{ className:'sg-faq__badge-wrap' },
                            el('span',{ className:'sg-faq__badge', style:{ borderColor: attr.badgeBorderColor||'#333', color: attr.badgeTextColor||'#aaa' } },
                                attr.badgeText
                            )
                        ),

                        /* Heading */
                        attr.heading &&
                        el('h2',{ className:'sg-faq__heading', style:{ color: attr.headingColor||'#fff' } }, attr.heading),

                        /* Subheading */
                        attr.showSubheading && attr.subheading &&
                        el('p',{ className:'sg-faq__subheading', style:{ color: attr.subheadingColor||'#888' } }, attr.subheading),

                        /* FAQ items */
                        faqs.length > 0 &&
                        el('div',{ className:'sg-faq__list', style:{
                            '--sg-faq-item-bg'     : attr.itemBg          || '#141414',
                            '--sg-faq-item-border' : attr.itemBorderColor || '#222222',
                            '--sg-faq-q-color'     : attr.questionColor   || '#ffffff',
                            '--sg-faq-a-color'     : attr.answerColor     || '#888888',
                            '--sg-faq-icon'        : attr.iconColor       || '#666666',
                            '--sg-faq-icon-active' : attr.iconActiveColor || '#ffffff',
                        }},
                            faqs.map(function(faq,i){
                                var isOpen = !!faq.openByDefault;
                                return el('div',{
                                    key:'prev-'+i,
                                    className:'sg-faq__item' + (isOpen ? ' sg-faq__item--open' : ''),
                                    style:{
                                        background   : attr.itemBg          || '#141414',
                                        border       : '1px solid ' + ( isOpen ? 'rgba(255,255,255,0.18)' : (attr.itemBorderColor||'#222') ),
                                        borderRadius : '10px',
                                        overflow     : 'hidden',
                                        marginBottom : '0',
                                    }
                                },
                                    /* Question row */
                                    el('div',{
                                        style:{
                                            display:'flex', alignItems:'center', justifyContent:'space-between',
                                            gap:'16px', padding:'20px 24px', cursor:'default',
                                        }
                                    },
                                        el('span',{style:{ fontSize:'15px', fontWeight:'500', color: attr.questionColor||'#fff', flex:1, lineHeight:'1.4' }},
                                            faq.question || '(empty question)'
                                        ),
                                        el('span',{style:{
                                            flexShrink:0, display:'flex', alignItems:'center', justifyContent:'center',
                                            width:'28px', height:'28px',
                                            color: isOpen ? (attr.iconActiveColor||'#fff') : (attr.iconColor||'#666'),
                                        }},
                                            chevronSvg(isOpen)
                                        )
                                    ),
                                    /* Answer */
                                    isOpen && faq.answer &&
                                    el('div',{ style:{ padding:'0 24px 20px', fontSize:'14px', lineHeight:'1.75', color: attr.answerColor||'#888' } },
                                        faq.answer
                                    )
                                );
                            })
                        )
                    )
                )
            );
        },

        save: function() { return null; }
    });

} )();
