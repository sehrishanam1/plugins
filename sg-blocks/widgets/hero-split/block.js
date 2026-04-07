( function ( blocks, element, blockEditor, components, i18n ) {
    var el            = element.createElement;
    var Fragment      = element.Fragment;
    var useState      = element.useState;
    var useEffect     = element.useEffect;
    var __            = i18n.__;
    var registerBlock = blocks.registerBlockType;

    var InspectorControls = blockEditor.InspectorControls;
    var MediaUpload       = blockEditor.MediaUpload;
    var MediaUploadCheck  = blockEditor.MediaUploadCheck;

    var PanelBody       = components.PanelBody;
    var TextControl     = components.TextControl;
    var TextareaControl = components.TextareaControl;
    var ToggleControl   = components.ToggleControl;
    var ColorPicker     = components.ColorPicker;
    var RangeControl    = components.RangeControl;
    var Button          = components.Button;
    var SelectControl   = components.SelectControl;
    var Notice          = components.Notice;

    /* ── tiny helper: parse icon items JSON ── */
    function parseItems( str ) {
        try { return JSON.parse( str ) || []; }
        catch(e) { return []; }
    }

    /* ── inline preview of title with {} accent ── */
    function renderTitle( line, color ) {
        var parts = line.split( /(\{[^}]*\})/g );
        return parts.map( function( part, i ) {
            if ( part.startsWith('{') && part.endsWith('}') ) {
                return el( 'span', { key: i, style: { color: color } }, part.slice(1,-1) );
            }
            return part;
        });
    }

    registerBlock( 'sg-blocks/hero-split', {
        title    : __( 'Hero Split', 'sg-blocks' ),
        icon     : 'cover-image',
        category : 'layout',
        keywords : [ 'hero', 'split', 'banner' ],

        attributes: {
            innerWidth       : { type:'string',  default:'1200px' },
            bgImage          : { type:'string',  default:'' },
            bgImageId        : { type:'integer', default:0 },
            bgOverlayOpacity : { type:'number',  default:0 },
            showBreadcrumb   : { type:'boolean', default:true },
            breadcrumbItems  : { type:'string',  default:'Services / Website Build' },
            titleLine1       : { type:'string',  default:'You Deserve a Website' },
            titleLine2       : { type:'string',  default:'{That Actually Wins Clients}' },
            primaryColor     : { type:'string',  default:'#00c8ff' },
            description      : { type:'string',  default:'Most websites in your industry look the same, load slowly, and lose leads the moment someone clicks. We build high-converting websites built around your buyer — not templates.' },
            showIconList     : { type:'boolean', default:true },
            iconItems        : { type:'string',  default:'[]' },
            showBtn1         : { type:'boolean', default:true },
            btn1Text         : { type:'string',  default:'Start My Website' },
            btn1Url          : { type:'string',  default:'#' },
            btn1Style        : { type:'string',  default:'solid' },
            showBtn2         : { type:'boolean', default:true },
            btn2Text         : { type:'string',  default:'See Past Projects' },
            btn2Url          : { type:'string',  default:'#' },
            btn2Style        : { type:'string',  default:'outline' },
            showStars        : { type:'boolean', default:true },
            starsText        : { type:'string',  default:'trusted by contractors, clinics & consultants across Dubai' },
            starsCount       : { type:'number',  default:5 },
            starsColor       : { type:'string',  default:'#f5a623' },
        },

        edit: function ( props ) {
            var attr = props.attributes;
            var set  = props.setAttributes;

            var iconItems = parseItems( attr.iconItems );

            /* ── icon list helpers ── */
            function setIconItem( index, key, val ) {
                var arr = parseItems( attr.iconItems );
                arr[ index ] = Object.assign( {}, arr[ index ], { [key]: val } );
                set({ iconItems: JSON.stringify( arr ) });
            }
            function addIconItem() {
                var arr = parseItems( attr.iconItems );
                arr.push({ label:'New Item', iconUrl:'', color: attr.primaryColor });
                set({ iconItems: JSON.stringify( arr ) });
            }
            function removeIconItem( index ) {
                var arr = parseItems( attr.iconItems );
                arr.splice( index, 1 );
                set({ iconItems: JSON.stringify( arr ) });
            }

            /* ── sidebar panels ── */
            var sidebar = el( InspectorControls, {},

                /* Layout */
                el( PanelBody, { title:'Layout', initialOpen:true },
                    el( TextControl, {
                        label   : 'Inner max-width (e.g. 1200px or 90%)',
                        value   : attr.innerWidth,
                        onChange: function(v){ set({ innerWidth:v }); }
                    })
                ),

                /* Background */
                el( PanelBody, { title:'Background Image (sticky right)', initialOpen:false },
                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect   : function(m){ set({ bgImage:m.url, bgImageId:m.id }); },
                            allowedTypes: ['image'],
                            value      : attr.bgImageId,
                            render     : function(p){
                                return el( Fragment, {},
                                    attr.bgImage && el('img',{
                                        src   : attr.bgImage,
                                        style : { width:'100%', marginBottom:'8px', borderRadius:'4px' }
                                    }),
                                    el( Button, {
                                        onClick  : p.open,
                                        variant  : 'secondary',
                                        isSmall  : true,
                                        style    : { marginBottom:'8px' }
                                    }, attr.bgImage ? 'Replace Image' : 'Upload / Select Image' ),
                                    attr.bgImage && el( Button, {
                                        onClick  : function(){ set({ bgImage:'', bgImageId:0 }); },
                                        variant  : 'link',
                                        isDestructive: true,
                                        isSmall  : true
                                    }, 'Remove Image' )
                                );
                            }
                        })
                    )
                ),

                /* Breadcrumb */
                el( PanelBody, { title:'Breadcrumb', initialOpen:false },
                    el( ToggleControl, {
                        label   : 'Show Breadcrumb',
                        checked : attr.showBreadcrumb,
                        onChange: function(v){ set({ showBreadcrumb:v }); }
                    }),
                    attr.showBreadcrumb && el( TextControl, {
                        label   : 'Breadcrumb text (use / as separator)',
                        value   : attr.breadcrumbItems,
                        onChange: function(v){ set({ breadcrumbItems:v }); }
                    })
                ),

                /* Title */
                el( PanelBody, { title:'Title', initialOpen:false },
                    el( TextControl, {
                        label   : 'Title line 1',
                        value   : attr.titleLine1,
                        onChange: function(v){ set({ titleLine1:v }); }
                    }),
                    el( TextControl, {
                        label   : 'Title line 2 (wrap text in {} for accent color)',
                        value   : attr.titleLine2,
                        onChange: function(v){ set({ titleLine2:v }); }
                    }),
                    el('p', { style:{ fontSize:'12px', color:'#999', margin:'-4px 0 8px' } },
                        'Example: {That Actually Wins Clients}'
                    ),
                    el('label', { style:{ fontSize:'11px', fontWeight:'500', display:'block', marginBottom:'8px' } }, 'Accent / Primary Color'),
                    el( ColorPicker, {
                        color   : attr.primaryColor,
                        onChange: function(v){ set({ primaryColor:v }); },
                        enableAlpha: false,
                    })
                ),

                /* Description */
                el( PanelBody, { title:'Description', initialOpen:false },
                    el( TextareaControl, {
                        label   : 'Description text (leave blank to hide)',
                        value   : attr.description,
                        rows    : 4,
                        onChange: function(v){ set({ description:v }); }
                    })
                ),

                /* Icon List */
                el( PanelBody, { title:'Icon List', initialOpen:false },
                    el( ToggleControl, {
                        label   : 'Show Icon List',
                        checked : attr.showIconList,
                        onChange: function(v){ set({ showIconList:v }); }
                    }),
                    attr.showIconList && el( Fragment, {},
                        iconItems.map( function( item, i ) {
                            return el('div', {
                                key  : i,
                                style: { border:'1px solid #eee', padding:'10px', borderRadius:'6px', marginBottom:'10px' }
                            },
                                el('div', { style:{ display:'flex', justifyContent:'space-between', alignItems:'center', marginBottom:'6px' } },
                                    el('strong', { style:{ fontSize:'12px' } }, 'Item ' + (i+1)),
                                    el( Button, {
                                        onClick      : function(){ removeIconItem(i); },
                                        variant      : 'link',
                                        isDestructive: true,
                                        isSmall      : true
                                    }, '✕ Remove')
                                ),
                                el( TextControl, {
                                    label   : 'Label',
                                    value   : item.label || '',
                                    onChange: function(v){ setIconItem(i,'label',v); }
                                }),
                                el('label', { style:{ fontSize:'11px', display:'block', marginBottom:'4px' } }, 'Icon (SVG or PNG)'),
                                el( MediaUploadCheck, {},
                                    el( MediaUpload, {
                                        onSelect   : function(m){ setIconItem(i,'iconUrl',m.url); },
                                        allowedTypes: ['image'],
                                        value      : item.iconId || 0,
                                        render     : function(p){
                                            return el('div', { style:{ marginBottom:'8px' } },
                                                item.iconUrl && el('img',{
                                                    src  : item.iconUrl,
                                                    style: { height:'24px', marginBottom:'4px', display:'block' }
                                                }),
                                                el( Button, {
                                                    onClick: p.open,
                                                    variant: 'secondary',
                                                    isSmall: true
                                                }, item.iconUrl ? 'Replace Icon' : 'Upload Icon')
                                            );
                                        }
                                    })
                                ),
                                el('label', { style:{ fontSize:'11px', fontWeight:'500', display:'block', marginBottom:'4px' } }, 'Icon color'),
                                el( ColorPicker, {
                                    color   : item.color || attr.primaryColor,
                                    onChange: function(v){ setIconItem(i,'color',v); },
                                    enableAlpha: false,
                                })
                            );
                        }),
                        el( Button, {
                            onClick: addIconItem,
                            variant: 'secondary',
                            isSmall: true,
                            style  : { marginTop:'4px' }
                        }, '+ Add Icon Item')
                    )
                ),

                /* Buttons */
                el( PanelBody, { title:'Buttons', initialOpen:false },
                    el( ToggleControl, {
                        label   : 'Show Button 1',
                        checked : attr.showBtn1,
                        onChange: function(v){ set({ showBtn1:v }); }
                    }),
                    attr.showBtn1 && el( Fragment, {},
                        el( TextControl, { label:'Button 1 Text', value:attr.btn1Text, onChange:function(v){ set({btn1Text:v}); } }),
                        el( TextControl, { label:'Button 1 URL',  value:attr.btn1Url,  onChange:function(v){ set({btn1Url:v}); } }),
                        el( SelectControl, {
                            label  : 'Button 1 Style',
                            value  : attr.btn1Style,
                            options: [ {label:'Solid (filled)',value:'solid'}, {label:'Outline',value:'outline'} ],
                            onChange: function(v){ set({btn1Style:v}); }
                        })
                    ),
                    el('hr', { style:{ margin:'8px 0', opacity:0.3 } }),
                    el( ToggleControl, {
                        label   : 'Show Button 2',
                        checked : attr.showBtn2,
                        onChange: function(v){ set({ showBtn2:v }); }
                    }),
                    attr.showBtn2 && el( Fragment, {},
                        el( TextControl, { label:'Button 2 Text', value:attr.btn2Text, onChange:function(v){ set({btn2Text:v}); } }),
                        el( TextControl, { label:'Button 2 URL',  value:attr.btn2Url,  onChange:function(v){ set({btn2Url:v}); } }),
                        el( SelectControl, {
                            label  : 'Button 2 Style',
                            value  : attr.btn2Style,
                            options: [ {label:'Solid (filled)',value:'solid'}, {label:'Outline',value:'outline'} ],
                            onChange: function(v){ set({btn2Style:v}); }
                        })
                    )
                ),

                /* Stars */
                el( PanelBody, { title:'Stars / Social Proof', initialOpen:false },
                    el( ToggleControl, {
                        label   : 'Show Stars',
                        checked : attr.showStars,
                        onChange: function(v){ set({ showStars:v }); }
                    }),
                    attr.showStars && el( Fragment, {},
                        el( RangeControl, {
                            label : 'Number of Stars',
                            value : attr.starsCount,
                            min   : 1,
                            max   : 5,
                            onChange: function(v){ set({ starsCount:v }); }
                        }),
                        el( TextControl, {
                            label   : 'Stars text',
                            value   : attr.starsText,
                            onChange: function(v){ set({ starsText:v }); }
                        }),
                        el('label', { style:{ fontSize:'11px', fontWeight:'500', display:'block', marginBottom:'4px' } }, 'Star color'),
                        el( ColorPicker, {
                            color   : attr.starsColor,
                            onChange: function(v){ set({ starsColor:v }); },
                            enableAlpha: false,
                        })
                    )
                )
            ); // end InspectorControls

            /* ── Editor preview ── */
            var previewStyle = {
                position       : 'relative',
                backgroundColor: '#0a0a0a',
                overflow       : 'hidden',
                width          : '100%',
            };

            var innerStyle = {
                maxWidth  : attr.innerWidth,
                margin    : '0 auto',
                padding   : '64px 40px',
                position  : 'relative',
                zIndex    : 2,
                boxSizing : 'border-box',
            };

            var starsEls = [];
            for ( var s = 0; s < attr.starsCount; s++ ) {
                starsEls.push( el('span', { key:s, style:{ color:attr.starsColor, fontSize:'18px' } }, '★' ) );
            }

            return el( Fragment, {},
                sidebar,
                el('section', { className:'sg-hs', style: previewStyle },

                    attr.bgImage && el('div', {
                        className: 'sg-hs__bg-image',
                        style    : { backgroundImage: 'url(' + attr.bgImage + ')' }
                    }),

                    el('div', { style: innerStyle },
                        el('div', { className:'sg-hs__content' },

                            attr.showBreadcrumb && attr.breadcrumbItems &&
                            el('div', { className:'sg-hs__breadcrumb' }, attr.breadcrumbItems),

                            ( attr.titleLine1 || attr.titleLine2 ) &&
                            el('h1', { className:'sg-hs__title' },
                                attr.titleLine1 && el('span', { className:'sg-hs__title-line1' }, attr.titleLine1),
                                attr.titleLine2 && el('span', { className:'sg-hs__title-line2' },
                                    renderTitle( attr.titleLine2, attr.primaryColor )
                                )
                            ),

                            attr.description &&
                            el('p', { className:'sg-hs__desc' }, attr.description),

                            attr.showIconList && iconItems.length > 0 &&
                            el('div', { className:'sg-hs__icon-list' },
                                iconItems.map( function(item, i) {
                                    return el('div', { key:i, className:'sg-hs__icon-item' },
                                        item.iconUrl
                                            ? el('img', { src:item.iconUrl, className:'sg-hs__icon-img', alt:'' })
                                            : el('span', { className:'sg-hs__icon-check', style:{ color: item.color||attr.primaryColor } }, '✔'),
                                        el('span', { className:'sg-hs__icon-label', style:{ color:item.color||attr.primaryColor } }, item.label||'' )
                                    );
                                })
                            ),

                            ( (attr.showBtn1 && attr.btn1Text) || (attr.showBtn2 && attr.btn2Text) ) &&
                            el('div', { className:'sg-hs__buttons' },
                                attr.showBtn1 && attr.btn1Text &&
                                el('a', { href:'#', className:'sg-hs__btn sg-hs__btn--' + attr.btn1Style }, attr.btn1Text),
                                attr.showBtn2 && attr.btn2Text &&
                                el('a', { href:'#', className:'sg-hs__btn sg-hs__btn--' + attr.btn2Style }, attr.btn2Text)
                            ),

                            attr.showStars && attr.starsText &&
                            el('div', { className:'sg-hs__stars' },
                                starsEls,
                                el('span', { className:'sg-hs__stars-text' }, attr.starsText)
                            )
                        )
                    )
                )
            );
        }, // end edit

        save: function () {
            // Dynamic block — rendered server-side via render_callback
            return null;
        }
    });

} )(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components,
    window.wp.i18n
);
