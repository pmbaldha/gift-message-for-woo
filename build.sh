#!/bin/bash

# Build script for Gift Message for WooCommerce plugin
# Creates a WordPress.org compliant zip file

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Plugin info
PLUGIN_SLUG="gift-message-for-woo"
PLUGIN_DIR=$(dirname "$0")
BUILD_DIR="${PLUGIN_DIR}/build"
DIST_DIR="${PLUGIN_DIR}/dist"

echo -e "${GREEN}Building ${PLUGIN_SLUG} plugin...${NC}"

# Clean up any existing build/dist directories
echo "Cleaning up old builds..."
rm -rf "$BUILD_DIR"
rm -rf "$DIST_DIR"

# Create directories
mkdir -p "$BUILD_DIR"
mkdir -p "$DIST_DIR"

# Copy plugin files to build directory
echo "Copying plugin files..."
cp -R "$PLUGIN_DIR"/* "$BUILD_DIR/" 2>/dev/null || true

# Navigate to build directory
cd "$BUILD_DIR" || exit 1

# Remove development and build files
echo "Removing development files..."
rm -rf build/
rm -rf dist/
rm -rf .git/
rm -rf .github/
rm -rf tests/
rm -rf node_modules/
rm -rf vendor/composer/
rm -rf .idea/
rm -rf .vscode/

# Remove development files
rm -f .gitignore
rm -f .gitattributes
rm -f .editorconfig
rm -f .distignore
rm -f .phpcs.xml
rm -f .phpcs.xml.dist
rm -f phpunit.xml
rm -f phpunit.xml.dist
rm -f composer.json
rm -f composer.lock
rm -f package.json
rm -f package-lock.json
rm -f webpack.config.js
rm -f Gruntfile.js
rm -f gulpfile.js
rm -f .eslintrc
rm -f .eslintrc.js
rm -f .eslintrc.json
rm -f .stylelintrc
rm -f .stylelintrc.json
rm -f CLAUDE.md
rm -f build.sh
rm -f *.md
rm -f *.log
rm -f *.lock

# Remove any hidden files (except .htaccess if it exists)
find . -name ".*" -not -name ".htaccess" -type f -delete 2>/dev/null || true

# Remove any empty directories
find . -type d -empty -delete 2>/dev/null || true

# Get version from main plugin file
VERSION=$(grep -Po 'Version:\s*\K[0-9.]+' "${PLUGIN_SLUG}.php" 2>/dev/null || echo "1.0.0")

# Create zip file
echo "Creating zip file..."
cd ..
ZIP_NAME="${PLUGIN_SLUG}-${VERSION}.zip"
zip -r "$DIST_DIR/$ZIP_NAME" "$(basename "$BUILD_DIR")" -x "*.DS_Store" -x "__MACOSX/*" -q

# Clean up build directory
rm -rf "$BUILD_DIR"

# File size info
if [ -f "$DIST_DIR/$ZIP_NAME" ]; then
    SIZE=$(du -h "$DIST_DIR/$ZIP_NAME" | cut -f1)
    echo -e "${GREEN}✓ Build complete!${NC}"
    echo -e "${GREEN}Plugin zip created: ${YELLOW}$DIST_DIR/$ZIP_NAME${NC} (${SIZE})"
    
    # List contents for verification
    echo -e "\n${YELLOW}Zip contents:${NC}"
    unzip -l "$DIST_DIR/$ZIP_NAME" | head -20
    echo "..."
else
    echo -e "${RED}✗ Build failed!${NC}"
    exit 1
fi