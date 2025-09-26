<?php
if (!defined('ABSPATH')) exit;

class Gallery_Filter_Shortcode
{
    public function __construct()
    {
        add_shortcode('gallery-filter', [$this, 'render_shortcode']);
    }

    public function render_shortcode($atts)
    {
        // Shortcode now works with no configurable attributes – fixed defaults keep the UI simple.
        $atts = [
            'per_page' => 12,
            'columns'  => 4,
        ];

        // Get categories with actual counts for the filter dropdown
        $event_location_categories = $this->get_child_categories_with_counts('gf-event-locations');
        $event_type_categories = $this->get_child_categories_with_counts('gf-event-types');

        // Get initial images for faster loading
        $initial_images = $this->get_initial_images($atts);
        $initial_query = $initial_images['query'];
        $initial_total_images = $initial_images['total_images'];
        $images = $initial_images['images'];

        // Generate unique gallery ID
        $gallery_id = 'gallery-filter-' . uniqid();

        ob_start();
        include GALLERY_FILTER_PLUGIN_DIR . 'templates/shortcode.php';
        return ob_get_clean();
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
     * Get initial images for server-side rendering
     */
    private function get_initial_images($atts)
    {
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => $atts['per_page'],
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
        ];

        // Always show only categorized images – shortcode no longer accepts category args.
        $args['tax_query'] = [[
            'taxonomy' => 'gf_image_category',
            'operator' => 'EXISTS'
        ]];

        $query = new WP_Query($args);
        $images = [];

        // Process images
        if ($query->have_posts()) {
            foreach ($query->posts as $image) {
                $categories_data = get_the_terms($image->ID, 'gf_image_category');
                $images[] = [
                    'id' => $image->ID,
                    'title' => $image->post_title ?: 'Untitled',
                    'url' => wp_get_attachment_url($image->ID),
                    'thumbnail' => wp_get_attachment_image_url($image->ID, 'large') ?: wp_get_attachment_image_url($image->ID, 'medium'),
                    'categories' => $categories_data ? array_map(function ($cat) {
                        return ['id' => $cat->term_id, 'name' => $cat->name];
                    }, $categories_data) : [],
                ];
            }
        }

        return [
            'query' => $query,
            'images' => $images,
            'total_images' => $query->found_posts
        ];
    }

    /**
     * Get total categorized images count
     */
    private function get_total_categorized_count()
    {
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => 'gf_image_category',
                'operator' => 'EXISTS'
            ]],
        ];

        $query = new WP_Query($args);
        return $query->found_posts;
    }
}
