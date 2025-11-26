# LatePoint+

A unified addon for LatePoint that consolidates multiple productivity features into a single, lightweight plugin.

## Features

### 🎯 Bulk Services
Add multiple services to orders at once with an intuitive bulk selection interface.

- **Quick bulk selection** - Select multiple services from a categorized list
- **Category filtering** - Filter services by category for easier selection
- **Seamless integration** - Works directly within the order edit screen
- **Smart UI** - Automatically hides when not needed

### ⚡ Quick Status
Toggle service status (active/disabled) directly from the services list.

- **One-click toggle** - Enable or disable services without opening edit screens
- **Visual feedback** - Clear button states show current status
- **AJAX powered** - Instant updates without page reload
- **Permission aware** - Respects user roles and capabilities

### 🚀 Quick Button
Add "New Item" buttons to page headers for faster navigation.

- **Smart placement** - Buttons appear automatically on relevant pages
- **Context aware** - Shows appropriate button for each page (New Service, New Agent, etc.)
- **Responsive design** - Adapts to mobile and desktop layouts
- **Pro support** - Includes buttons for LatePoint Pro features (Bundles, Custom Fields)

## Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.4 or higher
- **LatePoint**: Latest version required
- **LatePoint Pro**: Optional (for Pro features)

## Installation

1. Download the latest release from the [Releases](https://github.com/latepointdev/latepoint-plus/releases) page
2. Upload the `latepoint-plus` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Ensure LatePoint is installed and activated

## Usage

### Bulk Services

![image](/screenshots/bulk-services.gif)

1. Navigate to an existing order/booking
2. Click the "Bulk Add" button in the Order Items section
3. Select services from the list (use category filter if needed)
4. Click "Add Selected Services"
5. Services are added to the order instantly

### Quick Status

![image](/screenshots/quick-status-2.gif)

1. Go to **LatePoint → Services**
2. Each service tile shows an Enable/Disable button
3. Click the button to toggle the service status
4. Page reloads automatically to reflect changes

### Quick Button

![image](/screenshots/quick-button.gif)

1. Navigate to any LatePoint admin page (Services, Agents, Customers, etc.)
2. Look for the "New [Item]" button on the right side of the page header
3. Click to open the new item form
4. Works on all major LatePoint pages

## Supported Pages

Quick Button automatically appears on:

- Services
- Agents
- Customers
- Locations
- Service Categories
- Service Extras
- Processes
- Bundles (Pro)
- Custom Fields (Pro)

## Development

### File Structure

```
latepoint-plus/
├── assets/                 # CSS and JavaScript files
│   ├── bulk-services/
│   ├── quick-button/
│   └── quick-status/
├── languages/             # Translation files
├── lib/
│   ├── class-latepoint-plus.php    # Main plugin class
│   ├── helpers/                     # Helper classes
│   ├── controllers/                 # Controller classes
│   └── modules/                     # Module classes
│       ├── bulk-services/
│       ├── quick-button/
│       └── quick-status/
└── latepoint-plus.php     # Plugin entry point
```

### Hooks and Filters

The plugin uses standard LatePoint hooks:

- `latepoint_init` - Initialize addon with LatePoint
- `latepoint_includes` - Load additional files
- `latepoint_installed_addons` - Register addon
- `latepoint_admin_enqueue_scripts` - Load admin assets

## Changelog

### 1.0.0 (2025-11-26)
- Initial release
- Bulk Services module
- Quick Status module
- Quick Button module
- Full LatePoint integration
- Responsive design
- Translation ready

## Credits

Developed by [Latepoint Dev](https://latepoint.dev)

## License

GPL v2 or later - see [LICENSE](LICENSE) for details

## Support

For issues, feature requests, or questions:
- Open an issue on [GitHub](https://github.com/latepointdev/latepoint-plus/issues)
- Visit [Latepoint Dev](https://latepoint.dev)
