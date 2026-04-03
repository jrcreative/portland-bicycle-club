# SSH Key Authentication Diagnosis

## Problem: Key Authentication Not Working

You can connect with password but not with key. This indicates a server-side configuration or permissions issue.

## Diagnostic Steps

### Step 1: Test SSH with Verbose Output

```bash
# Connect with maximum verbosity to see what's failing
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -vvv your-username@your-server.com
```

Look for these lines in the output:

**Good signs:**
```
debug1: Offering public key: ~/.ssh/github_pwtc_deploy ED25519
debug1: Server accepts key
debug1: Authentication succeeded (publickey)
```

**Bad signs:**
```
debug1: Offering public key: ~/.ssh/github_pwtc_deploy ED25519
debug1: Authentications that can continue: publickey,password
# ^ This means server rejected your key
```

### Step 2: Check Server Logs

```bash
# SSH to server with password
ssh -p 2222 your-username@your-server.com

# Check SSH logs for clues
sudo tail -f /var/log/auth.log
# or
sudo tail -f /var/log/secure

# Then try to connect with key from another terminal
# Watch the logs for error messages
```

Common errors in logs:
- `Authentication refused: bad ownership or modes` - Permission problem
- `Could not open authorized keys` - File not found or wrong permissions
- `User not allowed` - User restrictions in sshd_config

### Step 3: Check File Permissions

```bash
# On the server, check permissions
ls -la ~/ | grep .ssh
# Should be: drwx------ (700)

ls -la ~/.ssh/
# authorized_keys should be: -rw------- (600)

stat ~/.ssh
stat ~/.ssh/authorized_keys

# Also check home directory
ls -la ~ | grep "^d"
# Home directory should be owned by you
```

### Step 4: Fix Permissions

```bash
# On server, fix all permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys

# Make sure you own the files
chown -R $(whoami):$(whoami) ~/.ssh

# Also check home directory permissions
chmod 755 ~/
```

### Step 5: Verify Key Format

```bash
# On your local machine
cat ~/.ssh/github_pwtc_deploy.pub

# Should look like:
# ssh-ed25519 AAAAC3NzaC1lZDI1NTE5AAAAIAbc... comment

# On server, check authorized_keys
cat ~/.ssh/authorized_keys

# Make sure:
# - Key is on ONE line (no line breaks)
# - No extra spaces
# - Starts with ssh-ed25519 or ssh-rsa
```

## Common Server Issues

### Issue 1: SELinux Blocking (CentOS/RHEL)

```bash
# On server, check SELinux
getenforce

# If it says "Enforcing", fix contexts:
restorecon -R ~/.ssh
```

### Issue 2: sshd_config Restrictions

```bash
# On server, check SSH config
sudo cat /etc/ssh/sshd_config | grep -i pubkey

# Should have:
# PubkeyAuthentication yes

# If it's commented out or "no", SSH to server won't use keys
```

Contact your hosting provider if you need to change this.

### Issue 3: AuthorizedKeysFile Location

```bash
# Check where server looks for authorized_keys
sudo cat /etc/ssh/sshd_config | grep AuthorizedKeysFile

# Common values:
# AuthorizedKeysFile .ssh/authorized_keys
# AuthorizedKeysFile /etc/ssh/authorized_keys/%u

# Make sure your key is in the right location
```

### Issue 4: Home Directory Permissions

```bash
# Home directory must NOT be writable by group/others
ls -la /home/ | grep $(whoami)

# Should be:
# drwxr-xr-x (755) or drwx------ (700)

# Fix:
chmod 755 ~/
```

## Alternative: Use Existing SSH Key

If you already have an SSH key that works:

```bash
# Find your existing key
ls -la ~/.ssh/

# Common names:
# - id_rsa / id_rsa.pub
# - id_ed25519 / id_ed25519.pub
# - id_ecdsa / id_ecdsa.pub

# Test with existing key
ssh -p 2222 -i ~/.ssh/id_ed25519 your-username@your-server.com

# If this works, use THIS key:
cat ~/.ssh/id_ed25519
# Add to GitHub as STAGING_SSH_PRIVATE_KEY
```

## Alternative: Deploy Via FTP/SFTP

If SSH keys continue to fail, you can modify the workflow to use FTP instead:

```yaml
- name: Deploy via FTP
  uses: SamKirkland/FTP-Deploy-Action@v4.3.5
  with:
    server: ${{ secrets.STAGING_FTP_HOST }}
    username: ${{ secrets.STAGING_FTP_USER }}
    password: ${{ secrets.STAGING_FTP_PASSWORD }}
    local-dir: ./
    server-dir: /public_html/
```

## Alternative: Manual Deployment

As a temporary workaround until SSH is fixed:

```bash
# Build locally
cd wp-content/themes/pbc
yarn install
yarn build

# Deploy via SFTP/FTP client (FileZilla, Cyberduck, etc.)
# Upload only the dist/ folder
```

## Get Help from Hosting Provider

Contact your hosting provider with these questions:

1. "Is public key authentication enabled?"
2. "What are the required permissions for .ssh directory?"
3. "Where should I put authorized_keys file?"
4. "Can you help me add an SSH public key to my account?"
5. "Are there any firewall rules blocking GitHub Actions IPs?"

Provide them with:
- Your public key
- GitHub Actions IP ranges: https://api.github.com/meta (look for "actions" IPs)

## Testing Matrix

Try these combinations to isolate the issue:

```bash
# Test 1: Password authentication
ssh -p 2222 your-username@your-server.com
# Works? ✓ Server is accessible

# Test 2: Key with password as fallback
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -o PreferredAuthentications=publickey,password your-username@your-server.com
# Still asks for password? Key is rejected

# Test 3: Key only (no password fallback)
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -o PreferredAuthentications=publickey your-username@your-server.com
# Fails immediately? Key is being rejected

# Test 4: Different key type
ssh-keygen -t rsa -b 4096 -f ~/.ssh/github_rsa_deploy -N ""
# Some servers prefer RSA over ED25519
```

## Quick Fix Checklist

On server, run these commands:

```bash
# Fix all permissions at once
mkdir -p ~/.ssh
chmod 700 ~/.ssh
touch ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
chown -R $(whoami):$(whoami) ~/.ssh
chmod 755 ~/

# Verify
ls -la ~/.ssh/
stat ~/.ssh/authorized_keys

# Add your public key (paste it)
nano ~/.ssh/authorized_keys

# Test
exit
ssh -p 2222 -i ~/.ssh/github_pwtc_deploy your-username@your-server.com
```

## Last Resort: Contact Me With Info

If still not working, provide:

1. Output of: `ssh -p 2222 -i ~/.ssh/github_pwtc_deploy -vvv user@server` (last 20 lines)
2. Output of: `ls -la ~/.ssh/` (on server)
3. Your hosting provider name
4. Whether password auth works

---

**Most Common Cause:** Incorrect permissions on server's ~/.ssh or ~/.ssh/authorized_keys

**Most Common Fix:** `chmod 700 ~/.ssh && chmod 600 ~/.ssh/authorized_keys`
