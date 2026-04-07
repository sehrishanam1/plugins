# Anexly Shortcodes Plugin

One plugin. All shortcodes live as self-contained modules in `/shortcodes/`.

## Adding a new shortcode

1. Create a new folder inside `/shortcodes/` — e.g. `shortcodes/my-feature/`
2. Add `index.php` — register your shortcode with `add_shortcode()` here
3. Optionally add `style.css` and/or `script.js` in the same folder
4. Call `anexly_sc_assets('my-feature')` inside your render function to auto-enqueue them
5. Done — the auto-loader picks it up on next page load

## Disabling a shortcode (without deleting)

Rename the folder with an underscore prefix:
`shortcodes/my-feature/` → `shortcodes/_my-feature/`

The auto-loader skips any folder starting with `_`.

## Current shortcodes

| Folder | Shortcode | Description |
|---|---|---|
| `example/` | `[anexly_example]` | Starter template — delete when not needed |
| `ajax-filter/` | `[anexly_filter_bar]` | Category tabs + search bar |
| `ajax-filter/` | `[anexly_products_grid]` | Product slider with AJAX filtering and loader |
| `bundle-widget/` | `[wc_bundle_widget]` | Subscription bundle selector with discount, countdown timer and social proof |
| `price-compare/` | `[anexly_price_compare id="123"]` | Price comparison calculator widget — auto-detects product on product pages |
| `shop-filter/` | `[shop_filter]` | Full shop page with sidebar filters, AJAX, grid/list view, load more |
| `shop-page/` | `[anexly_shop]` | Filterable product shop page |

---

## Shortcode reference

### `[anexly_example]`
Starter template showing the correct module pattern. Safe to delete once you understand the structure.

**Parameters:**
- `name` — display name (default: `World`)
- `color` — accent color (default: `coral`)

**Example:** `[anexly_example name="Anexly" color="blue"]`

---

### `[anexly_filter_bar]`
Renders category icon tabs and a search input. Place this above `[anexly_products_grid]` on the same page.

**No parameters.** Categories and icons are pulled automatically from WooCommerce product categories. To set a category icon, upload a thumbnail image on the category edit page in WooCommerce.

**Example:** `[anexly_filter_bar]`

---

### `[anexly_products_grid]`
Renders the AJAX product slider. Automatically responds to filter/search actions from `[anexly_filter_bar]`. Includes a branded loading spinner, no-results state, and a reset button.

**No parameters.** Products per page is hardcoded to 20.

**Example:** `[anexly_products_grid]`

**Typical page setup:**
```
[anexly_filter_bar]
[anexly_products_grid]
```

---

### `[wc_bundle_widget]`
Shows a checkbox-style subscription bundle picker. Users select which products they want, the widget calculates a live total, applies a bundle discount on 2+ items, and adds everything to cart as a single hidden WooCommerce product.

**No shortcode parameters.** All configuration is done in **Settings → Bundle Widget** in wp-admin.

**Settings available:**
- Products to include (select from WooCommerce, set price override per item)
- Discount % applied when 2 or more items are selected
- Countdown timer end date/time
- Social proof text (name + item)
- Bundle product name (shown in orders)

**Example:** `[wc_bundle_widget]`

---

### `[anexly_price_compare]`
Shows a 3-column price comparison card: Regular Price vs Your Store Price vs Savings. Calculates save % and total annual savings automatically.

**Parameters:**
- `id` — WooCommerce product ID. Optional on single product pages (auto-detects).

**Example:** `[anexly_price_compare id="123"]`

**Per-product fields** (set on the product edit page under "💰 Price Comparison Calculator"):
- Widget title and subtitle
- Brand label (center column heading)
- Market monthly + annual price
- Store monthly + annual price
- CTA button text and URL

---

### `[shop_filter]`
Full-featured shop page with a sticky filter sidebar, AJAX product grid, grid/list view toggle, sort dropdown, load more, and a mobile slide-in filter panel.

**Filters available:** Categories, Price range slider, Service Provider (product_brand taxonomy), Duration (pa_duration attribute)

**Parameters:**
- `per_page` — products per page (default: `6`)
- `columns` — grid columns (default: `3`)
- `title` — page heading (default: `All products`)
- `subtitle` — subheading (default: `Library of subscriptions`)
- `orderby` — default sort: `popular` | `rating` | `recent` | `default`

**Example:** `[shop_filter per_page="9" title="All Products" subtitle="Browse 600+ subscriptions" orderby="popular"]`

**Requirements:**
- WooCommerce active
- For brand filtering: `product_brand` taxonomy must exist
- For duration filtering: global WooCommerce attribute with slug `duration` (`pa_duration`) must exist with terms assigned to products

---

### `[anexly_shop]`
Alternative shop page with left sidebar filters (categories, price, service provider, duration), AJAX filtering, sort, and load more.

**Parameters:**
- `per_page` — products per page (default: `12`)
- `title` — page heading (default: `All products`)
- `subtitle` — subheading text

**Example:** `[anexly_shop title="All products" subtitle="Library of 600 subscriptions" per_page="12"]`

---

## Shortcode template (index.php)
```php
<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode( 'your_tag', 'your_tag_render' );

function your_tag_render( $atts ) {
    $atts = shortcode_atts( [
        'param' => 'default',
    ], $atts, 'your_tag' );

    anexly_sc_assets( 'your-folder-name' ); // loads style.css + script.js if they exist

    ob_start(); ?>
    <div class="your-shortcode">
        <!-- your HTML here -->
    </div>
    <?php return ob_get_clean();
}
```

[anexly_products_grid]
[wc_bundle_widget]
[anexly_price_compare]