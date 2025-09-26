<div class="gallery-filter-wrapper" id="<?php echo esc_attr($gallery_id); ?>">

    <div class="categories_filter">
        <select id="frontend-category-filter-event-location" class="category-filter-select">
            <option value="">Event Locations</option>
            <?php foreach ($event_location_categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>">
                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <select id="frontend-category-filter-event-type" class="category-filter-select">
            <option value="">Event Types</option>
            <?php foreach ($event_type_categories as $cat): ?>
                <option value="<?php echo esc_attr($cat->term_id); ?>">
                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="gallery-loading" id="frontend-loading" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Loading images...</p>
        </div>
    </div>

    <div id="frontend-gallery" class="masonry-grid"
        data-per-page="<?php echo esc_attr($atts['per_page']); ?>"
        data-columns="<?php echo esc_attr($atts['columns']); ?>">
    </div>

    <div id="frontend-pagination">
        <div class="pagination-buttons">
            <!-- Pagination will be populated by JavaScript -->
        </div>
    </div>
</div>