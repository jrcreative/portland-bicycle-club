# WordPress Deployment Guide for Beginners

**Welcome!** This guide will teach you how to deploy this WordPress site using GitHub Actions. Don't worry if you're new to deployment - we'll explain everything step by step.

## Table of Contents

1. [Understanding the Deployment Process](#understanding-the-deployment-process)
2. [Setting Up Your Local Development Environment](#setting-up-your-local-development-environment)
3. [How the Deployment Works](#how-the-deployment-works)
4. [Deploying to Staging](#deploying-to-staging)
5. [Deploying to Production](#deploying-to-production)
6. [Troubleshooting Common Issues](#troubleshooting-common-issues)
7. [Important Notes About Databases](#important-notes-about-databases)

---

## Understanding the Deployment Process

### What is Deployment?

Deployment is the process of moving your code from your local computer to a web server where people can see it. Think of it like publishing a book - you write it on your computer (local development), then send it to a publisher (deployment) so others can read it.

### What Gets Deployed?

This deployment process **ONLY deploys files**, such as:
- WordPress theme files (PHP, CSS, JavaScript)
- WordPress plugins
- WordPress core files
- Built/compiled assets (CSS and JavaScript)

### What Does NOT Get Deployed?

**Important:** The **database is NOT deployed**. This means:
- User accounts
- Posts and pages
- Settings and configurations
- Media uploads
- Any data stored in WordPress

Each environment (local, staging, production) has its **own separate database** that you must manage separately.

---

## Setting Up Your Local Development Environment

### Recommended Tool: LocalWP

We **strongly recommend** using [LocalWP](https://localwp.com/) for local WordPress development.

#### Why LocalWP?

- **Easy to use** - No need to manually configure Apache, MySQL, or PHP
- **One-click setup** - Creates a complete WordPress environment in minutes
- **Multiple sites** - Run multiple WordPress projects on your computer
- **Free** - No cost for the basic features you need
- **Cross-platform** - Works on Windows, Mac, and Linux

#### Installing LocalWP

1. **Download LocalWP**
   - Visit [https://localwp.com/](https://localwp.com/)
   - Click "Download" and choose your operating system
   - Install the application

2. **Create a New Site**
   - Open LocalWP
   - Click "Create a new site" (the **+** button)
   - Choose "Create a new site"
   - Enter a site name (e.g., "PWTC Local")
   - Choose "Preferred" environment (recommended settings)
   - Set up WordPress admin username and password
   - Click "Add Site"

3. **Import This Project**
   
   After creating your site, you'll need to replace the default WordPress files with this project:

   ```bash
   # Find where LocalWP created your site
   # On Mac: ~/Local Sites/your-site-name/app/public
   # On Windows: C:\Users\YourName\Local Sites\your-site-name\app\public
   
   # Navigate to your LocalWP site folder
   cd ~/Local\ Sites/pwtc/app/public
   
   # Remove the default WordPress files (if any)
   rm -rf *
   
   # Clone this repository (or copy your files here)
   git clone [your-repository-url] .
   ```

4. **Install Theme Dependencies**

   This theme uses build tools to compile CSS and JavaScript:

   ```bash
   # Navigate to the theme directory
   cd wp-content/themes/pbc
   
   # Install Node.js dependencies
   yarn install
   # OR if you prefer npm:
   npm install
   
   # Install PHP dependencies
   composer install
   
   # Build the theme assets
   yarn build
   # OR
   npm run build
   ```

5. **Start Your Local Site**
   - In LocalWP, click "Start site"
   - Click "Admin" to open WordPress admin panel
   - Click "Open site" to view your site in a browser

---

## How the Deployment Works

### Overview

This project uses **GitHub Actions** - an automation tool that runs whenever you push code to specific branches. Think of it as a robot that:

1. Takes your code from GitHub
2. Builds/compiles your theme files (CSS, JavaScript)
3. Packages everything up
4. Sends it to your web server
5. Tells WordPress to refresh its cache

### The Three Environments

| Environment | Purpose | How to Deploy | Risk Level |
|-------------|---------|---------------|------------|
| **Local** | Your computer - for development | LocalWP | No risk - only you see it |
| **Staging** | Testing server - preview changes | Push to `staging` branch | Low risk - safe for testing |
| **Production** | Live website - public sees this | Manual trigger from `main` branch | High risk - affects real users |

### Build Process Explained

When you deploy, GitHub Actions performs these steps:

```
1. Checkout Code
   ↓ (Gets your code from GitHub)
   
2. Install Node.js & PHP
   ↓ (Sets up the tools needed to build)
   
3. Build Theme Assets
   ↓ (Compiles SASS to CSS, bundles JavaScript)
   ↓ - Runs: yarn build:production
   ↓ - Creates optimized CSS and JS files
   
4. Install PHP Dependencies
   ↓ (Gets libraries needed for WordPress theme)
   ↓ - Runs: composer install --no-dev
   
5. Clean Up Development Files
   ↓ (Removes files not needed on server)
   ↓ - Deletes node_modules/
   ↓ - Deletes source files (SASS, uncompiled JS)
   ↓ - Deletes build configuration files
   
6. Deploy via rsync
   ↓ (Transfers ONLY production files to server)
   
7. Clear WordPress Cache
   ✓ (Ensures visitors see the latest version)
```

**Why build on GitHub instead of the server?**
- Faster deployments (only upload final files)
- Server doesn't need build tools installed
- More secure (fewer tools = fewer vulnerabilities)
- Consistent environment every time

---

## Deploying to Staging

Staging is your **testing environment** - a safe place to preview changes before they go live.

### Step-by-Step Process

#### 1. Make Your Changes Locally

```bash
# Make sure you're on the main branch
git checkout main

# Create a new feature branch (recommended)
git checkout -b feature/my-new-feature

# Make your changes to the code
# Test them in LocalWP
```

#### 2. Build and Test Locally

```bash
# Navigate to theme directory
cd wp-content/themes/pbc

# Build the theme
yarn build

# Check for errors in the terminal
# Test in your local browser
```

#### 3. Commit Your Changes

```bash
# Go back to project root
cd ../../..

# Check what files changed
git status

# Add your changes
git add .

# Commit with a clear message
git commit -m "Add new feature: describe what you did"
```

#### 4. Push to Staging

```bash
# Push your feature branch first
git push origin feature/my-new-feature

# Switch to staging branch
git checkout staging

# Merge your changes into staging
git merge feature/my-new-feature

# Push to trigger automatic deployment
git push origin staging
```

#### 5. Watch the Deployment

1. Go to your GitHub repository
2. Click the **Actions** tab
3. You'll see "Deploy to Staging" running
4. Click on it to watch the progress
5. Each step will show ✅ (success) or ❌ (failure)

#### 6. Verify on Staging

After deployment completes (usually 2-5 minutes):
- Visit your staging website URL
- Test your changes thoroughly
- Check for any errors or broken features

### What Triggers Staging Deployment?

**Automatic deployment happens when:**
```bash
git push origin staging
```

That's it! Any push to the `staging` branch triggers deployment.

---

## Deploying to Production

Production is your **live website** - real users see this. Be careful!

### Before You Deploy to Production

**Checklist:**
- [ ] Changes tested thoroughly on staging
- [ ] No errors or warnings on staging
- [ ] Team/client has approved the changes
- [ ] You've committed all changes to `main` branch
- [ ] You've had coffee (optional but recommended ☕)

### Step-by-Step Process

#### 1. Merge to Main Branch

```bash
# Make sure staging is working perfectly
# Then switch to main branch
git checkout main

# Pull latest changes
git pull origin main

# Merge staging into main
git merge staging

# Push to GitHub (doesn't deploy yet!)
git push origin main
```

#### 2. Manually Trigger Production Deployment

**You MUST manually trigger production deployments** - they don't happen automatically. This prevents accidents!

1. **Go to GitHub**
   - Open your repository in a browser
   - Click the **Actions** tab at the top

2. **Find the Production Workflow**
   - In the left sidebar, click **"Deploy to Production"**

3. **Run the Workflow**
   - Click the **"Run workflow"** button (top right)
   - A dropdown appears
   - **Important:** Type `deploy` in the confirmation field
   - Click the green **"Run workflow"** button

4. **Confirm the Branch**
   - Make sure it says "Branch: main"
   - Production deployments ONLY work from the `main` branch

#### 3. Watch the Deployment

The workflow will:
1. ✅ Verify you typed "deploy" correctly
2. ✅ Verify you're deploying from `main` branch
3. ✅ Create a backup on the production server
4. ✅ Build the theme
5. ✅ Deploy files to production server
6. ✅ Clear WordPress cache
7. ✅ Verify deployment succeeded

This takes about 3-7 minutes.

#### 4. Verify on Production

1. **Visit your live website**
2. **Test the changes you made**
3. **Check common pages:**
   - Homepage
   - Blog posts
   - Contact forms
   - Any pages you modified

4. **Check browser console for errors:**
   - Press `F12` in your browser
   - Click "Console" tab
   - Look for red error messages

---

## Troubleshooting Common Issues

### Problem: "Build Failed" Error

**Symptoms:**
- GitHub Actions shows ❌ on "Install and build" step
- Error mentions npm, yarn, or webpack

**Solution:**

```bash
# Test the build locally first
cd wp-content/themes/pbc
yarn build

# If it fails locally, check:
# 1. Are all dependencies installed?
yarn install

# 2. Is your package.json valid?
# 3. Are there syntax errors in your JS/SASS files?
```

**Common causes:**
- Syntax error in JavaScript or SASS file
- Missing dependency in package.json
- Wrong Node.js version (should be v20)

---

### Problem: "SSH Connection Failed"

**Symptoms:**
- Deployment fails at "Test SSH connection" step
- Error message about authentication or connection

**Solution:**

This is usually a server configuration issue. You'll need to:

1. **Verify GitHub Secrets are set correctly**
   - Go to repository Settings → Secrets and variables → Actions
   - Check these secrets exist:
     - `STAGING_SSH_PRIVATE_KEY` or `PRODUCTION_SSH_PRIVATE_KEY`
     - `STAGING_SSH_USER` or `PRODUCTION_SSH_USER`
     - `STAGING_SERVER_HOST` or `PRODUCTION_SERVER_HOST`
     - `STAGING_DEPLOY_PATH` or `PRODUCTION_DEPLOY_PATH`

2. **Contact your system administrator**
   - They may need to verify the SSH key is properly installed on the server

---

### Problem: "Permission Denied" During Deployment

**Symptoms:**
- rsync step fails
- Error about permissions

**Solution:**

The deployment user needs write permissions on the server:

```bash
# Server administrator needs to run:
chown -R deployment-user:deployment-user /path/to/wordpress
chmod -R 755 /path/to/wordpress
```

---

### Problem: Changes Don't Appear on Site

**Symptoms:**
- Deployment succeeded
- But you don't see your changes on the website

**Solutions:**

1. **Clear Browser Cache**
   ```
   - Chrome/Edge: Ctrl+Shift+R (Windows) or Cmd+Shift+R (Mac)
   - Firefox: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
   ```

2. **Clear WordPress Cache**
   - The deployment tries to do this automatically
   - But you can also clear it manually:
     - Log into WordPress admin
     - Go to your caching plugin settings
     - Click "Clear Cache"

3. **Check if Files Actually Deployed**
   - Look at the GitHub Actions log
   - Check the "Deploy via rsync" step
   - Should show files being transferred

4. **Verify Correct Branch**
   - Staging deploys from `staging` branch
   - Production deploys from `main` branch
   - Did you push to the right branch?

---

### Problem: "No Such File or Directory"

**Symptoms:**
- Error mentions a file path doesn't exist
- rsync fails

**Solution:**

The deployment path might be incorrect:

1. **Check the GitHub Secret**
   - Settings → Secrets → `STAGING_DEPLOY_PATH` or `PRODUCTION_DEPLOY_PATH`
   - Should be the FULL path like: `/home/username/public_html`

2. **Verify Path on Server**
   ```bash
   ssh user@yourserver.com
   pwd  # Shows current directory
   ls -la  # Lists files
   ```

---

### Problem: Plugin Error "Failed to open stream" or "vendor" Directory Missing

**Symptoms:**
- Error like: `require_once(...vendor/...): Failed to open stream: No such file or directory`
- Plugins fail after deployment
- Error mentions WooCommerce, memberships, or other plugins

**What Happened:**

Many WordPress plugins use **Composer** (a PHP dependency manager) and have a `vendor/` directory with required libraries. The deployment was accidentally excluding these files.

**Solution:**

This has been fixed in the deployment configuration! The exclusions now only apply to **theme** build files, not plugin dependencies.

**If you're still seeing this error after the fix:**

1. **Redeploy to restore missing files:**
   ```bash
   git push origin staging  # For staging
   # Or trigger production deployment from GitHub Actions
   ```

2. **If the plugin vendor directory is still missing locally:**
   ```bash
   # Navigate to the plugin directory
   cd wp-content/plugins/plugin-name
   
   # Install its dependencies
   composer install
   ```

3. **Check if the plugin is in your repository:**
   - Some plugins shouldn't be in Git (if installed via WordPress admin)
   - Check your `.gitignore` file
   - Plugins managed through WordPress should be installed directly on staging/production

**Prevention:**
- The deployment now correctly includes plugin `vendor/` directories
- Only theme build files (node_modules, source assets) are excluded
- Plugin dependencies will deploy correctly

---

## Important Notes About Databases

### Database Are NOT Deployed

This is **critical** to understand:

```
Local Database  ──────────  Staging Database  ──────────  Production Database
    ↓                            ↓                             ↓
  Separate                    Separate                      Separate
  
YOUR CODE/FILES are deployed →→→
But databases stay separate!
```

### What This Means

**When you deploy to staging:**
- ✅ Your code changes appear
- ✅ Theme files update
- ✅ Plugin files update
- ❌ Staging still has its own database
- ❌ Posts/pages from local won't appear
- ❌ Users from local won't exist

**When you deploy to production:**
- ✅ Your code changes go live
- ❌ Production keeps its own database
- ❌ All user data stays on production
- ❌ All posts/pages stay on production

### How to Sync Databases (If Needed)

**Syncing databases is a separate process** and should be done carefully!

#### Option 1: Using LocalWP (Easiest)

LocalWP has built-in tools to export/import databases, but **be very careful** with production!

#### Option 2: Using WP-CLI

```bash
# Export from production (creates a .sql file)
ssh user@production-server
cd /path/to/wordpress
wp db export production-backup.sql

# Download it
scp user@production-server:/path/to/production-backup.sql ~/Desktop/

# Import to local (CAREFUL - this overwrites your local database!)
# In LocalWP, right-click your site → Open Site SSH
wp db import ~/Desktop/production-backup.sql
```

#### Option 3: Using WordPress Plugins

- **WP Migrate DB** - Good for pushing/pulling databases
- **All-in-One WP Migration** - User-friendly interface

**⚠️ WARNING:** Never overwrite your production database unless you know exactly what you're doing! Always create backups first.

### Recommended Database Workflow

1. **Develop locally** with test data
2. **Deploy code** to staging/production
3. **If you need real data locally:**
   - Pull production database to local
   - Work with a copy, never push back to production
4. **Content changes** (posts, pages) should be made directly on production through WordPress admin

---

## Quick Reference Commands

### Local Development

```bash
# Start LocalWP site
# (Use LocalWP app interface)

# Build theme
cd wp-content/themes/pbc
yarn build

# Watch for changes while developing
yarn start
```

### Git Commands

```bash
# Check current branch
git branch

# Create new feature branch
git checkout -b feature/my-feature

# Check what files changed
git status

# Add all changes
git add .

# Commit changes
git commit -m "Description of changes"

# Push to GitHub
git push origin branch-name

# Switch branches
git checkout branch-name
```

### Deployment Commands

```bash
# Deploy to staging (automatic)
git checkout staging
git merge your-feature-branch
git push origin staging

# Deploy to production (manual trigger required)
git checkout main
git merge staging
git push origin main
# Then go to GitHub → Actions → Deploy to Production → Run workflow
```

---

## Getting Help

### Resources

1. **LocalWP Documentation**
   - [https://localwp.com/help-docs/](https://localwp.com/help-docs/)

2. **GitHub Actions Documentation**
   - [https://docs.github.com/en/actions](https://docs.github.com/en/actions)

3. **WordPress Documentation**
   - [https://wordpress.org/documentation/](https://wordpress.org/documentation/)

### When Things Go Wrong

1. **Check GitHub Actions logs**
   - Click on the failed workflow
   - Read the error messages (in red)
   - Google the error if unclear

2. **Test locally first**
   - If it doesn't work on your computer, it won't work on the server
   - Always build and test in LocalWP before deploying

3. **Ask for help**
   - Include the error message
   - Include what you were trying to do
   - Include what you've already tried

---

## Best Practices for Junior Developers

### Do's ✅

- **Always test locally first** in LocalWP
- **Use feature branches** for new work
- **Deploy to staging** before production
- **Write clear commit messages** describing what you changed
- **Ask questions** when unsure
- **Make small, frequent commits** rather than huge ones

### Don'ts ❌

- **Don't push directly to `main` branch** (use feature branches)
- **Don't skip testing on staging** 
- **Don't deploy on Friday afternoon** (in case something breaks)
- **Don't panic if something breaks** (we have backups)
- **Don't delete GitHub Actions workflows** without understanding them
- **Don't commit sensitive data** (passwords, API keys, etc.)

---

## Summary

**The deployment process in simple terms:**

1. **Develop locally** using LocalWP
2. **Build and test** your changes
3. **Commit to Git** with clear messages  
4. **Push to staging** to test on a real server
5. **Verify everything works** on staging
6. **Merge to main** branch
7. **Manually trigger production deployment** on GitHub
8. **Verify on live site**

**Remember:**
- Deployments only move **files**, not **databases**
- Staging deploys automatically, production requires manual trigger
- Always test before deploying to production
- GitHub Actions does the heavy lifting for you
- When in doubt, ask for help!

---

## Glossary

**Terms you'll encounter:**

- **rsync**: A tool that syncs files between computers efficiently
- **SSH**: Secure Shell - encrypted way to connect to servers
- **GitHub Actions**: Automation that runs tasks when you push code
- **Staging**: A test server that mimics production
- **Production**: The live website that users see
- **Build**: Process of compiling/optimizing code for the web
- **Deploy**: Moving code from development to a server
- **Commit**: Saving a snapshot of your code changes
- **Branch**: A separate version of your code
- **Merge**: Combining code from one branch into another
- **LocalWP**: Local development environment for WordPress

---

**Good luck with your deployments! 🚀**
