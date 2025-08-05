# Deployment Guide for Gift Message for Woo

This guide explains how to deploy the plugin to the WordPress.org repository using GitHub Actions.

## Prerequisites

1. **WordPress.org Account**: You need a WordPress.org account with commit access to the plugin
2. **GitHub Repository**: Push this code to a GitHub repository
3. **SVN Credentials**: Your WordPress.org username and password

## Setup

### 1. Configure GitHub Secrets

In your GitHub repository, go to Settings → Secrets and variables → Actions, and add:

- `SVN_USERNAME`: Your WordPress.org username
- `SVN_PASSWORD`: Your WordPress.org password

### 2. Prepare Assets

Add the following files to the `.wordpress-org` directory:

- `icon-128x128.png` - Plugin icon (128x128 pixels)
- `icon-256x256.png` - Plugin icon (256x256 pixels)
- `banner-772x250.png` - Plugin banner (772x250 pixels)
- `banner-1544x500.png` - Plugin banner for retina (1544x500 pixels)
- `screenshot-1.png` through `screenshot-8.png` - Screenshots mentioned in readme.txt

## Deployment Process

### Automatic Deployment (Recommended)

1. **Create a Release**:
   ```bash
   git tag 1.0.1
   git push origin 1.0.1
   ```

2. **Create GitHub Release**:
   - Go to your repository → Releases → Create a new release
   - Choose the tag you just created
   - Add release notes
   - Publish the release

3. The `wordpress-deploy.yml` workflow will automatically:
   - Build the plugin
   - Create a zip file
   - Deploy to WordPress.org SVN
   - Attach the zip to the GitHub release

### Manual Deployment

If you need to deploy manually:

1. Go to Actions → WordPress.org Deployment
2. Click "Run workflow"
3. Enter the version number
4. Click "Run workflow"

### Updating Only Assets or Readme

To update only assets or readme.txt without deploying a new version:

1. Make changes to files in `.wordpress-org` directory or `readme.txt`
2. Commit and push to main branch
3. The `wordpress-assets.yml` workflow will automatically update WordPress.org

## Workflow Files

### `wordpress-deploy.yml`
Main deployment workflow that:
- Triggers on new releases
- Builds the plugin (composer, npm)
- Creates a clean build without development files
- Deploys to WordPress.org SVN
- Creates a downloadable zip file

### `wordpress-assets.yml`
Assets update workflow that:
- Triggers when `.wordpress-org` directory changes
- Updates only assets and readme.txt
- Doesn't create a new plugin version

### `test.yml`
Testing workflow that:
- Runs PHP syntax checks
- Checks WordPress coding standards
- Validates readme.txt format
- Verifies assets exist

## Version Management

1. Update version in:
   - `gift-message-for-woo.php` (header comment)
   - `readme.txt` (Stable tag)
   - Any other files with version numbers

2. Update changelog in `readme.txt`

3. Commit changes:
   ```bash
   git add .
   git commit -m "Version 1.0.1"
   git push origin main
   ```

4. Create and push tag:
   ```bash
   git tag 1.0.1
   git push origin 1.0.1
   ```

## Troubleshooting

### Deployment Failed

1. Check GitHub Actions logs for errors
2. Verify SVN credentials are correct
3. Ensure version numbers match everywhere
4. Check that build process completes successfully

### Assets Not Updating

1. Verify files are in `.wordpress-org` directory
2. Check file naming matches exactly (e.g., `icon-128x128.png`)
3. Ensure files are committed and pushed

### Common Issues

- **Authentication failed**: Check SVN_USERNAME and SVN_PASSWORD secrets
- **Build failed**: Check if npm/composer dependencies are installed
- **Version mismatch**: Ensure version is consistent across all files

## Best Practices

1. **Test Locally**: Always test the plugin thoroughly before deploying
2. **Semantic Versioning**: Follow semantic versioning (MAJOR.MINOR.PATCH)
3. **Changelog**: Keep readme.txt changelog updated
4. **Assets**: Optimize images before uploading (use tools like TinyPNG)
5. **Review**: Review the build output in GitHub Actions before it deploys

## Security Notes

- Never commit SVN credentials to the repository
- Use GitHub Secrets for sensitive information
- Review the exclude list in deployment workflow to ensure no sensitive files are included