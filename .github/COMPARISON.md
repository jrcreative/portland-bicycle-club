# Build & Deploy: Before vs After

## Visual Comparison

### ❌ OLD METHOD (Build on Server)

```
┌──────────────┐
│   GitHub     │
│  Repository  │
└──────┬───────┘
       │ Push includes:
       │ • Source files (JS/SCSS)
       │ • Build configs
       │ • node_modules (400MB)
       │ • Everything
       ↓
┌──────────────────────────────┐
│         Server               │
│                              │
│  1. Receive ALL files        │
│     (450 MB upload!)         │
│                              │
│  2. Run npm install          │
│     (needs Node.js)          │
│                              │
│  3. Run webpack build        │
│     (uses server CPU/RAM)    │
│                              │
│  4. Serve website            │
│                              │
└──────────────────────────────┘
```

**Problems:**
- ⚠️ Large uploads (450 MB)
- ⚠️ Server needs Node.js/npm
- ⚠️ Server CPU used for builds
- ⚠️ Disk space wasted on node_modules
- ⚠️ Source files exposed on server
- ⚠️ Longer deployment times

---

### ✅ NEW METHOD (Build on GitHub)

```
┌──────────────┐
│   GitHub     │
│  Repository  │
└──────┬───────┘
       │
       ↓
┌─────────────────────────────────┐
│    GitHub Actions Runner        │
│                                 │
│  1. Install dependencies        │
│     npm ci                      │
│     composer install            │
│                                 │
│  2. Build assets                │
│     npm run build:production    │
│     → Creates dist/ folder      │
│                                 │
│  3. Clean up                    │
│     rm -rf node_modules/        │
│     rm -rf resources/assets/    │
│     rm package.json webpack.js  │
│                                 │
│  4. Deploy ONLY built files     │
└──────────┬──────────────────────┘
           │ Upload includes:
           │ • dist/ (built assets)
           │ • vendor/ (PHP deps)
           │ • Theme PHP files
           │ • WordPress core
           │ (40 MB upload)
           ↓
┌──────────────────────────────┐
│         Server               │
│                              │
│  1. Receive built files      │
│     (40 MB only!)            │
│                              │
│  2. Serve website            │
│     (no build needed)        │
│                              │
└──────────────────────────────┘
```

**Benefits:**
- ✅ Small uploads (40 MB)
- ✅ Server only needs PHP
- ✅ Server CPU free for serving content
- ✅ No wasted disk space
- ✅ No source files on server
- ✅ Faster deployments

---

## File Structure Comparison

### ❌ OLD: What Was on Server

```
wp-content/themes/pbc/
├── node_modules/              ← 400 MB (unused at runtime!)
│   ├── webpack/
│   ├── babel/
│   └── ... 1000+ packages
├── resources/
│   └── assets/
│       ├── scripts/           ← Source JS (not needed!)
│       │   └── main.js
│       ├── styles/            ← Source SCSS (not needed!)
│       │   └── main.scss
│       ├── build/             ← Build configs (not needed!)
│       └── config/
├── dist/                      ← Built files (NEEDED)
│   ├── scripts/main.js
│   └── styles/main.css
├── vendor/                    ← PHP deps (NEEDED)
├── package.json               ← Build config (not needed!)
├── webpack.config.js          ← Build config (not needed!)
├── composer.json              ← Config (not needed!)
└── functions.php              ← Theme files (NEEDED)
```

**Total size: ~450 MB**

---

### ✅ NEW: What's on Server Now

```
wp-content/themes/pbc/
├── dist/                      ← Built files ✓
│   ├── scripts/main.js        (minified, production-ready)
│   └── styles/main.css        (compiled, minified)
├── vendor/                    ← PHP deps ✓
│   └── timber/
├── app/                       ← Theme PHP ✓
├── views/                     ← Twig templates ✓
└── functions.php              ← Theme functions ✓
```

**Total size: ~40 MB**

---

## Deployment Speed Comparison

### ❌ OLD METHOD
```
Step                          Time
────────────────────────────────────
Upload files (450 MB)         ~3-5 min
npm install on server         ~2-3 min
Webpack build on server       ~1-2 min
────────────────────────────────────
Total:                        ~6-10 min
```

### ✅ NEW METHOD
```
Step                          Time
────────────────────────────────────
GitHub: npm install           ~2 min
GitHub: webpack build         ~1 min  
GitHub: composer install      ~30 sec
GitHub: cleanup               ~5 sec
Upload files (40 MB)          ~30-60 sec
────────────────────────────────────
Total:                        ~4-5 min
(Parallel work + smaller upload = faster!)
```

---

## Server Requirements Comparison

### ❌ OLD SERVER NEEDS

```
Required Software:
  ✓ PHP 7.4+
  ✓ Node.js 18+
  ✓ npm
  ✓ Build tools (webpack, babel, etc.)
  ✓ SSH access
  ✓ rsync

Resources:
  • CPU: Used for builds
  • RAM: 2GB+ for webpack
  • Disk: ~450 MB per theme
```

### ✅ NEW SERVER NEEDS

```
Required Software:
  ✓ PHP 7.4+
  ✓ SSH access
  ✓ rsync

Resources:
  • CPU: Free for serving content
  • RAM: Standard PHP requirements
  • Disk: ~40 MB per theme
```

---

## Security Comparison

### ❌ OLD: Security Concerns

```
Exposed on Server:
  ⚠️ Source code (resources/assets/)
  ⚠️ Build configs (webpack.config.js)
  ⚠️ node_modules/ (potential vulnerabilities)
  ⚠️ Development tools
  ⚠️ Package manifests (package.json)
```

### ✅ NEW: Improved Security

```
Exposed on Server:
  ✅ Only compiled assets (dist/)
  ✅ Only production PHP code
  ✅ No development tools
  ✅ No source files
  ✅ Smaller attack surface
```

---

## Cost Comparison

### Bandwidth Usage

| Deploy | Old Method | New Method | Savings |
|--------|-----------|-----------|---------|
| First  | 450 MB    | 40 MB     | 91%     |
| Update | 450 MB    | ~5-10 MB  | 98%     |

**Annual savings** (10 deployments/month):
- Old: ~54 GB/year
- New: ~5 GB/year
- **Savings: 90% less bandwidth**

### Server Resources

| Resource | Old Method | New Method | Impact |
|----------|-----------|-----------|---------|
| CPU      | Medium-High (builds) | Low (serving only) | More capacity |
| RAM      | 2GB+ (webpack) | <1GB (PHP) | Lower costs |
| Disk     | 450 MB | 40 MB | 91% less space |

---

## Summary Table

| Aspect | Old Method | New Method | Winner |
|--------|-----------|-----------|---------|
| **Upload Size** | 450 MB | 40 MB | ✅ New (91% less) |
| **Deploy Time** | 6-10 min | 4-5 min | ✅ New (40% faster) |
| **Server CPU** | Used for builds | Free | ✅ New |
| **Server RAM** | 2GB+ | <1GB | ✅ New |
| **Node.js Required** | Yes | No | ✅ New |
| **Build Tools Required** | Yes | No | ✅ New |
| **Source Files Exposed** | Yes | No | ✅ New |
| **Security** | Lower | Higher | ✅ New |
| **Disk Space** | 450 MB | 40 MB | ✅ New |
| **Bandwidth Usage** | High | Low | ✅ New |
| **Maintenance** | Complex | Simple | ✅ New |

## The Bottom Line

### Before (Old Method)
```
❌ Slow deployments
❌ Large file transfers  
❌ Server resources wasted
❌ Security concerns
❌ Complex server setup
```

### After (New Method)
```
✅ Fast deployments
✅ Minimal file transfers
✅ Efficient server usage
✅ Better security
✅ Simple server setup
```

---

## Migration Impact

### What Happens on First Deploy?

```
1. GitHub Actions builds everything
2. Cleanup removes unnecessary files
3. rsync uploads to server (~40 MB)
4. rsync --delete removes old files:
   • Deletes node_modules/
   • Deletes source files
   • Deletes build configs
5. Server now has only production files

✅ Automatic cleanup, no manual work needed!
```

### Rollback if Needed?

Previous workflows are in git history:
```bash
git log --oneline .github/workflows/
git show <commit>:.github/workflows/deploy-staging.yml
```

Can revert if needed, but new method is strictly better.

---

**Recommendation: Use the new method. It's faster, more secure, and more efficient in every way.**
