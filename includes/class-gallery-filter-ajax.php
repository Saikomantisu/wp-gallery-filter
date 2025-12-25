<?php
if (!defined('ABSPATH')) exit;

class Gallery_Filter_AJAX
{
    public function __construct()
    {
        add_action('wp_ajax_get_images', [$this, 'get_images']);
        add_action('wp_ajax_nopriv_get_images', [$this, 'get_images']);
        add_action('wp_ajax_save_image_categories', [$this, 'save_image_categories']);
        add_action('wp_ajax_get_image_categories', [$this, 'get_image_categories']);
        add_action('wp_ajax_get_filtered_categories', [$this, 'get_filtered_categories']);
        add_action('wp_ajax_nopriv_get_filtered_categories', [$this, 'get_filtered_categories']);
    }

    public function get_images()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gallery_filter_nonce')) {
            wp_send_json_error('Security check failed', 403);
        }

        $paged = max(1, intval($_POST['paged'] ?? 1));
        $category = sanitize_text_field($_POST['category'] ?? '');
        $search = sanitize_text_field($_POST['search'] ?? '');
        $event_location = sanitize_text_field($_POST['event_location'] ?? '');
        $event_type = sanitize_text_field($_POST['event_type'] ?? '');

        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => current_user_can('edit_posts') ? 20 : 12,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);

        $tax_query = [];

        if (!empty($event_location)) {
            $tax_query[] = [
                'taxonomy' => 'gf_image_category',
                'field' => 'term_id',
                'terms' => [$event_location],
            ];
        }

        if (!empty($event_type)) {
            $tax_query[] = [
                'taxonomy' => 'gf_image_category',
                'field' => 'term_id',
                'terms' => [$event_type],
            ];
        }

        $relation = count($tax_query) > 1 ? "AND" : "OR";

        if (!empty($tax_query) && $category !== 'all') {
            $args['tax_query'] = array_merge(
                ['relation' => $relation],
                $tax_query
            );
        } else {
            // For frontend, only show categorized images
            if (!current_user_can('edit_posts')) {
                $args['tax_query'] = [[
                    'taxonomy' => 'gf_image_category',
                    'operator' => 'EXISTS'
                ]];
            }
        }

        $query = new WP_Query($args);
        $images = [];

        foreach ($query->posts as $image) {
            $categories = get_the_terms($image->ID, 'gf_image_category');

            $images[] = [
                'id' => $image->ID,
                'title' => $image->post_title ?: 'Untitled',
                'url' => wp_get_attachment_url($image->ID),
                'thumbnail' => wp_get_attachment_image_url($image->ID, 'medium_large') ?: wp_get_attachment_image_url($image->ID, 'large'),
                'categories' => $categories ? array_map(function ($cat) {
                    return ['id' => $cat->term_id, 'name' => $cat->name];
                }, $categories) : [],
            ];
        }

        wp_send_json_success([
            'images' => $images,
            'current_page' => $paged,
            'max_pages' => $query->max_num_pages,
            'total' => $query->found_posts,
        ]);
    }

    public function save_image_categories()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gallery_filter_nonce')) {
            wp_die('Security check failed');
        }

        if (!current_user_can('edit_posts')) {
            wp_die('Insufficient permissions');
        }

        $image_id = intval($_POST['image_id'] ?? 0);
        $categories = array_map('intval', $_POST['categories'] ?? []);

        if ($image_id <= 0) {
            wp_send_json_error('Invalid image ID', 400);
        }

        $result = wp_set_object_terms($image_id, $categories, 'gf_image_category');

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        }

        wp_send_json_success('Categories saved');
    }

    public function get_image_categories()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gallery_filter_nonce')) {
            wp_die('Security check failed');
        }

        $image_id = intval($_POST['image_id'] ?? 0);
        $categories = get_the_terms($image_id, 'gf_image_category');

        if (!$categories || is_wp_error($categories)) {
            wp_send_json_success(['categories' => []]);
        }

        $cat_data = array_map(function ($cat) {
            return ['id' => $cat->term_id, 'name' => $cat->name];
        }, $categories);

        wp_send_json_success(['categories' => $cat_data]);
    }

    public function get_filtered_categories()
    {
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'gallery_filter_nonce')) {
            wp_send_json_error('Security check failed', 403);
        }

        $filter_type = sanitize_text_field($_POST['filter_type'] ?? '');
        $selected_location = sanitize_text_field($_POST['selected_location'] ?? '');
        $selected_type = sanitize_text_field($_POST['selected_type'] ?? '');

        if (!in_array($filter_type, ['event_locations', 'event_types'])) {
            wp_send_json_error('Invalid filter type', 400);
        }

        $parent_slug = $filter_type === 'event_locations' ? 'gf-event-locations' : 'gf-event-types';
        $other_selected = $filter_type === 'event_locations' ? $selected_type : $selected_location;

        $parent_term = get_term_by('slug', $parent_slug, 'gf_image_category');

        if (!$parent_term) {
            wp_send_json_success(['categories' => []]);
        }

        $categories = get_terms([
            'taxonomy' => 'gf_image_category',
            'parent' => $parent_term->term_id,
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        $filtered_categories = [];

        foreach ($categories as $category) {
            $tax_query = [
                [
                    'taxonomy' => 'gf_image_category',
                    'field' => 'term_id',
                    'terms' => [$category->term_id],
                ],
            ];

            if (!empty($other_selected)) {
                $tax_query[] = [
                    'taxonomy' => 'gf_image_category',
                    'field' => 'term_id',
                    'terms' => [$other_selected],
                ];
                $tax_query['relation'] = 'AND';
            }

            $args = [
                'post_type' => 'attachment',
                'post_status' => 'inherit',
                'post_mime_type' => 'image',
                'posts_per_page' => -1,
                'fields' => 'ids',
                'tax_query' => $tax_query,
            ];

            $query = new WP_Query($args);
            $count = $query->found_posts;

            if ($count > 0 || empty($other_selected)) {
                $filtered_categories[] = [
                    'id' => $category->term_id,
                    'name' => $category->name,
                    'count' => $count,
                ];
            }
        }

        usort($filtered_categories, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        wp_send_json_success(['categories' => $filtered_categories]);
    }
}
