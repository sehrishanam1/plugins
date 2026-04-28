# Team Members – WordPress Plugin

A fully dynamic Team Members plugin for WordPress with:
- Custom Post Type (CPT)
- Custom meta fields (position, social links, custom order)
- Elementor widget with style controls
- `[team_members]` shortcode
- Hover overlay, image zoom, and "Read more" JS toggle
- Responsive grid (1–4 columns)

---

## Installation

1. Upload the `team-members` folder to `/wp-content/plugins/`.
2. Activate the plugin via **Plugins › Installed Plugins**.
3. Flush permalinks: **Settings › Permalinks › Save Changes**.

---

## Adding Team Members

1. Go to **Team Members › Add New** in the WordPress admin.
2. Enter the member's **name** in the Title field.
3. Upload a **Featured Image** (the member's photo).
4. Fill in the **excerpt** (short bio shown on the card). If blank, the post content is used.
5. Complete the **Member Details** meta box:
   | Field | Notes |
   |-------|-------|
   | Position / Job Title | Required – shown under the name |
   | Email Address | Optional – email icon in overlay |
   | Custom Display Order | Lower number = shown first |
   | LinkedIn URL | Optional social link |
   | Twitter / X URL | Optional social link |

---

## Displaying Team Members

### Shortcode

Add `[team_members]` anywhere in a post, page or widget area.

**All available attributes:**

| Attribute | Default | Options | Description |
|-----------|---------|---------|-------------|
| `limit` | `3` | any integer | Number of members to display (`-1` = all) |
| `orderby` | `date` | `date`, `custom_order`, `title` | Sort order |
| `order` | `DESC` | `ASC`, `DESC` | Direction (ignored for `custom_order`) |
| `columns` | `3` | `1`–`4` | Grid columns |
| `department` | *(empty)* | taxonomy slug | Filter by department |

**Examples:**

```
[team_members]
[team_members limit="6" columns="2" orderby="custom_order"]
[team_members department="engineering" limit="3"]
```

### Elementor Widget

1. Edit any page with Elementor.
2. Search for **"Team Members"** in the widget panel.
3. Drag it onto the canvas.
4. Configure query, layout, and styling options in the panel.

---

## Sorting Options

| `orderby` value | Behaviour |
|-----------------|-----------|
| `date` | Newest members first (default) |
| `custom_order` | Ascending by the **Custom Display Order** meta field |
| `title` | Alphabetically by name |

---

## Grouping by Department

1. Go to **Team Members › Departments** and create your departments (e.g. *Engineering*, *Design*).
2. Assign members to departments from the edit screen.
3. Filter with the shortcode: `[team_members department="design"]`

---

## Frontend Interactions

- **Image zoom** – smooth scale on card hover.
- **Overlay** – gradient with social links slides up on hover (always visible on mobile).
- **Read more / Show less** – click to expand the full bio. Fully accessible (ARIA).

---

## File Structure

```
team-members/
├── team-members.php                  ← Plugin entry point
├── includes/
│   ├── class-cpt.php                 ← Custom Post Type + Taxonomy
│   ├── class-meta-boxes.php          ← Custom meta fields
│   ├── class-shortcode.php           ← [team_members] shortcode
│   ├── class-assets.php              ← CSS/JS enqueue
│   └── class-elementor-widget.php    ← Elementor widget
├── templates/
│   └── team-grid.php                 ← Shared HTML template
└── assets/
    ├── css/team-members.css
    ├── js/team-members.js
    └── img/placeholder.svg
```

---

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Elementor 3.0+ *(optional – shortcode works without it)*

---

## Customisation

Override the card template in your theme by copying
`templates/team-grid.php` to `your-theme/team-members/team-grid.php`.
The plugin checks for a theme override first.

> Add this to the plugin's `render()` and `shortcode render()` methods:
> ```php
> $template = locate_template( 'team-members/team-grid.php' );
> include $template ?: TM_PLUGIN_DIR . 'templates/team-grid.php';
> ```
