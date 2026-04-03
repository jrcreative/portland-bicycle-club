# Manual SSH Key Setup (When ssh-copy-id Fails)

## The Error You're Seeing

```
sh: line 1: dirname: command not found
mkdir: cannot create directory '': No such file or directory
```

This means `ssh-copy-id` is having issues with your server. Let's do it manually instead.

## Manual Setup (3 Steps)

### Step 1: Generate SSH Key

```bash
# Generate key (no passphrase!)
ssh-keygen -t ed25519 -C "github-pwtc-deploy" -f ~/.ssh/github_pwtc_deploy -N ""

# This creates:
# - ~/.ssh/github_pwtc_deploy (private key)
# - ~/.ssh/github_pwtc_deploy.pub (public key)
```

### Step 2: View Your Public Key

```bash
# Display the public key
cat ~/.ssh/github_pwtc_deploy.pub

# Output looks like:
# ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAbc123def456... github-pwtc-deploy

# Copy this ENTIRE line
```

### Step 3: Add Public Key to Server MANUALLY

#### Method A: Via SSH (If you can connect with password)

```bash
# Connect to your server with password
ssh -p 2222 your-username@your-server.com

# Once connected, run these commands:
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys

# In nano editor:
# 1. Paste your public key (from Step 2)
# 2. Press Ctrl+O to save
# 3. Press Enter
# 4. Press Ctrl+X to exit

# Set correct permissions
chmod 600 ~/.ssh/authorized_keys

# Verify it was added
cat ~/.ssh/authorized_keys

# Exit server
exit
```

#### Method B: Via Control Panel / File Manager

If your hosting has a control panel (cPanel, Plesk, etc.):

1. Login to control panel
2. Go to SSH/Shell Access or File Manager
3. Navigate to `~/.ssh/` directory (or `/home/username/.ssh/`)
4. Open or create `authorized_keys` file
5. Paste your public key on a new line
6. Save the file
7. Set permissions to 600

#### Method C: Via FTP/SFTP

1. Connect via FTP/SFTP
2. Navigate to your home directory
3. Create `.ssh` folder if it doesn't exist
4. Upload or edit `authorized_keys` file
5. Add your public key
6. Set file permissions to 600 (via FTP client or SSH)

### Step 4: Test Connection

```bash
# Test with your new key
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy your-username@your-server.com

# Should connect WITHOUT asking for password!
# If it still asks for password, something is wrong
```

## If It Still Asks for Password

### Check Server Permissions

```bash
# SSH to server (with password)
ssh -p 2222 your-username@your-server.com

# Check permissions
ls -la ~/.ssh/

# Should show:
# drwx------ (700) .ssh directory
# -rw------- (600) authorized_keys file

# Fix if needed:
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

### Check Key Is Really There

```bash
# On server
cat ~/.ssh/authorized_keys

# Should show your public key (starting with ssh-ed25519...)
# Make sure it's all on ONE line (no line breaks)
```

### Check Server SSH Config

Your server might have SSH restrictions. Contact your hosting provider if:
- Password authentication is disabled
- Key authentication is disabled
- Home directory permissions are wrong
- SELinux is blocking (on CentOS/RHEL)

## Add Private Key to GitHub

Once SSH connection works:

### Step 1: Display Private Key

```bash
cat ~/.ssh/github_pwtc_deploy
```

### Step 2: Copy Output

Copy the ENTIRE output including:
```
-----BEGIN OPENSSH PRIVATE KEY-----
... all the lines of the key ...
-----END OPENSSH PRIVATE KEY-----
```

### Step 3: Add to GitHub

1. Go to: https://github.com/jrcreative/portland-bicycle-club/settings/secrets/actions
2. Click "New repository secret"
3. Name: `STAGING_SSH_PRIVATE_KEY`
4. Value: Paste the entire private key
5. Click "Add secret"

### Step 4: Add Other Secrets

Add these 3 more secrets:

**STAGING_SSH_USER**
- Example: `pwtcuser` or `username`
- The username you use to SSH to the server

**STAGING_SERVER_HOST**
- Example: `staging.portlandbicycleclub.com`
- The hostname or IP address

**STAGING_DEPLOY_PATH**
- Example: `/home/pwtcuser/public_html`
- The full path to WordPress on the server

To find the deploy path:
```bash
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@server
pwd  # Shows current directory
cd public_html  # Or wherever WordPress is
pwd  # This is your STAGING_DEPLOY_PATH
```

## Verification Commands

### Test Everything Locally First

```bash
# 1. SSH connection works
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@server "echo SUCCESS"
# Should print: SUCCESS

# 2. Can access WordPress directory
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@server "ls /path/to/wordpress"
# Should list WordPress files

# 3. rsync works (dry run)
rsync -avz --dry-run -e "ssh -p 2222 -i ~/.ssh/github_pwtc_deploy" \
  ./ username@server:/path/to/wordpress/
# Should show what would be transferred
```

All three must work before GitHub Actions will work.

## Common Server Issues

### Issue: "Too many authentication failures"

**Fix:** Server is rejecting after too many attempts

```bash
# Limit to only your key
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -o IdentitiesOnly=yes username@server
```

### Issue: "Permission denied (publickey)"

**Fix:** Key authentication not enabled on server

Contact hosting provider to:
- Enable public key authentication
- Check sshd_config has `PubkeyAuthentication yes`

### Issue: "Connection timed out"

**Fix:** Port 2222 is blocked or server is down

```bash
# Test if port is open
nc -zv your-server.com 2222

# Or
telnet your-server.com 2222
```

### Issue: authorized_keys ignored by server

**Fix:** Wrong permissions or ownership

```bash
# On server
ls -la ~ | grep .ssh
# Should be: drwx------ username username .ssh

ls -la ~/.ssh/
# authorized_keys should be: -rw------- username username

# Fix ownership
chown -R username:username ~/.ssh
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

## Quick Setup Script

If you have password access to the server:

```bash
#!/bin/bash

# Generate key
ssh-keygen -t ed25519 -C "github-deploy" -f ~/.ssh/github_pwtc_deploy -N ""

# Display public key
echo "=== Copy this public key ==="
cat ~/.ssh/github_pwtc_deploy.pub
echo ""
echo "Now SSH to your server and run:"
echo "mkdir -p ~/.ssh && chmod 700 ~/.ssh"
echo "nano ~/.ssh/authorized_keys"
echo "# Paste the public key, save, and exit"
echo "chmod 600 ~/.ssh/authorized_keys"
echo ""
read -p "Press Enter after you've added the key to the server..."

# Test connection
echo "Testing connection..."
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy username@server "echo 'Connection successful!'"

if [ $? -eq 0 ]; then
  echo ""
  echo "✅ SSH connection works!"
  echo ""
  echo "=== Private key for GitHub (copy this) ==="
  cat ~/.ssh/github_pwtc_deploy
  echo ""
  echo "Add this to GitHub as STAGING_SSH_PRIVATE_KEY"
else
  echo ""
  echo "❌ SSH connection failed. Check server setup."
fi
```

## Checklist

Before GitHub Actions will work:

- [ ] SSH key pair generated
- [ ] Public key on server (in ~/.ssh/authorized_keys)
- [ ] Server authorized_keys has 600 permissions
- [ ] SSH connection works: `ssh -p 2222 -i key user@server`
- [ ] rsync test works (dry-run)
- [ ] Private key added to GitHub (STAGING_SSH_PRIVATE_KEY)
- [ ] STAGING_SSH_USER added to GitHub
- [ ] STAGING_SERVER_HOST added to GitHub
- [ ] STAGING_DEPLOY_PATH added to GitHub

## Summary

**The Problem:** GitHub Actions can't authenticate to your server

**The Solution:** 
1. Generate SSH key
2. Add public key to server manually
3. Test connection works locally
4. Add private key to GitHub Secrets

**See:** `.github/SECRETS-SETUP.md` for detailed GitHub Secrets configuration

---

**Status: Follow these steps to configure SSH authentication**
