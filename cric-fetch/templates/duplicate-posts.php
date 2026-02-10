<?php
function sa_duplicate_post_as_draft() {
    global $wpdb;

    if (!isset($_GET['post']) || !isset($_REQUEST['duplicate_nonce']) || !wp_verify_nonce($_REQUEST['duplicate_nonce'], basename(__FILE__)))
        return;

    $post_id = absint($_GET['post']);
    $post = get_post($post_id);

    if ($post) {
        $new_post = array(
            'post_title'     => $post->post_title . ' (Copy)',
            'post_content'   => $post->post_content,
            'post_status'    => 'draft',
            'post_author'    => get_current_user_id(),
            'post_type'      => $post->post_type,
            'post_excerpt'   => $post->post_excerpt,
        );

        $new_post_id = wp_insert_post($new_post);

        // Copy categories, tags, and meta
        $taxonomies = get_object_taxonomies($post->post_type);
        foreach ($taxonomies as $taxonomy) {
            $terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
            wp_set_object_terms($new_post_id, $terms, $taxonomy, false);
        }

        $meta = get_post_meta($post_id);
        foreach ($meta as $key => $values) {
            foreach ($values as $value) {
                add_post_meta($new_post_id, $key, maybe_unserialize($value));
            }
        }

        wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
        exit;
    }
}
add_action('admin_action_sa_duplicate_post_as_draft', 'sa_duplicate_post_as_draft');

function sa_duplicate_post_link($actions, $post) {
    if (current_user_can('edit_posts')) {
        $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=sa_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce') . '" title="Duplicate this item">Duplicate</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'sa_duplicate_post_link', 10, 2);
?>