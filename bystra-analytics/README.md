# Bystra Analytics

A WordPress plugin for safely adding tracking and analytics scripts to your WordPress site without editing theme files.

## Features

- Inject scripts in the Header, Body, and Footer sections of your site
- User-friendly admin interface with syntax highlighting
- Secure handling of script content
- Works with popular analytics tools like Google Tag Manager, Facebook Pixel, and more

## Installation

1. Upload the plugin files to the `/wp-content/plugins/bystra-analytics` directory.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Bystra Analytics screen to configure the plugin.

## Usage

### Adding Scripts

1. Navigate to **Settings > Bystra Analytics**
2. Add your script to the appropriate section:
   - **Header**: For scripts that need to be in the `<head>` section
   - **After Opening Body Tag**: For scripts that should be placed immediately after the `<body>` tag
   - **Footer**: For scripts that should be placed before the closing `</body>` tag
3. Click **Save Settings**

### Common Use Cases

- **Google Tag Manager**: Place the main script in the Header section and the noscript part in the Body section
- **Facebook Pixel**: Add to the Footer section
- **Google Analytics**: Add to the Header section

## Security Notes

This plugin allows for the injection of custom scripts into your site. While it includes basic sanitization, you should only paste code from trusted sources.

## Changelog

### 1.4
- Enhanced security with improved sanitization
- Added more detailed examples and documentation
- Interface improvements

### 1.3
- Initial public release

## License

GPL2
