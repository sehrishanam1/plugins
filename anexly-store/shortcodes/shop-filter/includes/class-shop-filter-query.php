<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Shop_Filter_Query
 * Fetches real WooCommerce products based on filter params
 */
class Shop_Filter_Query {

    /**
     * Get filtered products
     *
     * @param array $filters {
     *   categories  => array of category slugs
     *   brands      => array of brand term slugs  (taxonomy: product_brand)
     *   duration    => array of duration term slugs (attribute: pa_duration / pa_3-months etc)
     *   price_min   => float
     *   price_max   => float
     *   orderby     => popular | rating | recent | default
     *   paged       => int
     *   per_page    => int
     * }
     * @return array { products, total, max_price, min_price }
     */
    public static function get_products( $filters = array() ) {

        $defaults = array(
            'categories' => array(),
            'brands'     => array(),
            'duration'   => array(),
            'price_min'  => 0,
            'price_max'  => 999999,
            'orderby'    => 'popular',
            'paged'      => 1,
            'per_page'   => 6,
        );
        $filters = wp_parse_args( $filters, $defaults );

        // --- Base args ---
        $args = array(
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => intval( $filters['per_page'] ),
            'paged'          => intval( $filters['paged'] ),
            'meta_query'     => array( 'relation' => 'AND' ),
            'tax_query'      => array( 'relation' => 'AND' ),
        );

        // --- Price filter ---
        $args['meta_query'][] = array(
            'key'     => '_price',
            'value'   => array( floatval( $filters['price_min'] ), floatval( $filters['price_max'] ) ),
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        );

        // --- Categories filter ---
        if ( ! empty( $filters['categories'] ) ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_text_field', (array) $filters['categories'] ),
                'operator' => 'IN',
            );
        }

        // --- Brands filter (custom taxonomy: product_brand) ---
        if ( ! empty( $filters['brands'] ) ) {
            $args['tax_query'][] = array(
                'taxonomy' => 'product_brand',
                'field'    => 'slug',
                'terms'    => array_map( 'sanitize_text_field', (array) $filters['brands'] ),
                'operator' => 'IN',
            );
        }

        // --- Duration filter ---
        if ( ! empty( $filters['duration'] ) ) {
            $dur_terms = (array) $filters['duration'];
            global $wpdb;
            
            $like_queries = array();
            foreach ( $dur_terms as $dur ) {
                $dur_clean = sanitize_text_field( wp_unslash( $dur ) );
                $dur_base = rtrim( $dur_clean, 'sS' ); // e.g. "3 month"
                $like_queries[] = $wpdb->prepare( "meta_value LIKE %s", '%' . $wpdb->esc_like( $dur_base ) . '%' );
            }
            $like_sql = implode( ' OR ', $like_queries );
            
            // Find IDs for local matching
            $local_ids = $wpdb->get_col( "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_product_attributes' AND ($like_sql)" );
            
            // Taxonomy matching
            $tax_ids = array();
            $duration_tax_terms = array_map( 'sanitize_title', $dur_terms );
            if ( taxonomy_exists( 'pa_duration' ) && ! empty( $duration_tax_terms ) ) {
                $placeholders = implode( ',', array_fill( 0, count( $duration_tax_terms ), '%s' ) );
                $tax_ids = $wpdb->get_col( $wpdb->prepare(
                    "SELECT tr.object_id FROM {$wpdb->term_relationships} tr
                     JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                     JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                     WHERE tt.taxonomy = 'pa_duration' AND t.slug IN ($placeholders)",
                    $duration_tax_terms
                ) );
            }
            
            $combined_ids = array_unique( array_merge( $local_ids, $tax_ids ) );
            if ( empty( $combined_ids ) ) {
                $args['post__in'] = array( 0 );
            } elseif ( isset( $args['post__in'] ) ) {
                $args['post__in'] = array_intersect( $args['post__in'], $combined_ids );
                if ( empty( $args['post__in'] ) ) $args['post__in'] = array( 0 );
            } else {
                $args['post__in'] = $combined_ids;
            }
        }

        // --- Orderby ---
        switch ( $filters['orderby'] ) {
            case 'popular':
                $args['meta_key'] = 'total_sales';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'recent':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            default:
                $args['orderby'] = 'menu_order';
                $args['order']   = 'ASC';
        }

        $query    = new WP_Query( $args );
        $products = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $product = wc_get_product( get_the_ID() );
                if ( ! $product ) continue;

                // Get categories
                $cats = wp_get_post_terms( get_the_ID(), 'product_cat', array( 'fields' => 'names' ) );

                // Get brands
                $brands = wp_get_post_terms( get_the_ID(), 'product_brand', array( 'fields' => 'names' ) );

                // Get duration attribute
                $duration_terms = wp_get_post_terms( get_the_ID(), 'pa_duration', array( 'fields' => 'names' ) );
                if ( is_wp_error( $duration_terms ) ) {
                    $duration_terms = array();
                }

                $attrs = get_post_meta( get_the_ID(), '_product_attributes', true );
                if ( is_array( $attrs ) ) {
                    foreach ( $attrs as $attr ) {
                        if ( ! isset( $attr['name'] ) ) continue;
                        $name = strtolower( trim( $attr['name'] ) );
                        if ( in_array( $name, array( 'months', 'purchase months', 'purchase month' ) ) ) {
                            if ( empty( $attr['is_taxonomy'] ) ) {
                                $vals = array_map( 'trim', explode( '|', $attr['value'] ) );
                                foreach( $vals as $v ) {
                                    if ( ! empty( $v ) ) {
                                        $v_lower = strtolower( $v );
                                        if ( strpos( $v_lower, '3 month' ) !== false ) $v = '3 months';
                                        elseif ( strpos( $v_lower, '6 month' ) !== false ) $v = '6 months';
                                        elseif ( strpos( $v_lower, '12 month' ) !== false ) $v = '12 months';
                                        
                                        if ( ! in_array( $v, $duration_terms ) ) {
                                            $duration_terms[] = $v;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Get rating
                $rating      = $product->get_average_rating();
                $review_count = $product->get_review_count();

                // Get image
                $image_url = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' );
                if ( ! $image_url ) {
                    $image_url = wc_placeholder_img_src( 'thumbnail' );
                }

                // Get price
                $price         = $product->get_price();
                $regular_price = $product->get_regular_price();
                $sale_price    = $product->get_sale_price();
                $on_sale       = $product->is_on_sale();

                // Badge logic
                $badge = null;
                if ( $on_sale ) $badge = 'sale';
                elseif ( $product->is_featured() ) $badge = 'featured';

                $products[] = array(
                    'id'            => get_the_ID(),
                    'name'          => get_the_title(),
                    'permalink'     => get_permalink(),
                    'image'         => $image_url,
                    'price'         => $price,
                    'regular_price' => $regular_price,
                    'sale_price'    => $sale_price,
                    'on_sale'       => $on_sale,
                    'rating'        => floatval( $rating ),
                    'review_count'  => intval( $review_count ),
                    'categories'    => ! is_wp_error( $cats ) ? $cats : array(),
                    'brands'        => ! is_wp_error( $brands ) ? $brands : array(),
                    'duration'      => ! is_wp_error( $duration_terms ) ? $duration_terms : array(),
                    'badge'         => $badge,
                    'add_to_cart_url' => $product->add_to_cart_url(),
                    'add_to_cart_text' => $product->add_to_cart_text(),
                );
            }
            wp_reset_postdata();
        }

        return array(
            'products'  => $products,
            'total'     => $query->found_posts,
            'max_pages' => $query->max_num_pages,
        );
    }

    /**
     * Get all filter options (categories, brands, duration, price range)
     */
    public static function get_filter_options() {

        // Categories (exclude uncategorized)
        $categories = get_terms( array(
            'taxonomy'   => 'product_cat',
            'hide_empty' => true,
            'exclude'    => get_option( 'default_product_cat' ),
        ));

        // Brands
        $brands = array();
        if ( taxonomy_exists( 'product_brand' ) ) {
            $brands = get_terms( array(
                'taxonomy'   => 'product_brand',
                'hide_empty' => true,
            ));
        }

        // Duration attribute (Hardcoded to 3, 6, 12 months as requested)
        $duration = array(
            array( 'slug' => '3 months', 'name' => '3 months' ),
            array( 'slug' => '6 months', 'name' => '6 months' ),
            array( 'slug' => '12 months', 'name' => '12 months' ),
        );

        // Price range
        global $wpdb;
        $price_range = $wpdb->get_row(
            "SELECT MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                    MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price
             FROM {$wpdb->postmeta}
             WHERE meta_key = '_price'
             AND meta_value != ''
             AND meta_value > 0"
        );

        return array(
            'categories' => ! is_wp_error( $categories ) ? $categories : array(),
            'brands'     => ! is_wp_error( $brands ) ? $brands : array(),
            'duration'   => ! is_wp_error( $duration ) ? $duration : array(),
            'min_price'  => $price_range ? floatval( $price_range->min_price ) : 0,
            'max_price'  => $price_range ? floatval( $price_range->max_price ) : 1000,
        );
    }
}