# WooCommerce Order Notifications

A simple WordPress plugin that displays random order notifications in the bottom corner of your website, using real WooCommerce orders to create social proof and encourage purchases.

## Description

WooCommerce Order Notifications displays attractive, animated notifications showing recent customer purchases. The plugin uses real order data from your WooCommerce store, ensuring authenticity and building trust with visitors. Notifications appear at configurable intervals and can include product images, customer names (first name only for privacy), and additional item counts.

## Features

- **Real Order Data**: Only uses actual completed/processing orders from your WooCommerce store
- **Privacy-Focused**: Shows only customer first names
- **Configurable Display**: Customize position, timing, styling, and content
- **Product Images**: Optional product thumbnails in notifications
- **Additional Items**: Shows count of other items purchased in the same order
- **Action Variations**: Randomly varies action words (purchased, ordered, bought, got) for natural variety
- **Accessibility**: Full screen reader support with ARIA attributes
- **Dev Mode**: Test with fake orders using a JSON file
- **CSS Variables**: Easy styling customization via CSS variables

## Installation

1. Upload the `woo-notifications` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings in the plugin file (see Configuration below)

## Configuration

All settings are configured in the `config.php` file. Open `config.php` to customize:

### Basic Settings

- **`order_limit`** (default: 20) - Number of latest orders to fetch
- **`notification_interval`** (default: 10) - Seconds between notifications
- **`notification_duration`** (default: 5) - Seconds each notification is visible
- **`position`** (default: 'bottom-right') - Notification position: 'bottom-right', 'bottom-left', 'top-right', 'top-left'
- **`show_product_image`** (default: true) - Display product images in notifications
- **`show_additional_items`** (default: true) - Show count of additional items purchased

### Template Customization

- **`notification_template`** - Customize the notification text format
  - Available placeholders: `{name}`, `{product}`, `{action}`, `{additional_items}`
  - Default: `'{name} just {action} {product}{additional_items}'`

### Action Variations

- **`action_variations`** - Array of action words randomly selected
  - Default: `['purchased', 'ordered', 'bought', 'got']`
  - Add or remove variations as desired

### CSS Variables

Customize appearance via CSS variables in the `css_variables` array in `config.php`. All variables are prefixed with `--woo-notif-`:

| Variable                          | Default Value                    | Description                                           |
| --------------------------------- | -------------------------------- | ----------------------------------------------------- |
| `--woo-notif-bg-color`            | `#ffffff`                        | Background color of the notification                  |
| `--woo-notif-text-color`          | `#333333`                        | Text color for notification content                   |
| `--woo-notif-border-radius`       | `8px`                            | Border radius for rounded corners                     |
| `--woo-notif-padding`             | `16px 20px`                      | Padding inside the notification (vertical horizontal) |
| `--woo-notif-font-size`           | `16px`                           | Font size for notification text                       |
| `--woo-notif-box-shadow`          | `0 4px 12px rgba(0, 0, 0, 0.15)` | Box shadow for depth effect                           |
| `--woo-notif-animation-duration`  | `0.3s`                           | Duration of show/hide animations                      |
| `--woo-notif-link-color`          | `#0073aa`                        | Color for product links within notifications          |
| `--woo-notif-z-index`             | `9999`                           | Z-index for notification positioning                  |
| `--woo-notif-max-width`           | `350px`                          | Maximum width of the notification                     |
| `--woo-notif-margin`              | `20px`                           | Margin from screen edges (for positioning)            |
| `--woo-notif-image-size`          | `60px`                           | Size of product image (width and height)              |
| `--woo-notif-image-border-radius` | `6px`                            | Border radius for product images                      |

**Example customization:**

```php
'css_variables' => array(
    '--woo-notif-bg-color' => '#f0f0f0',
    '--woo-notif-text-color' => '#000000',
    '--woo-notif-border-radius' => '12px',
    '--woo-notif-max-width' => '400px',
    '--woo-notif-image-size' => '80px',
    // ... other variables
),
```

### Dev Mode

Enable dev mode to test with fake orders:

1. Add to `wp-config.php`: `define('WOO_NOTIFICATIONS_DEV_MODE', true);`
2. Or set `'dev_mode' => true` in the config array
3. Edit `test-orders.json` to customize test data

## How It Works

1. **Order Fetching**: The plugin fetches the latest completed/processing orders from WooCommerce
2. **Data Processing**: For each order, it extracts:
   - Customer first name
   - Random product from the order
   - Product image (if enabled)
   - Total item count for additional items display
3. **Notification Display**: JavaScript randomly selects from the order pool and displays notifications at configured intervals
4. **Caching**: File modification times are used for cache busting, ensuring updates are immediately visible

## File Structure

```
woo-notifications/
├── woo-notifications.php    # Main plugin file
├── config.php               # Plugin configuration
├── assets/
│   ├── css/
│   │   └── woo-notifications.css
│   └── js/
│       └── woo-notifications.js
├── test-orders.json         # Test data for dev mode
└── README.md               # This file
```

## Requirements

- WordPress 5.0+
- PHP 7.4+
- WooCommerce 3.0+
- WooCommerce HPOS (High-Performance Order Storage) compatible

## Browser Support

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Responsive design for mobile devices
- Full accessibility support for screen readers

## License

This plugin is licensed under the GPL v2 or later.

```
Copyright (C) 2024 Taylor Drayson

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
```

## Support

For issues, questions, or contributions, please visit the [GitHub repository](https://github.com/tdrayson/woo-notifications).

## Changelog

### 1.0.0

- Initial release
- Real order notifications
- Configurable settings
- Product images support
- Additional items count
- Action word variations
- Accessibility features
- Dev mode for testing
