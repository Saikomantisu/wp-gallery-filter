# Gallery Filter

Filter and display WordPress media attachments by custom categories. The plugin registers an `image_category` taxonomy, provides an AJAX-powered admin screen to tag images in bulk, and outputs a responsive masonry gallery via shortcode.

---

## Features

• Custom taxonomy for images (`gf_image_category`).  
• Bulk tagging and search in a no-reload admin grid.  
• Two category dropdown filters on the front-end.  
• Lazy-loaded, CSS masonry layout with pagination.

---

## Installation

1. Copy the `gallery-filter` folder to `wp-content/plugins/`.  
2. Activate **Gallery Filter** in the Plugins screen.  
3. Open **Media → Gallery Filter** to start tagging images.

---

## Usage

Add the shortcode where the gallery should appear:

```text
[gallery_filter]
```

Defaults: 12 images per page, four masonry columns on large screens. The gallery automatically shows the two category filters and pagination controls.

---

## Stack

| Layer | Details |
|-------|---------|
| PHP   | OOP classes, AJAX via `wp_ajax`, custom taxonomy |
| JS    | ES6 + WordPress-bundled jQuery (`assets/js/`) |
| CSS   | Modern flex / column layout (`assets/css/`) |

No external build tools or frameworks.