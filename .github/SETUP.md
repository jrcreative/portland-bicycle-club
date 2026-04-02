# GitHub Actions Deployment Setup

Follow these steps to complete the deployment setup for the Portland Bicycle Club WordPress site.

## Step 1: Generate SSH Keys

Generate a dedicated SSH key pair for GitHub Actions deployments:

```bash
# Generate ED25519 key (recommended)
ssh-keygen -t ed25519 -C "github-actions-pwtc-deploy" -f ~/.ssh/github_deploy_pwtc

# When prompted for passphrase, press Enter (no passphrase)
```

## Step 2: Add Public Key to Servers

Copy the public key to both staging and production servers:

```bash
# View your public key
cat ~/.ssh/github_deploy_pwtc.pub

# Copy to staging server (replace with your actual details)
ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub username@staging-server.com

# Copy to production server (replace with your actual details)
ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub username@production-server.com
```

## Step 3: Get Private Key for GitHub

Display the private key (you'll need to copy this to GitHub):

```bash
cat ~/.ssh/github_deploy_pwtc
```

**Important:** Copy the ENTIRE output, including the `-----BEGIN OPENSSH PRIVATE KEY-----` and `-----END OPENSSH PRIVATE KEY-----` lines.

## Step 4: Add Secrets to GitHub

1. Go to your GitHub repository: https://github.com/jrcreative/portland-bicycle-club

2. Navigate to: **Settings** → **Secrets and variables** → **Actions**

3. Click **"New repository secret"** and add each of these:

### Staging Secrets

| Secret Name | Value | Example |
|------------|-------|---------|
| `STAGING_SSH_PRIVATE_KEY` | Private key from Step 3 | (entire key content) |
| `STAGING_SSH_USER` | SSH username | `pwtcuser` |
| `STAGING_SERVER_HOST` | Server hostname or IP | `staging.portlandbicycleclub.com` |
| `STAGING_DEPLOY_PATH` | Full path to WordPress | `/home/pwtcuser/public_html` |

### Production Secrets

| Secret Name | Value | Example |
|------------|-------|---------|
| `PRODUCTION_SSH_PRIVATE_KEY` | Private key from Step 3 | (entire key content) |
| `PRODUCTION_SSH_USER` | SSH username | `pwtcuser` |
| `PRODUCTION_SERVER_HOST` | Server hostname or IP | `portlandbicycleclub.com` |
| `PRODUCTION_DEPLOY_PATH` | Full path to WordPress | `/home/pwtcuser/public_html` |

## Step 5: Test SSH Connection

Before pushing to GitHub, test that your SSH keys work:

```bash
# Test staging connection
ssh -i ~/.ssh/github_deploy_pwtc username@staging-server.com "ls -la"

# Test production connection
ssh -i ~/.ssh/github_deploy_pwtc username@production-server.com "ls -la"
```

Both should connect without asking for a password.

## Step 6: Create Staging Branch

Create and push the staging branch:

```bash
# Make sure you're on main and up to date
git checkout main
git pull origin main

# Create staging branch
git checkout -b staging
git push -u origin staging

# Return to main
git checkout main
```

## Step 7: Commit GitHub Actions Files

Commit the new workflow files to your repository:

```bash
# Add the new files
git add .github/ DEPLOYMENT.md .gitignore

# Commit
git commit -m "Add GitHub Actions deployment workflows

- Add staging auto-deploy workflow
- Add production manual deploy workflow
- Add test build workflow for PRs
- Update .gitignore for GitHub Actions
- Add deployment documentation"

# Push to main
git push origin main

# Also push to staging
git checkout staging
git merge main
git push origin staging
git checkout main
```

## Step 8: Verify Setup

1. Go to GitHub Actions: https://github.com/jrcreative/portland-bicycle-club/actions

2. You should see the workflows listed:
   - ✅ Deploy to Staging
   - ✅ Deploy to Production
   - ✅ Test Build

3. Make a test commit to staging branch to trigger a deployment:
   ```bash
   git checkout staging
   echo "# Test deployment" >> test.txt
   git add test.txt
   git commit -m "Test staging deployment"
   git push origin staging
   ```

4. Watch the workflow run in GitHub Actions

## Step 9: Test Production Deployment

Once staging deployment works:

1. Go to: https://github.com/jrcreative/portland-bicycle-club/actions
2. Select **"Deploy to Production"**
3. Click **"Run workflow"**
4. Select branch: `main`
5. Type `deploy` in the confirmation box
6. Click **"Run workflow"**

## Troubleshooting

### "Permission denied (publickey)"
- Verify public key is on the server: `cat ~/.ssh/authorized_keys`
- Check SSH key permissions: `chmod 600 ~/.ssh/github_deploy_pwtc`
- Test connection manually: `ssh -i ~/.ssh/github_deploy_pwtc user@server`

### "No such file or directory"
- Verify `STAGING_DEPLOY_PATH` and `PRODUCTION_DEPLOY_PATH` are correct
- SSH into server and check: `ls -la /home/user/public_html`

### Build fails
- Check that Node.js 18 is compatible with theme
- Verify `package.json` exists in `wp-content/themes/pbc/`
- Check build script exists: `npm run build:production`

### rsync errors
- Ensure rsync is installed on server: `which rsync`
- Check file permissions on target directory

## Next Steps

1. ✅ Set up GitHub Secrets
2. ✅ Create staging branch
3. ✅ Test staging deployment
4. ✅ Test production deployment
5. ✅ Document server details for team
6. ✅ Set up environment protection rules (optional)

## Optional: Environment Protection

Add required reviewers for production deployments:

1. Go to: **Settings** → **Environments**
2. Click **"New environment"** → Name it `production`
3. Check **"Required reviewers"**
4. Add team members who must approve deployments
5. Save protection rules

This adds an extra approval step before production deployments.

## Support

- Review the main deployment guide: `DEPLOYMENT.md`
- Check GitHub Actions documentation: https://docs.github.com/en/actions
- Contact your system administrator for server access issues
