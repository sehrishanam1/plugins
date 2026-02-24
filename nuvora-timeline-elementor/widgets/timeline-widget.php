<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor Timeline Widget
 */
class Timeline_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name
     */
    public function get_name() {
        return 'timeline_widget';
    }

    /**
     * Get widget title
     */
    public function get_title() {
        return __('Timeline', 'nuvora-timeline-elementor');
    }

    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-time-line';
    }

    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['general'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls() {

        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Timeline Items', 'nuvora-timeline-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'timeline_style',
            [
                'label' => __('Timeline Style', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'style1',
                'options' => [
                    'style1' => __('Style 1 - Vertical Alternating', 'nuvora-timeline-elementor'),
                    'style2' => __('Style 2 - Horizontal Cards', 'nuvora-timeline-elementor'),
                ],
            ]
        );

        $this->add_control(
            'style2_grouping',
            [
                'label' => __('Grouping Type (Style 2)', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'month_and_date',
                'options' => [
                    'none' => __('No Grouping', 'nuvora-timeline-elementor'),
                    'month_only' => __('Group by Month Only', 'nuvora-timeline-elementor'),
                    'month_and_date' => __('Group by Month and Date', 'nuvora-timeline-elementor'),
                ],
                'condition' => [
                    'timeline_style' => 'style2',
                ],
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            'icon',
            [
                'label' => __('Icon', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'lni-cake',
                    'library' => 'lineicons',
                ],
            ]
        );

        $repeater->add_control(
            'date',
            [
                'label' => __('Date', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('20-08-2019', 'nuvora-timeline-elementor'),
            ]
        );

        $repeater->add_control(
            'group_title',
            [
                'label' => __('Group Title (Style 2 Only)', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => '',
                'description' => __('Used for month/section headers in Style 2', 'nuvora-timeline-elementor'),
            ]
        );

        $repeater->add_control(
            'title',
            [
                'label' => __('Title', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Event Title', 'nuvora-timeline-elementor'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'description',
            [
                'label' => __('Description', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel, nam! Nam eveniet ut aliquam ab asperiores, accusamus iure veniam corporis incidunt reprehenderit accusantium id aut architecto harum quidem dolorem in!', 'nuvora-timeline-elementor'),
                'rows' => 5,
            ]
        );

        $repeater->add_control(
            'use_custom_colors',
            [
                'label' => __('Custom Colors', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'nuvora-timeline-elementor'),
                'label_off' => __('No', 'nuvora-timeline-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );

        $repeater->add_control(
            'primary_color',
            [
                'label' => __('Primary Color', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#9251ac',
                'condition' => [
                    'use_custom_colors' => 'yes',
                ],
            ]
        );

        $repeater->add_control(
            'secondary_color',
            [
                'label' => __('Secondary Color', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f6a4ec',
                'condition' => [
                    'use_custom_colors' => 'yes',
                ],
            ]
        );

        $repeater->add_control(
            'color_scheme',
            [
                'label' => __('Color Scheme', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'type1',
                'options' => [
                    'type1' => __('Purple/Pink', 'nuvora-timeline-elementor'),
                    'type2' => __('Blue', 'nuvora-timeline-elementor'),
                    'type3' => __('Green', 'nuvora-timeline-elementor'),
                ],
                'condition' => [
                    'use_custom_colors!' => 'yes',
                ],
            ]
        );

        $repeater->add_control(
            'animation_delay',
            [
                'label' => __('Animation Delay', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'delay-1s',
                'options' => [
                    '' => __('No Delay', 'nuvora-timeline-elementor'),
                    'delay-1s' => __('1 Second', 'nuvora-timeline-elementor'),
                    'delay-2s' => __('2 Seconds', 'nuvora-timeline-elementor'),
                    'delay-3s' => __('3 Seconds', 'nuvora-timeline-elementor'),
                    'delay-4s' => __('4 Seconds', 'nuvora-timeline-elementor'),
                ],
            ]
        );

        $this->add_control(
            'timeline_items',
            [
                'label' => __('Timeline Items', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::REPEATER,
                'fields' => $repeater->get_controls(),
                'default' => [
                    [
                        'icon' => [
                            'value' => 'lni-cake',
                            'library' => 'lineicons',
                        ],
                        'date' => '20, Tuesday',
                        'group_title' => 'August, 2024',
                        'title' => 'Birthday',
                        'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Vel, nam! Nam eveniet ut aliquam ab asperiores.',
                        'color_scheme' => 'type1',
                        'animation_delay' => 'delay-3s',
                        'use_custom_colors' => 'no',
                    ],
                    [
                        'icon' => [
                            'value' => 'lni-burger',
                            'library' => 'lineicons',
                        ],
                        'date' => '21, Wednesday',
                        'group_title' => 'August, 2024',
                        'title' => 'Lunch',
                        'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Accusamus iure veniam corporis incidunt.',
                        'color_scheme' => 'type2',
                        'animation_delay' => 'delay-2s',
                        'use_custom_colors' => 'no',
                    ],
                    [
                        'icon' => [
                            'value' => 'lni-slim',
                            'library' => 'lineicons',
                        ],
                        'date' => '22, Thursday',
                        'group_title' => 'August, 2024',
                        'title' => 'Exercise',
                        'description' => 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Reprehenderit accusantium id aut architecto.',
                        'color_scheme' => 'type3',
                        'animation_delay' => 'delay-1s',
                        'use_custom_colors' => 'no',
                    ],
                    [
                        'icon' => [
                            'value' => 'lni-star',
                            'library' => 'lineicons',
                        ],
                        'date' => '1, Monday',
                        'group_title' => 'September, 2024',
                        'title' => 'New Month',
                        'description' => 'Starting fresh with new goals and plans for the month ahead.',
                        'color_scheme' => 'type1',
                        'animation_delay' => '',
                        'use_custom_colors' => 'no',
                    ],
                ],
                'title_field' => '{{{ title }}}',
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'nuvora-timeline-elementor'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'enable_animation',
            [
                'label' => __('Enable Animation', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'nuvora-timeline-elementor'),
                'label_off' => __('No', 'nuvora-timeline-elementor'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );

        $this->add_responsive_control(
            'timeline_width',
            [
                'label' => __('Timeline Width', 'nuvora-timeline-elementor'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px', 'vw'],
                'range' => [
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                    'px' => [
                        'min' => 200,
                        'max' => 2000,
                    ],
                    'vw' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'vw',
                    'size' => 50,
                ],
                'selectors' => [
                    '{{WRAPPER}} .timeline__event' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $timeline_style = $settings['timeline_style'];

        if ($timeline_style === 'style2') {
            $this->render_style2($settings);
        } else {
            $this->render_style1($settings);
        }
    }

    /**
     * Render Style 1 - Vertical Alternating
     */
    protected function render_style1($settings) {
        $no_anim_class = $settings['enable_animation'] === 'yes' ? '' : ' timeline-no-animation';
        ?>
        <div class="timeline timeline-style1<?php echo esc_attr($no_anim_class); ?>">
            <?php foreach ($settings['timeline_items'] as $index => $item) : 
                $color_scheme_class = $item['use_custom_colors'] === 'yes' ? '' : 'timeline__event--' . $item['color_scheme'];
                
                $connector_style = '';
                if ($item['use_custom_colors'] === 'yes') {
                    $connector_style = 'style="--connector-color: ' . esc_attr($item['secondary_color']) . ';"';
                }
            ?>
                <?php
                // Build data attribute for JS animation delay
                $delay_val = isset($item['animation_delay']) ? $item['animation_delay'] : '';
                $data_delay = 'data-delay="' . esc_attr($delay_val) . '"';
                ?>
                <div class="timeline__event <?php echo esc_attr($color_scheme_class); ?>" <?php echo wp_kses_post($connector_style); ?> <?php echo esc_attr($delay_val) ? 'data-delay="' . esc_attr($delay_val) . '"' : ''; ?>>
                    <div class="timeline__event__icon" <?php 
                        if ($item['use_custom_colors'] === 'yes') {
                            echo 'style="background-color: ' . esc_attr($item['secondary_color']) . '; color: ' . esc_attr($item['primary_color']) . ';"';
                        }
                    ?>>
                        <?php 
                        if (!empty($item['icon']['value'])) {
                            $icon_attributes = [
                                'aria-hidden' => 'true',
                                'class' => 'timeline-icon',
                            ];
                            
                            // Add width and height for SVGs
                            if (isset($item['icon']['library']) && $item['icon']['library'] === 'svg') {
                                $icon_attributes['width'] = '32';
                                $icon_attributes['height'] = '32';
                            }
                            
                            \Elementor\Icons_Manager::render_icon($item['icon'], $icon_attributes); 
                        }
                        ?>
                    </div>
                    <div class="timeline__event__date" <?php 
                        if ($item['use_custom_colors'] === 'yes') {
                            echo 'style="background-color: ' . esc_attr($item['primary_color']) . '; color: ' . esc_attr($item['secondary_color']) . ';"';
                        }
                    ?>>
                        <?php echo esc_html($item['date']); ?>
                    </div>
                    <div class="timeline__event__content">
                        <div class="timeline__event__title" <?php 
                            if ($item['use_custom_colors'] === 'yes') {
                                echo 'style="color: ' . esc_attr($item['primary_color']) . ';"';
                            }
                        ?>>
                            <?php echo esc_html($item['title']); ?>
                        </div>
                        <div class="timeline__event__description">
                            <p><?php echo esc_html($item['description']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

    /**
     * Render Style 2 - Horizontal Cards
     */
    protected function render_style2($settings) {
        $grouping = isset($settings['style2_grouping']) ? $settings['style2_grouping'] : 'month_and_date';
        
        if ($grouping === 'none') {
            // No grouping - render all items in order
            ?>
            <div class="timeline timeline-style2">
                <div class="timeline-section">
                    <div class="timeline-row">
                        <?php foreach ($settings['timeline_items'] as $item) : ?>
                        <div class="timeline-col">
                            <?php $this->render_timeline_box($item); ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php
        } elseif ($grouping === 'month_only') {
            // Group by month only
            $grouped_items = [];
            foreach ($settings['timeline_items'] as $item) {
                $group = !empty($item['group_title']) ? $item['group_title'] : 'Ungrouped';
                if (!isset($grouped_items[$group])) {
                    $grouped_items[$group] = [];
                }
                $grouped_items[$group][] = $item;
            }
            ?>
            <div class="timeline timeline-style2">
                <?php foreach ($grouped_items as $group_title => $group_items) : 
                    $entry_count = count($group_items);
                    /* translators: %d: number of entries */
                    $entries_text = sprintf(_n('%d Entry', '%d Entries', $entry_count, 'nuvora-timeline-elementor'), $entry_count);
                ?>
                    <?php if ($group_title !== 'Ungrouped') : ?>
                    <div class="timeline-month">
                        <?php echo esc_html($group_title); ?>
                        <span><?php echo esc_html($entries_text); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="timeline-section">
                        <div class="timeline-row">
                            <?php foreach ($group_items as $item) : ?>
                            <div class="timeline-col">
                                <?php $this->render_timeline_box($item); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        } else {
            // Group by month AND date (default)
            $grouped_items = [];
            foreach ($settings['timeline_items'] as $item) {
                $group = !empty($item['group_title']) ? $item['group_title'] : 'Ungrouped';
                if (!isset($grouped_items[$group])) {
                    $grouped_items[$group] = [];
                }
                $grouped_items[$group][] = $item;
            }
            
            ?>
            <div class="timeline timeline-style2">
                <?php foreach ($grouped_items as $group_title => $group_items) : 
                    $entry_count = count($group_items);
                    /* translators: %d: number of entries */
                    $entries_text = sprintf(_n('%d Entry', '%d Entries', $entry_count, 'nuvora-timeline-elementor'), $entry_count);
                ?>
                    <?php if ($group_title !== 'Ungrouped') : ?>
                    <div class="timeline-month">
                        <?php echo esc_html($group_title); ?>
                        <span><?php echo esc_html($entries_text); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php
                    // Group by date within the month
                    $date_groups = [];
                    foreach ($group_items as $item) {
                        $date = !empty($item['date']) ? $item['date'] : 'No Date';
                        if (!isset($date_groups[$date])) {
                            $date_groups[$date] = [];
                        }
                        $date_groups[$date][] = $item;
                    }
                    
                    foreach ($date_groups as $date => $date_items) :
                    ?>
                    <div class="timeline-section">
                        <div class="timeline-date"><?php echo esc_html($date); ?></div>
                        <div class="timeline-row">
                            <?php foreach ($date_items as $item) : ?>
                            <div class="timeline-col">
                                <?php $this->render_timeline_box($item); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <?php
        }
    }

    /**
     * Helper function to render a timeline box for Style 2
     */
    protected function render_timeline_box($item) {
        ?>
        <div class="timeline-box" <?php 
            if ($item['use_custom_colors'] === 'yes') {
                echo 'style="background-color: ' . esc_attr($item['secondary_color']) . '; border-color: ' . esc_attr($item['primary_color']) . ';"';
            }
        ?>>
            <div class="box-title" <?php 
                if ($item['use_custom_colors'] === 'yes') {
                    echo 'style="border-color: ' . esc_attr($item['primary_color']) . ';"';
                }
            ?>>
                <?php 
                if (!empty($item['icon']['value'])) {
                    $icon_attributes = [
                        'aria-hidden' => 'true',
                        'class' => 'timeline-icon',
                    ];
                    
                    if (isset($item['icon']['library']) && $item['icon']['library'] === 'svg') {
                        $icon_attributes['width'] = '16';
                        $icon_attributes['height'] = '16';
                    }
                    
                    \Elementor\Icons_Manager::render_icon($item['icon'], $icon_attributes); 
                }
                ?>
                <span <?php 
                    if ($item['use_custom_colors'] === 'yes') {
                        echo 'style="color: ' . esc_attr($item['primary_color']) . ';"';
                    }
                ?>><?php echo esc_html($item['title']); ?></span>
            </div>
            <div class="box-content">
                <p><?php echo esc_html($item['description']); ?></p>
                <?php if (!empty($item['date'])) : ?>
                <div class="box-footer"><?php echo esc_html($item['date']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <# if (settings.timeline_style === 'style1') { #>
            <# if (settings.timeline_items.length) { #>
                <# 
                var noAnimClass = settings.enable_animation !== 'yes' ? ' timeline-no-animation' : '';
                #>
                <div class="timeline timeline-style1{{{ noAnimClass }}}">
                    <# _.each(settings.timeline_items, function(item, index) { 
                        var colorSchemeClass = item.use_custom_colors === 'yes' ? '' : 'timeline__event--' + item.color_scheme;
                        var connectorStyle = item.use_custom_colors === 'yes' ? 'style="--connector-color: ' + item.secondary_color + ';"' : '';
                    #>
                        <div class="timeline__event tl-box-done tl-line-h tl-line-v {{{ colorSchemeClass }}}" {{{ connectorStyle }}} data-delay="{{{ item.animation_delay }}}">
                            <div class="timeline__event__icon" <# if (item.use_custom_colors === 'yes') { #>style="background-color: {{{ item.secondary_color }}}; color: {{{ item.primary_color }}};"<# } #>>
                                <# 
                                var iconHTML = elementor.helpers.renderIcon( view, item.icon, { 'aria-hidden': true, 'class': 'timeline-icon' }, 'i' , 'object' );
                                if (iconHTML && iconHTML.rendered) { 
                                    print(iconHTML.value);
                                }
                                #>
                            </div>
                            <div class="timeline__event__date" <# if (item.use_custom_colors === 'yes') { #>style="background-color: {{{ item.primary_color }}}; color: {{{ item.secondary_color }}};"<# } #>>
                                {{{ item.date }}}
                            </div>
                            <div class="timeline__event__content">
                                <div class="timeline__event__title" <# if (item.use_custom_colors === 'yes') { #>style="color: {{{ item.primary_color }}};"<# } #>>
                                    {{{ item.title }}}
                                </div>
                                <div class="timeline__event__description">
                                    <p>{{{ item.description }}}</p>
                                </div>
                            </div>
                        </div>
                    <# }); #>
                </div>
            <# } #>
        <# } else if (settings.timeline_style === 'style2') { #>
            <div class="timeline timeline-style2">
                <div class="timeline-section">
                    <div class="timeline-row">
                        <# _.each(settings.timeline_items, function(item) { #>
                        <div class="timeline-col">
                            <div class="timeline-box">
                                <div class="box-title">
                                    <# 
                                    var iconHTML = elementor.helpers.renderIcon( view, item.icon, { 'aria-hidden': true, 'class': 'timeline-icon' }, 'i' , 'object' );
                                    if (iconHTML && iconHTML.rendered) { 
                                        print(iconHTML.value);
                                    }
                                    #>
                                    <span>{{{ item.title }}}</span>
                                </div>
                                <div class="box-content">
                                    <p>{{{ item.description }}}</p>
                                </div>
                            </div>
                        </div>
                        <# }); #>
                    </div>
                </div>
            </div>
        <# } #>
        <?php
    }
}