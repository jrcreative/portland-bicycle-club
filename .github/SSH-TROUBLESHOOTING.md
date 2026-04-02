# SSH Connection Troubleshooting

## Current Error

```
Permission denied (publickey,password).
rsync: connection unexpectedly closed
```

This means the SSH authentication is failing. The server is rejecting the connection.

## Root Cause

One of these is the problem:

1. ❌ **STAGING_SSH_PRIVATE_KEY** not set in GitHub Secrets
2. ❌ **Public key not on server** (not in ~/.ssh/authorized_keys)
3. ❌ **Wrong private key** in GitHub Secrets
4. ❌ **Server permissions incorrect** (authorized_keys must be 600)
5. ❌ **Wrong username** (STAGING_SSH_USER)

## Step-by-Step Fix

### Step 1: Generate SSH Key (If You Don't Have One)

```bash
# Generate new SSH key for deployment
ssh-keygen -t ed25519 -C "github-actions-pwtc-staging" -f ~/.ssh/github_pwtc_deploy

# When prompted for passphrase: press Enter (no passphrase!)
```

This creates:
- `~/.ssh/github_pwtc_deploy` (private key - for GitHub)
- `~/.ssh/github_pwtc_deploy.pub` (public key - for server)

### Step 2: Add Public Key to Server

```bash
# View your public key
cat ~/.ssh/github_pwtc_deploy.pub

# Copy the entire line (starts with ssh-ed25519...)

# Connect to your server
ssh -p 2222 your-username@your-server.com

# Add the public key
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo "paste-your-public-key-here" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
exit
```

### Step 3: Test SSH Connection

```bash
# Test with your key
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy your-username@your-server.com

# Should connect WITHOUT asking for password
# If it asks for password, the key isn't working
```

### Step 4: Add Private Key to GitHub

```bash
# Display private key
cat ~/.ssh/github_pwtc_deploy

# Copy ENTIRE output including these lines:
# -----BEGIN OPENSSH PRIVATE KEY-----
# ... all the key content ...
# -----END OPENSSH PRIVATE KEY-----
```

Go to GitHub:
1. https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions
2. Click "New repository secret"
3. Name: `STAGING_SSH_PRIVATE_KEY`
4. Value: Paste the ENTIRE private key (including BEGIN/END lines)
5. Click "Add secret"

### Step 5: Add Other Secrets

While you're in GitHub Secrets, add these too:

**STAGING_SSH_USER**
- Your SSH username (e.g., `username` or `pwtc`)

**STAGING_SERVER_HOST**  
- Your server hostname (e.g., `staging.example.com`)

**STAGING_DEPLOY_PATH**
- Full path to WordPress (e.g., `/home/username/public_html`)

### Step 6: Verify Secrets

After adding all secrets:

```bash
# In GitHub, go to:
# Settings → Secrets and variables → Actions

# You should see:
# ✓ STAGING_SSH_PRIVATE_KEY
# ✓ STAGING_SSH_USER
# ✓ STAGING_SERVER_HOST
# ✓ STAGING_DEPLOY_PATH
```

### Step 7: Test Deployment

```bash
# Push to staging branch
git push origin staging

# Watch deployment
# Go to: https://github.com/jrcreative/portland-bicycle-club/actions
```

## Common Issues

### Issue: "Permission denied (publickey)"

**Problem:** Public key not on server or wrong key

**Fix:**
```bash
# On your local machine
cat ~/.ssh/github_pwtc_deploy.pub

# SSH to server
ssh -p 2222 username@server

# Add public key
echo "your-public-key-here" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### Issue: "Connection refused"

**Problem:** Wrong port, wrong hostname, or firewall

**Fix:**
```bash
# Test connection
telnet your-server.com 2222

# If it doesn't connect, check:
# - Hostname is correct
# - Port 2222 is open
# - Firewall allows GitHub Actions IPs
```

### Issue: "Host key verification failed"

**Problem:** Server not in known_hosts

**Fix:** Workflow now handles this automatically with fallback to StrictHostKeyChecking=no

### Issue: "Permission denied, please try again"

**Problem:** Server is asking for password (key auth not working)

**Fix:**
```bash
# Verify public key is on server
ssh -p 2222 username@server "cat ~/.ssh/authorized_keys"

# Should show your public key
# If not there, add it (see Step 2 above)
```

## Verify Your Setup Locally

Before using GitHub Actions, test everything locally:

```bash
# 1. Test SSH connection
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@your-server.com

# 2. Test rsync
rsync -avz -e "ssh -p 2222 -i ~/.ssh/github_pwtc_deploy" \
  --dry-run \
  ./ username@your-server.com:/path/to/wordpress/

# 3. Test commands
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@your-server.com "pwd; ls -la"
```

All three should work without asking for passwords.

## GitHub Actions SSH Agent

The workflow uses `webfactory/ssh-agent@v0.9.0` which:
- Loads your private key into SSH agent
- Makes it available to all SSH commands
- Handles key authentication automatically

**Requirements:**
- Private key must have NO passphrase
- Private key must be in correct format (OpenSSH)
- Public key must be on server

## Quick Checklist

Before deployment works, verify:

- [ ] SSH key pair generated
- [ ] Public key added to server's ~/.ssh/authorized_keys
- [ ] Server authorized_keys has correct permissions (600)
- [ ] Can SSH manually: `ssh -p 2222 -i key username@server`
- [ ] Private key added to GitHub as STAGING_SSH_PRIVATE_KEY
- [ ] STAGING_SSH_USER secret set
- [ ] STAGING_SERVER_HOST secret set
- [ ] STAGING_DEPLOY_PATH secret set
- [ ] All secrets verified in GitHub settings

## Need Help?

### Check GitHub Actions Logs

The workflow now includes:
1. **Verify secrets** - Confirms all secrets are set
2. **Test SSH connection** - Tests connection before rsync
3. **Better error messages** - Tells you what's wrong

### Test Connection Command

Use this exact command to test (replace with your values):

```bash
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -o StrictHostKeyChecking=no username@your-server.com "echo SUCCESS"
```

Should print: `SUCCESS`

If it asks for password or fails, fix that first before using GitHub Actions.

## Summary

**Current Issue:** SSH authentication failing
**Most Likely Cause:** Public key not on server or wrong private key in GitHub
**Next Action:** Follow steps 1-6 above to properly configure SSH keys

---

**Once SSH keys are properly configured, deployment will work!**
