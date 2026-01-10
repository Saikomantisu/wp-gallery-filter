<?php
$total_event_locations = (new Gallery_Filter_Admin())->get_category_image_count_by_slug('gf-event-locations');
$total_event_types = (new Gallery_Filter_Admin())->get_category_image_count_by_slug('gf-event-types');
?>

<div class="wrap gallery-filter-admin">
    <div class="admin-header">
        <h1>Gallery Filter</h1>
        <p class="admin-subtitle">Welcome! Let's organize your photos with ease</p>
    </div>

    <div class="admin-container">
        <div class="sidebar">
            <div class="sidebar-section">
                <h3><i class="dashicons dashicons-filter"></i> Find Your Photos</h3>
                <div class="form-group">
                    <label for="search">Search by name or filename</label>
                    <input type="text" id="search" placeholder="Try typing a name..." class="form-control">
                </div>

                <div class="form-group">
                    <label for="category-filter-event-location">Where was it?</label>
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
                    <label for="category-filter-event-type">What kind of event?</label>
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
                <h3><i class="dashicons dashicons-admin-tools"></i> Quick Actions</h3>

                <div class="form-group">
                    <label for="bulk-event-types">Choose event types</label>
                    <div id="bulk-event-types" class="bulk-checkbox-list">
                        <?php foreach ($event_type_categories as $cat): ?>
                            <label class="bulk-checkbox-label">
                                <input type="checkbox" class="bulk-checkbox" value="<?php echo esc_attr($cat->term_id); ?>">
                                <span class="bulk-checkbox-text"><?php echo esc_html($cat->name); ?> <span class="category-count">(<?php echo $cat->actual_count; ?>)</span></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bulk-event-locations">Pick locations</label>
                    <div id="bulk-event-locations" class="bulk-checkbox-list">
                        <?php foreach ($event_location_categories as $cat): ?>
                            <label class="bulk-checkbox-label">
                                <input type="checkbox" class="bulk-checkbox" value="<?php echo esc_attr($cat->term_id); ?>">
                                <span class="bulk-checkbox-text"><?php echo esc_html($cat->name); ?> <span class="category-count">(<?php echo $cat->actual_count; ?>)</span></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="bulk-actions">
                    <button id="bulk-add" class="btn btn-success">
                        <i class="dashicons dashicons-plus-alt"></i> Add Tags
                    </button>
                    <button id="bulk-remove" class="btn btn-warning">
                        <i class="dashicons dashicons-minus"></i> Remove Tags
                    </button>
                    <button id="select-all" class="btn btn-outline">
                        <i class="dashicons dashicons-yes-alt"></i> All Photos
                    </button>
                    <button id="deselect-all" class="btn btn-outline">
                        <i class="dashicons dashicons-dismiss"></i> Clear Selection
                    </button>
                </div>

                <div class="selection-info">
                    <i class="dashicons dashicons-images-alt2"></i>
                    <span id="selected-count">0</span> photos selected
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-header">
                <h2>Your Photos</h2>
                <div class="view-options">
                    <span class="results-count" id="results-count">Loading...</span>
                </div>
            </div>

            <div class="loading-overlay" id="loading-overlay" style="display: none;">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Almost there, loading your photos...</p>
                </div>
            </div>

            <div id="image-grid" class="image-grid"></div>
            <div id="pagination" class="pagination-wrapper"></div>
        </div>
    </div>

    <!-- Footer -->
    <div class="admin-footer">
        <div class="footer-content">
            <div class="footer-brand">
                <span class="footer-brand-text">Built by <a href="https://nexgendevs.lk" target="_blank" rel="noopener noreferrer" class="footer-brand-link">
                    NexGen Devs
                </a></span>
            </div>
            <div class="footer-version">
                Gallery Filter v<?php echo GALLERY_FILTER_VERSION; ?>
            </div>
        </div>
    </div>

    <!-- Modern Modal -->
    <div id="modal" class="modal" style="display:none;">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3><i class="dashicons dashicons-format-image"></i> Edit Photo Tags</h3>
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
                        <h4>Current Tags</h4>
                        <div id="current-categories-display" class="category-chips">
                            <span class="loading-text">Loading categories...</span>
                        </div>
                    </div>

                    <div class="available-categories">
                        <div class="category-group">
                            <h4>Event Types</h4>
                            <div id="image-event-types" class="category-checkbox-list">
                                <?php foreach ($event_type_categories as $cat): ?>
                                    <label class="category-checkbox-label">
                                        <input type="checkbox" class="category-checkbox" value="<?php echo esc_attr($cat->term_id); ?>">
                                        <span class="category-checkbox-text"><?php echo esc_html($cat->name); ?> <span class="category-count">(<?php echo $cat->actual_count; ?>)</span></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="category-group">
                            <h4>Event Locations</h4>
                            <div id="image-event-locations" class="category-checkbox-list">
                                <?php foreach ($event_location_categories as $cat): ?>
                                    <label class="category-checkbox-label">
                                        <input type="checkbox" class="category-checkbox" value="<?php echo esc_attr($cat->term_id); ?>">
                                        <span class="category-checkbox-text"><?php echo esc_html($cat->name); ?> <span class="category-count">(<?php echo $cat->actual_count; ?>)</span></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button id="save-categories" class="btn btn-primary">
                    <i class="dashicons dashicons-saved"></i> Save Tags
                </button>
                <button class="modal-close btn btn-secondary">
                    <i class="dashicons dashicons-dismiss"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>