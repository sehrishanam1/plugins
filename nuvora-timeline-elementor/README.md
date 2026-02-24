# Nuvora Timeline Elementor

A beautiful, animated timeline widget for Elementor with multiple color schemes and customization options.

## Features

- 🎨 **3 Color Schemes**: Purple/Pink, Blue, and Green
- ⚡ **Animated**: Smooth fadeInUp animations with customizable delays
- 📱 **Responsive**: Looks great on all devices
- 🎯 **Easy to Use**: Simple drag-and-drop interface in Elementor
- 🔧 **Customizable**: Control animation, width, and individual item settings
- 🎭 **Icon Support**: Uses LineIcons library with hundreds of icons
- ♾️ **Unlimited Items**: Add as many timeline events as you need

## Requirements

- WordPress 5.0 or higher
- Elementor 3.0.0 or higher
- PHP 7.0 or higher

## Installation

### Method 1: Upload via WordPress Admin

1. Download the plugin zip file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" button
4. Choose the zip file and click "Install Now"
5. Activate the plugin

### Method 2: Manual Upload

1. Extract the zip file
2. Upload the `timeline-widget` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

1. **Edit with Elementor**: Open any page/post with Elementor
2. **Find Widget**: Search for "Timeline" in the Elementor widget panel
3. **Drag & Drop**: Drag the Timeline widget to your desired location
4. **Customize**: Configure your timeline items in the left panel

## Widget Settings

### Timeline Items (Repeater)

Each timeline item includes:

- **Icon**: Choose from LineIcons library or upload custom icon
- **Date**: Display date/time for the event
- **Title**: Event title (appears in uppercase)
- **Description**: Detailed description of the event
- **Color Scheme**: Choose from 3 pre-designed color themes
  - Purple/Pink (Type 1)
  - Blue (Type 2)
  - Green (Type 3)
- **Animation Delay**: Control when each item animates (0-4 seconds)

### Style Settings

- **Enable Animation**: Toggle fade-in animations on/off
- **Timeline Width**: Adjust the width of the timeline (responsive)

## Customization

### Changing Colors

You can modify the colors by editing `/assets/css/timeline-style.css`:

```css
/* Type 1 - Purple/Pink */
--color1: #9251ac; /* Main color */
--color2: #f6a4ec; /* Accent color */

/* Type 2 - Blue */
--color3: #87bbfe; /* Main color */
--color4: #555ac0; /* Accent color */

/* Type 3 - Green */
--color5: #24b47e; /* Main color */
--color6: #aff1b6; /* Accent color */
```

### Adding Custom Icons

The widget uses LineIcons by default. To use different icons:

1. Enqueue your icon library in `timeline-widget.php`
2. Update the icon control in `widgets/timeline-widget.php`

## File Structure

```
timeline-widget/
├── timeline-widget.php          # Main plugin file
├── widgets/
│   └── timeline-widget.php      # Widget class
├── assets/
│   └── css/
│       └── timeline-style.css   # Widget styles
└── README.md                    # Documentation
```

## Hooks & Filters

Currently, there are no custom hooks or filters. Future updates may include:
- `timeline_widget_before_render` - Action before timeline renders
- `timeline_widget_after_render` - Action after timeline renders
- `timeline_widget_default_colors` - Filter to modify default colors

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Widget Not Showing

- Make sure Elementor is installed and activated
- Clear Elementor cache: Elementor → Tools → Regenerate CSS
- Clear browser cache

### Icons Not Displaying

- Check if LineIcons CSS is loading
- Try using a different icon from the icon picker

### Animations Not Working

- Ensure Animate.css is loading
- Check if "Enable Animation" is turned on
- Try disabling other animation plugins that might conflict

## Credits

- Original Timeline Design: [CodePen by hunzaboy](https://codepen.io/hunzaboy/pen/qBWRBXw)
- Icons: [LineIcons](https://lineicons.com/)
- Animations: [Animate.css](https://animate.style/)

## Changelog

### Version 1.0.0
- Initial release
- 3 color schemes
- Animated timeline
- Fully responsive
- Elementor integration

## Support

For support, feature requests, or bug reports, please contact the plugin developer.

## License

This plugin is licensed under GPL v2 or later.

## Screenshots

(Add screenshots of your widget in action here)

1. Timeline widget in Elementor editor
2. Purple/Pink color scheme
3. Blue color scheme
4. Green color scheme
5. Mobile responsive view

---

**Made with ❤️ for Elementor**
