(function () {

    var el = window.wp.element.createElement;
    var Fragment = window.wp.element.Fragment;

    var InspectorControls = window.wp.blockEditor.InspectorControls;
    var MediaUpload = window.wp.blockEditor.MediaUpload;
    var MediaUploadCheck = window.wp.blockEditor.MediaUploadCheck;

    var PanelBody = window.wp.components.PanelBody;
    var TextControl = window.wp.components.TextControl;
    var TextareaControl = window.wp.components.TextareaControl;
    var ToggleControl = window.wp.components.ToggleControl;
    var RangeControl = window.wp.components.RangeControl;
    var ColorPicker = window.wp.components.ColorPicker;
    var Button = window.wp.components.Button;
    var SelectControl = window.wp.components.SelectControl;

    function parseCards(str) {
        if (!str) return [];
        try { return JSON.parse(str) || []; }
        catch (e) { return []; }
    }

    function safeStr(v) {
        return (v !== null && v !== undefined) ? String(v) : '';
    }

    window.wp.blocks.registerBlockType('sg-blocks/feature-grid', {
        title: 'Feature Grid',
        icon: 'grid-view',
        category: 'layout',
        keywords: ['features', 'grid', 'cards', 'icons'],

        attributes: {
            showBadge: { type: 'boolean', default: true },
            badgeText: { type: 'string', default: 'Whats wrong with Your Current Website' },
            showHeading: { type: 'boolean', default: true },
            headingLine1: { type: 'string', default: 'Most Websites' },
            headingLine2: { type: 'string', default: 'Leak Revenue' },
            headingLine2Color: { type: 'string', default: '#00c8ff' },
            showSubheading: { type: 'boolean', default: true },
            subheading: { type: 'string', default: 'If your website was built by a freelancer, a junior dev, or an offshore agency — it was probably designed to look good in a proposal.' },
            columns: { type: 'number', default: 3 },
            cards: { type: 'string', default: '[]' },
            bgColor: { type: 'string', default: '#000000' },
            cardBgColor: { type: 'string', default: '#141414' },
            cardBorderColor: { type: 'string', default: '#2a2a2a' },
            innerWidth: { type: 'string', default: '1100px' },
            iconColor: { type: 'string', default: '#00c8ff' },
        },

        edit: function (props) {
            var attr = props.attributes;
            var set = props.setAttributes;
            var cards = parseCards(attr.cards);

            function setCard(index, key, val) {
                var arr = parseCards(attr.cards);
                if (!arr[index]) arr[index] = {};
                arr[index][key] = val;
                set({ cards: JSON.stringify(arr) });
            }

            function addCard() {
                var arr = parseCards(attr.cards);
                arr.push({ title: 'Card Title', desc: 'Card description goes here.', iconUrl: '', iconId: 0, iconSvg: '', iconColor: attr.iconColor, bgColor: attr.cardBgColor, borderColor: attr.cardBorderColor });
                set({ cards: JSON.stringify(arr) });
            }

            function removeCard(index) {
                var arr = parseCards(attr.cards);
                arr.splice(index, 1);
                set({ cards: JSON.stringify(arr) });
            }

            function moveCard(index, dir) {
                var arr = parseCards(attr.cards);
                var swap = index + dir;
                if (swap < 0 || swap >= arr.length) return;
                var tmp = arr[index];
                arr[index] = arr[swap];
                arr[swap] = tmp;
                set({ cards: JSON.stringify(arr) });
            }

            /* ── Sidebar ── */
            var sidebar = el(InspectorControls, {},

                /* Section Header */
                el(PanelBody, { title: 'Section Header', initialOpen: true },
                    el(ToggleControl, {
                        label: 'Show Badge', checked: !!attr.showBadge,
                        onChange: function (v) { set({ showBadge: v }); }
                    }),
                    attr.showBadge && el(TextControl, {
                        label: 'Badge text', value: attr.badgeText || '',
                        onChange: function (v) { set({ badgeText: v }); }
                    }),
                    el(ToggleControl, {
                        label: 'Show Heading', checked: !!attr.showHeading,
                        onChange: function (v) { set({ showHeading: v }); }
                    }),
                    attr.showHeading && el(Fragment, { key: 'heading-fields' },
                        el(TextControl, {
                            label: 'Heading line 1 (white)', value: attr.headingLine1 || '',
                            onChange: function (v) { set({ headingLine1: v }); }
                        }),
                        el(TextControl, {
                            label: 'Heading line 2 (accent color)', value: attr.headingLine2 || '',
                            onChange: function (v) { set({ headingLine2: v }); }
                        }),
                        el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '10px 0 4px' } }, 'Line 2 color'),
                        el(ColorPicker, {
                            color: attr.headingLine2Color || '#00c8ff',
                            onChange: function (v) { set({ headingLine2Color: v }); },
                            enableAlpha: false
                        })
                    ),
                    el(ToggleControl, {
                        label: 'Show Subheading', checked: !!attr.showSubheading,
                        onChange: function (v) { set({ showSubheading: v }); }
                    }),
                    attr.showSubheading && el(TextareaControl, {
                        label: 'Subheading text', value: attr.subheading || '', rows: 3,
                        onChange: function (v) { set({ subheading: v }); }
                    })
                ),

                /* Grid Settings */
                el(PanelBody, { title: 'Grid Settings', initialOpen: true },
                    el(RangeControl, {
                        label: 'Columns', value: attr.columns || 3, min: 1, max: 6,
                        onChange: function (v) { set({ columns: v }); }
                    }),
                    el(TextControl, {
                        label: 'Inner max-width', value: attr.innerWidth || '1100px',
                        onChange: function (v) { set({ innerWidth: v }); }
                    })
                ),

                /* Cards repeater */
                el(PanelBody, { title: 'Cards (' + cards.length + ')', initialOpen: true },
                    cards.map(function (card, i) {
                        if (!card) return null;
                        return el('div', {
                            key: 'card-' + i,
                            style: { border: '1px solid #333', borderRadius: '6px', padding: '12px', marginBottom: '12px' }
                        },
                            /* Card header row */
                            el('div', { style: { display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '10px' } },
                                el('strong', { style: { fontSize: '12px', color: '#ccc' } }, 'Card ' + (i + 1)),
                                el('div', { style: { display: 'flex', gap: '4px' } },
                                    el(Button, { onClick: function () { moveCard(i, -1); }, variant: 'tertiary', isSmall: true, disabled: i === 0 }, '↑'),
                                    el(Button, { onClick: function () { moveCard(i, 1); }, variant: 'tertiary', isSmall: true, disabled: i === cards.length - 1 }, '↓'),
                                    el(Button, { onClick: function () { removeCard(i); }, variant: 'tertiary', isDestructive: true, isSmall: true }, '✕')
                                )
                            ),

                            /* Icon upload */
                            el('p', { style: { fontSize: '11px', margin: '0 0 4px', color: '#999' } }, 'Icon (SVG or PNG)'),
                            el(MediaUploadCheck, {},
                                el(MediaUpload, {
                                    onSelect: function (m) {
                                        if (!m || !m.url) return;
                                        var arr = parseCards(attr.cards);
                                        if (!arr[i]) arr[i] = {};
                                        arr[i]['iconUrl'] = safeStr(m.url);
                                        arr[i]['iconId'] = m.id || 0;
                                        set({ cards: JSON.stringify(arr) });
                                    },
                                    allowedTypes: ['image'],
                                    value: card.iconId || 0,
                                    render: function (p) {
                                        return el('div', { style: { marginBottom: '8px' } },
                                            card.iconUrl && el('div', { style: { display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '4px' } },
                                                el('img', { src: card.iconUrl, style: { height: '28px', width: 'auto', objectFit: 'contain' } }),
                                                el(Button, {
                                                    onClick: function () {
                                                        var arr = parseCards(attr.cards);
                                                        if (!arr[i]) arr[i] = {};
                                                        arr[i]['iconUrl'] = '';
                                                        arr[i]['iconId'] = 0;
                                                        set({ cards: JSON.stringify(arr) });
                                                    }
                                                    , variant: 'link', isDestructive: true, isSmall: true
                                                }, 'Remove')
                                            ),
                                            el(Button, { onClick: p.open, variant: 'secondary', isSmall: true }, card.iconUrl ? 'Replace Icon' : 'Upload Icon')
                                        );
                                    }
                                })
                            ),

                            /* Icon SVG paste */
                            el(TextareaControl, {
                                label: 'Or paste SVG code', value: card.iconSvg || '', rows: 2,
                                onChange: function (v) { setCard(i, 'iconSvg', v); }
                            }),

                            /* Icon color */
                            el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '6px 0 4px' } }, 'Icon color'),
                            el(ColorPicker, {
                                color: card.iconColor || attr.iconColor || '#00c8ff',
                                onChange: function (v) { setCard(i, 'iconColor', v); },
                                enableAlpha: false
                            }),

                            el(TextControl, {
                                label: 'Title', value: card.title || '',
                                onChange: function (v) { setCard(i, 'title', v); }
                            }),
                            el(TextareaControl, {
                                label: 'Description', value: card.desc || '', rows: 3,
                                onChange: function (v) { setCard(i, 'desc', v); }
                            }),

                            /* Per-card overrides */
                            el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '6px 0 4px' } }, 'Card bg color'),
                            el(ColorPicker, {
                                color: card.bgColor || attr.cardBgColor || '#141414',
                                onChange: function (v) { setCard(i, 'bgColor', v); },
                                enableAlpha: false
                            }),
                            el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '6px 0 4px' } }, 'Card border color'),
                            el(ColorPicker, {
                                color: card.borderColor || attr.cardBorderColor || '#2a2a2a',
                                onChange: function (v) { setCard(i, 'borderColor', v); },
                                enableAlpha: false
                            })
                        );
                    }),

                    el(Button, {
                        onClick: addCard,
                        variant: 'secondary',
                        style: { width: '100%', justifyContent: 'center', marginTop: '8px' }
                    }, '+ Add Card')
                ),

                /* Global style */
                el(PanelBody, { title: 'Global Colors', initialOpen: false },
                    el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '0 0 4px' } }, 'Section background'),
                    el(ColorPicker, {
                        color: attr.bgColor || '#000000',
                        onChange: function (v) { set({ bgColor: v }); },
                        enableAlpha: false
                    }),
                    el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '10px 0 4px' } }, 'Default card background'),
                    el(ColorPicker, {
                        color: attr.cardBgColor || '#141414',
                        onChange: function (v) { set({ cardBgColor: v }); },
                        enableAlpha: false
                    }),
                    el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '10px 0 4px' } }, 'Default card border'),
                    el(ColorPicker, {
                        color: attr.cardBorderColor || '#2a2a2a',
                        onChange: function (v) { set({ cardBorderColor: v }); },
                        enableAlpha: false
                    }),
                    el('p', { style: { fontSize: '11px', fontWeight: '500', margin: '10px 0 4px' } }, 'Default icon color'),
                    el(ColorPicker, {
                        color: attr.iconColor || '#00c8ff',
                        onChange: function (v) { set({ iconColor: v }); },
                        enableAlpha: false
                    })
                )
            );

            /* ── Editor Preview ── */
            var gridStyle = {
                display: 'grid',
                gap: '20px',
                gridTemplateColumns: 'repeat(' + (attr.columns || 3) + ', 1fr)',
                marginTop: '40px',
            };

            return el(Fragment, {},
                sidebar,
                el('section', {
                    className: 'sg-fg',
                    style: { backgroundColor: attr.bgColor || '#000000' }
                },
                    el('div', { className: 'sg-fg__inner', style: { maxWidth: attr.innerWidth || '1100px', margin: '0 auto' } },

                        attr.showBadge && attr.badgeText &&
                        el('div', { className: 'sg-fg__header-top' },
                            el('span', { className: 'sg-fg__badge' }, attr.badgeText)
                        ),

                        attr.showHeading && (attr.headingLine1 || attr.headingLine2) &&
                        el('h2', { className: 'sg-fg__heading' },
                            attr.headingLine1 && el('span', { className: 'sg-fg__heading-line1' }, attr.headingLine1),
                            attr.headingLine2 && el('span', { className: 'sg-fg__heading-line2', style: { color: attr.headingLine2Color || '#00c8ff' } }, ' ' + attr.headingLine2)
                        ),

                        attr.showSubheading && attr.subheading &&
                        el('p', { className: 'sg-fg__subheading' }, attr.subheading),

                        cards.length === 0
                            ? el('div', {
                                style: { textAlign: 'center', padding: '40px', color: '#555', fontSize: '13px', border: '1px dashed #333', borderRadius: '8px', marginTop: '32px' }
                            }, '← Add cards from the sidebar')
                            : el('div', { style: gridStyle },
                                cards.map(function (card, i) {
                                    if (!card) return null;
                                    return el('div', {
                                        key: 'prev-' + i,
                                        className: 'sg-fg__card',
                                        style: {
                                            background: card.bgColor || attr.cardBgColor || '#141414',
                                            borderColor: card.borderColor || attr.cardBorderColor || '#2a2a2a',
                                        }
                                    },
                                        (card.iconUrl || card.iconSvg) &&
                                        el('div', { className: 'sg-fg__card-icon', style: { color: card.iconColor || attr.iconColor || '#00c8ff' } },
                                            card.iconUrl
                                                ? el('img', { src: card.iconUrl, style: { height: '36px', width: 'auto' } })
                                                : el('span', { dangerouslySetInnerHTML: { __html: card.iconSvg || '' } })
                                        ),
                                        card.title && el('h3', { className: 'sg-fg__card-title' }, card.title),
                                        card.desc && el('p', { className: 'sg-fg__card-desc' }, card.desc)
                                    );
                                })
                            )
                    )
                )
            );
        },

        save: function () {
            return null;
        }
    });

})();
