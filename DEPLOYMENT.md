# Deployment Guide

This repository uses GitHub Actions for automated deployments to staging and production servers.

## Prerequisites

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
1. Build the pbc theme assets
2. Install production dependencies
3. Deploy to staging server via rsync
4. Clear WordPress cache

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
2. Build the pbc theme assets
3. Install production dependencies
4. Deploy to production server via rsync
5. Clear WordPress cache
6. Verify deployment

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

## What Gets Deployed

### Included:
- WordPress core files
- Themes (with built assets)
- Plugins
- Custom configurations

### Excluded:
- `.git/` and `.github/`
- `node_modules/`
- `wp-content/uploads/`
- `wp-content/cache/`
- `wp-config.php`
- Log files

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
- Ensure WP-CLI is installed on server
- Manually clear cache via WordPress admin
- Install WP-CLI:
  ```bash
  curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  ```

## Rollback Procedure

If deployment fails or issues occur:

1. **Automatic Backup**: Each production deployment creates a backup in `../backups/`

2. **Manual Rollback**:
   ```bash
   ssh user@production-server
   cd /path/to/wordpress
   tar -xzf ../backups/backup-YYYYMMDD-HHMMSS.tar.gz
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
