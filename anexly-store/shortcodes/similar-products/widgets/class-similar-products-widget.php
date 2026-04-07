<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Anexly_Similar_Products_Widget extends \Elementor\Widget_Base {

    public function get_name()       { return 'anexly_similar_products'; }
    public function get_title()      { return __( 'Similar Products Slider (Woo)', 'anexly-shortcodes' ); }
    public function get_icon()       { return 'eicon-products'; }
    public function get_categories() { return [ 'anexly' ]; }
    public function get_keywords()   { return [ 'similar', 'products', 'slider', 'carousel', 'woocommerce', 'woo', 'anexly' ]; }

    public function get_style_depends()  { return [ 'swiper', 'anx-similar-products' ]; }
    public function get_script_depends() { return [ 'swiper' ]; }

    /* ── Controls ─────────────────────────────────────────────── */
    protected function register_controls() {
        $this->start_controls_section( 'section_content', [
            'label' => __( 'Content', 'anexly-shortcodes' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );

        $this->add_control( 'heading', [
            'label'       => __( 'Heading', 'anexly-shortcodes' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => __( 'Similar Products', 'anexly-shortcodes' ),
            'label_block' => true,
        ] );

        $this->add_control( 'products_source', [
            'label'   => __( 'Products Source', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'latest',
            'options' => [
                'latest'   => __( 'Latest Products',   'anexly-shortcodes' ),
                'featured' => __( 'Featured Products', 'anexly-shortcodes' ),
                'sale'     => __( 'On Sale Products',  'anexly-shortcodes' ),
            ],
        ] );

        $this->add_control( 'product_category', [
            'label'   => __( 'Category Filter', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '',
            'options' => $this->get_product_categories_options(),
        ] );

        $this->add_control( 'posts_per_page', [
            'label'   => __( 'Products Count', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 6,
            'min'     => 1,
            'max'     => 20,
        ] );

        $this->add_control( 'slides_per_view_desktop', [
            'label'   => __( 'Slides Per View (Desktop)', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::NUMBER,
            'default' => 3,
            'min'     => 1,
            'max'     => 6,
        ] );

        $this->add_control( 'orderby', [
            'label'   => __( 'Order By', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'date',
            'options' => [
                'date'       => __( 'Date',       'anexly-shortcodes' ),
                'title'      => __( 'Title',      'anexly-shortcodes' ),
                'menu_order' => __( 'Menu Order', 'anexly-shortcodes' ),
                'rand'       => __( 'Random',     'anexly-shortcodes' ),
            ],
        ] );

        $this->add_control( 'order', [
            'label'   => __( 'Order', 'anexly-shortcodes' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => 'DESC',
            'options' => [
                'DESC' => 'DESC',
                'ASC'  => 'ASC',
            ],
        ] );

        $this->add_control( 'button_text', [
            'label'       => __( 'Button Text', 'anexly-shortcodes' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => __( 'Purchase now', 'anexly-shortcodes' ),
            'label_block' => true,
        ] );

        $this->add_control( 'show_sale_badge', [
            'label'        => __( 'Show Sale Badge', 'anexly-shortcodes' ),
            'type'         => \Elementor\Controls_Manager::SWITCHER,
            'label_on'     => __( 'Yes', 'anexly-shortcodes' ),
            'label_off'    => __( 'No',  'anexly-shortcodes' ),
            'return_value' => 'yes',
            'default'      => 'yes',
        ] );

        $this->add_control( 'price_suffix', [
            'label'       => __( 'Price Suffix Text', 'anexly-shortcodes' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '',
            'placeholder' => __( 'Example: /month', 'anexly-shortcodes' ),
            'label_block' => true,
        ] );

        $this->end_controls_section();
    }

    /* ── Render ───────────────────────────────────────────────── */
    protected function render() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            echo '<p style="color:red;">WooCommerce is required.</p>';
            return;
        }

        // Elementor passes its own settings array — normalise through the same
        // helper so behaviour is identical to the shortcode.
        $raw      = $this->get_settings_for_display();
        $settings = anx_sp_normalise_settings( $raw );
        $products = anx_sp_get_products( $settings );

        if ( empty( $products ) ) {
            echo '<div class="anx-similar-products-widget"><p>No WooCommerce products found.</p></div>';
            return;
        }

        // Enqueue assets (CSS + Swiper). Elementor also handles style/script
        // depends declared above, but calling this ensures the inline CSS is
        // always output even in the editor preview.
        anx_sp_enqueue_assets();

        // Use the Elementor widget instance ID for a unique DOM id.
        $widget_id = 'anx-sp-' . $this->get_id();
        $per_view  = $settings['slides_per_view_desktop'];

        // Shared template renders identical HTML for both shortcode + Elementor.
        include ANEXLY_SP_PATH . 'templates/widget.php';
    }

    /* ── Helpers ──────────────────────────────────────────────── */
    private function get_product_categories_options() {
        $options = [ '' => __( 'All Categories', 'anexly-shortcodes' ) ];
        $terms   = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => false ] );
        if ( ! is_wp_error( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->slug ] = $term->name;
            }
        }
        return $options;
    }
}
