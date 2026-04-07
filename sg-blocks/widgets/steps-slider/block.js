( function () {

    var el          = window.wp.element.createElement;
    var Fragment    = window.wp.element.Fragment;

    var InspectorControls = window.wp.blockEditor.InspectorControls;
    var PanelBody       = window.wp.components.PanelBody;
    var TextControl     = window.wp.components.TextControl;
    var TextareaControl = window.wp.components.TextareaControl;
    var ToggleControl   = window.wp.components.ToggleControl;
    var RangeControl    = window.wp.components.RangeControl;
    var ColorPicker     = window.wp.components.ColorPicker;
    var Button          = window.wp.components.Button;

    function parseSteps( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; }
        catch(e) { return []; }
    }

    function defaultStep( index ) {
        var steps = [
            { title:'Discovery & Alignment',      desc:'We run a strategic session to clarify your positioning, target audience, offers, and revenue goals.' },
            { title:'Audit & Growth Strategy',    desc:'We analyze your digital presence and competitors, then define a clear conversion strategy and site structure.' },
            { title:'Structure & Wireframing',    desc:'We map your layout, messaging hierarchy, and calls to action to design a streamlined user journey.' },
            { title:'Design & Development',       desc:'Your site comes to life with a bespoke design, built mobile-first for speed and conversion.' },
            { title:'Review & Refinements',       desc:'You review every page and request changes. We refine until you love it.' },
            { title:'Launch & Handover',          desc:'We launch your site and hand you full control with a walkthrough and training.' },
        ];
        return steps[ index ] || { title: 'Step ' + (index+1), desc: 'Describe this step.' };
    }

    window.wp.blocks.registerBlockType( 'sg-blocks/steps-slider', {
        title    : 'Steps Slider',
        icon     : 'slides',
        category : 'layout',
        keywords : [ 'steps', 'process', 'slider', 'how it works' ],

        attributes: {
            badgeText        : { type:'string',  default:'How We Build Your Website' },
            showBadge        : { type:'boolean', default:true },
            headingLine1     : { type:'string',  default:'From Brief to Live' },
            headingLine2     : { type:'string',  default:'in 6 Steps.' },
            headingLine2Color: { type:'string',  default:'#00c8ff' },
            subtext          : { type:'string',  default:"No surprises. No chasing.\nYou stay focused on your business." },
            leftBgColor      : { type:'string',  default:'#0d1117' },
            steps            : { type:'string',  default:'[]' },
            stepBgColor      : { type:'string',  default:'#1e2028' },
            stepBgGradient   : { type:'boolean', default:true },
            stepNumberColor  : { type:'string',  default:'#888888' },
            stepTitleColor   : { type:'string',  default:'#ffffff' },
            stepDescColor    : { type:'string',  default:'#999999' },
            autoPlay         : { type:'boolean', default:true },
            autoPlayDelay    : { type:'number',  default:3000 },
            visibleCards     : { type:'number',  default:3 },
            showArrows       : { type:'boolean', default:true },
            arrowColor       : { type:'string',  default:'#ffffff' },
            bgColor          : { type:'string',  default:'#000000' },
            innerWidth       : { type:'string',  default:'1200px' },
        },

        edit: function ( props ) {
            var attr  = props.attributes;
            var set   = props.setAttributes;
            var steps = parseSteps( attr.steps );

            // Seed 6 default steps if empty
            if ( steps.length === 0 ) {
                var seeded = [];
                for ( var s = 0; s < 6; s++ ) seeded.push( defaultStep(s) );
                set({ steps: JSON.stringify( seeded ) });
                steps = seeded;
            }

            function setStep( index, key, val ) {
                var arr = parseSteps( attr.steps );
                if ( ! arr[index] ) arr[index] = {};
                arr[index][key] = val;
                set({ steps: JSON.stringify(arr) });
            }

            function addStep() {
                var arr = parseSteps( attr.steps );
                arr.push( defaultStep( arr.length ) );
                set({ steps: JSON.stringify(arr) });
            }

            function removeStep( index ) {
                var arr = parseSteps( attr.steps );
                arr.splice( index, 1 );
                set({ steps: JSON.stringify(arr) });
            }

            function moveStep( index, dir ) {
                var arr  = parseSteps( attr.steps );
                var swap = index + dir;
                if ( swap < 0 || swap >= arr.length ) return;
                var tmp    = arr[index];
                arr[index] = arr[swap];
                arr[swap]  = tmp;
                set({ steps: JSON.stringify(arr) });
            }

            function pad( n ) { return n < 10 ? '0' + n : '' + n; }

            var sidebar = el( InspectorControls, {},

                /* Left panel */
                el( PanelBody, { title:'Left Panel', initialOpen:true },
                    el( ToggleControl, {
                        label:'Show badge', checked: !! attr.showBadge,
                        onChange: function(v){ set({ showBadge:v }); }
                    }),
                    attr.showBadge && el( TextControl, {
                        label:'Badge text', value: attr.badgeText || '',
                        onChange: function(v){ set({ badgeText:v }); }
                    }),
                    el( TextControl, {
                        label:'Heading line 1', value: attr.headingLine1 || '',
                        onChange: function(v){ set({ headingLine1:v }); }
                    }),
                    el( TextControl, {
                        label:'Heading line 2 (accent)', value: attr.headingLine2 || '',
                        onChange: function(v){ set({ headingLine2:v }); }
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Line 2 color'),
                    el( ColorPicker, {
                        color: attr.headingLine2Color || '#00c8ff',
                        onChange: function(v){ set({ headingLine2Color:v }); },
                        enableAlpha: false
                    }),
                    el( TextareaControl, {
                        label:'Subtext', value: attr.subtext || '', rows:3,
                        onChange: function(v){ set({ subtext:v }); }
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Left panel background'),
                    el( ColorPicker, {
                        color: attr.leftBgColor || '#0d1117',
                        onChange: function(v){ set({ leftBgColor:v }); },
                        enableAlpha: false
                    })
                ),

                /* Slider settings */
                el( PanelBody, { title:'Slider Settings', initialOpen:true },
                    el( RangeControl, {
                        label: 'Visible cards at once', value: attr.visibleCards || 3, min:1, max:5,
                        onChange: function(v){ set({ visibleCards:v }); }
                    }),
                    el( ToggleControl, {
                        label:'Auto-play', checked: !! attr.autoPlay,
                        onChange: function(v){ set({ autoPlay:v }); }
                    }),
                    attr.autoPlay && el( RangeControl, {
                        label: 'Auto-play delay (ms)', value: attr.autoPlayDelay || 3000, min:1000, max:10000, step:500,
                        onChange: function(v){ set({ autoPlayDelay:v }); }
                    }),
                    el( ToggleControl, {
                        label:'Show prev/next arrows', checked: !! attr.showArrows,
                        onChange: function(v){ set({ showArrows:v }); }
                    }),
                    attr.showArrows && el( Fragment, { key:'arrow-color' },
                        el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Arrow color'),
                        el( ColorPicker, {
                            color: attr.arrowColor || '#ffffff',
                            onChange: function(v){ set({ arrowColor:v }); },
                            enableAlpha:false
                        })
                    )
                ),

                /* Steps repeater */
                el( PanelBody, { title:'Steps (' + steps.length + ')', initialOpen:true },
                    steps.map( function( step, i ) {
                        if ( ! step ) return null;
                        return el( PanelBody, {
                            key: 'step-' + i,
                            title: pad(i+1) + ' — ' + ( step.title || 'Step ' + (i+1) ),
                            initialOpen: false
                        },
                            el('div', { style:{ display:'flex', gap:'6px', marginBottom:'10px' } },
                                el( Button, { onClick:function(){ moveStep(i,-1); }, variant:'secondary', isSmall:true, disabled:i===0 }, '↑' ),
                                el( Button, { onClick:function(){ moveStep(i,1);  }, variant:'secondary', isSmall:true, disabled:i===steps.length-1 }, '↓' ),
                                el( Button, { onClick:function(){ removeStep(i); }, variant:'secondary', isDestructive:true, isSmall:true }, '✕ Remove' )
                            ),
                            el( TextControl, {
                                label:'Title', value: step.title || '',
                                onChange: function(v){ setStep(i,'title',v); }
                            }),
                            el( TextareaControl, {
                                label:'Description', value: step.desc || '', rows:4,
                                onChange: function(v){ setStep(i,'desc',v); }
                            })
                        );
                    }),
                    el( Button, {
                        onClick: addStep,
                        variant: 'primary',
                        style  : { width:'100%', justifyContent:'center', marginTop:'8px' }
                    }, '+ Add Step')
                ),

                /* Card style */
                el( PanelBody, { title:'Card Style', initialOpen:false },
                    el( ToggleControl, {
                        label:'Use gradient background (like screenshot)',
                        checked: !! attr.stepBgGradient,
                        onChange: function(v){ set({ stepBgGradient:v }); }
                    }),
                    ! attr.stepBgGradient && el( Fragment, { key:'solid-bg' },
                        el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Card background color'),
                        el( ColorPicker, {
                            color: attr.stepBgColor || '#1e2028',
                            onChange: function(v){ set({ stepBgColor:v }); },
                            enableAlpha:false
                        })
                    ),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Step number color'),
                    el( ColorPicker, {
                        color: attr.stepNumberColor || '#888888',
                        onChange: function(v){ set({ stepNumberColor:v }); },
                        enableAlpha:false
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Title color'),
                    el( ColorPicker, {
                        color: attr.stepTitleColor || '#ffffff',
                        onChange: function(v){ set({ stepTitleColor:v }); },
                        enableAlpha:false
                    }),
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'8px 0 4px' } }, 'Description color'),
                    el( ColorPicker, {
                        color: attr.stepDescColor || '#999999',
                        onChange: function(v){ set({ stepDescColor:v }); },
                        enableAlpha:false
                    })
                ),

                /* Section */
                el( PanelBody, { title:'Section', initialOpen:false },
                    el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'0 0 4px' } }, 'Section background'),
                    el( ColorPicker, {
                        color: attr.bgColor || '#000000',
                        onChange: function(v){ set({ bgColor:v }); },
                        enableAlpha:false
                    }),
                    el( TextControl, {
                        label:'Inner max-width', value: attr.innerWidth || '1200px',
                        onChange: function(v){ set({ innerWidth:v }); }
                    })
                )
            );

            /* ── Editor preview ── */
            function pad2(n){ return n < 10 ? '0'+n : ''+n; }

            var cardBgStyle = attr.stepBgGradient
                ? { background:'linear-gradient(145deg, #2a2c35 0%, #1a1c24 100%)' }
                : { backgroundColor: attr.stepBgColor || '#1e2028' };

            // Show only first `visibleCards` in editor for simplicity
            var previewSteps = steps.slice( 0, attr.visibleCards || 3 );

            return el( Fragment, {},
                sidebar,
                el('section', { className:'sg-ss', style:{ backgroundColor: attr.bgColor||'#000' } },
                    el('div', { className:'sg-ss__inner', style:{ maxWidth: attr.innerWidth||'1200px', margin:'0 auto' } },

                        /* Left panel */
                        el('div', { className:'sg-ss__left', style:{ backgroundColor: attr.leftBgColor||'#0d1117' } },
                            attr.showBadge && attr.badgeText &&
                            el('span', { className:'sg-ss__badge' }, attr.badgeText ),
                            el('h2', { className:'sg-ss__heading' },
                                attr.headingLine1 && el('span', { className:'sg-ss__heading-line1' }, attr.headingLine1 ),
                                attr.headingLine1 && el('br'),
                                attr.headingLine2 && el('span', { className:'sg-ss__heading-line2', style:{ color:attr.headingLine2Color||'#00c8ff' } }, attr.headingLine2 )
                            ),
                            attr.subtext && el('p', { className:'sg-ss__subtext',
                                dangerouslySetInnerHTML:{ __html: attr.subtext.replace(/\n/g,'<br>') }
                            })
                        ),

                        /* Slider preview (static in editor) */
                        el('div', { className:'sg-ss__slider-wrap', style:{ pointerEvents:'none' } },

                            attr.showArrows && el('button', { className:'sg-ss__arrow sg-ss__arrow--prev', style:{ color:attr.arrowColor||'#fff' }, disabled:true },
                                el('svg', { width:'20', height:'20', viewBox:'0 0 24 24', fill:'none', stroke:'currentColor', strokeWidth:'2', strokeLinecap:'round', strokeLinejoin:'round' },
                                    el('polyline', { points:'15 18 9 12 15 6' })
                                )
                            ),

                            el('div', { className:'sg-ss__viewport' },
                                el('div', { className:'sg-ss__track', style:{ '--visible': attr.visibleCards||3 } },
                                    previewSteps.map( function( step, i ) {
                                        if(!step) return null;
                                        return el('div', {
                                            key: 'p-'+i,
                                            className:'sg-ss__card',
                                            style: cardBgStyle
                                        },
                                            el('span', { className:'sg-ss__num', style:{ color:attr.stepNumberColor||'#888' } }, pad2(i+1) ),
                                            step.title && el('h3', { className:'sg-ss__card-title', style:{ color:attr.stepTitleColor||'#fff' } }, step.title ),
                                            step.desc  && el('p',  { className:'sg-ss__card-desc',  style:{ color:attr.stepDescColor||'#999'  } }, step.desc  )
                                        );
                                    }),
                                    steps.length > ( attr.visibleCards||3 ) && el('div', {
                                        style:{ color:'#555', fontSize:'12px', padding:'32px 16px', flexShrink:0, display:'flex', alignItems:'center' }
                                    }, '+ ' + ( steps.length - (attr.visibleCards||3) ) + ' more (slider on frontend)' )
                                )
                            ),

                            attr.showArrows && el('button', { className:'sg-ss__arrow sg-ss__arrow--next', style:{ color:attr.arrowColor||'#fff' }, disabled:true },
                                el('svg', { width:'20', height:'20', viewBox:'0 0 24 24', fill:'none', stroke:'currentColor', strokeWidth:'2', strokeLinecap:'round', strokeLinejoin:'round' },
                                    el('polyline', { points:'9 18 15 12 9 6' })
                                )
                            )
                        )
                    )
                )
            );
        },

        save: function() { return null; }
    });

} )();
