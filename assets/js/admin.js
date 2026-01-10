jQuery(document).ready(function($) {
    let selectedImages = [];
    let currentImageId = null;
    let currentPage = 1;
    
    // Update selected count display
    function updateSelectedCount() {
        $('#selected-count').text(selectedImages.length);
    }
    
    // Load images with loading overlay
    function loadImages(page = 1) {
        currentPage = page;
        $('#loading-overlay').show();
        
        $.post(galleryFilter.ajaxUrl, {
            action: 'get_images',
            nonce: galleryFilter.nonce,
            paged: page,
            event_location: $('#category-filter-event-location').val(),
            event_type: $('#category-filter-event-type').val(),
            search: $('#search').val()
        }, function(response) {
            if (response.success) {
                displayImages(response.data.images);
                displayPagination(response.data.current_page, response.data.max_pages);
                updateResultsCount(response.data.total, response.data.images.length);
            } else {
                showError('Failed to load images');
            }
        }).fail(function() {
            showError('Network error occurred');
        }).always(function() {
            $('#loading-overlay').hide();
        });
    }
    
    // Display images with modern design
    function displayImages(images) {
        const grid = $('#image-grid');
        grid.empty();
        selectedImages = [];
        updateSelectedCount();
        
        if (images.length === 0) {
            grid.html('<div class="no-results"><p>No images found. Try adjusting your filters.</p></div>');
            return;
        }
        
        images.forEach(function(image) {
            const categories = image.categories.map(c => c.name).join(', ') || 'No categories';
            const isSelected = selectedImages.includes(image.id.toString());
            
            const html = `
                <div class="image-item ${isSelected ? 'selected' : ''}" data-id="${image.id}">
                    <input type="checkbox" class="image-checkbox" value="${image.id}" ${isSelected ? 'checked' : ''}>
                    <img src="${image.thumbnail}" alt="${image.title}" loading="lazy">
                    <div class="image-item-info">
                        <div class="image-item-title">${image.title}</div>
                        <div class="image-item-categories">${categories}</div>
                    </div>
                </div>
            `;
            grid.append(html);
        });
    }
    
    // Display pagination with modern styling
    function displayPagination(current, max) {
        const pagination = $('#pagination');
        pagination.empty();
        
        if (max <= 1) return;
        
        // Previous button
        if (current > 1) {
            pagination.append(`<a href="#" class="page-btn" data-page="${current - 1}">← Previous</a>`);
        }
        
        // Page numbers (show current ±2)
        const start = Math.max(1, current - 2);
        const end = Math.min(max, current + 2);
        
        if (start > 1) {
            pagination.append(`<a href="#" class="page-btn" data-page="1">1</a>`);
            if (start > 2) {
                pagination.append('<span class="pagination-dots">...</span>');
            }
        }
        
        for (let i = start; i <= end; i++) {
            const activeClass = i === current ? 'active' : '';
            pagination.append(`<a href="#" class="page-btn ${activeClass}" data-page="${i}">${i}</a>`);
        }
        
        if (end < max) {
            if (end < max - 1) {
                pagination.append('<span class="pagination-dots">...</span>');
            }
            pagination.append(`<a href="#" class="page-btn" data-page="${max}">${max}</a>`);
        }
        
        // Next button
        if (current < max) {
            pagination.append(`<a href="#" class="page-btn" data-page="${current + 1}">Next →</a>`);
        }
    }
    
    // Update results count
    function updateResultsCount(total, showing) {
        $('#results-count').text(`Showing ${showing} of ${total} images`);
    }
    
    // Show error message
    function showError(message) {
        // You can implement a toast notification here
        console.error(message);
        alert(message);
    }
    
    // Show success message
    function showSuccess(message) {
        // You can implement a toast notification here
        console.log(message);
    }
    
    // Event Handlers
    $('#apply-filters').on('click', () => loadImages(1));
    
    $('#clear-filters').on('click', function() {
        $('#search').val('');
        $('#category-filter-event-location').val('');
        $('#category-filter-event-type').val('');
        loadImages(1);
    });
    
    $(document).on('click', '.page-btn', function(e) {
        e.preventDefault();
        loadImages($(this).data('page'));
    });
    
    // Image selection
    $(document).on('change', '.image-checkbox', function() {
        const id = $(this).val();
        const item = $(this).closest('.image-item');
        
        if ($(this).is(':checked')) {
            if (!selectedImages.includes(id)) {
                selectedImages.push(id);
            }
            item.addClass('selected');
        } else {
            selectedImages = selectedImages.filter(i => i !== id);
            item.removeClass('selected');
        }
        updateSelectedCount();
    });
    
    // Select/Deselect all
    $('#select-all').on('click', function() {
        $('.image-checkbox').prop('checked', true).trigger('change');
    });
    
    $('#deselect-all').on('click', function() {
        $('.image-checkbox').prop('checked', false).trigger('change');
        selectedImages = [];
        $('.image-item').removeClass('selected');
        updateSelectedCount();
    });
    
    // Open modal when clicking on image
    $(document).on('click', '.image-item img', function() {
        const item = $(this).closest('.image-item');
        currentImageId = item.data('id');
        
        // Set image in modal
        $('#modal-image').attr('src', $(this).attr('src'));
        $('#modal-image-title').text(item.find('.image-item-title').text());
        $('#modal-image-info').text(`Image ID: ${currentImageId}`);
        
        // Load current categories
        loadImageCategories(currentImageId);
        
        $('#modal').show();
        $('body').addClass('modal-open');
    });
    
    // Close modal
    $('.modal-close, .modal-backdrop').on('click', function() {
        $('#modal').hide();
        $('body').removeClass('modal-open');
    });
    
    // Load image categories
    function loadImageCategories(imageId) {
        $('#current-categories-display').html('<span class="loading-text">Loading categories...</span>');

        $.post(galleryFilter.ajaxUrl, {
            action: 'get_image_categories',
            nonce: galleryFilter.nonce,
            image_id: imageId
        }, function(response) {
            if (response.success) {
                displayCurrentCategories(response.data.categories);

                const selectedCategories = response.data.categories.map(cat => cat.id.toString());

                // Uncheck all checkboxes first
                $('#image-event-types .category-checkbox').prop('checked', false);
                $('#image-event-locations .category-checkbox').prop('checked', false);

                // Check selected checkboxes
                selectedCategories.forEach(function(id) {
                    $('#image-event-types .category-checkbox[value="' + id + '"]').prop('checked', true);
                    $('#image-event-locations .category-checkbox[value="' + id + '"]').prop('checked', true);
                });
            } else {
                $('#current-categories-display').html('<span class="loading-text">Error loading categories</span>');
            }
        });
    }
    
    // Display current categories as chips
    function displayCurrentCategories(categories) {
        const container = $('#current-categories-display');
        container.empty();
        
        if (categories.length === 0) {
            container.html('<span class="loading-text">No categories assigned</span>');
            return;
        }
        
        categories.forEach(function(cat) {
            const chip = $(`
                <span class="category-chip">
                    ${cat.name}
                    <button type="button" class="category-chip-remove" data-category-id="${cat.id}">×</button>
                </span>
            `);
            container.append(chip);
        });
    }
    
    // Remove category chip
    $(document).on('click', '.category-chip-remove', function() {
        const categoryId = $(this).data('category-id').toString();

        // Uncheck the checkbox for this category
        $('#image-event-types .category-checkbox[value="' + categoryId + '"]').prop('checked', false);
        $('#image-event-locations .category-checkbox[value="' + categoryId + '"]').prop('checked', false);

        // Remove the chip
        $(this).closest('.category-chip').remove();

        // Update chips from both dropdowns
        updateModalCategoryChips();
    });
    
    // Update chips when event types dropdown changes
    $('#image-event-types').on('change', updateModalCategoryChips);

    // Update chips when event locations dropdown changes
    $('#image-event-locations').on('change', updateModalCategoryChips);

    // Helper function to update chips from both dropdowns
    function updateModalCategoryChips() {
        const eventTypes = $('#image-event-types').val() || [];
        const eventLocations = $('#image-event-locations').val() || [];
        const categories = [];

        eventTypes.forEach(function(id) {
            const option = $(`#image-event-types option[value="${id}"]`);
            if (option.length) {
                categories.push({ id: parseInt(id), name: option.text() });
            }
        });

        eventLocations.forEach(function(id) {
            const option = $(`#image-event-locations option[value="${id}"]`);
            if (option.length) {
                categories.push({ id: parseInt(id), name: option.text() });
            }
        });

        displayCurrentCategories(categories);
    }
    
    // Save categories
    $('#save-categories').on('click', function() {
        const eventTypes = $('#image-event-types').val() || [];
        const eventLocations = $('#image-event-locations').val() || [];
        const categories = [...eventTypes, ...eventLocations];

        $(this).prop('disabled', true).html('<i class="dashicons dashicons-update"></i> Saving...');

        $.post(galleryFilter.ajaxUrl, {
            action: 'save_image_categories',
            nonce: galleryFilter.nonce,
            image_id: currentImageId,
            categories: categories
        }, function(response) {
            if (response.success) {
                $('#modal').hide();
                $('body').removeClass('modal-open');
                loadImages(currentPage); // Refresh current page
                showSuccess('Categories saved successfully!');
            } else {
                alert('Error: ' + response.data);
            }
        }).always(function() {
            $('#save-categories').prop('disabled', false).html('<i class="dashicons dashicons-saved"></i> Save Changes');
        });
    });
    
    // Bulk add categories
    $('#bulk-add').on('click', function() {
        if (selectedImages.length === 0) {
            alert('Please select some images first');
            return;
        }

        const eventTypes = $('#bulk-event-types').val() || [];
        const eventLocations = $('#bulk-event-locations').val() || [];
        const categories = [...eventTypes, ...eventLocations];

        if (categories.length === 0) {
            alert('Please select categories to add');
            return;
        }

        performBulkAction('add', categories);
    });

    // Bulk remove categories
    $('#bulk-remove').on('click', function() {
        if (selectedImages.length === 0) {
            alert('Please select some images first');
            return;
        }

        const eventTypes = $('#bulk-event-types').val() || [];
        const eventLocations = $('#bulk-event-locations').val() || [];
        const categories = [...eventTypes, ...eventLocations];

        if (categories.length === 0) {
            alert('Please select categories to remove');
            return;
        }

        performBulkAction('remove', categories);
    });
    
    // Perform bulk action
    function performBulkAction(action, categories) {
        const actionText = action === 'add' ? 'add to' : 'remove from';
        if (!confirm(`${action === 'add' ? 'Add' : 'Remove'} selected categories ${actionText} ${selectedImages.length} images?`)) {
            return;
        }
        
        const $button = action === 'add' ? $('#bulk-add') : $('#bulk-remove');
        $button.prop('disabled', true).html(`<i class="dashicons dashicons-update"></i> ${action === 'add' ? 'Adding' : 'Removing'}...`);
        
        let completed = 0;
        let errors = 0;
        
        selectedImages.forEach(function(imageId) {
            if (action === 'add') {
                // For add: get current categories and merge with new ones
                $.post(galleryFilter.ajaxUrl, {
                    action: 'get_image_categories',
                    nonce: galleryFilter.nonce,
                    image_id: imageId
                }, function(response) {
                    const currentCategories = response.success ? response.data.categories.map(cat => cat.id.toString()) : [];
                    const newCategories = [...new Set([...currentCategories, ...categories])]; // Remove duplicates
                    
                    saveImageCategories(imageId, newCategories);
                });
            } else {
                // For remove: get current categories and filter out selected ones
                $.post(galleryFilter.ajaxUrl, {
                    action: 'get_image_categories',
                    nonce: galleryFilter.nonce,
                    image_id: imageId
                }, function(response) {
                    const currentCategories = response.success ? response.data.categories.map(cat => cat.id.toString()) : [];
                    const newCategories = currentCategories.filter(catId => !categories.includes(catId));
                    
                    saveImageCategories(imageId, newCategories);
                });
            }
        });
        
        function saveImageCategories(imageId, categoryIds) {
            $.post(galleryFilter.ajaxUrl, {
                action: 'save_image_categories',
                nonce: galleryFilter.nonce,
                image_id: imageId,
                categories: categoryIds
            }, function(response) {
                if (!response.success) errors++;
            }).always(function() {
                completed++;
                if (completed === selectedImages.length) {
                    // All done
                    $button.prop('disabled', false).html(`<i class="dashicons dashicons-${action === 'add' ? 'plus-alt' : 'minus'}"></i> ${action === 'add' ? 'Add to' : 'Remove from'} Selected`);
                    
                    if (errors === 0) {
                        showSuccess(`Categories ${action === 'add' ? 'added to' : 'removed from'} all selected images!`);
                    } else {
                        alert(`Completed with ${errors} errors`);
                    }
                    
                    loadImages(currentPage); // Refresh
                }
            });
        }
    }
    
    // Prevent body scroll when modal is open
    $('head').append(`
        <style>
            body.modal-open {
                overflow: hidden;
            }
            .pagination-dots {
                padding: 10px 8px;
                color: #c3c4c7;
            }
            .no-results {
                grid-column: 1 / -1;
                text-align: center;
                padding: 60px 20px;
                color: #646970;
            }
        </style>
    `);
    
    // Initialize
    loadImages();
});