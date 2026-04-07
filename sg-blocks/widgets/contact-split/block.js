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
    var SelectControl   = window.wp.components.SelectControl;

    /* ── Helpers ── */
    function parseFields( str ) {
        if ( ! str ) return [];
        try { return JSON.parse( str ) || []; } catch(e) { return []; }
    }

    function defaultFields() {
        return [
            { type:'text',         label:'Your Name',           placeholder:'Ahmed Al Rashid',       required:true,  width:'half', options:[] },
            { type:'text',         label:'Company Name',         placeholder:'Al Rashid Contracting', required:false, width:'half', options:[] },
            { type:'text',         label:'Your Industry',        placeholder:'Enter your industry',   required:false, width:'full', options:[] },
            { type:'checkboxgroup',label:'What do you need?',    placeholder:'',                      required:false, width:'full', options:['Landing Page','Full Website','Not Sure'] },
            { type:'tel',          label:'Whatsapp Number',      placeholder:'+971 50 123 4567',      required:false, width:'half', options:[] },
            { type:'email',        label:'Email Address',        placeholder:'ahmed@gmail.com',       required:true,  width:'half', options:[] },
            { type:'textarea',     label:'Anything Else? (optional)', placeholder:'Write your description here…', required:false, width:'full', options:[] },
        ];
    }

    var FIELD_TYPE_OPTIONS = [
        { label:'Text',           value:'text'         },
        { label:'Email',          value:'email'        },
        { label:'Phone / Tel',    value:'tel'          },
        { label:'Textarea',       value:'textarea'     },
        { label:'Checkbox Group', value:'checkboxgroup'},
    ];

    var WIDTH_OPTIONS = [
        { label:'Full width', value:'full' },
        { label:'Half width', value:'half' },
    ];

    /* ── Color label helper ── */
    function clrLabel( text ) {
        return el('p', { style:{ fontSize:'11px', fontWeight:'500', margin:'12px 0 4px', color:'#999' } }, text );
    }

    /* ── Small section divider ── */
    function divider() {
        return el('hr', { style:{ border:'none', borderTop:'1px solid #2a2a2a', margin:'16px 0' } });
    }

    /* ═══════════════════════════
       Register block
    ═══════════════════════════ */
    window.wp.blocks.registerBlockType( 'sg-blocks/contact-split', {
        title    : 'Contact Split',
        icon     : 'email-alt',
        category : 'layout',
        keywords : [ 'contact', 'form', 'split', 'proposal' ],

        attributes: {
            bgColor            : { type:'string',  default:'#080808' },
            innerWidth         : { type:'string',  default:'1100px'  },
            imagePosition      : { type:'string',  default:'left'    },
            leftImage          : { type:'string',  default:''        },
            leftImageId        : { type:'integer', default:0         },
            leftBg             : { type:'string',  default:'#111111' },
            leftOverlayColor   : { type:'string',  default:'rgba(0,0,0,0.35)' },
            leftOverlayEnable  : { type:'boolean', default:true      },
            showBadge          : { type:'boolean', default:false     },
            badgeText          : { type:'string',  default:'Contact Us' },
            showHeading        : { type:'boolean', default:false     },
            heading            : { type:'string',  default:'Get in Touch' },
            showSubheading     : { type:'boolean', default:false     },
            subheading         : { type:'string',  default:'Fill in the form and we will get back to you.' },
            rightBg            : { type:'string',  default:'#080808' },
            labelColor         : { type:'string',  default:'#cccccc' },
            inputBg            : { type:'string',  default:'#111111' },
            inputBorderColor   : { type:'string',  default:'#2a2a2a' },
            inputTextColor     : { type:'string',  default:'#ffffff' },
            placeholderColor   : { type:'string',  default:'#555555' },
            checkboxBorder     : { type:'string',  default:'#444444' },
            checkboxLabelColor : { type:'string',  default:'#cccccc' },
            btnText            : { type:'string',  default:'Send My Free Proposal Request' },
            btnBg              : { type:'string',  default:'#00bcd4' },
            btnTextColor       : { type:'string',  default:'#000000' },
            btnBgHover         : { type:'string',  default:'#00acc1' },
            fields             : { type:'string',  default:'[]'      },
            formAction         : { type:'string',  default:''        },
            formMethod         : { type:'string',  default:'POST'    },
            successMessage     : { type:'string',  default:"Thank you! We'll be in touch soon." },
        },

        edit: function ( props ) {
            var attr   = props.attributes;
            var set    = props.setAttributes;
            var fields = parseFields( attr.fields );

            /* Seed defaults */
            if ( fields.length === 0 ) {
                var seed = defaultFields();
                set({ fields: JSON.stringify( seed ) });
                fields = seed;
            }

            /* ── Field helpers ── */
            function setField( i, key, val ) {
                var arr = parseFields( attr.fields );
                if ( ! arr[i] ) arr[i] = {};
                arr[i][key] = val;
                set({ fields: JSON.stringify(arr) });
            }
            function addField() {
                var arr = parseFields( attr.fields );
                arr.push({ type:'text', label:'New Field', placeholder:'', required:false, width:'full', options:[] });
                set({ fields: JSON.stringify(arr) });
            }
            function removeField( i ) {
                var arr = parseFields( attr.fields );
                arr.splice(i,1);
                set({ fields: JSON.stringify(arr) });
            }
            function moveField( i, dir ) {
                var arr = parseFields( attr.fields );
                var sw  = i + dir;
                if ( sw < 0 || sw >= arr.length ) return;
                var tmp = arr[i]; arr[i] = arr[sw]; arr[sw] = tmp;
                set({ fields: JSON.stringify(arr) });
            }

            /* ═══════════════ SIDEBAR ═══════════════ */
            var sidebar = el( InspectorControls, {},

                /* Layout */
                el( PanelBody, { title:'Layout', initialOpen:true },
                    el( TextControl, { label:'Inner max-width', value:attr.innerWidth||'1100px', onChange:function(v){ set({innerWidth:v}); } }),
                    el( SelectControl, {
                        label:'Image panel position',
                        value: attr.imagePosition||'left',
                        options:[ {label:'Left',value:'left'}, {label:'Right',value:'right'} ],
                        onChange:function(v){ set({imagePosition:v}); }
                    }),
                    clrLabel('Section background color'),
                    el( ColorPicker, { color:attr.bgColor||'#080808', onChange:function(v){ set({bgColor:v}); }, enableAlpha:false })
                ),

                /* Image panel */
                el( PanelBody, { title:'Image Panel', initialOpen:true },
                    el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'0 0 6px',color:'#999'}}, 'Panel image'),
                    el( MediaUploadCheck, {},
                        el( MediaUpload, {
                            onSelect:function(m){ if(!m||!m.url) return; set({ leftImage:m.url, leftImageId:m.id||0 }); },
                            allowedTypes:['image'],
                            value: attr.leftImageId||0,
                            render:function(p){
                                return el('div',{},
                                    attr.leftImage && el('div',{style:{marginBottom:'8px'}},
                                        el('img',{src:attr.leftImage,style:{width:'100%',borderRadius:'6px',maxHeight:'120px',objectFit:'cover',opacity:0.8}}),
                                        el(Button,{onClick:function(){set({leftImage:'',leftImageId:0});},variant:'link',isDestructive:true,isSmall:true,style:{marginTop:'4px'}},'Remove image')
                                    ),
                                    el(Button,{onClick:p.open,variant:'secondary',isSmall:true}, attr.leftImage?'Replace image':'Upload image')
                                );
                            }
                        })
                    ),
                    clrLabel('Panel background color (shown if no image)'),
                    el( ColorPicker, { color:attr.leftBg||'#111111', onChange:function(v){ set({leftBg:v}); }, enableAlpha:false }),
                    attr.leftImage && el( Fragment, { key:'overlay' },
                        divider(),
                        el( ToggleControl, { label:'Enable image overlay', checked:!!attr.leftOverlayEnable, onChange:function(v){ set({leftOverlayEnable:v}); } }),
                        attr.leftOverlayEnable && el( Fragment, { key:'ov-c' },
                            clrLabel('Overlay color (use alpha slider)'),
                            el( ColorPicker, { color:attr.leftOverlayColor||'rgba(0,0,0,0.35)', onChange:function(v){ set({leftOverlayColor:v}); }, enableAlpha:true })
                        )
                    )
                ),

                /* Form panel header */
                el( PanelBody, { title:'Form Panel Header', initialOpen:false },
                    el( ToggleControl, { label:'Show Badge',      checked:!!attr.showBadge,      onChange:function(v){ set({showBadge:v}); } }),
                    attr.showBadge      && el( TextControl, { label:'Badge text',    value:attr.badgeText||'',    onChange:function(v){ set({badgeText:v}); } }),
                    el( ToggleControl, { label:'Show Heading',    checked:!!attr.showHeading,    onChange:function(v){ set({showHeading:v}); } }),
                    attr.showHeading    && el( TextControl, { label:'Heading',        value:attr.heading||'',      onChange:function(v){ set({heading:v}); } }),
                    el( ToggleControl, { label:'Show Subheading', checked:!!attr.showSubheading, onChange:function(v){ set({showSubheading:v}); } }),
                    attr.showSubheading && el( TextControl, { label:'Subheading',     value:attr.subheading||'',   onChange:function(v){ set({subheading:v}); } })
                ),

                /* Colors */
                el( PanelBody, { title:'Form Colors', initialOpen:false },
                    clrLabel('Form panel background'),
                    el( ColorPicker, { color:attr.rightBg||'#080808',    onChange:function(v){ set({rightBg:v}); },            enableAlpha:false }),
                    clrLabel('Label color'),
                    el( ColorPicker, { color:attr.labelColor||'#cccccc', onChange:function(v){ set({labelColor:v}); },         enableAlpha:false }),
                    clrLabel('Input background'),
                    el( ColorPicker, { color:attr.inputBg||'#111111',    onChange:function(v){ set({inputBg:v}); },            enableAlpha:false }),
                    clrLabel('Input border color'),
                    el( ColorPicker, { color:attr.inputBorderColor||'#2a2a2a', onChange:function(v){ set({inputBorderColor:v}); }, enableAlpha:false }),
                    clrLabel('Input text color'),
                    el( ColorPicker, { color:attr.inputTextColor||'#ffffff',   onChange:function(v){ set({inputTextColor:v}); },   enableAlpha:false }),
                    clrLabel('Placeholder color'),
                    el( ColorPicker, { color:attr.placeholderColor||'#555555', onChange:function(v){ set({placeholderColor:v}); }, enableAlpha:false }),
                    clrLabel('Checkbox border color'),
                    el( ColorPicker, { color:attr.checkboxBorder||'#444444',   onChange:function(v){ set({checkboxBorder:v}); },   enableAlpha:false }),
                    clrLabel('Checkbox label color'),
                    el( ColorPicker, { color:attr.checkboxLabelColor||'#cccccc', onChange:function(v){ set({checkboxLabelColor:v}); }, enableAlpha:false })
                ),

                /* Button */
                el( PanelBody, { title:'Submit Button', initialOpen:false },
                    el( TextControl, { label:'Button text', value:attr.btnText||'', onChange:function(v){ set({btnText:v}); } }),
                    clrLabel('Button background'),
                    el( ColorPicker, { color:attr.btnBg||'#00bcd4',     onChange:function(v){ set({btnBg:v}); },     enableAlpha:false }),
                    clrLabel('Button text color'),
                    el( ColorPicker, { color:attr.btnTextColor||'#000', onChange:function(v){ set({btnTextColor:v}); }, enableAlpha:false }),
                    clrLabel('Button hover background'),
                    el( ColorPicker, { color:attr.btnBgHover||'#00acc1', onChange:function(v){ set({btnBgHover:v}); }, enableAlpha:false })
                ),

                /* Form settings */
                el( PanelBody, { title:'Form Settings', initialOpen:false },
                    el( TextControl, { label:'Form action URL (leave blank for JS-only)', value:attr.formAction||'', onChange:function(v){ set({formAction:v}); } }),
                    el( SelectControl, {
                        label:'Method',
                        value: attr.formMethod||'POST',
                        options:[ {label:'POST',value:'POST'}, {label:'GET',value:'GET'} ],
                        onChange:function(v){ set({formMethod:v}); }
                    }),
                    el( TextControl, { label:'Success message', value:attr.successMessage||'', onChange:function(v){ set({successMessage:v}); } })
                ),

                /* Fields */
                el( PanelBody, { title:'Form Fields (' + fields.length + ')', initialOpen:true },

                    fields.map(function(field,i){
                        var label = field.label ? ( field.label.length > 36 ? field.label.slice(0,36)+'…' : field.label ) : 'Field '+(i+1);
                        return el( PanelBody, {
                            key:'field-'+i,
                            title: '[' + (field.type||'text') + '] ' + label,
                            initialOpen:false,
                        },
                            /* Move / Remove */
                            el('div',{style:{display:'flex',gap:'6px',marginBottom:'10px'}},
                                el(Button,{onClick:function(){moveField(i,-1);},variant:'secondary',isSmall:true,disabled:i===0},'↑ Up'),
                                el(Button,{onClick:function(){moveField(i,1);},variant:'secondary',isSmall:true,disabled:i===fields.length-1},'↓ Down'),
                                el(Button,{onClick:function(){removeField(i);},variant:'secondary',isDestructive:true,isSmall:true},'✕ Remove')
                            ),

                            el( SelectControl, {
                                label:'Field type',
                                value: field.type||'text',
                                options: FIELD_TYPE_OPTIONS,
                                onChange:function(v){ setField(i,'type',v); }
                            }),
                            el( TextControl, { label:'Label',       value:field.label||'',       onChange:function(v){ setField(i,'label',v); } }),
                            field.type !== 'checkboxgroup' &&
                            el( TextControl, { label:'Placeholder', value:field.placeholder||'', onChange:function(v){ setField(i,'placeholder',v); } }),
                            el( SelectControl, {
                                label:'Width',
                                value: field.width||'full',
                                options: WIDTH_OPTIONS,
                                onChange:function(v){ setField(i,'width',v); }
                            }),
                            el( ToggleControl, { label:'Required', checked:!!field.required, onChange:function(v){ setField(i,'required',v); } }),

                            /* Checkbox options */
                            field.type === 'checkboxgroup' && el( Fragment, { key:'cb-opts' },
                                el('p',{style:{fontSize:'11px',fontWeight:'500',margin:'10px 0 4px',color:'#999'}}, 'Options (one per line)'),
                                el( TextareaControl, {
                                    value: (field.options||[]).join('\n'),
                                    rows:4,
                                    onChange:function(v){
                                        setField(i,'options', v.split('\n').map(function(s){return s.trim();}).filter(Boolean));
                                    }
                                })
                            )
                        );
                    }),

                    el( Button, {
                        onClick:addField, variant:'primary',
                        style:{width:'100%',justifyContent:'center',marginTop:'10px'}
                    }, '+ Add Field')
                )
            ); /* end sidebar */

            /* ═══════════════ EDITOR PREVIEW ═══════════════ */
            var leftStyle = { background: attr.leftBg||'#111', position:'relative', overflow:'hidden', minHeight:'400px' };
            if ( attr.leftImage ) {
                leftStyle.backgroundImage    = 'url(' + attr.leftImage + ')';
                leftStyle.backgroundSize     = 'cover';
                leftStyle.backgroundPosition = 'center';
                leftStyle.backgroundRepeat   = 'no-repeat';
            }

            var cssVars = {
                '--sg-cs-input-bg'  : attr.inputBg          || '#111',
                '--sg-cs-input-bdr' : attr.inputBorderColor  || '#2a2a2a',
                '--sg-cs-input-txt' : attr.inputTextColor    || '#fff',
                '--sg-cs-ph'        : attr.placeholderColor  || '#555',
                '--sg-cs-label'     : attr.labelColor        || '#ccc',
                '--sg-cs-cb-bdr'    : attr.checkboxBorder    || '#444',
                '--sg-cs-cb-lbl'    : attr.checkboxLabelColor|| '#ccc',
                '--sg-cs-btn-bg'    : attr.btnBg             || '#00bcd4',
                '--sg-cs-btn-txt'   : attr.btnTextColor      || '#000',
                '--sg-cs-btn-hover' : attr.btnBgHover        || '#00acc1',
            };

            /* Render a field preview */
            function renderFieldPreview( field, i ) {
                var ftype  = field.type || 'text';
                var flabel = field.label || '';
                var fph    = field.placeholder || '';
                var fwidth = (field.width||'full') === 'half' ? 'half' : 'full';

                var colStyle = { gridColumn: fwidth === 'half' ? 'span 1' : '1 / -1' };
                var inputStyle = {
                    width:'100%', boxSizing:'border-box',
                    background: attr.inputBg||'#111',
                    border:'1px solid '+(attr.inputBorderColor||'#2a2a2a'),
                    borderRadius:'8px', padding:'11px 14px',
                    fontSize:'14px', color: attr.inputTextColor||'#fff',
                    fontFamily:'inherit',
                };
                var labelStyle = { display:'block', fontSize:'13px', fontWeight:'500', color:attr.labelColor||'#ccc', marginBottom:'7px' };

                return el('div',{ key:'pf-'+i, style:colStyle },

                    flabel && el('label',{style:labelStyle}, flabel, field.required && el('span',{style:{color:'#e05c5c',marginLeft:'3px'}},'*')),

                    ftype === 'textarea'
                    ? el('textarea',{ style:Object.assign({},inputStyle,{resize:'vertical',minHeight:'110px',opacity:0.8}), placeholder:fph, readOnly:true })

                    : ftype === 'checkboxgroup'
                    ? el('div',{style:{display:'flex',flexWrap:'wrap',gap:'8px 18px',paddingTop:'4px'}},
                        (field.options||[]).map(function(opt,oi){
                            return el('label',{key:'cb'+oi,style:{display:'flex',alignItems:'center',gap:'7px',fontSize:'13.5px',color:attr.checkboxLabelColor||'#ccc',cursor:'default'}},
                                el('span',{style:{width:'16px',height:'16px',border:'1.5px solid '+(attr.checkboxBorder||'#444'),borderRadius:'4px',background:'transparent',flexShrink:0,display:'inline-block'}}),
                                opt
                            );
                        })
                      )

                    : el('input',{ type:ftype, style:Object.assign({},inputStyle,{opacity:0.8}), placeholder:fph, readOnly:true })
                );
            }

            var imgPos = attr.imagePosition === 'right' ? 'right' : 'left';
            var gridOrder = imgPos === 'right'
                ? [ renderFormPanel(), renderImagePanel() ]
                : [ renderImagePanel(), renderFormPanel() ];

            function renderImagePanel() {
                return el('div',{ key:'img-panel', style:leftStyle },
                    attr.leftImage && attr.leftOverlayEnable && el('div',{style:{position:'absolute',inset:0,background:attr.leftOverlayColor||'rgba(0,0,0,0.35)',zIndex:1,pointerEvents:'none'}})
                );
            }

            function renderFormPanel() {
                return el('div',{
                    key:'form-panel',
                    style:Object.assign({ padding:'48px 44px', display:'flex', flexDirection:'column', justifyContent:'center', background:attr.rightBg||'#080808' }, cssVars)
                },
                    attr.showBadge && attr.badgeText && el('div',{style:{marginBottom:'14px'}},
                        el('span',{style:{display:'inline-block',border:'1px solid #333',borderRadius:'999px',padding:'5px 18px',fontSize:'12px',color:'#aaa',background:'transparent'}}, attr.badgeText)
                    ),
                    attr.showHeading && attr.heading && el('h2',{style:{fontSize:'clamp(22px,2.5vw,34px)',fontWeight:'700',color:'#fff',margin:'0 0 10px',lineHeight:'1.2'}}, attr.heading),
                    attr.showSubheading && attr.subheading && el('p',{style:{fontSize:'14px',color:'#888',margin:'0 0 24px',lineHeight:'1.6'}}, attr.subheading),

                    el('div',{style:{display:'grid',gridTemplateColumns:'1fr 1fr',gap:'18px 16px'}},
                        fields.map(function(field,i){ return renderFieldPreview(field,i); })
                    ),

                    el('div',{style:{marginTop:'20px'}},
                        el('button',{
                            style:{
                                display:'inline-block', padding:'14px 32px',
                                background:attr.btnBg||'#00bcd4', color:attr.btnTextColor||'#000',
                                fontSize:'15px', fontWeight:'600', border:'none',
                                borderRadius:'999px', cursor:'default', fontFamily:'inherit',
                            }
                        }, attr.btnText||'Send My Free Proposal Request')
                    )
                );
            }

            return el( Fragment, {},
                sidebar,
                el('section',{ className:'sg-cs', style:{ background:attr.bgColor||'#080808' } },
                    el('div',{
                        style:{ display:'grid', gridTemplateColumns:'1fr 1fr', minHeight:'560px', maxWidth:attr.innerWidth||'1100px', margin:'0 auto', width:'100%' }
                    },
                        imgPos === 'right'
                        ? [ renderFormPanel(), renderImagePanel() ]
                        : [ renderImagePanel(), renderFormPanel() ]
                    )
                )
            );
        },

        save: function() { return null; }
    });

} )();
