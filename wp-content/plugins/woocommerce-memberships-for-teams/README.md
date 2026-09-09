# WooCommerce Memberships for Teams

Split off from mono-repo: [wc-memberships-addons](https://github.com/gdcorp-partners/wc-memberships-addons)

## Prerequisites

Composer packages are pulled through a private Artifactory registry (`gdartifactory1.jfrog.io`), so you need to authenticate before running `composer install` for the first time, or whenever your Artifactory access token expires.

1. Install the JFrog CLI: https://docs.jfrog.com/integrations/docs/jfrog-cli-quick-start
2. Authenticate (use `https://gdartifactory1.jfrog.io` as the JFrog Platform URL):
   ```bash
   jf login
   ```
3. Configure Composer to use your Artifactory access token:
   ```bash
   composer config --global http-basic.gdartifactory1.jfrog.io "" "$(jf atc | jq -r .access_token)"
   ```
