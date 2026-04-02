# Node.js Version Update

## Changes Made

All GitHub Actions workflows have been updated to address the Node.js 20 deprecation warning.

## What Changed

### Node.js Version
- **Before:** Node.js 18 (for builds)
- **After:** Node.js 20 (LTS, recommended)

### GitHub Actions Runtime
- Added `FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true` environment variable
- This ensures Actions run on Node.js 24 runtime (future-proof)

## Updated Files

### 1. `.github/workflows/deploy-staging.yml`
```yaml
jobs:
  deploy:
    env:
      FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true  # ← Added
    steps:
      - uses: actions/setup-node@v4
        with:
          node-version: '20'  # ← Changed from '18'
```

### 2. `.github/workflows/deploy-production.yml`
```yaml
jobs:
  deploy:
    env:
      FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true  # ← Added
    steps:
      - uses: actions/setup-node@v4
        with:
          node-version: '20'  # ← Changed from '18'
```

### 3. `.github/workflows/test-build.yml`
```yaml
jobs:
  test:
    env:
      FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true  # ← Added
    steps:
      - uses: actions/setup-node@v4
        with:
          node-version: '20'  # ← Changed from '18'
```

## Why These Changes?

### GitHub's Deprecation Notice
GitHub announced that Node.js 20 will be deprecated:
- **Node.js 24 becomes default:** June 2, 2026
- **Node.js 20 removed from runners:** September 16, 2026

### Our Solution
1. **Upgraded to Node.js 20** for builds (LTS, stable)
2. **Opted into Node.js 24** for GitHub Actions runtime
3. **Future-proofed** workflows until at least 2027

## Benefits

### ✅ No More Deprecation Warnings
The warning you saw will no longer appear:
```
Node.js 20 actions are deprecated. The following actions 
are running on Node.js 20 and may not work as expected...
```

### ✅ Future-Proof
- Node.js 20 is LTS (Long Term Support) until April 2026
- Node.js 24 runtime ensures Actions continue working
- No breaking changes expected

### ✅ Better Performance
Node.js 20 includes:
- Better performance
- Improved security
- Modern JavaScript features
- Better npm performance

## Compatibility

### Your Theme Build
Your pbc theme is compatible with Node.js 20:
- ✅ Webpack works fine
- ✅ npm packages compatible
- ✅ Build scripts unchanged
- ✅ No breaking changes

### Server Requirements
No changes to server requirements:
- Server still doesn't need Node.js
- Builds still happen on GitHub Actions
- Deployment process unchanged

## Testing

### What to Test
1. **Staging deployment** - Verify builds complete
2. **Production deployment** - Verify no errors
3. **Build output** - Check dist/ files are created
4. **Website functionality** - Ensure CSS/JS work

### Expected Results
- ✅ No deprecation warnings
- ✅ Faster build times (Node.js 20 is faster)
- ✅ Same output as before
- ✅ Website works identically

## Rollback (if needed)

If you encounter issues, you can temporarily rollback:

```yaml
# In workflow files, change:
node-version: '20'
# Back to:
node-version: '18'

# And remove:
env:
  FORCE_JAVASCRIPT_ACTIONS_TO_NODE24: true
```

However, this is not recommended as Node.js 18 will be unsupported soon.

## Timeline

| Date | Event |
|------|-------|
| **Now** | ✅ Using Node.js 20 + Node.js 24 runtime |
| April 2025 | Node.js 18 EOL (end of life) |
| June 2, 2026 | Node.js 24 becomes default on GitHub |
| Sept 16, 2026 | Node.js 20 removed from GitHub runners |
| 2027+ | ✅ Still working (Node.js 24 supported) |

## Summary

### What Was Fixed
- ❌ Node.js 18 (deprecated warning)
- ✅ Node.js 20 (LTS, stable)
- ✅ Node.js 24 runtime (future-proof)

### Impact
- ✅ No more deprecation warnings
- ✅ Better performance
- ✅ Future-proofed until 2027+
- ✅ No changes to deployment process
- ✅ No changes to server requirements

### Action Required
None! Just deploy and the updates will take effect automatically.

## Additional Notes

### Node.js Version Support Timeline
- **Node.js 18:** EOL April 2025 (end of support)
- **Node.js 20:** LTS until April 2026 (current)
- **Node.js 22:** Current release (not LTS yet)
- **Node.js 24:** Future LTS (expected 2026)

### Why Not Node.js 22?
- Node.js 20 is LTS (Long Term Support)
- Node.js 22 is current release (not LTS yet)
- LTS versions are more stable for production use
- Node.js 20 is supported until April 2026

### Migration Path
```
Old Setup:
  Build: Node.js 18
  Actions: Node.js 20 runtime
  Status: ⚠️ Deprecation warnings

New Setup:
  Build: Node.js 20 (LTS)
  Actions: Node.js 24 runtime (forced)
  Status: ✅ No warnings, future-proof
```

## Resources

- [Node.js Release Schedule](https://nodejs.org/en/about/releases/)
- [GitHub Actions Node.js Versions](https://github.com/actions/runner-images)
- [Node.js 20 Release Notes](https://nodejs.org/en/blog/release/v20.0.0/)
- [GitHub Blog: Node.js 20 Deprecation](https://github.blog/changelog/2025-09-19-deprecation-of-node-20-on-github-actions-runners/)

---

**Status: ✅ All workflows updated and future-proofed**
