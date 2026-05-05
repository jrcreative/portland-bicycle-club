# Build and Deployment Process

This document explains how the build and deployment process works for the Portland Bicycle Club WordPress site.

## Architecture Overview

```
┌─────────────────┐      ┌──────────────────┐      ┌─────────────────┐
│  Developer      │      │  GitHub Actions  │      │  Server         │
│  Local Machine  │      │  Build Runner    │      │  (No Node.js)   │
└─────────────────┘      └──────────────────┘      └─────────────────┘
         │                        │                         │
         │  1. Push Code          │                         │
         │───────────────────────>│                         │
         │                        │                         │
         │                        │  2. Build Assets        │
         │                        │    - npm install        │
         │                        │    - webpack build      │
         │                        │    - composer install   │
         │                        │                         │
         │                        │  3. Clean Up            │
         │                        │    - Remove node_modules│
         │                        │    - Remove src files   │
         │                        │    - Remove configs     │
         │                        │                         │
         │                        │  4. Deploy Built Files  │
         │                        │────────────────────────>│
         │                        │                         │
         │                        │                         │  5. Website
         │                        │                         │     Serves
         │                        │                         │     Content
```

## Build Process (GitHub Actions)

### Step 1: Checkout Code
```yaml
- uses: actions/checkout@v4
```
Gets the latest code from the repository.

### Step 2: Setup Build Environment
```yaml
- Setup Node.js 18
- Setup PHP 8.1
- Setup Composer
```
Configures the build environment with necessary tools.

### Step 3: Install Dependencies
```bash
cd wp-content/themes/pbc
npm ci --legacy-peer-deps          # Install JS dependencies
npm run build:production           # Build CSS/JS assets
composer install --no-dev          # Install PHP dependencies
```

**What gets built:**
- `resources/assets/scripts/**/*.js` → `dist/scripts/main.js`
- `resources/assets/styles/**/*.scss` → `dist/styles/main.css`
- Images optimized and copied to `dist/`

### Step 4: Clean Development Files
```bash
# Remove node_modules (400+ MB)
find wp-content/themes -type d -name "node_modules" -exec rm -rf {} +

# Remove source files
rm -rf resources/assets/build/
rm -rf resources/assets/config/

# Remove build configuration files
rm -f package.json package-lock.json
rm -f webpack.*.js gulpfile.js
rm -f .eslintrc.js .stylelintrc.js
rm -f composer.json composer.lock
```

**Before cleanup:**
```
pbc/
├── dist/                    # Built assets (KEEP)
├── vendor/                  # PHP dependencies (KEEP)
├── node_modules/            # 400MB (DELETE)
├── resources/
│   └── assets/
│       ├── build/          # Build configs (DELETE)
│       ├── config/         # Build configs (DELETE)
│       ├── scripts/        # Source JS (DELETE)
│       └── styles/         # Source SCSS (DELETE)
├── package.json            # (DELETE)
└── webpack.config.js       # (DELETE)
```

**After cleanup:**
```
pbc/
├── dist/                   # Built assets ✓
│   ├── scripts/main.js    # Minified JS
│   └── styles/main.css    # Compiled CSS
├── vendor/                # PHP dependencies ✓
├── app/                   # Theme PHP files ✓
├── views/                 # Twig templates ✓
└── functions.php          # Theme functions ✓
```

### Step 5: Deploy via rsync
```bash
rsync -avz --delete \
  --exclude='node_modules/' \
  --exclude='resources/assets/' \
  --exclude='package.json' \
  --exclude='webpack.*.js' \
  ./ user@server:/path/to/wordpress/
```

**What gets uploaded:**
- ✅ Built CSS/JS files (`dist/`)
- ✅ PHP files (theme templates, functions)
- ✅ Vendor dependencies (composer packages)
- ❌ No node_modules
- ❌ No source SCSS/JS files
- ❌ No build configuration files

## File Size Comparison

### Before Optimization (Old Method - Build on Server)
```
Total upload size: ~450 MB
- node_modules/           400 MB
- vendor/                  30 MB
- source files             15 MB
- built files               5 MB
```

### After Optimization (New Method - Build on GitHub)
```
Total upload size: ~40 MB
- vendor/                  30 MB
- built files               5 MB
- theme PHP files           5 MB
```

**Result:** ~90% reduction in upload size and deployment time!

## Server Requirements

### What the Server NEEDS:
- ✅ PHP 7.4+ (for WordPress)
- ✅ rsync (for file transfer)
- ✅ SSH access with key auth

### What the Server DOES NOT NEED:
- ❌ Node.js
- ❌ npm
- ❌ webpack
- ❌ Build tools
- ❌ Git

## Deployment Triggers

### Staging (Automatic)
```
Push to 'staging' branch → GitHub Actions builds → Auto-deploy to staging
```

### Production (Manual)
```
Manual trigger from GitHub UI → Confirmation required → Build → Deploy to production
```

## Benefits of This Approach

### 1. Server Efficiency
- Server doesn't waste CPU on builds
- No build dependencies needed
- Faster PHP response (less disk I/O)

### 2. Security
- No development tools on production
- Smaller attack surface
- No exposed source files

### 3. Speed
- 90% smaller file transfers
- Faster deployment
- Less bandwidth usage

### 4. Consistency
- Builds always use same environment
- No "works on my machine" issues
- Reproducible builds

### 5. Reliability
- Server can't run out of memory during builds
- No disk space issues from node_modules
- Simpler server maintenance

## What Happens on Each Deploy

### Staging Deploy (Automatic)
```
1. Developer pushes to staging branch
2. GitHub Actions triggered automatically
3. Code checked out on GitHub runner
4. Node.js dependencies installed (~2 min)
5. Assets built (webpack) (~1 min)
6. PHP dependencies installed (~30 sec)
7. Development files removed (~5 sec)
8. Files synced to staging server (~30 sec)
9. WordPress cache cleared
Total time: ~4 minutes
```

### Production Deploy (Manual)
```
1. Developer triggers workflow from GitHub UI
2. Types "deploy" to confirm
3. System creates backup on server (~30 sec)
4. GitHub Actions runs build process (~4 min)
5. Files synced to production server (~30 sec)
6. WordPress cache cleared
7. Deployment verified
Total time: ~5 minutes
```

## Troubleshooting

### Build fails
**Issue:** npm install or webpack build fails

**Solution:**
- Check Node.js version compatibility
- Review build logs in GitHub Actions
- Verify package.json is committed
- Check for syntax errors in source files

### Deploy fails
**Issue:** rsync cannot connect to server

**Solution:**
- Verify SSH keys in GitHub Secrets
- Check server firewall settings
- Ensure rsync is installed on server
- Test SSH connection manually

### Missing files on server
**Issue:** Built files not appearing on server

**Solution:**
- Check rsync exclude patterns
- Verify build process created dist/ folder
- Check server path is correct
- Review deployment logs for errors

## File Tracking

### Files in Git Repository
```
✅ Source files (resources/assets/scripts/, resources/assets/styles/)
✅ Theme PHP files (app/, views/, functions.php)
✅ Build configuration (package.json, webpack.config.js)
❌ Built files (dist/) - generated during build
❌ Dependencies (node_modules/, vendor/) - installed during build
```

### Files on Server
```
✅ Built files (dist/)
✅ PHP dependencies (vendor/)
✅ Theme PHP files
❌ Source files (resources/assets/)
❌ Build configs (package.json, webpack.config.js)
❌ Node modules
```

## Summary

This deployment strategy separates **build** (GitHub) from **runtime** (server):

- **GitHub Actions**: Handles all building and compilation
- **Server**: Only serves the final built application

This results in faster, more secure, and more reliable deployments.
