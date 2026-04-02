# Quick Start Guide

Get your deployment up and running in 10 minutes.

## 1️⃣ Generate SSH Key (2 min)

```bash
ssh-keygen -t ed25519 -C "github-actions-pwtc" -f ~/.ssh/github_deploy_pwtc
# Press Enter for no passphrase (2x)
```

## 2️⃣ Add Key to Servers (3 min)

```bash
# Replace with your actual server details
ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub username@staging.example.com
ssh-copy-id -i ~/.ssh/github_deploy_pwtc.pub username@production.example.com
```

## 3️⃣ Get Private Key (1 min)

```bash
cat ~/.ssh/github_deploy_pwtc
# Copy entire output including BEGIN/END lines
```

## 4️⃣ Add Secrets to GitHub (3 min)

Go to: `https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions`

Add these 8 secrets:

**Staging:**
1. `STAGING_SSH_PRIVATE_KEY` → Paste private key from step 3
2. `STAGING_SSH_USER` → Your SSH username
3. `STAGING_SERVER_HOST` → staging.example.com
4. `STAGING_DEPLOY_PATH` → /home/user/public_html

**Production:**
5. `PRODUCTION_SSH_PRIVATE_KEY` → Same private key from step 3
6. `PRODUCTION_SSH_USER` → Your SSH username
7. `PRODUCTION_SERVER_HOST` → production.example.com
8. `PRODUCTION_DEPLOY_PATH` → /home/user/public_html

## 5️⃣ Push to GitHub (1 min)

```bash
# Create staging branch
git checkout -b staging
git push -u origin staging

# Commit workflows
git checkout main
git add .github/ DEPLOYMENT.md .gitignore
git commit -m "Add GitHub Actions deployment workflows"
git push origin main
```

## ✅ Done!

### Deploy to Staging (automatic):
```bash
git checkout staging
git merge main
git push origin staging
# Deploys automatically!
```

### Deploy to Production (manual):
1. Go to: https://github.com/jrcreative/portland-bicycle-club/actions
2. Click "Deploy to Production"
3. Click "Run workflow"
4. Type `deploy`
5. Click "Run workflow"

## 🆘 Troubleshooting

**SSH connection fails:**
```bash
ssh -i ~/.ssh/github_deploy_pwtc username@server.com "ls -la"
```

**Need help?**
- Detailed setup: `.github/SETUP.md`
- Full checklist: `.github/CHECKLIST.md`
- Deployment guide: `DEPLOYMENT.md`

## 📊 What Each Workflow Does

| Workflow | Trigger | Action |
|----------|---------|--------|
| **Deploy to Staging** | Push to `staging` | Builds & deploys automatically |
| **Deploy to Production** | Manual from `main` | Builds, backs up, deploys |
| **Test Build** | Pull request | Tests build without deploying |

## 🎯 Server Requirements

- [x] SSH access with key authentication
- [x] rsync installed (`which rsync`)
- [x] WordPress installed at deploy path
- [x] Write permissions for SSH user

## 🔐 Security Checklist

- [x] SSH key has no passphrase (for automation)
- [x] Private key never committed to git
- [x] wp-config.php excluded from deployment
- [x] Backups created before production deploy
- [x] Manual confirmation required for production

---

**Time to deploy:** ~10 minutes setup + instant deployments thereafter
