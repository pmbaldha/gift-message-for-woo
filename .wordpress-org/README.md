# WordPress.org Plugin Assets

This directory contains assets that are displayed on the WordPress.org plugin page.

## Required Assets

### Plugin Icon
- **Filenames**: `icon-128x128.png` and `icon-256x256.png` (or .jpg)
- **Description**: Square plugin icon displayed in search results and plugin header

### Plugin Banner
- **Filenames**: `banner-772x250.png` and `banner-1544x500.png` (or .jpg)
- **Description**: Banner displayed at the top of your plugin page
- **Low-res**: 772x250 pixels
- **High-res**: 1544x500 pixels (for retina displays)

### Screenshots
- **Filenames**: `screenshot-1.png`, `screenshot-2.png`, etc. (or .jpg, .gif)
- **Description**: Screenshots referenced in readme.txt
- **Order**: Must match the screenshot descriptions in readme.txt

## Asset Guidelines

1. **File Types**: PNG or JPG for static images, GIF for animations
2. **File Size**: Keep files optimized (under 1MB each recommended)
3. **Design**: Follow WordPress.org design guidelines
4. **Naming**: Use exact filenames as specified above

## Updating Assets

Assets are automatically deployed when:
1. Changes are pushed to the `.wordpress-org` directory
2. The `wordpress-assets.yml` workflow is triggered

## Current Assets Status

- [ ] Icon 128x128
- [ ] Icon 256x256
- [ ] Banner 772x250
- [ ] Banner 1544x500
- [ ] Screenshots (8 planned according to readme.txt)