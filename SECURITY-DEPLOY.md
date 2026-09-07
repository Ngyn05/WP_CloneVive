# Security steps after restoring with Prime Mover

The theme provides defense-in-depth controls, but it cannot clean or secure
files above its own directory. Perform these steps on a clean hosting account.

## Before restoring

1. Delete the old document root; do not merge the backup over infected files.
2. Reset hosting, SFTP/SSH, database, WordPress administrator, Cloudflare and
   email passwords from a clean device.
3. Create a new database user/password instead of reusing the compromised one.
4. Install fresh WordPress core and fresh copies of every plugin.
5. Restore only a backup known to predate the compromise.

## wp-config.php

Add these above `/* That's all, stop editing! */`:

```php
define( 'DISALLOW_FILE_EDIT', true );
define( 'FORCE_SSL_ADMIN', true );
define( 'WP_ENVIRONMENT_TYPE', 'production' );
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_DISPLAY', false );
```

Generate fresh authentication salts at <https://api.wordpress.org/secret-key/1.1/salt/>
and replace every old salt. Use a unique database table prefix for the new
installation. Do not enable `DISALLOW_FILE_MODS` until core/plugins are current.

## Hosting / Cloudflare

- Use PHP 8.2 or newer supported by all installed plugins.
- Disable PHP execution in `wp-content/uploads` at Apache/LiteSpeed or Nginx
  level. The theme attempts to create an Apache `.htaccess`, but Nginx ignores it.
- Turn on Cloudflare managed WAF rules and rate limiting for `/wp-login.php`.
- Enable two-factor authentication for every administrator.
- Confirm no unknown cron jobs, SSH keys, FTP accounts, WordPress users,
  must-use plugins or files remain.
- Verify `/qing2.php`, `/index.php?p=`, `/awp-eal.php`, `/awp-prevy.php` and
  `/check-opcache.php` return 404 after deployment.

## Important

Do not restore the compromised production `index.php`, `.htaccess`,
`wp-config.php`, `wp-admin`, `wp-includes`, plugins or PHP files from uploads.
