# Deployment Guide

This repository uses GitHub Actions for automated deployments to staging and production servers.

## Local Development Setup

### Recommended Environment

**LocalWP** - The recommended local WordPress development environment
- Download: https://localwp.com/
- Includes everything needed: PHP, MySQL, nginx/Apache
- Built-in database management with **AdminNeo**

### Required Dependencies

Install these on your local machine:

1. **Node.js and npm** (v18 or higher)
   - Install from: https://nodejs.org/
   - Verify: `node --version && npm --version`
   - Required for building theme assets

2. **Composer** (PHP dependency manager)
   - Install from: https://getcomposer.org/
   - Verify: `composer --version`
   - Required for PHP dependencies

3. **Git**
   - Install from: https://git-scm.com/
   - Verify: `git --version`
   - Required for version control

### Initial Setup

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd <project-directory>
   ```

2. **Install dependencies:**
   ```bash
   # Install Node.js dependencies for theme builds
   npm install
   
   # Install PHP dependencies (if any)
   composer install
   ```

3. **Build theme assets:**
   ```bash
   # Development build (with watch)
   npm run watch
   
   # Production build
   npm run build:production
   ```

4. **Access database:**
   - Open LocalWP
   - Right-click on your site
   - Select **"Open AdminNeo"** to manage the database
   - AdminNeo provides a modern interface for viewing/editing database tables

## Server Deployment Prerequisites

1. SSH access to staging and production servers
2. rsync installed on servers
3. SSH keys configured in GitHub Secrets

## Deployment Workflows

### Staging Deployment (Automatic)

**Trigger:** Push to `staging` branch

```bash
git checkout staging
git merge main  # or make changes
git push origin staging
```

This will automatically:
1. Build the pbc theme assets on GitHub Actions runner
2. Install production PHP dependencies (Composer)
3. Remove development files and node_modules
4. Deploy only production-ready files to staging server via rsync
5. Clear WordPress cache

### Production Deployment (Manual)

**Trigger:** Manual workflow dispatch from `main` branch

1. Ensure your code is merged to `main`:
   ```bash
   git checkout main
   git pull origin main
   git push origin main
   ```

2. Go to GitHub:
   - Navigate to **Actions** tab
   - Select **"Deploy to Production"** workflow
   - Click **"Run workflow"**
   - Type `deploy` in the confirmation field
   - Click **"Run workflow"** button

This will:
1. Create a backup on production server
2. Build the pbc theme assets on GitHub Actions runner
3. Install production PHP dependencies (Composer)
4. Remove development files and node_modules
5. Deploy only production-ready files to production server via rsync
6. Clear WordPress cache
7. Verify deployment

## GitHub Secrets Configuration

### Required Secrets

Configure these in: **Settings → Secrets and variables → Actions → New repository secret**

#### Staging Secrets
- `STAGING_SSH_PRIVATE_KEY` - SSH private key for staging
- `STAGING_SSH_USER` - SSH username (e.g., `username`)
- `STAGING_SERVER_HOST` - Server hostname (e.g., `staging.example.com`)
- `STAGING_DEPLOY_PATH` - Full path (e.g., `/home/user/public_html`)

#### Production Secrets
- `PRODUCTION_SSH_PRIVATE_KEY` - SSH private key for production
- `PRODUCTION_SSH_USER` - SSH username
- `PRODUCTION_SERVER_HOST` - Server hostname
- `PRODUCTION_DEPLOY_PATH` - Full path to WordPress install

## SSH Key Setup

### Generate Deployment Keys

```bash
# Generate new SSH key for deployments
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_deploy_key

# Don't set a passphrase (press Enter when prompted)
```

### Add Public Key to Servers

```bash
# Copy to staging server
ssh-copy-id -i ~/.ssh/github_deploy_key.pub user@staging-server

# Copy to production server
ssh-copy-id -i ~/.ssh/github_deploy_key.pub user@production-server
```

### Add Private Key to GitHub

```bash
# Display private key
cat ~/.ssh/github_deploy_key
```

Copy the entire output (including `-----BEGIN` and `-----END` lines) and add it to GitHub Secrets.

## How Deployment Works

### Build Process (GitHub Actions Runner)
1. **Checkout code** from repository
2. **Install Node.js dependencies** (`npm ci`)
3. **Build theme assets** (`npm run build:production`)
4. **Install PHP dependencies** (`composer install --no-dev`)
5. **Clean up development files**:
   - Remove all `node_modules/`
   - Remove source asset files (`resources/assets/`)
   - Remove build config files (`webpack.*.js`, `package.json`, etc.)
   - Remove test files and configs
6. **Keep only production files**:
   - Built CSS/JS in `dist/`
   - PHP vendor dependencies
   - Theme templates and functions

### What Gets Deployed

**Included:**
- WordPress core files
- Themes with built assets (dist/ folder)
- PHP vendor dependencies (composer) - **including plugin vendors**
- Plugins (with their vendor directories)
- Theme templates and PHP files

**Excluded:**
- `.git/` and `.github/` (version control)
- `node_modules/` (all JS dependencies)
- Theme source files (`wp-content/themes/*/resources/assets/`)
- Theme build configs (`wp-content/themes/*/package.json`, `webpack.*.js`, etc.)
- Theme composer files (`wp-content/themes/*/composer.json`, `composer.lock`)
- `.eslintrc`, `.babelrc`, etc. (dev configs in themes)
- `wp-content/uploads/` (user content)
- `wp-content/cache/` (temporary files)
- `wp-config.php` (server-specific config)
- `.env` (environment variables)
- `*.log` (log files)

**Important:** Plugin `vendor/` directories and dependencies ARE included. Only theme build files are excluded.

### Why Build on GitHub Actions?

**Benefits:**
- ✅ Server doesn't need Node.js or npm installed
- ✅ Server doesn't need build tools (webpack, etc.)
- ✅ Faster deployment (only upload built files)
- ✅ Smaller file transfer (no node_modules)
- ✅ Consistent build environment
- ✅ Server CPU not used for builds
- ✅ More secure (no build dependencies on server)

## Troubleshooting

### Build Fails
- Check Node.js version compatibility
- Verify all npm dependencies are listed in `package.json`
- Review build logs for specific errors

### Deployment Fails
- Verify SSH keys are correctly configured
- Check server firewall allows GitHub Actions IPs
- Ensure deploy path exists and has correct permissions
- Test SSH connection manually:
  ```bash
  ssh -i ~/.ssh/github_deploy_key user@server
  ```

### Cache Not Clearing
- Clear cache via WordPress admin dashboard
- Use hosting control panel cache tools
- Check server cache configuration

### Plugin Errors After Deployment
If you see errors like `Failed to open stream: No such file or directory` mentioning `vendor/`:

**Cause:** Plugin dependencies weren't deployed correctly.

**Solution:**
1. The deployment now correctly includes plugin vendor directories
2. Redeploy to restore any missing files:
   ```bash
   git push origin staging  # triggers staging deployment
   ```
3. If a specific plugin is missing its vendor directory, SSH to the server and run:
   ```bash
   cd wp-content/plugins/plugin-name
   composer install --no-dev
   ```

## Rollback Procedure

If deployment fails or issues occur:

1. **Automatic Backup**: Each production deployment creates a backup in `wp-content/backups/`

2. **Manual Rollback**:
   ```bash
   ssh user@production-server
   cd /path/to/wordpress
   tar -xzf wp-content/backups/backup-YYYYMMDD-HHMMSS.tar.gz
   ```

3. **Redeploy Previous Version**:
   - Find the last working commit
   - Manually trigger deployment from that commit

## Branch Strategy

- `main` - Production-ready code
- `staging` - Testing and QA
- Feature branches - Development work

**Workflow:**
```
feature-branch → staging → main → production
```

## Security Notes

1. Never commit secrets to repository
2. Rotate SSH keys periodically
3. Use environment protection rules for production
4. Limit server permissions for deployment user
5. Keep backups for at least 30 days

## Support

For deployment issues:
1. Check GitHub Actions logs
2. Review this documentation
3. Contact system administrator
