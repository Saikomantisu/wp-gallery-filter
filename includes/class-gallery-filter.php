<?php
if (!defined('ABSPATH')) exit;

class Gallery_Filter
{
    public function __construct()
    {
        add_action('init', [$this, 'register_taxonomy']);
    }

    public function register_taxonomy()
    {
        register_taxonomy('gf_image_category', 'attachment', [
            'labels' => [
                'name' => 'Image Categories',
                'singular_name' => 'Image Category',
                'menu_name' => 'Categories',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_ui' => true,
            'show_admin_column' => true,
        ]);

        // Create parent categories
        if (!term_exists('gf-event-locations', 'gf_image_category')) {
            wp_insert_term('Gallery Filter Event Locations', 'gf_image_category', [
                'description' => 'Categories for event locations',
                'slug' => 'gf-event-locations'
            ]);
        }
    
        if (!term_exists('gf-event-types', 'gf_image_category')) {
            wp_insert_term('Gallery Filter Event Types', 'gf_image_category', [
                'description' => 'Categories for event types',
                'slug' => 'gf-event-types'
            ]);
        }
    }
}
