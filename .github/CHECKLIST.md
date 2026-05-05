# Deployment Setup Checklist

Use this checklist to ensure all steps are completed for GitHub Actions deployment.

## ☐ Pre-Setup

- [ ] Verify you have SSH access to staging server
- [ ] Verify you have SSH access to production server
- [ ] Verify rsync is installed on both servers
- [ ] Verify you have admin access to GitHub repository
- [ ] Review `SETUP.md` documentation

## ☐ SSH Key Generation

- [ ] Generate SSH key pair: `ssh-keygen -t ed25519 -C "github-actions-pwtc-deploy" -f ~/.ssh/github_deploy_pwtc`
- [ ] Leave passphrase empty (press Enter)
- [ ] Save location of private key: `~/.ssh/github_deploy_pwtc`
- [ ] Save location of public key: `~/.ssh/github_deploy_pwtc.pub`

## ☐ Server Setup - Staging

- [ ] Copy public key to staging server: `ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub user@staging-server`
- [ ] Test connection: `ssh -i ~/.ssh/github_deploy_pwtc user@staging-server "ls -la"`
- [ ] Verify WordPress directory path: `/home/user/public_html` (update as needed)
- [ ] Check rsync is installed: `ssh user@staging-server "which rsync"`
- [ ] Verify directory permissions allow writing

## ☐ Server Setup - Production

- [ ] Copy public key to production server: `ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub user@production-server`
- [ ] Test connection: `ssh -i ~/.ssh/github_deploy_pwtc user@production-server "ls -la"`
- [ ] Verify WordPress directory path: `/home/user/public_html` (update as needed)
- [ ] Check rsync is installed: `ssh user@production-server "which rsync"`
- [ ] Create backups directory: `ssh user@production-server "mkdir -p ~/backups"`
- [ ] Verify directory permissions allow writing

## ☐ GitHub Secrets - Staging

Go to: https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions

- [ ] Add `STAGING_SSH_PRIVATE_KEY` (content from `cat ~/.ssh/github_deploy_pwtc`)
- [ ] Add `STAGING_SSH_USER` (your SSH username)
- [ ] Add `STAGING_SERVER_HOST` (e.g., `staging.example.com`)
- [ ] Add `STAGING_DEPLOY_PATH` (e.g., `/home/user/public_html`)

## ☐ GitHub Secrets - Production

- [ ] Add `PRODUCTION_SSH_PRIVATE_KEY` (content from `cat ~/.ssh/github_deploy_pwtc`)
- [ ] Add `PRODUCTION_SSH_USER` (your SSH username)
- [ ] Add `PRODUCTION_SERVER_HOST` (e.g., `portlandbicycleclub.com`)
- [ ] Add `PRODUCTION_DEPLOY_PATH` (e.g., `/home/user/public_html`)

## ☐ Git Repository Setup

- [ ] Create staging branch: `git checkout -b staging`
- [ ] Push staging branch: `git push -u origin staging`
- [ ] Return to main: `git checkout main`
- [ ] Commit GitHub Actions files: `git add .github/ DEPLOYMENT.md .gitignore`
- [ ] Push changes: `git push origin main`
- [ ] Merge to staging: `git checkout staging && git merge main && git push`

## ☐ Test Staging Deployment

- [ ] Go to: https://github.com/jrcreative/portland-bicycle-club/actions
- [ ] Make test commit to staging branch
- [ ] Watch workflow run and complete successfully
- [ ] Verify files deployed to staging server
- [ ] Check staging website is working

## ☐ Test Production Deployment (Optional)

- [ ] Go to Actions → "Deploy to Production"
- [ ] Click "Run workflow"
- [ ] Select branch: `main`
- [ ] Type `deploy` in confirmation
- [ ] Watch workflow run
- [ ] Verify backup was created
- [ ] Verify files deployed to production
- [ ] Check production website is working

## ☐ Optional: Environment Protection

- [ ] Go to Settings → Environments
- [ ] Create `production` environment
- [ ] Add required reviewers
- [ ] Set deployment branch rules
- [ ] Save protection rules

## ☐ Documentation

- [ ] Share `DEPLOYMENT.md` with team
- [ ] Document server credentials (securely)
- [ ] Add deployment process to team wiki
- [ ] Schedule key rotation reminder (every 6 months)

## ☐ Post-Setup Validation

- [ ] Staging deploys automatically on push to staging branch
- [ ] Production requires manual trigger and confirmation
- [ ] Build tests run on pull requests
- [ ] Backups are created before production deployments
- [ ] WordPress cache is cleared after deployments
- [ ] Team members understand deployment process

## Troubleshooting

If any step fails, refer to:
- `SETUP.md` - Detailed setup instructions
- `DEPLOYMENT.md` - Deployment guide and troubleshooting
- `.github/README.md` - Workflow documentation

## Server Information Template

Fill this out and store securely (DO NOT commit to repository):

### Staging Server
- **Host:** _________________
- **User:** _________________
- **Path:** _________________
- **SSH Key:** `~/.ssh/github_deploy_pwtc`

### Production Server
- **Host:** _________________
- **User:** _________________
- **Path:** _________________
- **SSH Key:** `~/.ssh/github_deploy_pwtc`

## Completion

- [ ] All items checked
- [ ] First staging deployment successful
- [ ] First production deployment successful
- [ ] Team trained on deployment process
- [ ] Documentation distributed

**Setup completed by:** _________________ **Date:** _________
