<?php
if (!defined('ABSPATH')) exit;

class Gallery_Filter_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

    }

    public function add_admin_menu()
    {
        add_menu_page(
            'Gallery Filter',
            'Gallery Filter',
            'manage_options',
            'gallery-filter',
            [$this, 'render_admin_page'],
            'dashicons-camera'
        );
    }

    public function enqueue_admin_scripts($hook)
    {
        if ($hook !== 'toplevel_page_gallery-filter') return;

        wp_enqueue_script(
            'gallery-filter-admin',
            GALLERY_FILTER_ASSETS_URL . 'js/admin.js',
            ['jquery'],
            GALLERY_FILTER_VERSION,
            true
        );

        wp_enqueue_style(
            'gallery-filter-admin',
            GALLERY_FILTER_ASSETS_URL . 'css/admin.css',
            [],
            GALLERY_FILTER_VERSION
        );

        wp_localize_script('gallery-filter-admin', 'galleryFilter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('gallery_filter_nonce'),
        ]);
    }

    public function render_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }

        // Get categories with proper counts
        $categories = $this->get_categories_with_counts();
        $event_location_categories = $this->get_child_categories_with_counts('gf-event-locations');
        $event_type_categories = $this->get_child_categories_with_counts('gf-event-types');

        include GALLERY_FILTER_PLUGIN_DIR . 'templates/admin-page.php';
    }

    /**
     * Get child categories with image counts for a given parent category slug
     */
    private function get_child_categories_with_counts($category_slug)
    {
        $parent_term = get_term_by('slug', $category_slug, 'gf_image_category');

        if ($parent_term) {
            $categories = get_terms([
                'taxonomy' => 'gf_image_category',
                'parent' => $parent_term->term_id,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC',
            ]);

            // Get actual counts for each category
            foreach ($categories as $category) {
                $category->actual_count = $this->get_category_image_count($category->term_id);
            }

            return $categories;
        }

        return [];
    }

    /**
     * Get categories with actual image counts
     */
    private function get_categories_with_counts()
    {
        // Get all categories
        $categories = get_terms([
            'taxonomy' => 'gf_image_category',
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);

        if (is_wp_error($categories)) {
            return [];
        }

        // Get actual counts for each category
        foreach ($categories as $category) {
            $category->actual_count = $this->get_category_image_count($category->term_id);
        }

        return $categories;
    }

    /**
     * Get the actual count of images in a category
     */
    private function get_category_image_count($category_id)
    {
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => 'gf_image_category',
                'field' => 'term_id',
                'terms' => [$category_id],
            ]],
        ];

        $query = new WP_Query($args);
        return $query->found_posts;
    }

    /**
     * Get the actual count of images in a category by category slug
     */
    public function get_category_image_count_by_slug($category_slug)
    {
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => 'gf_image_category',
                'field' => 'slug',
                'terms' => [$category_slug],
            ]],
        ];

        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
