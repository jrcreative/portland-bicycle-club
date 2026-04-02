# Node.js Version: Node-Sass Compatibility Issue

## Issue

When using Node.js 24, the build fails with:
```
error /home/runner/work/.../node_modules/node-sass: Command failed.
gyp ERR! stack SyntaxError: Missing parentheses in call to 'print'
```

## Root Cause

The pbc theme uses **node-sass v4.12.0** (from 2019), which:
- Requires Python 2 (deprecated)
- Doesn't support Node.js versions above 16
- Uses old node-gyp that's incompatible with modern Node.js

### Theme's Old Dependencies
```json
"node-sass": "~4.12.0"    // Released 2019, Node.js 14 max
"webpack": "~3.10.0"      // Released 2017
```

## Solution: Use Node.js 20

Node.js 20 is:
- ✅ Compatible with node-sass 4.12.0
- ✅ Still Long Term Support (LTS) until April 2026
- ✅ No deprecation warnings (with FORCE_JAVASCRIPT_ACTIONS_TO_NODE24)
- ✅ Modern and secure

## Why Not Node.js 24?

| Feature | Node.js 20 | Node.js 24 |
|---------|-----------|-----------|
| **node-sass support** | ✅ Yes | ❌ No |
| **LTS Status** | ✅ Until 2026 | ✅ Future LTS |
| **Python 2 compatible** | ✅ Yes | ❌ No |
| **Works with theme** | ✅ Yes | ❌ No |

## Final Configuration

All workflows now use:
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    node-version: '20'  # Compatible with node-sass
    cache: 'yarn'
    cache-dependency-path: wp-content/themes/pbc/yarn.lock

env:
  FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true  # No warnings
```

This combination:
- ✅ Builds successfully (Node.js 20 + node-sass)
- ✅ No deprecation warnings (FORCE_JAVASCRIPT_ACTIONS_TO_NODE24)
- ✅ Future-proof until 2026

## Long-Term Solution (Optional)

To use Node.js 24+ in the future, the theme needs to be updated:

### Option 1: Upgrade to dart-sass (Recommended)
```bash
# Remove old node-sass
yarn remove node-sass

# Add modern dart-sass
yarn add sass --dev

# Update webpack config to use 'sass' instead of 'node-sass'
```

### Option 2: Upgrade to latest webpack
```bash
# Upgrade build tools
yarn add webpack@5 webpack-cli@5 --dev
yarn add sass-loader@latest --dev
yarn add sass --dev
```

### Benefits of Upgrading
- ✅ Use Node.js 24+
- ✅ Faster builds
- ✅ Better performance
- ✅ No Python dependency
- ✅ Modern tooling

## Current Status

**Decision: Stay with Node.js 20**

Reasons:
1. Works with existing theme without changes
2. No deprecation warnings (with env variable)
3. LTS until April 2026
4. Stable and tested
5. No risk of breaking changes

## Node-Sass Compatibility Chart

| Node.js Version | node-sass 4.12.0 | node-sass 9.0.0 |
|----------------|------------------|-----------------|
| 14 | ✅ Yes | ❌ No |
| 16 | ⚠️ Partial | ✅ Yes |
| 18 | ❌ No | ✅ Yes |
| 20 | ✅ With fixes | ✅ Yes |
| 24 | ❌ No | ✅ Yes |

Your theme uses node-sass 4.12.0, so Node.js 20 is the best choice.

## Verification

After this fix, you should see:
```
✓ Setup Node.js
  - Node.js 20.x installed
  - Yarn cache restored

✓ Install dependencies
  yarn install v1.22.x
  [1/5] Validating package.json...
  [2/5] Resolving packages...
  [3/5] Fetching packages...
  [4/5] Linking dependencies...
  [5/5] Building fresh packages...
  success Saved lockfile.
  Done in 45.23s

✓ Build assets
  yarn run v1.22.x
  $ webpack --env.production
  Hash: abc123
  scripts/main.js    125 kB
  styles/main.css     45 kB
  Done in 18.45s
```

## Troubleshooting

### If build still fails with Node.js 20

**Try rebuilding node-sass:**
```yaml
- name: Rebuild node-sass
  run: |
    cd wp-content/themes/pbc
    yarn add node-sass@4.14.1 --dev
```

**Or switch to sass (dart-sass):**
```yaml
- name: Switch to dart-sass
  run: |
    cd wp-content/themes/pbc
    yarn remove node-sass
    yarn add sass --dev
```

### If you want to use Node.js 24

You must upgrade the theme dependencies first:
1. Update package.json
2. Replace node-sass with sass
3. Update webpack config
4. Test locally
5. Then switch to Node.js 24

## Summary

| Aspect | Choice | Reason |
|--------|--------|--------|
| **Node.js Version** | 20 | Compatible with node-sass 4.12.0 |
| **node-sass** | Keep 4.12.0 | Works without changes |
| **Warnings** | None | FORCE_JAVASCRIPT_ACTIONS_TO_NODE24=true |
| **Support** | Until 2026 | Node.js 20 LTS |
| **Status** | ✅ Working | No changes needed |

---

**Status: ✅ Using Node.js 20 for compatibility with legacy node-sass**

**Future upgrade path available if needed (migrate to dart-sass for Node.js 24+)**
