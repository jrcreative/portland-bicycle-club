# GitHub Actions Workflows

This directory contains GitHub Actions workflows for automating deployments and testing.

## Workflows

### 🚀 deploy-staging.yml
**Trigger:** Automatic on push to `staging` branch

Deploys code to the staging server automatically when changes are pushed to the staging branch.

**What it does:**
- Builds pbc theme assets (npm run build:production)
- Installs PHP dependencies (composer)
- Deploys via rsync to staging server
- Clears WordPress cache

### 🚀 deploy-production.yml
**Trigger:** Manual workflow dispatch from `main` branch

Deploys code to production server with manual confirmation required.

**What it does:**
- Creates backup on production server
- Builds pbc theme assets
- Installs PHP dependencies
- Deploys via rsync to production server
- Clears WordPress cache
- Verifies deployment

**Safety features:**
- Requires typing "deploy" to confirm
- Must be run from main branch
- Creates backup before deployment
- Uses GitHub environment protection

### 🧪 test-build.yml
**Trigger:** Automatic on pull requests to `main` or `staging`

Tests that the theme builds successfully before merging.

**What it does:**
- Installs dependencies
- Runs production build
- Reports build status on PR

## Quick Start

1. Follow setup instructions in `SETUP.md`
2. Configure GitHub Secrets
3. Push to staging branch for automatic deployment
4. Use Actions UI for production deployment

## Documentation

- **Setup Guide:** `.github/SETUP.md`
- **Deployment Guide:** `DEPLOYMENT.md` (root)

## File Structure

```
.github/
├── workflows/
│   ├── deploy-staging.yml      # Auto deploy to staging
│   ├── deploy-production.yml   # Manual deploy to production
│   └── test-build.yml          # Test builds on PRs
├── SETUP.md                    # Initial setup instructions
└── README.md                   # This file
```

## Required Secrets

Configure these in GitHub repository settings:

**Staging:**
- `STAGING_SSH_PRIVATE_KEY`
- `STAGING_SSH_USER`
- `STAGING_SERVER_HOST`
- `STAGING_DEPLOY_PATH`

**Production:**
- `PRODUCTION_SSH_PRIVATE_KEY`
- `PRODUCTION_SSH_USER`
- `PRODUCTION_SERVER_HOST`
- `PRODUCTION_DEPLOY_PATH`

## Branch Strategy

```
main (production) ←── merge ←── staging ←── merge ←── feature-branch
     ↓                           ↓
  (manual)                   (automatic)
     ↓                           ↓
  production                  staging
   server                     server
```

## Support

For issues with GitHub Actions, check:
1. Workflow logs in Actions tab
2. GitHub Actions status page
3. Server SSH access and permissions
4. Secret configuration

For deployment issues, see `DEPLOYMENT.md`.
