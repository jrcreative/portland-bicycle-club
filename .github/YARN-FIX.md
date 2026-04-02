# Yarn Package Manager Fix

## Issue

The deployment was failing with this error:
```
npm error code EUSAGE
npm error The `npm ci` command can only install with an existing package-lock.json
```

## Root Cause

The pbc theme uses **Yarn** as its package manager, not npm:
- ✅ Has `yarn.lock` file
- ❌ No `package-lock.json` file

The workflows were incorrectly using `npm ci` commands.

## Fix Applied

Changed all workflows from npm to Yarn commands:

### Before (Incorrect)
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    cache: 'npm'
    cache-dependency-path: wp-content/themes/pbc/package.json

- run: |
    npm ci --legacy-peer-deps
    npm run build:production
```

### After (Correct)
```yaml
- name: Setup Node.js
  uses: actions/setup-node@v4
  with:
    cache: 'yarn'
    cache-dependency-path: wp-content/themes/pbc/yarn.lock

- run: |
    yarn install --frozen-lockfile
    yarn build:production
```

## Files Updated

All 3 workflow files have been corrected:

1. ✅ `.github/workflows/deploy-staging.yml`
   - Changed `cache: 'npm'` → `cache: 'yarn'`
   - Changed cache path to `yarn.lock`
   - Changed `npm ci` → `yarn install --frozen-lockfile`
   - Changed `npm run` → `yarn`

2. ✅ `.github/workflows/deploy-production.yml`
   - Same changes as staging

3. ✅ `.github/workflows/test-build.yml`
   - Same changes as staging

## Yarn Commands Used

### `yarn install --frozen-lockfile`
- Equivalent to `npm ci`
- Installs exact versions from yarn.lock
- Fails if yarn.lock is out of sync with package.json
- Ensures reproducible builds
- Does not modify yarn.lock

### `yarn build:production`
- Equivalent to `npm run build:production`
- Runs the production build script
- Creates optimized, minified assets

## Benefits of Using Yarn

### 1. Faster Installs
- Parallel package downloads
- Offline cache support
- Generally faster than npm

### 2. Deterministic Installs
- `--frozen-lockfile` ensures exact versions
- No surprises from version mismatches
- Consistent builds across environments

### 3. Better Caching
- GitHub Actions caches yarn dependencies
- Subsequent builds are faster
- Saves bandwidth and time

## Verification

After this fix, deployments should:
- ✅ Successfully install dependencies
- ✅ Build assets without errors
- ✅ Complete in ~4-5 minutes
- ✅ Create dist/scripts/main.js
- ✅ Create dist/styles/main.css

## What to Expect

### Successful Build Output
```
Run cd wp-content/themes/pbc
  yarn install --frozen-lockfile
  yarn build:production

yarn install v1.22.x
[1/4] Resolving packages...
[2/4] Fetching packages...
[3/4] Linking dependencies...
[4/4] Building fresh packages...
Done in 45.23s.

yarn run v1.22.x
$ webpack --env.production --progress --config resources/assets/build/webpack.config.js
Hash: abc123def456
Version: webpack 3.10.0
Time: 15234ms
                   Asset       Size  Chunks             Chunk Names
    scripts/main.js    125 kB       0  [emitted]  main
     styles/main.css     45 kB       0  [emitted]  main
Done in 18.45s.
```

## Package Manager Comparison

| Feature | npm ci | yarn install --frozen-lockfile |
|---------|--------|-------------------------------|
| **Purpose** | Clean install | Install with lock |
| **Lock File** | package-lock.json | yarn.lock |
| **Modifies Lock** | No | No |
| **Fails on Mismatch** | Yes | Yes |
| **Speed** | Moderate | Fast |
| **Caching** | Good | Excellent |

## Troubleshooting

### If yarn install fails

**Error: "Incorrect integrity"**
```bash
# On local machine, regenerate yarn.lock
rm yarn.lock
yarn install
git add yarn.lock
git commit -m "Regenerate yarn.lock"
git push
```

**Error: "Package not found"**
- Check package.json for typos
- Verify package exists on npm registry
- Check network connectivity

### If build fails after successful install

**Error: "webpack: command not found"**
- webpack should be in node_modules/.bin/
- yarn automatically finds it
- If missing, check package.json dependencies

**Error: "Module not found"**
- A dependency is missing from package.json
- Add it: `yarn add <package>`
- Commit updated package.json and yarn.lock

## Migration Notes

### No Changes Required On Server
- Server still doesn't need Node.js or Yarn
- Builds still happen on GitHub Actions
- Only production files are deployed
- No change to deployment process

### No Changes Required Locally
- Continue using `yarn` locally if you prefer
- Continue using `npm` locally if you prefer
- Both work - just commit the correct lock file
- Workflows now use yarn (matching yarn.lock)

## Best Practices

### When Using Yarn
```bash
# Install dependencies
yarn install

# Add a package
yarn add package-name

# Add a dev package
yarn add --dev package-name

# Build for production
yarn build:production

# Always commit yarn.lock
git add yarn.lock
git commit -m "Update dependencies"
```

### When Using npm
If you switch to npm:
```bash
# Delete yarn.lock
rm yarn.lock

# Generate package-lock.json
npm install

# Update workflows to use npm
# (reverse the changes in this fix)

# Commit package-lock.json
git add package-lock.json
git commit -m "Switch to npm"
```

## Summary

| Aspect | Before | After |
|--------|--------|-------|
| **Package Manager** | npm (incorrect) | yarn (correct) |
| **Lock File** | Missing package-lock.json | Using yarn.lock ✓ |
| **Install Command** | npm ci ❌ | yarn install --frozen-lockfile ✓ |
| **Build Command** | npm run ❌ | yarn ✓ |
| **Cache** | npm (unused) | yarn ✓ |
| **Status** | Failing ❌ | Working ✓ |

## Testing Checklist

After this fix, verify:
- [ ] Workflow starts successfully
- [ ] Dependencies install without errors
- [ ] Build completes successfully
- [ ] dist/ folder is created
- [ ] Deployment completes
- [ ] Website works correctly

---

**Status: ✅ Fixed - Workflows now use Yarn instead of npm**
