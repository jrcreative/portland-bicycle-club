# GitHub Secrets Configuration

## Progress Update

✅ **Build is now working!** (dart-sass upgrade successful)

⚠️ **Next issue:** SSH configuration needs GitHub Secrets to be set up

## Error Encountered

```
Error: Process completed with exit code 1.
(in "Add server to known hosts" step)
```

This means the GitHub Secrets are not yet configured.

## Required GitHub Secrets

You must add these 8 secrets to your GitHub repository before deployment can work.

### How to Add Secrets

1. Go to: https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions

2. Click **"New repository secret"**

3. Add each secret below:

## Staging Secrets (4 required)

### 1. STAGING_SSH_PRIVATE_KEY
**What it is:** Your SSH private key for accessing the staging server

**How to get it:**
```bash
# Generate a new key (if you don't have one)
ssh-keygen -t ed25519 -C "github-deploy-staging" -f ~/.ssh/github_pwtc_staging
# Press Enter for no passphrase

# Display the private key
cat ~/.ssh/github_pwtc_staging

# Copy ALL output including:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ... key content ...
# -----END OPENSSH PRIVATE KEY-----
```

**Where to paste:** GitHub Secrets as `STAGING_SSH_PRIVATE_KEY`

---

### 2. STAGING_SSH_USER
**What it is:** Your SSH username on the staging server

**Example:** `username` or `pwtcstaging` or `ubuntu`

**Where to paste:** GitHub Secrets as `STAGING_SSH_USER`

---

### 3. STAGING_SERVER_HOST
**What it is:** Your staging server hostname or IP address

**Examples:**
- `staging.portlandbicycleclub.com`
- `staging.example.com`
- `192.168.1.100`

**Where to paste:** GitHub Secrets as `STAGING_SERVER_HOST`

---

### 4. STAGING_DEPLOY_PATH
**What it is:** Full path to WordPress installation on staging server

**Examples:**
- `/home/username/public_html`
- `/var/www/html`
- `/home/pwtc/staging`

**How to find it:**
```bash
ssh username@staging-server
pwd  # Shows current directory
cd public_html  # Or wherever WordPress is
pwd  # This is your deploy path
```

**Where to paste:** GitHub Secrets as `STAGING_DEPLOY_PATH`

---

## Production Secrets (4 required)

### 5. PRODUCTION_SSH_PRIVATE_KEY
Same as staging, but for production server. Can be the same key or different key.

```bash
cat ~/.ssh/github_pwtc_production
# or use the same key as staging
cat ~/.ssh/github_pwtc_staging
```

---

### 6. PRODUCTION_SSH_USER
Your SSH username on production server

**Example:** `username` or `pwtc` or `ubuntu`

---

### 7. PRODUCTION_SERVER_HOST
Production server hostname

**Examples:**
- `portlandbicycleclub.com`
- `www.portlandbicycleclub.com`
- `192.168.1.50`

---

### 8. PRODUCTION_DEPLOY_PATH
Full path to WordPress on production

**Example:** `/home/username/public_html`

---

## SSH Key Setup on Servers

After generating keys, add the PUBLIC key to your servers:

### Staging Server
```bash
# Copy public key to staging
ssh-copy-id -i ~/.ssh/github_pwtc_staging.pub username@staging-server

# Or manually:
cat ~/.ssh/github_pwtc_staging.pub
# Then SSH to server and add to ~/.ssh/authorized_keys
```

### Production Server
```bash
# Copy public key to production
ssh-copy-id -i ~/.ssh/github_pwtc_production.pub username@production-server

# Or manually:
cat ~/.ssh/github_pwtc_production.pub
# Then SSH to server and add to ~/.ssh/authorized_keys
```

## Verify SSH Access

Test that your keys work BEFORE adding to GitHub:

```bash
# Test staging
ssh -i ~/.ssh/github_pwtc_staging username@staging-server "ls -la"

# Test production  
ssh -i ~/.ssh/github_pwtc_production username@production-server "ls -la"
```

Both should connect without asking for a password.

## Checklist

Before deploying, verify:

- [ ] Generated SSH keys (or using existing ones)
- [ ] Added public keys to both servers
- [ ] Tested SSH connection to staging server
- [ ] Tested SSH connection to production server
- [ ] Added all 4 staging secrets to GitHub
- [ ] Added all 4 production secrets to GitHub
- [ ] Verified no typos in hostnames or paths

## Common Issues

### "Host key verification failed"
- **Cause:** Server not in known_hosts
- **Fix:** Workflow now handles this automatically

### "Permission denied (publickey)"
- **Cause:** Public key not on server or wrong private key in GitHub
- **Fix:** Run `ssh-copy-id` and verify private key in GitHub Secrets

### "No such file or directory" 
- **Cause:** DEPLOY_PATH is incorrect
- **Fix:** SSH to server, find WordPress directory, update secret

### "Connection refused"
- **Cause:** Wrong hostname or firewall blocking
- **Fix:** Verify hostname resolves: `ping staging-server.com`

## Security Best Practices

1. **Use dedicated keys** - Don't reuse your personal SSH key
2. **No passphrase** - GitHub Actions can't enter passphrases
3. **Rotate regularly** - Change keys every 6-12 months
4. **Limit permissions** - Server key should only access WordPress directory
5. **Never commit** - Keys stay in GitHub Secrets only

## Quick Setup Script

```bash
#!/bin/bash

# Generate keys
ssh-keygen -t ed25519 -C "github-pwtc-staging" -f ~/.ssh/github_pwtc_staging -N ""
ssh-keygen -t ed25519 -C "github-pwtc-production" -f ~/.ssh/github_pwtc_production -N ""

# Copy to servers (replace with your details)
ssh-copy-id -i ~/.ssh/github_pwtc_staging.pub user@staging-server
ssh-copy-id -i ~/.ssh/github_pwtc_production.pub user@production-server

# Display private keys (copy these to GitHub)
echo "=== STAGING PRIVATE KEY ==="
cat ~/.ssh/github_pwtc_staging
echo ""
echo "=== PRODUCTION PRIVATE KEY ==="
cat ~/.ssh/github_pwtc_production
```

## After Adding Secrets

Once all 8 secrets are added to GitHub:

1. **Verify secrets exist:**
   - Go to: https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions
   - Should see all 8 secrets listed

2. **Push to staging to trigger deployment:**
   ```bash
   git push origin staging
   ```

3. **Watch it work:**
   - Go to: https://github.com/jrcreative/portland-bicycle-club/actions
   - Watch the deployment run
   - Should complete successfully!

## Summary

**Current Status:**
- ✅ Build working (dart-sass upgrade successful)
- ⚠️ Deployment blocked (needs GitHub Secrets)
- ⚠️ SSH configuration improved (better error handling)

**Next Action:**
1. Add 8 secrets to GitHub
2. Push to staging
3. Deployment will work!

---

**Once secrets are configured, deployment will complete successfully!**
