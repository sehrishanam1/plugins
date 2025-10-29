<?php
/*
Plugin Name: Flatsome Custom Posts Widget + Shortcode
Description: Displays posts from a selected category with featured image for the first post only (widget + shortcode).
Version: 1.2
Author: Sehrish Anam
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// ====================================================
// 1️⃣ WIDGET CLASS
// ====================================================
class Flatsome_Custom_Posts_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'flatsome_custom_posts_widget',
            __('Flatsome: Category Posts', 'flatsome'),
            array('description' => __('Show posts from a selected category with featured image on first post only.', 'flatsome'))
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo flatsome_render_custom_posts_section(
            $instance['category'] ?? '',
            $instance['number'] ?? 5
        );
        echo $args['after_widget'];
    }

    public function form($instance) {
        $category = !empty($instance['category']) ? $instance['category'] : '';
        $number   = !empty($instance['number']) ? absint($instance['number']) : 5;

        $categories = get_categories(array('hide_empty' => false));
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('category'); ?>">
                <?php _e('Select Category:'); ?>
            </label>
            <select class="widefat"
                    id="<?php echo $this->get_field_id('category'); ?>"
                    name="<?php echo $this->get_field_name('category'); ?>">
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat->term_id); ?>"
                        <?php selected($category, $cat->term_id); ?>>
                        <?php echo esc_html($cat->name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>">
                <?php _e('Number of Posts:'); ?>
            </label>
            <input class="tiny-text"
                   id="<?php echo $this->get_field_id('number'); ?>"
                   name="<?php echo $this->get_field_name('number'); ?>"
                   type="number"
                   step="1"
                   min="1"
                   value="<?php echo $number; ?>"
                   size="3" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['category'] = !empty($new_instance['category']) ? strip_tags($new_instance['category']) : '';
        $instance['number']   = !empty($new_instance['number']) ? absint($new_instance['number']) : 5;
        return $instance;
    }
}

function register_flatsome_custom_posts_widget() {
    register_widget('Flatsome_Custom_Posts_Widget');
}
add_action('widgets_init', 'register_flatsome_custom_posts_widget');


// ====================================================
// 2️⃣ REUSABLE RENDER FUNCTION (used by both widget + shortcode)
// ====================================================
function flatsome_render_custom_posts_section($category = '', $number = 5) {
    ob_start();

    // Support category slug or ID
    if (!empty($category) && !is_numeric($category)) {
        $term = get_term_by('slug', $category, 'category');
        if ($term && !is_wp_error($term)) {
            $category_id = $term->term_id;
        } else {
            $category_id = 0; // invalid category
        }
    } else {
        $category_id = (int) $category;
    }

    // Header (only if valid category found)
    if ($category_id) {
        $cat_obj = get_category($category_id);
        if ($cat_obj) {
            echo '<div class="flatsome-widget-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">';
            echo '<h3 class="widget-title" style="margin:0;">' . esc_html($cat_obj->name) . '</h3>';
            echo '<a href="' . esc_url(get_category_link($cat_obj->term_id)) . '" class="see-all-link" style="font-size:14px;color:#0073aa;text-decoration:none;">See All</a>';
            echo '</div>';
        }
    }

    // Query posts by category (slug or ID)
    $query_args = array(
        'posts_per_page' => $number,
        'post_status'    => 'publish',
    );

    if ($category_id) {
        $query_args['cat'] = $category_id;
    } elseif (!empty($category)) {
        $query_args['category_name'] = $category; // fallback for slug
    }

    $query = new WP_Query($query_args);

    if ($query->have_posts()) {
        echo '<div class="flatsome-custom-posts">';
        $count = 0;

        while ($query->have_posts()) {
            $query->the_post();
            $count++;

            echo '<div class="flatsome-post-item" style="margin-bottom:12px;">';
            echo '<a href="' . esc_url(get_permalink()) . '" style="text-decoration:none;color:inherit;display:block;">';

            if ($count === 1 && has_post_thumbnail()) {
                echo '<div class="flatsome-post-thumb" style="margin-bottom:8px;">';
                the_post_thumbnail('medium', array(
                    'style' => 'width:100%;height:auto;border-radius:6px;'
                ));
                echo '</div>';
            }

            echo '<h4 class="flatsome-post-title" style="margin:0 0 4px;font-size:16px;">' . get_the_title() . '</h4>';
            echo '<div class="flatsome-post-excerpt" style="font-size:14px;color:#666;">' . wp_trim_words(get_the_excerpt(), 50, '...') . '</div>';

            echo '</a>';
            echo '</div>';
        }

        echo '</div>';
        wp_reset_postdata();
    } else {
        echo '<p>No posts found in this category.</p>';
    }

    return ob_get_clean();
}



// ====================================================
// 3️⃣ SHORTCODE
// ====================================================
function flatsome_category_posts_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'number'   => 5,
    ), $atts, 'flatsome_category_posts');

    return flatsome_render_custom_posts_section(
        $atts['category'],
        $atts['number']
    );
}
add_shortcode('flatsome_category_posts', 'flatsome_category_posts_shortcode');