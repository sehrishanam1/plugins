( function () {

    var el       = window.wp.element.createElement;
    var Fragment = window.wp.element.Fragment;

    var InspectorControls = window.wp.blockEditor.InspectorControls;

    var PanelBody       = window.wp.components.PanelBody;
    var TextControl     = window.wp.components.TextControl;
    var ToggleControl   = window.wp.components.ToggleControl;
    var ColorPicker     = window.wp.components.ColorPicker;
    var Button          = window.wp.components.Button;
    var SelectControl   = window.wp.components.SelectControl;

    /* ── JSON helpers ── */
    function parsePlans( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; } catch(e) { return []; }
    }
    function parseSections( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; } catch(e) { return []; }
    }

    /* ── Default data ── */
    function defaultPlans() {
        return [
            {
                name       : 'Landing Page',
                price      : '4,999',
                priceSuffix: 'AED',
                btnText    : 'Get a Landing Page',
                btnUrl     : '#',
                btnNewTab  : false,
            },
            {
                name       : 'Full Website',
                price      : '14,999',
                priceSuffix: 'AED',
                btnText    : 'Get a Full Website',
                btnUrl     : '#',
                btnNewTab  : false,
            },
        ];
    }

    function defaultSections() {
        return [
            {
                label: 'Design & Build',
                rows: [
                    { label: 'Custom Design',              values: [ 'check', 'check' ] },
                    { label: 'Number of Pages',            values: [ '1 Page', 'Up to 8 Pages' ] },
                    { label: 'Mobile Responsive',          values: [ 'check', 'check' ] },
                    { label: 'Fast Load Speed',            values: [ 'check', 'check' ] },
                    { label: 'Brand Color & Font System',  values: [ 'Basic', 'Full System' ] },
                    { label: 'Project/Portfolio Gallery',  values: [ 'dash', 'check' ] },
                    { label: 'Blog / News Section',        values: [ 'dash', 'check' ] },
                    { label: 'Testimonials Section',       values: [ 'dash', 'check' ] },
                ],
            },
            {
                label: 'Lead Generation',
                rows: [
                    { label: 'Contact Form',          values: [ 'check', 'check' ] },
                    { label: 'Whatsapp CTA Button',   values: [ 'check', 'check' ] },
                    { label: 'Call-to-Action Buttons', values: [ '1 CTA', 'Multiple CTAs' ] },
                    { label: 'Quote Request Form',    values: [ 'dash', 'check' ] },
                ],
            },
            {
                label: 'Analytics',
                rows: [
                    { label: 'Google Search Console Setup', values: [ 'dash', 'check' ] },
                    { label: 'Google Analytics 4 Setup',    values: [ 'dash', 'check' ] },
                    { label: 'XML Sitemap & Robots.txt',    values: [ 'dash', 'check' ] },
                    { label: 'SSL Certificate',             values: [ 'check', 'check' ] },
                ],
            },
            {
                label: 'Delivery & Support',
                rows: [
                    { label: 'Delivery Time',           values: [ '2 Weeks', '6 Weeks' ] },
                    { label: 'Revisions Included',      values: [ '2 Rounds', '2 Rounds' ] },
                    { label: 'Post-Launch Support',     values: [ '30 Days', '60 Days' ] },
                    { label: 'Hand-off Training Session',values: [ 'dash', 'check' ] },
                    { label: 'Hosting Setup Assistance', values: [ 'check', 'check' ] },
                ],
            },
        ];
    }

    /* ── Value type helpers ── */
    var VALUE_OPTIONS = [
        { label: '✓ Check',   value: 'check' },
        { label: '— Dash',    value: 'dash'  },
        { label: 'Custom…',   value: '_custom' },
    ];

    function getValueType( v ) {
        if ( v === 'check' ) return 'check';
        if ( v === 'dash' || v === '' ) return 'dash';
        return '_custom';
    }

    /* ─────────────────────────────────────────────
       Register block
    ───────────────────────────────────────────── */
    window.wp.blocks.registerBlockType( 'sg-blocks/plan-comparison-table', {
        title    : 'Plan Comparison Table',
        icon     : 'list-view',
        category : 'layout',
        keywords : [ 'comparison', 'pricing', 'table', 'plans', 'features' ],

        attributes: {
            showBadge        : { type:'boolean', default:true },
            badgeText        : { type:'string',  default:'Pricing Breakdown' },
            headingBefore    : { type:'string',  default:'Find the Plan' },
            headingHighlight : { type:'string',  default:"That's Best for You" },
            highlightColor   : { type:'string',  default:'#e0445e' },
            bgColor          : { type:'string',  default:'#0a0a0a' },
            labelColBg       : { type:'string',  default:'#0d9488' },
            labelColText     : { type:'string',  default:'#ffffff' },
            headerTextColor  : { type:'string',  default:'#ffffff' },
            rowBgEven        : { type:'string',  default:'#141414' },
            rowBgOdd         : { type:'string',  default:'#0f0f0f' },
            rowTextColor     : { type:'string',  default:'#cccccc' },
            dividerColor     : { type:'string',  default:'#222222' },
            checkColor       : { type:'string',  default:'#e0445e' },
            innerWidth       : { type:'string',  default:'860px' },
            showFooterNote   : { type:'boolean', default:true },
            footerNoteText   : { type:'string',  default:'* Hosting available from AED 99/mo' },
            plans            : { type:'string',  default:'[]' },
            sections         : { type:'string',  default:'[]' },
        },

        edit: function ( props ) {
            var attr     = props.attributes;
            var set      = props.setAttributes;
            var plans    = parsePlans( attr.plans );
            var sections = parseSections( attr.sections );

            /* Seed defaults if empty */
            if ( plans.length === 0 ) {
                var sp = defaultPlans();
                set({ plans: JSON.stringify( sp ) });
                plans = sp;
            }
            if ( sections.length === 0 ) {
                var ss = defaultSections();
                set({ sections: JSON.stringify( ss ) });
                sections = ss;
            }

            /* ── Plan helpers ── */
            function setPlan( i, key, val ) {
                var arr = parsePlans( attr.plans );
                if ( ! arr[i] ) arr[i] = {};
                arr[i][key] = val;
                set({ plans: JSON.stringify(arr) });
            }
            function addPlan() {
                var arr = parsePlans( attr.plans );
                arr.push({ name:'New Plan', price:'', priceSuffix:'AED', btnText:'Get Started', btnUrl:'#', btnNewTab:false });
                // Add 'dash' value to every existing row
                var secs = parseSections( attr.sections );
                secs.forEach(function(sec){ (sec.rows||[]).forEach(function(row){ (row.values = row.values || []).push('dash'); }); });
                set({ plans: JSON.stringify(arr), sections: JSON.stringify(secs) });
            }
            function removePlan( i ) {
                var arr  = parsePlans( attr.plans );
                arr.splice(i,1);
                var secs = parseSections( attr.sections );
                secs.forEach(function(sec){ (sec.rows||[]).forEach(function(row){ if(row.values) row.values.splice(i,1); }); });
                set({ plans: JSON.stringify(arr), sections: JSON.stringify(secs) });
            }

            /* ── Section helpers ── */
            function setSections( secs ) {
                set({ sections: JSON.stringify(secs) });
            }
            function addSection() {
                var secs = parseSections( attr.sections );
                secs.push({ label:'New Section', rows:[] });
                setSections(secs);
            }
            function removeSection( si ) {
                var secs = parseSections( attr.sections );
                secs.splice(si,1);
                setSections(secs);
            }
            function setSectionLabel( si, val ) {
                var secs = parseSections( attr.sections );
                secs[si].label = val;
                setSections(secs);
            }
            function moveSection( si, dir ) {
                var secs = parseSections( attr.sections );
                var sw   = si + dir;
                if ( sw < 0 || sw >= secs.length ) return;
                var tmp = secs[si]; secs[si] = secs[sw]; secs[sw] = tmp;
                setSections(secs);
            }

            /* ── Row helpers ── */
            function addRow( si ) {
                var secs = parseSections( attr.sections );
                var vals = plans.map(function(){ return 'check'; });
                secs[si].rows.push({ label:'New Feature', values: vals });
                setSections(secs);
            }
            function removeRow( si, ri ) {
                var secs = parseSections( attr.sections );
                secs[si].rows.splice(ri,1);
                setSections(secs);
            }
            function setRowLabel( si, ri, val ) {
                var secs = parseSections( attr.sections );
                secs[si].rows[ri].label = val;
                setSections(secs);
            }
            function setRowValue( si, ri, pi, val ) {
                var secs = parseSections( attr.sections );
                if ( ! secs[si].rows[ri].values ) secs[si].rows[ri].values = [];
                secs[si].rows[ri].values[pi] = val;
                setSections(secs);
            }
            function moveRow( si, ri, dir ) {
                var secs = parseSections( attr.sections );
                var sw   = ri + dir;
                var rows = secs[si].rows;
                if ( sw < 0 || sw >= rows.length ) return;
                var tmp = rows[ri]; rows[ri] = rows[sw]; rows[sw] = tmp;
                setSections(secs);
            }

            /* ═══════════════════════════
               SIDEBAR
            ═══════════════════════════ */
            var sidebar = el( InspectorControls, {},

                /* Header */
                el( PanelBody, { title:'Section Header', initialOpen:true },
                    el( ToggleControl, { label:'Show Badge', checked:!!attr.showBadge, onChange:function(v){ set({showBadge:v}); } }),
                    attr.showBadge && el( TextControl, { label:'Badge text', value:attr.badgeText||'', onChange:function(v){ set({badgeText:v}); } }),
                    el( TextControl, { label:'Heading (plain part)',     value:attr.headingBefore||'',    onChange:function(v){ set({headingBefore:v}); } }),
                    el( TextControl, { label:'Heading (highlight part)', value:attr.headingHighlight||'', onChange:function(v){ set({headingHighlight:v}); } }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'8px 0 4px'}}, 'Highlight color'),
                    el( ColorPicker, { color:attr.highlightColor||'#e0445e', onChange:function(v){ set({highlightColor:v}); }, enableAlpha:false })
                ),

                /* Colors */
                el( PanelBody, { title:'Colors & Layout', initialOpen:false },
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 4px'}}, 'Page background'),
                    el( ColorPicker, { color:attr.bgColor||'#0a0a0a', onChange:function(v){ set({bgColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Label column background'),
                    el( ColorPicker, { color:attr.labelColBg||'#0d9488', onChange:function(v){ set({labelColBg:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Label column text'),
                    el( ColorPicker, { color:attr.labelColText||'#ffffff', onChange:function(v){ set({labelColText:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Header text color'),
                    el( ColorPicker, { color:attr.headerTextColor||'#ffffff', onChange:function(v){ set({headerTextColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Row bg (even)'),
                    el( ColorPicker, { color:attr.rowBgEven||'#141414', onChange:function(v){ set({rowBgEven:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Row bg (odd)'),
                    el( ColorPicker, { color:attr.rowBgOdd||'#0f0f0f', onChange:function(v){ set({rowBgOdd:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Row text color'),
                    el( ColorPicker, { color:attr.rowTextColor||'#cccccc', onChange:function(v){ set({rowTextColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Divider / border color'),
                    el( ColorPicker, { color:attr.dividerColor||'#222222', onChange:function(v){ set({dividerColor:v}); }, enableAlpha:false }),
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'12px 0 4px'}}, 'Check icon color'),
                    el( ColorPicker, { color:attr.checkColor||'#e0445e', onChange:function(v){ set({checkColor:v}); }, enableAlpha:false }),
                    el( TextControl, { label:'Inner max-width (e.g. 860px)', value:attr.innerWidth||'860px', onChange:function(v){ set({innerWidth:v}); } })
                ),

                /* Plans */
                el( PanelBody, { title:'Plans / Columns (' + plans.length + ')', initialOpen:true },
                    plans.map(function(plan,i){
                        return el( PanelBody, {
                            key:'plan-'+i,
                            title: (plan.name||('Plan '+(i+1))),
                            initialOpen:false
                        },
                            el( TextControl, { label:'Plan name',    value:plan.name||'',         onChange:function(v){ setPlan(i,'name',v); } }),
                            el( TextControl, { label:'Price',        value:plan.price||'',        onChange:function(v){ setPlan(i,'price',v); } }),
                            el( TextControl, { label:'Price suffix (e.g. AED)', value:plan.priceSuffix||'', onChange:function(v){ setPlan(i,'priceSuffix',v); } }),
                            el( TextControl, { label:'Button text',  value:plan.btnText||'',      onChange:function(v){ setPlan(i,'btnText',v); } }),
                            el( TextControl, { label:'Button URL',   value:plan.btnUrl||'',       onChange:function(v){ setPlan(i,'btnUrl',v); } }),
                            el( ToggleControl, { label:'Open in new tab', checked:!!plan.btnNewTab, onChange:function(v){ setPlan(i,'btnNewTab',v); } }),
                            el( Button, {
                                onClick:function(){ removePlan(i); },
                                variant:'secondary', isDestructive:true, isSmall:true,
                                style:{marginTop:'8px'}
                            }, '✕ Remove this plan')
                        );
                    }),
                    el( Button, {
                        onClick:addPlan, variant:'primary',
                        style:{ width:'100%', justifyContent:'center', marginTop:'10px' }
                    }, '+ Add Plan Column')
                ),

                /* Sections & Rows */
                el( PanelBody, { title:'Comparison Rows (' + sections.length + ' sections)', initialOpen:true },

                    sections.map(function(section,si){
                        var rows = section.rows || [];
                        return el( PanelBody, {
                            key:'sec-'+si,
                            title: (section.label||('Section '+(si+1))) + ' (' + rows.length + ' rows)',
                            initialOpen:false
                        },

                            /* Section controls */
                            el('div',{style:{display:'flex',gap:'6px',marginBottom:'10px'}},
                                el(Button,{onClick:function(){moveSection(si,-1);},variant:'secondary',isSmall:true,disabled:si===0},'↑'),
                                el(Button,{onClick:function(){moveSection(si,1);},variant:'secondary',isSmall:true,disabled:si===sections.length-1},'↓'),
                                el(Button,{onClick:function(){removeSection(si);},variant:'secondary',isDestructive:true,isSmall:true},'✕ Remove Section')
                            ),
                            el( TextControl, {
                                label:'Section heading',
                                value:section.label||'',
                                onChange:function(v){ setSectionLabel(si,v); }
                            }),

                            /* Rows */
                            rows.map(function(row,ri){
                                var vals = row.values || [];
                                return el('div',{
                                    key:'row-'+ri,
                                    style:{background:'rgba(255,255,255,0.03)',border:'1px solid rgba(255,255,255,0.07)',borderRadius:'6px',padding:'10px',marginBottom:'8px'}
                                },
                                    /* Row header */
                                    el('div',{style:{display:'flex',alignItems:'center',justifyContent:'space-between',marginBottom:'8px'}},
                                        el('strong',{style:{fontSize:'12px',color:'#aaa'}},'Row '+(ri+1)),
                                        el('div',{style:{display:'flex',gap:'4px'}},
                                            el(Button,{onClick:function(){moveRow(si,ri,-1);},variant:'secondary',isSmall:true,disabled:ri===0},'↑'),
                                            el(Button,{onClick:function(){moveRow(si,ri,1);},variant:'secondary',isSmall:true,disabled:ri===rows.length-1},'↓'),
                                            el(Button,{onClick:function(){removeRow(si,ri);},variant:'secondary',isDestructive:true,isSmall:true},'✕')
                                        )
                                    ),
                                    el( TextControl, {
                                        label:'Row label',
                                        value:row.label||'',
                                        onChange:function(v){ setRowLabel(si,ri,v); }
                                    }),
                                    /* Value per plan */
                                    plans.map(function(plan,pi){
                                        var currentVal  = vals[pi] !== undefined ? vals[pi] : 'dash';
                                        var currentType = getValueType(currentVal);
                                        return el('div',{ key:'val-'+pi, style:{marginBottom:'6px'} },
                                            el('label',{style:{fontSize:'11px',fontWeight:'500',display:'block',marginBottom:'3px',color:'#999'}},
                                                (plan.name||('Plan '+(pi+1))) + ' value'
                                            ),
                                            el( SelectControl, {
                                                value: currentType,
                                                options: VALUE_OPTIONS,
                                                onChange: function(v){
                                                    if ( v === '_custom' ) {
                                                        setRowValue(si,ri,pi, currentType === '_custom' ? currentVal : '');
                                                    } else {
                                                        setRowValue(si,ri,pi, v);
                                                    }
                                                }
                                            }),
                                            currentType === '_custom' && el( TextControl, {
                                                value: currentVal,
                                                placeholder:'e.g. 1 Page, 2 Weeks…',
                                                onChange:function(v){ setRowValue(si,ri,pi,v); }
                                            })
                                        );
                                    })
                                );
                            }),

                            el( Button, {
                                onClick:function(){ addRow(si); },
                                variant:'secondary', isSmall:true,
                                style:{width:'100%',justifyContent:'center',marginTop:'6px'}
                            }, '+ Add Row')
                        );
                    }),

                    el( Button, {
                        onClick:addSection, variant:'primary',
                        style:{width:'100%',justifyContent:'center',marginTop:'12px'}
                    }, '+ Add Section')
                ),

                /* Footer note */
                el( PanelBody, { title:'Footer Note', initialOpen:false },
                    el( ToggleControl, { label:'Show footer note', checked:!!attr.showFooterNote, onChange:function(v){ set({showFooterNote:v}); } }),
                    attr.showFooterNote && el( TextControl, {
                        label:'Note text',
                        value:attr.footerNoteText||'',
                        onChange:function(v){ set({footerNoteText:v}); }
                    })
                )
            ); /* end sidebar */

            /* ═══════════════════════════
               EDITOR PREVIEW
            ═══════════════════════════ */
            var hiColor     = attr.highlightColor  || '#e0445e';
            var labelBg     = attr.labelColBg      || '#0d9488';
            var labelTxt    = attr.labelColText    || '#ffffff';
            var headerTxt   = attr.headerTextColor || '#ffffff';
            var rowEven     = attr.rowBgEven       || '#141414';
            var rowOdd      = attr.rowBgOdd        || '#0f0f0f';
            var rowTxt      = attr.rowTextColor    || '#cccccc';
            var divider     = attr.dividerColor    || '#222222';
            var checkClr    = attr.checkColor      || '#e0445e';

            /* Check / Dash icons */
            function renderCellValue( val ) {
                if ( val === 'check' ) {
                    return el('span',{style:{
                        display:'inline-flex',alignItems:'center',justifyContent:'center',
                        width:'28px',height:'28px',borderRadius:'50%',
                        background:'rgba(255,255,255,0.07)',color:checkClr
                    }},
                        el('svg',{width:'18',height:'18',viewBox:'0 0 24 24',fill:'none',stroke:'currentColor',strokeWidth:'2.5',strokeLinecap:'round',strokeLinejoin:'round'},
                            el('polyline',{points:'20 6 9 17 4 12'})
                        )
                    );
                }
                if ( val === 'dash' || val === '' ) {
                    return el('span',{style:{fontSize:'18px',color:'#444'}},'—');
                }
                return el('span',{style:{fontSize:'13px',fontWeight:'500',color:rowTxt}}, val);
            }

            var thStyle = { padding:'22px 16px', fontSize:'13px', fontWeight:'600', textAlign:'center', verticalAlign:'bottom', borderBottom:'1px solid '+divider, color:headerTxt };
            var tdBase  = { padding:'12px 16px', fontSize:'13px', verticalAlign:'middle', textAlign:'center', borderLeft:'1px solid '+divider, color:rowTxt };
            var tdLabel = { padding:'12px 16px', fontSize:'13px', fontWeight:'500', verticalAlign:'middle', textAlign:'left', background:labelBg, color:labelTxt, borderRight:'1px solid rgba(255,255,255,0.1)' };

            return el( Fragment, {},
                sidebar,

                el('section',{ className:'sg-pct', style:{ background: attr.bgColor||'#0a0a0a' } },
                    el('div',{ className:'sg-pct__inner', style:{ maxWidth: attr.innerWidth||'860px', margin:'0 auto' } },

                        /* Badge */
                        attr.showBadge && attr.badgeText &&
                        el('div',{ className:'sg-pct__header-top' },
                            el('span',{ className:'sg-pct__badge' }, attr.badgeText)
                        ),

                        /* Heading */
                        ( attr.headingBefore || attr.headingHighlight ) &&
                        el('h2',{ className:'sg-pct__heading' },
                            attr.headingBefore    && el('span',{style:{color:headerTxt}}, attr.headingBefore + ' '),
                            attr.headingHighlight && el('span',{style:{color:hiColor}},   attr.headingHighlight)
                        ),

                        /* Table */
                        ( plans.length > 0 ) &&
                        el('div',{ className:'sg-pct__table-wrap', style:{ '--sg-pct-divider':divider, '--sg-pct-check':checkClr, border:'1px solid '+divider, borderRadius:'14px', overflowX:'auto' } },
                            el('table',{ className:'sg-pct__table', style:{ width:'100%', borderCollapse:'collapse', tableLayout:'fixed' } },

                                /* thead */
                                el('thead',{},
                                    el('tr',{},
                                        el('th',{ style:Object.assign({},thStyle,{ textAlign:'left', background:labelBg, color:labelTxt, borderRadius:'14px 0 0 0', verticalAlign:'middle', fontSize:'15px', fontWeight:'700', width:'220px' }) },
                                            'Compare Plan Details'
                                        ),
                                        plans.map(function(plan,pi){
                                            return el('th',{ key:'th'+pi, style:thStyle },
                                                el('span',{style:{display:'block',fontSize:'18px',fontWeight:'600',marginBottom:'6px',color:headerTxt}}, plan.name||''),
                                                plan.price && el('span',{style:{display:'block',fontSize:'32px',fontWeight:'800',letterSpacing:'-0.02em',lineHeight:'1',color:hiColor}},
                                                    plan.price,
                                                    plan.priceSuffix && el('span',{style:{fontSize:'13px',fontWeight:'500',marginLeft:'4px',opacity:0.75,color:headerTxt}}, plan.priceSuffix)
                                                )
                                            );
                                        })
                                    )
                                ),

                                /* tbody */
                                el('tbody',{},
                                    (function(){
                                        var cells = [];
                                        var gRow  = 0;
                                        sections.forEach(function(section,si){
                                            /* Section heading */
                                            if (section.label) {
                                                cells.push(
                                                    el('tr',{ key:'sec-head-'+si, style:{ background:rowOdd } },
                                                        el('td',{
                                                            colSpan: plans.length+1,
                                                            style:{ padding:'9px 18px', fontSize:'11px', fontWeight:'600', textTransform:'uppercase', letterSpacing:'0.1em', opacity:0.5, color:rowTxt, borderBottom:'1px solid '+divider, borderTop:'1px solid '+divider }
                                                        }, section.label)
                                                    )
                                                );
                                            }
                                            (section.rows||[]).forEach(function(row,ri){
                                                var bg = (gRow%2===0) ? rowEven : rowOdd;
                                                gRow++;
                                                cells.push(
                                                    el('tr',{ key:'row-'+si+'-'+ri, style:{ background:bg, borderBottom:'1px solid '+divider } },
                                                        el('td',{ style:tdLabel }, row.label||''),
                                                        plans.map(function(plan,pi){
                                                            var val = (row.values||[])[pi] !== undefined ? (row.values||[])[pi] : 'dash';
                                                            return el('td',{ key:'cell'+pi, style:tdBase }, renderCellValue(val));
                                                        })
                                                    )
                                                );
                                            });
                                        });
                                        return cells;
                                    })()
                                ),

                                /* tfoot — CTA buttons */
                                el('tfoot',{},
                                    el('tr',{ style:{ background: attr.bgColor||'#0a0a0a' } },
                                        el('td',{ style:Object.assign({},tdLabel,{ borderTop:'1px solid '+divider, padding:'18px 16px' }) }),
                                        plans.map(function(plan,pi){
                                            return el('td',{ key:'cta'+pi, style:Object.assign({},tdBase,{ borderTop:'1px solid '+divider, padding:'18px 16px' }) },
                                                el('a',{
                                                    href:'#',
                                                    className:'sg-pct__btn',
                                                    style:{ display:'inline-block', width:'100%', textAlign:'center', padding:'12px 16px', borderRadius:'999px', border:'1.5px solid rgba(255,255,255,0.3)', color:'#fff', fontSize:'14px', fontWeight:'500', textDecoration:'none', boxSizing:'border-box', whiteSpace:'nowrap' }
                                                }, plan.btnText||'Get Started')
                                            );
                                        })
                                    )
                                )
                            )
                        ),

                        /* Footer note */
                        attr.showFooterNote && attr.footerNoteText &&
                        el('p',{ className:'sg-pct__footer-note', style:{ marginTop:'14px', fontSize:'12px', color:'#555' } }, attr.footerNoteText)
                    )
                )
            );
        },

        save: function() { return null; }
    });

} )();
