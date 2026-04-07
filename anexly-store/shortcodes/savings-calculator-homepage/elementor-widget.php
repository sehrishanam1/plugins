<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'ELEMENTOR_VERSION' ) ) return;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

/**
 * Elementor Widget: Savings Calculator Homepage
 *
 * Wraps the [savings_calculator_homepage] shortcode with a full
 * Elementor control panel — including a repeater for manual product selection.
 */
class Anexly_SCH_Elementor_Widget extends Widget_Base {

    public function get_name()        { return 'anexly_savings_calculator'; }
    public function get_title()       { return __( 'Savings Calculator', 'anexly' ); }
    public function get_icon()        { return 'eicon-counter'; }
    public function get_categories()  { return [ 'general' ]; }
    public function get_keywords()    { return [ 'savings', 'calculator', 'price', 'compare', 'woocommerce' ]; }

    // ── Register controls ──────────────────────────────────────────────────────
    protected function register_controls() {

        // ── SECTION: Content ───────────────────────────────────────────────────
        $this->start_controls_section( 'section_content', [
            'label' => __( 'Content', 'anexly' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'title', [
            'label'       => __( 'Title', 'anexly' ),
            'type'        => Controls_Manager::TEXT,
            'default'     => __( 'See How Much You Save Instantly', 'anexly' ),
            'label_block' => true,
        ] );

        $this->add_control( 'subtitle', [
            'label'       => __( 'Subtitle', 'anexly' ),
            'type'        => Controls_Manager::TEXTAREA,
            'default'     => '',
            'placeholder' => __( 'Leave empty to auto-generate from site name', 'anexly' ),
            'label_block' => true,
        ] );

        $this->end_controls_section();

        // ── SECTION: Products ──────────────────────────────────────────────────
        $this->start_controls_section( 'section_products', [
            'label' => __( 'Products', 'anexly' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'product_source', [
            'label'   => __( 'Product Source', 'anexly' ),
            'type'    => Controls_Manager::SELECT,
            'default' => 'auto',
            'options' => [
                'auto'   => __( 'Auto (latest / popular / etc.)', 'anexly' ),
                'manual' => __( 'Manual — pick specific products', 'anexly' ),
            ],
        ] );

        // ── Manual products repeater ───────────────────────────────────────────
        $repeater = new \Elementor\Repeater();

        $repeater->add_control( 'product_id', [
            'label'       => __( 'Product ID', 'anexly' ),
            'type'        => Controls_Manager::NUMBER,
            'min'         => 1,
            'placeholder' => __( 'e.g. 42', 'anexly' ),
            'description' => __( 'Find the ID in WooCommerce → Products (hover the product row).', 'anexly' ),
        ] );

        $repeater->add_control( 'product_label', [
            'label'       => __( 'Label (optional)', 'anexly' ),
            'type'        => Controls_Manager::TEXT,
            'placeholder' => __( 'Auto from product title', 'anexly' ),
            'description' => __( 'Leave empty to use the product name.', 'anexly' ),
        ] );

        $this->add_control( 'manual_products', [
            'label'       => __( 'Products List', 'anexly' ),
            'type'        => Controls_Manager::REPEATER,
            'fields'      => $repeater->get_controls(),
            'default'     => [],
            'title_field' => '{{{ product_label || "Product ID: " + product_id }}}',
            'condition'   => [ 'product_source' => 'manual' ],
        ] );

        // ── Auto source options ────────────────────────────────────────────────
        $this->add_control( 'limit', [
            'label'     => __( 'Number of Products', 'anexly' ),
            'type'      => Controls_Manager::NUMBER,
            'default'   => 10,
            'min'       => 1,
            'max'       => 50,
            'condition' => [ 'product_source' => 'auto' ],
        ] );

        $this->add_control( 'orderby', [
            'label'     => __( 'Order By', 'anexly' ),
            'type'      => Controls_Manager::SELECT,
            'default'   => 'date',
            'options'   => [
                'date'       => __( 'Date (newest)', 'anexly' ),
                'popularity' => __( 'Popularity', 'anexly' ),
                'rating'     => __( 'Rating', 'anexly' ),
                'price'      => __( 'Price (low → high)', 'anexly' ),
                'title'      => __( 'Title (A–Z)', 'anexly' ),
            ],
            'condition' => [ 'product_source' => 'auto' ],
        ] );

        $this->end_controls_section();

        // ── SECTION: Calculator ────────────────────────────────────────────────
        $this->start_controls_section( 'section_calc', [
            'label' => __( 'Calculator Settings', 'anexly' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'months', [
            'label'   => __( 'Months for Annual Calc', 'anexly' ),
            'type'    => Controls_Manager::NUMBER,
            'default' => 12,
            'min'     => 1,
            'max'     => 24,
        ] );

        $this->end_controls_section();

        // ── SECTION: CTA Button ────────────────────────────────────────────────
        $this->start_controls_section( 'section_cta', [
            'label' => __( 'CTA Button', 'anexly' ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'cta_text', [
            'label'   => __( 'Button Text', 'anexly' ),
            'type'    => Controls_Manager::TEXT,
            'default' => __( 'Browse Deals', 'anexly' ),
        ] );

        $this->add_control( 'cta_url', [
            'label'         => __( 'Button URL', 'anexly' ),
            'type'          => Controls_Manager::URL,
            'placeholder'   => '/shop',
            'show_external' => true,
            'default'       => [ 'url' => '/shop' ],
        ] );

        $this->end_controls_section();
    }

    // ── Render ─────────────────────────────────────────────────────────────────
    protected function render() {

        $settings = $this->get_settings_for_display();

        // Build shortcode attributes
        $sc_atts = [
            'title'    => ! empty( $settings['title'] )    ? $settings['title']    : '',
            'subtitle' => ! empty( $settings['subtitle'] ) ? $settings['subtitle'] : '',
            'months'   => ! empty( $settings['months'] )   ? intval( $settings['months'] ) : 12,
            'cta_text' => ! empty( $settings['cta_text'] ) ? $settings['cta_text'] : 'Browse Deals',
            'cta_url'  => ! empty( $settings['cta_url']['url'] ) ? $settings['cta_url']['url'] : '/shop',
        ];

        if ( $settings['product_source'] === 'manual' && ! empty( $settings['manual_products'] ) ) {
            $ids = array_filter( array_map( function( $row ) {
                return absint( $row['product_id'] );
            }, $settings['manual_products'] ) );

            $sc_atts['products'] = implode( ',', $ids );
        } else {
            $sc_atts['limit']   = ! empty( $settings['limit'] )   ? intval( $settings['limit'] ) : 10;
            $sc_atts['orderby'] = ! empty( $settings['orderby'] ) ? $settings['orderby'] : 'date';
        }

        // Render via the shortcode function directly (avoids double escaping)
        echo anexly_sch_render( $sc_atts ); // phpcs:ignore WordPress.Security.EscapeOutput
    }

    // ── Editor placeholder ─────────────────────────────────────────────────────
    protected function content_template() {
        ?>
        <div style="padding:40px;text-align:center;background:#F7F7F8;border-radius:16px;">
            <p style="font-size:15px;color:#555;margin:0;">
                <strong>Savings Calculator</strong><br>
                Preview available in the frontend.
            </p>
        </div>
        <?php
    }
}
