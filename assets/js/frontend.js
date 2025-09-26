jQuery(document).ready(function ($) {
	let currentImages = [];
	let currentImageIndex = 0;
	let currentPage = 1;
	let maxPages = 1;
	let currentCategory = '';
	let isLoading = false;

	// Get gallery settings
	const $gallery = $('#frontend-gallery');
	const perPage = parseInt($gallery.data('per-page')) || 12;

	function loadImages(page = 1, category = '') {
		if (isLoading) return;

		isLoading = true;
		currentPage = page;
		currentCategory = category;

		showLoading();

		$.post(
			galleryFilter.ajaxUrl,
			{
				action: 'get_images',
				nonce: galleryFilter.nonce,
				paged: page,
				event_location: $('#frontend-category-filter-event-location').val(),
				event_type: $('#frontend-category-filter-event-type').val(),
				per_page: perPage,
			},
			function (response) {
				if (response.success) {
					displayImages(response.data.images);
					updatePagination(response.data.current_page, response.data.max_pages);
					updateResultsInfo(
						response.data.images.length,
						response.data.total || 0
					);
				} else {
					showError('Failed to load images. Please try again.', '📷');
				}
			}
		)
			.fail(function () {
				showError('Network error. Please check your connection.', '🌐');
			})
			.always(function () {
				hideLoading();
				isLoading = false;
			});
	}

	function displayImages(images) {
		$gallery.empty();
		currentImages = images;

		if (images.length === 0) {
			$gallery.html(`
                <div style="text-align: center; line-height: 1; column-span: all;">
                    <div class="no-images-icon">📷</div>
                    <h3>No images found</h3>
                    <p>Try selecting a different category or check back later.</p>
                </div>
            `);
			return;
		}

		images.forEach(function (image, index) {
			const categories = image.categories || [];
			const categoryTags = categories
				.map((cat) => `<span class="category-tag">${cat.name}</span>`)
				.join('');

			const html = `
                <div class="masonry-item" data-index="${index}" data-id="${image.id}">
                    <a href="${image.url}" data-lightbox="gf-gallery" data-title="${image.title}">
                        <img src="${image.thumbnail}" 
                         alt="${image.title}"
                         loading="lazy">
                        <div class="image-overlay">
                            <div class="image-categories">${categoryTags}</div>
                        </div>
                    </a>
                </div>
            `;
			$gallery.append(html);
		});

		// Scroll to top of gallery smoothly
		if (currentPage > 1) {
			$('html, body').animate(
				{
					scrollTop: $('.gallery-filter-wrapper').offset().top - 100,
				},
				500
			);
		}
	}

	function updatePagination(current, max) {
		maxPages = max;
		const $container = $('.pagination-buttons');
		const $info = $('.pagination-info');

		if (max <= 1) {
			$('.pagination-buttons').hide();
			return;
		}

		$('.pagination-buttons').show();
		$container.empty();

		// Previous button
		const prevBtn = $(`
            <button class="page-btn ${
							current <= 1 ? 'disabled' : ''
						}" data-page="${current - 1}">
                ← Previous
            </button>
        `);
		if (current <= 1) prevBtn.prop('disabled', true);
		$container.append(prevBtn);

		// Page numbers
		const startPage = Math.max(1, current - 2);
		const endPage = Math.min(max, current + 2);

		// First page and ellipsis
		if (startPage > 1) {
			$container.append(`<button class="page-btn" data-page="1">1</button>`);
			if (startPage > 2) {
				$container.append('<span class="page-ellipsis">...</span>');
			}
		}

		// Page range
		for (let i = startPage; i <= endPage; i++) {
			const activeClass = i === current ? 'active' : '';
			$container.append(
				`<button class="page-btn ${activeClass}" data-page="${i}">${i}</button>`
			);
		}

		// Last page and ellipsis
		if (endPage < max) {
			if (endPage < max - 1) {
				$container.append('<span class="page-ellipsis">...</span>');
			}
			$container.append(
				`<button class="page-btn" data-page="${max}">${max}</button>`
			);
		}

		// Next button
		const nextBtn = $(`
            <button class="page-btn ${
							current >= max ? 'disabled' : ''
						}" data-page="${current + 1}">
                Next →
            </button>
        `);
		if (current >= max) nextBtn.prop('disabled', true);
		$container.append(nextBtn);
	}

	function updateResultsInfo(showing, total) {
		const startNum = (currentPage - 1) * perPage + 1;
		const endNum = Math.min(startNum + showing - 1, total);
		const text =
			total > 0
				? `Showing ${startNum}-${endNum} of ${total} images`
				: 'No images found';
		$('#results-info').text(text);
	}

	function showLoading() {
		$('#frontend-loading').show();
		$gallery.css('opacity', '0.5');
	}

	function hideLoading() {
		$('#frontend-loading').hide();
		$gallery.css('opacity', '1');
	}

	function showError(message, icon) {
		$gallery.html(`
            <div style="text-align: center; line-height: 1; column-span: all;">
                <div class="no-images-icon">${icon}</div>
                <h3>${message}</h3>
                <p>Please make sure some images are assigned to categories, or adjust your filter.</p>
            </div>
        `);
	}

	// Event Handlers

	// Category filter change
	$('#frontend-category-filter-event-location').on('change', function () {
		const category = $(this).val();
		loadImages(1, category);
	});

	$('#frontend-category-filter-event-type').on('change', function () {
		const category = $(this).val();
		loadImages(1, category);
	});

	// Pagination clicks
	$(document).on('click', '.page-btn:not(.disabled)', function (e) {
		e.preventDefault();
		const page = $(this).data('page');
		if (page && page !== currentPage) {
			loadImages(page, currentCategory);
		}
	});

	// Add missing method to shortcode class
	if (typeof window.Gallery_Filter_Shortcode === 'undefined') {
		window.Gallery_Filter_Shortcode = {
			get_total_categorized_count: function () {
				// This will be calculated server-side
				return 0;
			},
		};
	}

	if (typeof lightbox !== 'undefined') {
		lightbox.option({
			resizeDuration: 0,
			disableScrolling: true,
			wrapAround: false,
			fadeDuration: 0,
			imageFadeDuration: 0,
			// Add any other desired Lightbox2 options here
			albumLabel: 'Image %1 of %2', // Custom album label
		});
	}

	// Initialize - only load via AJAX if no server-side images
	loadImages(1, '');
});
