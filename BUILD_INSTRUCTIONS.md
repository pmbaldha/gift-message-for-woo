# Build Instructions for Gift Message for WooCommerce

This plugin includes automated build scripts to create WordPress.org compliant zip files for distribution.

## Build Scripts

### PHP Build Script (Recommended)
The PHP build script is cross-platform and works on Windows, Mac, and Linux.

**Usage:**
```bash
php build.php
```

### Windows Batch Script
For Windows users who prefer a native batch script:

**Usage:**
```cmd
build.bat
```

### Unix/Linux Shell Script
For Unix/Linux/Mac users:

**Usage:**
```bash
./build.sh
```

## What the Build Script Does

1. **Cleans up** any existing build and dist directories
2. **Copies** all plugin files to a temporary build directory
3. **Removes** development files and directories:
   - Version control files (.git, .github)
   - Development dependencies (node_modules, vendor/composer)
   - Build configuration files (composer.json, package.json, etc.)
   - IDE files (.vscode, .idea)
   - WordPress.org repository assets (.wordpress-org)
   - Documentation and build scripts
4. **Creates** a zip file with version number from the plugin header
5. **Outputs** the zip file to the `dist/` directory

## Output

The build script creates a zip file in the format:
```
dist/gift-message-for-woo-{version}.zip
```

This zip file is ready to be uploaded to WordPress.org or distributed to users.

## Excluding Files

The `.distignore` file contains patterns for files that should be excluded from the distribution zip. The build scripts automatically exclude these files.

## Requirements

- **PHP Build Script**: PHP 5.6+ with ZipArchive extension
- **Windows Batch Script**: Windows with PowerShell
- **Shell Script**: Unix/Linux/Mac with zip command

## Troubleshooting

If the build fails:
1. Ensure you have write permissions in the plugin directory
2. Check that PHP has the ZipArchive extension enabled
3. On Windows, ensure PowerShell execution policy allows scripts
4. Make sure the plugin's main file contains a valid Version header