( function () {
    var blocks      = window.wp.blocks;
    var el          = window.wp.element.createElement;
    var Fragment    = window.wp.element.Fragment;
    var __          = window.wp.i18n.__;

    var InspectorControls = window.wp.blockEditor.InspectorControls;
    var MediaUpload       = window.wp.blockEditor.MediaUpload;
    var MediaUploadCheck  = window.wp.blockEditor.MediaUploadCheck;

    var PanelBody     = window.wp.components.PanelBody;
    var TextControl   = window.wp.components.TextControl;
    var ToggleControl = window.wp.components.ToggleControl;
    var RangeControl  = window.wp.components.RangeControl;
    var ColorPicker   = window.wp.components.ColorPicker;
    var Button        = window.wp.components.Button;

    function parseLogos( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; }
        catch(e) { return []; }
    }

    function safeStr( val ) {
        return ( val !== null && val !== undefined ) ? String( val ) : '';
    }

    blocks.registerBlockType( 'sg-blocks/logo-slider', {
        title    : 'Logo Slider',
        icon     : 'images-alt2',
        category : 'layout',
        keywords : [ 'logo', 'slider', 'marquee', 'trusted', 'brands' ],

        attributes: {
            title       : { type:'string',  default:'Trusted by' },
            showTitle   : { type:'boolean', default:true },
            showBorder  : { type:'boolean', default:true },
            logos       : { type:'string',  default:'[]' },
            speed       : { type:'number',  default:30 },
            logoHeight  : { type:'number',  default:32 },
            bgColor     : { type:'string',  default:'#111111' },
            borderColor : { type:'string',  default:'#2a2a2a' },
            logoOpacity : { type:'number',  default:50 },
        },

        edit: function ( props ) {
            var attr  = props.attributes;
            var set   = props.setAttributes;
            var logos = parseLogos( attr.logos );

            function addLogos( mediaItems ) {
                try {
                    var arr = parseLogos( attr.logos );
                    // MediaUpload can return single object or array
                    var items = Array.isArray( mediaItems ) ? mediaItems : [ mediaItems ];
                    items.forEach( function( media ) {
                        if ( ! media || ! media.url ) return;
                        arr.push({
                            url : safeStr( media.url ),
                            id  : media.id || 0,
                            alt : safeStr( media.alt || media.title || '' )
                        });
                    });
                    set({ logos: JSON.stringify( arr ) });
                } catch(e) {
                    console.error( 'SG Logo Slider addLogos error:', e );
                }
            }

            function removeLogo( index ) {
                var arr = parseLogos( attr.logos );
                arr.splice( index, 1 );
                set({ logos: JSON.stringify( arr ) });
            }

            function moveLogo( index, dir ) {
                var arr  = parseLogos( attr.logos );
                var swap = index + dir;
                if ( swap < 0 || swap >= arr.length ) return;
                var tmp    = arr[ index ];
                arr[ index ] = arr[ swap ];
                arr[ swap ]  = tmp;
                set({ logos: JSON.stringify( arr ) });
            }

            var logoCount  = logos.length;
            var duration   = Math.max( 5, ( logoCount * attr.speed ) / 5 );
            var opacityVal = ( attr.logoOpacity || 50 ) / 100;

            var borderStyle = attr.showBorder
                ? { borderTop:'1px solid ' + attr.borderColor, borderBottom:'1px solid ' + attr.borderColor }
                : {};

            var allLogos = logos.concat( logos );

            var sidebar = el( InspectorControls, {},

                el( PanelBody, { title:'Title & Border', initialOpen:true },
                    el( ToggleControl, {
                        label   : 'Show Title',
                        checked : !! attr.showTitle,
                        onChange: function(v){ set({ showTitle:v }); }
                    }),
                    attr.showTitle && el( TextControl, {
                        label   : 'Title text',
                        value   : attr.title || '',
                        onChange: function(v){ set({ title:v }); }
                    }),
                    el( ToggleControl, {
                        label   : 'Show top & bottom border',
                        checked : !! attr.showBorder,
                        onChange: function(v){ set({ showBorder:v }); }
                    })
                ),

                el( PanelBody, { title: 'Logos (' + logoCount + ')', initialOpen:true },

                    logos.map( function( logo, i ) {
                        if ( ! logo || ! logo.url ) return null;
                        var filename = logo.url.split('/').pop().split('?')[0];
                        return el('div', {
                            key  : 'logo-' + i,
                            style: {
                                display       : 'flex',
                                alignItems    : 'center',
                                gap           : '6px',
                                marginBottom  : '6px',
                                background    : '#1e1e1e',
                                padding       : '6px 8px',
                                borderRadius  : '4px'
                            }
                        },
                            el('img', {
                                src  : logo.url,
                                style: { height:'24px', width:'auto', objectFit:'contain', flexShrink:0, opacity:0.7, maxWidth:'60px' }
                            }),
                            el('span', {
                                style: { flex:'1', fontSize:'11px', color:'#999', overflow:'hidden',
                                         textOverflow:'ellipsis', whiteSpace:'nowrap' }
                            }, filename ),
                            el( Button, {
                                onClick  : function(){ moveLogo(i, -1); },
                                variant  : 'tertiary',
                                isSmall  : true,
                                disabled : i === 0
                            }, '↑' ),
                            el( Button, {
                                onClick  : function(){ moveLogo(i, 1); },
                                variant  : 'tertiary',
                                isSmall  : true,
                                disabled : i === logos.length - 1
                            }, '↓' ),
                            el( Button, {
                                onClick      : function(){ removeLogo(i); },
                                variant      : 'tertiary',
                                isDestructive: true,
                                isSmall      : true
                            }, '✕' )
                        );
                    }),

                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect    : addLogos,
                            allowedTypes: [ 'image' ],
                            multiple    : true,
                            render      : function( p ) {
                                return el( Button, {
                                    onClick: p.open,
                                    variant: 'secondary',
                                    style  : { marginTop:'8px', width:'100%', justifyContent:'center' }
                                }, '+ Add Logo(s)' );
                            }
                        })
                    )
                ),

                el( PanelBody, { title:'Appearance', initialOpen:false },
                    el( RangeControl, {
                        label   : 'Speed (lower = faster)',
                        value   : attr.speed || 30,
                        min     : 5,
                        max     : 80,
                        onChange: function(v){ set({ speed: v }); }
                    }),
                    el( RangeControl, {
                        label   : 'Logo height (px)',
                        value   : attr.logoHeight || 32,
                        min     : 16,
                        max     : 80,
                        onChange: function(v){ set({ logoHeight: v }); }
                    }),
                    el( RangeControl, {
                        label   : 'Logo opacity (%)',
                        value   : attr.logoOpacity || 50,
                        min     : 10,
                        max     : 100,
                        onChange: function(v){ set({ logoOpacity: v }); }
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'12px 0 4px', color:'#1e1e1e' } }, 'Background color'),
                    el( ColorPicker, {
                        color      : attr.bgColor || '#111111',
                        onChange   : function(v){ set({ bgColor: v }); },
                        enableAlpha: false
                    }),
                    attr.showBorder && el( Fragment, { key:'border-color' },
                        el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'12px 0 4px', color:'#1e1e1e' } }, 'Border color'),
                        el( ColorPicker, {
                            color      : attr.borderColor || '#2a2a2a',
                            onChange   : function(v){ set({ borderColor: v }); },
                            enableAlpha: false
                        })
                    )
                )
            );

            return el( Fragment, {},
                sidebar,
                el('div', {
                    className : 'sg-ls',
                    style     : Object.assign( {}, { backgroundColor: attr.bgColor || '#111111' }, borderStyle )
                },
                    attr.showTitle && attr.title
                        ? el('div', { className:'sg-ls__title' }, attr.title)
                        : null,

                    logos.length === 0
                        ? el('div', {
                            style: { padding:'32px', textAlign:'center', color:'#666', fontSize:'13px' }
                          }, '← Add logos from the sidebar panel')
                        : el('div', { className:'sg-ls__viewport' },
                            el('div', {
                                className : 'sg-ls__track',
                                style     : { animationDuration: duration + 's' }
                            },
                                allLogos.map( function( logo, i ) {
                                    if ( ! logo || ! logo.url ) return null;
                                    return el('div', { key: 'item-' + i, className:'sg-ls__item' },
                                        el('img', {
                                            src  : logo.url,
                                            alt  : logo.alt || '',
                                            style: {
                                                height    : ( attr.logoHeight || 32 ) + 'px',
                                                opacity   : opacityVal,
                                                maxWidth  : '140px',
                                                objectFit : 'contain'
                                            }
                                        })
                                    );
                                })
                            )
                        )
                )
            );
        },

        save: function() {
            return null;
        }
    });

} )();
