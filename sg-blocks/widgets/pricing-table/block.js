( function () {

    var el           = window.wp.element.createElement;
    var Fragment     = window.wp.element.Fragment;

    var InspectorControls = window.wp.blockEditor.InspectorControls;
    var MediaUpload       = window.wp.blockEditor.MediaUpload;
    var MediaUploadCheck  = window.wp.blockEditor.MediaUploadCheck;

    var PanelBody       = window.wp.components.PanelBody;
    var TextControl     = window.wp.components.TextControl;
    var TextareaControl = window.wp.components.TextareaControl;
    var ToggleControl   = window.wp.components.ToggleControl;
    var ColorPicker     = window.wp.components.ColorPicker;
    var Button          = window.wp.components.Button;
    var SelectControl   = window.wp.components.SelectControl;

    function parsePlans( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; }
        catch(e) { return []; }
    }

    function safeStr( v ) {
        return ( v !== null && v !== undefined ) ? String( v ) : '';
    }

    function defaultPlan( index ) {
        var isFeatured = index === 1;
        return {
            price       : index === 0 ? 'AED 4,999' : 'AED 14,999',
            name        : index === 0 ? 'Landing Page' : 'Full Website',
            desc        : index === 0 ? 'Best for new businesses and quick market tests.' : 'Best for established firms and brand authority.',
            features    : index === 0
                ? "Single landing page\nProfessional copywriting included\nMobile-first development\nLead form integration\nDelivered in 2-3 weeks"
                : "Multi-page\nCustom brand design system\nWhatsApp + form + CTAs\nBlog and news section\nDelivered in 3-4 weeks",
            btnText     : index === 0 ? 'Get Your Landing Page' : 'Get Your Full Website',
            btnUrl      : '#',
            btnNewTab   : false,
            featured    : isFeatured,
            cardBg      : isFeatured ? '#0d7a72' : '#1a1a1a',
            cardBorder  : isFeatured ? '#0d9488' : '#2a2a2a',
            priceColor  : '#ffffff',
            checkColor  : isFeatured ? 'rgba(255,255,255,0.75)' : '#555555',
        };
    }

    window.wp.blocks.registerBlockType( 'sg-blocks/pricing-table', {
        title    : 'Pricing Table',
        icon     : 'tag',
        category : 'layout',
        keywords : [ 'pricing', 'plans', 'table', 'packages' ],

        attributes: {
            showBadge       : { type:'boolean', default:true },
            badgeText       : { type:'string',  default:'What We Build — Service Tiers' },
            showHeading     : { type:'boolean', default:true },
            heading         : { type:'string',  default:'Choose Your Website' },
            showSubheading  : { type:'boolean', default:true },
            subheading      : { type:'string',  default:'Both are custom-built. Zero templates. 100% yours.' },
            showFooterNote  : { type:'boolean', default:true },
            footerNoteLabel : { type:'string',  default:'HOSTING:' },
            footerNoteText  : { type:'string',  default:'AED 99/mo or AED 299/mo' },
            bgColor         : { type:'string',  default:'#050505' },
            bgImage         : { type:'string',  default:'' },
            bgImageId       : { type:'integer', default:0 },
            bgImageSide     : { type:'string',  default:'left' },
            innerWidth      : { type:'string',  default:'900px' },
            plans           : { type:'string',  default:'[]' },
        },

        edit: function ( props ) {
            var attr  = props.attributes;
            var set   = props.setAttributes;
            var plans = parsePlans( attr.plans );

            // If no plans yet, seed with 2 defaults
            if ( plans.length === 0 ) {
                var seeded = [ defaultPlan(0), defaultPlan(1) ];
                set({ plans: JSON.stringify( seeded ) });
                plans = seeded;
            }

            function setPlan( index, key, val ) {
                var arr = parsePlans( attr.plans );
                if ( ! arr[ index ] ) arr[ index ] = {};
                arr[ index ][ key ] = val;
                set({ plans: JSON.stringify( arr ) });
            }

            function addPlan() {
                var arr = parsePlans( attr.plans );
                arr.push( defaultPlan( arr.length ) );
                set({ plans: JSON.stringify( arr ) });
            }

            function removePlan( index ) {
                var arr = parsePlans( attr.plans );
                arr.splice( index, 1 );
                set({ plans: JSON.stringify( arr ) });
            }

            function movePlan( index, dir ) {
                var arr  = parsePlans( attr.plans );
                var swap = index + dir;
                if ( swap < 0 || swap >= arr.length ) return;
                var tmp    = arr[ index ];
                arr[ index ] = arr[ swap ];
                arr[ swap ]  = tmp;
                set({ plans: JSON.stringify( arr ) });
            }

            /* ── Sidebar ── */
            var sidebar = el( InspectorControls, {},

                /* Section Header */
                el( PanelBody, { title:'Section Header', initialOpen:true },
                    el( ToggleControl, {
                        label:'Show Badge', checked: !! attr.showBadge,
                        onChange: function(v){ set({ showBadge:v }); }
                    }),
                    attr.showBadge && el( TextControl, {
                        label:'Badge text', value: attr.badgeText || '',
                        onChange: function(v){ set({ badgeText:v }); }
                    }),
                    el( ToggleControl, {
                        label:'Show Heading', checked: !! attr.showHeading,
                        onChange: function(v){ set({ showHeading:v }); }
                    }),
                    attr.showHeading && el( TextControl, {
                        label:'Heading', value: attr.heading || '',
                        onChange: function(v){ set({ heading:v }); }
                    }),
                    el( ToggleControl, {
                        label:'Show Subheading', checked: !! attr.showSubheading,
                        onChange: function(v){ set({ showSubheading:v }); }
                    }),
                    attr.showSubheading && el( TextareaControl, {
                        label:'Subheading', value: attr.subheading || '', rows:2,
                        onChange: function(v){ set({ subheading:v }); }
                    })
                ),

                /* Background */
                el( PanelBody, { title:'Background', initialOpen:false },
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'0 0 6px' } }, 'Background color'),
                    el( ColorPicker, {
                        color: attr.bgColor || '#050505',
                        onChange: function(v){ set({ bgColor:v }); },
                        enableAlpha: false
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'12px 0 6px' } }, 'Side image (jellyfish etc.)'),
                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect: function(m){
                                if(!m||!m.url) return;
                                set({ bgImage: safeStr(m.url), bgImageId: m.id||0 });
                            },
                            allowedTypes:['image'],
                            value: attr.bgImageId || 0,
                            render: function(p){
                                return el('div', {},
                                    attr.bgImage && el('div', { style:{ marginBottom:'6px' } },
                                        el('img', { src:attr.bgImage, style:{ width:'100%', borderRadius:'4px', opacity:0.7 } }),
                                        el( Button, {
                                            onClick: function(){ set({ bgImage:'', bgImageId:0 }); },
                                            variant:'link', isDestructive:true, isSmall:true,
                                            style:{ marginTop:'4px' }
                                        }, 'Remove image')
                                    ),
                                    el( Button, { onClick:p.open, variant:'secondary', isSmall:true },
                                        attr.bgImage ? 'Replace image' : 'Upload image'
                                    )
                                );
                            }
                        })
                    ),
                    attr.bgImage && el( SelectControl, {
                        label:'Image side',
                        value: attr.bgImageSide || 'left',
                        options:[
                            { label:'Left',  value:'left'  },
                            { label:'Right', value:'right' },
                        ],
                        onChange: function(v){ set({ bgImageSide:v }); }
                    }),
                    el( TextControl, {
                        label:'Inner max-width', value: attr.innerWidth || '900px',
                        onChange: function(v){ set({ innerWidth:v }); }
                    })
                ),

                /* Plans repeater */
                el( PanelBody, { title:'Pricing Plans (' + plans.length + ')', initialOpen:true },

                    plans.map( function( plan, i ) {
                        if ( ! plan ) return null;
                        var label = plan.name ? plan.name : 'Plan ' + (i+1);
                        return el( PanelBody, {
                            key      : 'plan-' + i,
                            title    : el('span', { style:{ display:'flex', alignItems:'center', gap:'6px' } },
                                plan.featured && el('span', { style:{ background:'#0d9488', color:'#fff', fontSize:'10px', padding:'1px 6px', borderRadius:'4px' } }, '★ Featured'),
                                el('span', {}, label)
                            ),
                            initialOpen: false,
                            style: { borderLeft: plan.featured ? '3px solid #0d9488' : '3px solid #333' }
                        },
                            /* Move / Remove */
                            el('div', { style:{ display:'flex', gap:'6px', marginBottom:'12px' } },
                                el( Button, { onClick:function(){ movePlan(i,-1); }, variant:'secondary', isSmall:true, disabled:i===0 }, '↑ Move up'),
                                el( Button, { onClick:function(){ movePlan(i,1);  }, variant:'secondary', isSmall:true, disabled:i===plans.length-1 }, '↓ Move down'),
                                el( Button, { onClick:function(){ removePlan(i); }, variant:'secondary', isDestructive:true, isSmall:true }, '✕ Remove')
                            ),

                            el( ToggleControl, {
                                label:'Mark as Featured (highlighted card)',
                                checked: !! plan.featured,
                                onChange: function(v){ setPlan(i,'featured',v); }
                            }),

                            el( TextControl, {
                                label:'Price', value: plan.price || '',
                                onChange: function(v){ setPlan(i,'price',v); }
                            }),
                            el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'6px 0 4px' } }, 'Price color'),
                            el( ColorPicker, {
                                color: plan.priceColor || '#ffffff',
                                onChange: function(v){ setPlan(i,'priceColor',v); },
                                enableAlpha:false
                            }),

                            el( TextControl, {
                                label:'Plan name', value: plan.name || '',
                                onChange: function(v){ setPlan(i,'name',v); }
                            }),
                            el( TextareaControl, {
                                label:'Short description', value: plan.desc || '', rows:2,
                                onChange: function(v){ setPlan(i,'desc',v); }
                            }),
                            el( TextareaControl, {
                                label:'Features (one per line)', value: plan.features || '', rows:6,
                                onChange: function(v){ setPlan(i,'features',v); }
                            }),
                            el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'6px 0 4px' } }, 'Check icon color'),
                            el( ColorPicker, {
                                color: plan.checkColor || '#555555',
                                onChange: function(v){ setPlan(i,'checkColor',v); },
                                enableAlpha:false
                            }),

                            el( TextControl, {
                                label:'Button text', value: plan.btnText || '',
                                onChange: function(v){ setPlan(i,'btnText',v); }
                            }),
                            el( TextControl, {
                                label:'Button URL', value: plan.btnUrl || '',
                                onChange: function(v){ setPlan(i,'btnUrl',v); }
                            }),
                            el( ToggleControl, {
                                label:'Open in new tab',
                                checked: !! plan.btnNewTab,
                                onChange: function(v){ setPlan(i,'btnNewTab',v); }
                            }),

                            el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'10px 0 4px' } }, 'Card background'),
                            el( ColorPicker, {
                                color: plan.cardBg || '#1a1a1a',
                                onChange: function(v){ setPlan(i,'cardBg',v); },
                                enableAlpha:false
                            }),
                            el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'6px 0 4px' } }, 'Card border color'),
                            el( ColorPicker, {
                                color: plan.cardBorder || '#2a2a2a',
                                onChange: function(v){ setPlan(i,'cardBorder',v); },
                                enableAlpha:false
                            })
                        );
                    }),

                    el( Button, {
                        onClick: addPlan,
                        variant: 'primary',
                        style  : { width:'100%', justifyContent:'center', marginTop:'12px' }
                    }, '+ Add Pricing Plan')
                ),

                /* Footer note */
                el( PanelBody, { title:'Footer Note', initialOpen:false },
                    el( ToggleControl, {
                        label:'Show footer note', checked: !! attr.showFooterNote,
                        onChange: function(v){ set({ showFooterNote:v }); }
                    }),
                    attr.showFooterNote && el( Fragment, { key:'footer-fields' },
                        el( TextControl, {
                            label:'Label (e.g. HOSTING:)', value: attr.footerNoteLabel || '',
                            onChange: function(v){ set({ footerNoteLabel:v }); }
                        }),
                        el( TextControl, {
                            label:'Note text', value: attr.footerNoteText || '',
                            onChange: function(v){ set({ footerNoteText:v }); }
                        })
                    )
                )
            );

            /* ── Editor Preview ── */
            var sectionStyle = { backgroundColor: attr.bgColor || '#050505', position:'relative', overflow:'hidden' };
            if ( attr.bgImage ) {
                var side = attr.bgImageSide === 'right' ? 'right center' : 'left center';
                sectionStyle.backgroundImage     = 'url(' + attr.bgImage + ')';
                sectionStyle.backgroundSize      = 'auto 100%';
                sectionStyle.backgroundPosition  = side;
                sectionStyle.backgroundRepeat    = 'no-repeat';
            }

            var colCount   = plans.length;
            var gridCols   = colCount <= 4 ? colCount : 3;

            return el( Fragment, {},
                sidebar,
                el('section', { className:'sg-pt', style: sectionStyle },
                    el('div', { className:'sg-pt__inner', style:{ maxWidth: attr.innerWidth || '900px', margin:'0 auto' } },

                        attr.showBadge && attr.badgeText &&
                        el('div', { className:'sg-pt__header-top' },
                            el('span', { className:'sg-pt__badge' }, attr.badgeText)
                        ),

                        attr.showHeading && attr.heading &&
                        el('h2', { className:'sg-pt__heading' }, attr.heading ),

                        attr.showSubheading && attr.subheading &&
                        el('p', { className:'sg-pt__subheading' }, attr.subheading ),

                        plans.length === 0
                        ? el('div', { style:{ textAlign:'center', padding:'40px', color:'#555', fontSize:'13px', border:'1px dashed #333', borderRadius:'8px' } },
                            '← Add pricing plans from the sidebar'
                          )
                        : el('div', {
                            className: 'sg-pt__grid sg-pt__grid--count-' + colCount,
                          },
                            plans.map( function( plan, i ) {
                                if ( ! plan ) return null;
                                var featureList = ( plan.features || '' ).split('\n').filter( function(f){ return f.trim(); } );
                                return el('div', {
                                    key      : 'prev-' + i,
                                    className: 'sg-pt__card' + ( plan.featured ? ' sg-pt__card--featured' : '' ),
                                    style    : {
                                        background  : plan.cardBg     || ( plan.featured ? '#0d7a72' : '#1a1a1a' ),
                                        borderColor : plan.cardBorder || ( plan.featured ? '#0d9488' : '#2a2a2a' ),
                                    }
                                },
                                    plan.price && el('div', { className:'sg-pt__price', style:{ color: plan.priceColor||'#fff' } }, plan.price ),
                                    plan.name  && el('h3', { className:'sg-pt__plan-name' }, plan.name ),
                                    plan.desc  && el('p',  { className:'sg-pt__plan-desc'  }, plan.desc  ),

                                    featureList.length > 0 &&
                                    el('ul', { className:'sg-pt__features' },
                                        featureList.map( function( feat, fi ) {
                                            return el('li', { key:'f'+fi, className:'sg-pt__feature' },
                                                el('span', { className:'sg-pt__check', style:{ color: plan.checkColor||'#555' } },
                                                    el('svg', { width:'16', height:'16', viewBox:'0 0 24 24', fill:'none', stroke:'currentColor', strokeWidth:'2', strokeLinecap:'round', strokeLinejoin:'round' },
                                                        el('path', { d:'M22 11.08V12a10 10 0 1 1-5.93-9.14' }),
                                                        el('polyline', { points:'22 4 12 14.01 9 11.01' })
                                                    )
                                                ),
                                                el('span', {}, feat)
                                            );
                                        })
                                    ),

                                    plan.btnText &&
                                    el('div', { className:'sg-pt__btn-wrap' },
                                        el('a', { href:'#', className:'sg-pt__btn' }, plan.btnText )
                                    )
                                );
                            })
                        ),

                        attr.showFooterNote && ( attr.footerNoteLabel || attr.footerNoteText ) &&
                        el('div', { className:'sg-pt__footer-note' },
                            attr.footerNoteLabel && el('span', { className:'sg-pt__footer-label' }, attr.footerNoteLabel ),
                            attr.footerNoteText  && el('span', { className:'sg-pt__footer-text'  }, attr.footerNoteText  )
                        )
                    )
                )
            );
        },

        save: function() { return null; }
    });

} )();
