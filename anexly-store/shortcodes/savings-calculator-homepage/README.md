# Savings Calculator Homepage

## Files
- `index.php` — main shortcode + styles + Elementor widget loader
- `elementor-widget.php` — Elementor widget class (auto-loaded by index.php)

---

## Shortcode Usage

```
[savings_calculator_homepage]
```

### All available attributes

| Attribute  | Default                        | Description                                      |
|------------|-------------------------------|--------------------------------------------------|
| `title`    | "See How Much You Save…"      | Heading text                                     |
| `subtitle` | Auto from site name           | Subheading text                                  |
| `months`   | `12`                          | Months used for annual calculation               |
| `limit`    | `10`                          | Products to show (auto mode)                     |
| `orderby`  | `date`                        | `date` / `popularity` / `rating` / `price`       |
| `products` | *(empty)*                     | Comma-separated product IDs — **overrides limit/orderby** |
| `cta_text` | `Browse Deals`                | Button label                                     |
| `cta_url`  | `/shop`                       | Button URL                                       |

### Manual product selection example

```
[savings_calculator_homepage products="12,45,78,102" title="Top Picks" cta_text="Shop Now"]
```

Products appear in the **order you list them**.

---

## Elementor Widget

The widget registers automatically if Elementor is active.

1. Open Elementor editor
2. Search for **"Savings Calculator"** in the widget panel
3. Drag onto the page
4. In **Products** tab → choose **Auto** or **Manual**
5. If Manual: click **"Add Item"** and enter each product ID

---

## What changed from v1

- Native `<select>` replaced with a fully custom dropdown
- Each dropdown item now shows the **product thumbnail / icon**
- `products=""` shortcode attribute for **manual product selection**
- Elementor widget with **Repeater control** for picking products by ID
- Keyboard navigation (Enter, Space, Escape) and ARIA attributes for accessibility
- Smooth open/close animation on the chevron icon
