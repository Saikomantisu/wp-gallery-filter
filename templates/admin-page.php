<?php
$total_event_locations = (new Gallery_Filter_Admin())->get_category_image_count_by_slug('gf-event-locations');
$total_event_types = (new Gallery_Filter_Admin())->get_category_image_count_by_slug('gf-event-types');
?>

<div class="wrap gallery-filter-admin">
    <div class="admin-header">
        <h1>Gallery Filter</h1>
        <p class="admin-subtitle">Organize and categorize your media library</p>
    </div>

    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-section">
                <h3><i class="dashicons dashicons-filter"></i> Filter by Category</h3>
                <div class="form-group">
                    <label for="search">Search Images</label>
                    <input type="text" id="search" placeholder="Search by title or filename..." class="form-control">
                </div>

                <div class="form-group">
                    <label for="category-filter-event-location">Event Locations</label>
                    <select id="category-filter-event-location" class="form-control category-select-dropdown">
                        <option value="">All Categories (<?php echo $total_event_locations; ?>)</option>
                        <?php foreach ($event_location_categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>">
                                <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category-filter-event-type">Event Types</label>
                    <select id="category-filter-event-type" class="form-control category-select-dropdown">
                        <option value="">All Categories (<?php echo $total_event_types; ?>)</option>
                        <?php foreach ($event_type_categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>">
                                <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-actions">
                    <button id="apply-filters" class="btn btn-primary">
                        <i class="dashicons dashicons-search"></i> Apply Filters
                    </button>
                    <button id="clear-filters" class="btn btn-secondary">
                        <i class="dashicons dashicons-dismiss"></i> Clear
                    </button>
                </div>
            </div>

            <div class="sidebar-section">
                <h3><i class="dashicons dashicons-admin-tools"></i> Bulk Actions</h3>

                <div class="form-group">
                    <label for="bulk-categories">Select Categories</label>
                    <select id="bulk-categories" multiple class="form-control bulk-select">
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>">
                                <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="bulk-actions">
                    <button id="bulk-add" class="btn btn-success">
                        <i class="dashicons dashicons-plus-alt"></i> Add to Selected
                    </button>
                    <button id="bulk-remove" class="btn btn-warning">
                        <i class="dashicons dashicons-minus"></i> Remove from Selected
                    </button>
                    <button id="select-all" class="btn btn-outline">
                        <i class="dashicons dashicons-yes-alt"></i> Select All
                    </button>
                    <button id="deselect-all" class="btn btn-outline">
                        <i class="dashicons dashicons-dismiss"></i> Deselect All
                    </button>
                </div>

                <div class="selection-info">
                    <i class="dashicons dashicons-images-alt2"></i>
                    <span id="selected-count">0</span> images selected
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h2>Media Library</h2>
                <div class="view-options">
                    <span class="results-count" id="results-count">Loading...</span>
                </div>
            </div>

            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading images...</p>
                </div>
            </div>

            <div id="image-grid" class="image-grid"></div>
            <div id="pagination" class="pagination-wrapper"></div>
        </div>
    </div>

    <!-- Modern Modal -->
    <div id="modal" class="modal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="dashicons dashicons-format-image"></i> Edit Image Categories</h3>
                <button class="modal-close" aria-label="Close">
                    <i class="dashicons dashicons-no-alt"></i>
                </button>
            </div>

            <div class="modal-content">
                <div class="image-preview-section">
                    <div class="image-preview">
                        <img id="modal-image" src="" alt="">
                    </div>
                    <div class="image-details">
                        <h4 id="modal-image-title">Image Title</h4>
                        <p id="modal-image-info" class="image-meta">Loading...</p>
                    </div>
                </div>

                <div class="categories-section">
                    <div class="current-categories">
                        <h4>Current Categories</h4>
                        <div id="current-categories-display" class="category-chips">
                            <span class="loading-text">Loading categories...</span>
                        </div>
                    </div>

                    <div class="available-categories">
                        <h4>Available Categories</h4>
                        <select id="image-categories" multiple class="form-control category-select">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo esc_attr($cat->term_id); ?>">
                                    <?php echo esc_html($cat->name); ?> (<?php echo $cat->actual_count; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button id="save-categories" class="btn btn-primary">
                    <i class="dashicons dashicons-saved"></i> Save Changes
                </button>
                <button class="modal-close btn btn-secondary">
                    <i class="dashicons dashicons-dismiss"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>