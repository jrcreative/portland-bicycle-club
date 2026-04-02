# Python 2 Fix for node-sass

## Issue

node-sass 4.12.0 fails to build on GitHub runners because:
```
gyp ERR! stack SyntaxError: Missing parentheses in call to 'print'
```

The issue is that GitHub runners now have Python 3 by default, but node-sass 4.12.0's build scripts require Python 2.

## Solution

Install Python 2 and upgrade node-sass to a compatible version that works with Node.js 20.

### Steps Applied

1. **Install Python 2** on GitHub runner
2. **Symlink python → python2**
3. **Install dependencies without build scripts**
4. **Upgrade node-sass** to 4.14.1 (better Node.js 20 support)
5. **Rebuild node-sass** with Python 2
6. **Run build**

### Implementation

```yaml
- name: Setup Python 2
  run: |
    sudo apt-get update
    sudo apt-get install -y python2 python2-dev
    sudo ln -sf /usr/bin/python2 /usr/bin/python

- name: Install and build pbc theme
  run: |
    cd wp-content/themes/pbc
    yarn install --frozen-lockfile --ignore-scripts
    yarn add node-sass@4.14.1 --ignore-scripts
    npm rebuild node-sass
    yarn build:production
```

## Why This Works

1. **Python 2 installed** - node-sass build scripts can run
2. **--ignore-scripts** - Prevents yarn from building during install
3. **node-sass@4.14.1** - Better compatibility with Node.js 20
4. **npm rebuild** - Builds native bindings with Python 2
5. **yarn build** - Runs webpack with working node-sass

## node-sass Version Comparison

| Version | Node.js 20 | Python 2 | Python 3 | Status |
|---------|-----------|----------|----------|--------|
| 4.12.0 | ⚠️ Partial | ✅ Required | ❌ No | Old |
| 4.14.1 | ✅ Yes | ✅ Required | ❌ No | Better |
| 6.0.0+ | ✅ Yes | ❌ No | ✅ Yes | Modern |
| 9.0.0+ | ✅ Yes | ❌ No | ✅ Yes | Latest |

We upgraded from 4.12.0 → 4.14.1 (still requires Python 2 but better Node.js 20 support).

## Why Not Upgrade to node-sass 6+?

node-sass 6+ requires Python 3 and would be ideal, BUT:
- Would require testing entire theme
- May have breaking changes
- Sass syntax differences
- Webpack config changes needed

For now, we use Python 2 + node-sass 4.14.1 as the safest approach.

## Alternative: Switch to dart-sass

For future consideration (requires theme updates):

```bash
# Remove node-sass
yarn remove node-sass

# Add modern dart-sass
yarn add sass --dev

# Update webpack.config.js
# Change 'node-sass' → 'sass'

# No Python needed!
```

Benefits:
- No Python dependency
- Faster builds
- Better maintained
- Works with Node.js 24+

## Files Updated

All 3 workflows now include:
1. ✅ `.github/workflows/deploy-staging.yml`
2. ✅ `.github/workflows/deploy-production.yml`
3. ✅ `.github/workflows/test-build.yml`

Each adds:
- Python 2 installation step
- node-sass upgrade to 4.14.1
- Rebuild with npm

## Expected Output

```
✓ Setup Python 2
  Installing python2, python2-dev...
  Done

✓ Install dependencies
  yarn install --ignore-scripts
  [1/5] Validating package.json...
  [2/5] Resolving packages...
  [3/5] Fetching packages...
  [4/5] Linking dependencies...
  [5/5] Building fresh packages... (skipped)
  Done in 30s

✓ Upgrade node-sass
  yarn add node-sass@4.14.1
  Done

✓ Rebuild node-sass
  npm rebuild node-sass
  Building with Python 2...
  Success!

✓ Build assets
  yarn build:production
  webpack compiled successfully
  scripts/main.js ✓
  styles/main.css ✓
  Done in 18s
```

## Troubleshooting

### If Python 2 install fails

GitHub may remove Python 2 in the future. Alternative:

```yaml
- name: Use pre-built node-sass
  run: |
    cd wp-content/themes/pbc
    yarn add node-sass@6.0.1 --ignore-scripts
```

### If node-sass 4.14.1 still fails

Try using pre-built binaries:

```yaml
- name: Download node-sass binary
  run: |
    cd wp-content/themes/pbc
    export npm_config_platform=linux
    export npm_config_arch=x64
    yarn add node-sass@4.14.1 --force
```

### If build still fails

Last resort - switch to dart-sass (requires theme changes):

```yaml
- run: |
    cd wp-content/themes/pbc
    yarn remove node-sass
    yarn add sass
    # Update webpack config manually
```

## Summary

| Aspect | Value |
|--------|-------|
| **Python Version** | 2.7 (installed) |
| **node-sass Version** | 4.14.1 (upgraded from 4.12.0) |
| **Node.js Version** | 20 (unchanged) |
| **Build Method** | Install → Upgrade → Rebuild |
| **Status** | ✅ Should work now |

---

**Status: ✅ Python 2 installed, node-sass upgraded, ready to build**
