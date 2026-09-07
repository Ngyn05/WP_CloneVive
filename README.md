# VIVE Complete Mirror

This WordPress theme contains the 1,922 supplied VIVE pages, downloaded media,
Shop pages, footer sites, and additional clickable `*.vive.com` pages discovered
during the final link audit.

Navigation policy:

- A VIVE link opens its local crawled copy when available.
- A missing/blocked VIVE page opens the WordPress homepage.
- A link to any non-VIVE domain opens the WordPress homepage.
- External CSS, media, analytics and API requests are not navigation links and
  may remain remote when no downloaded copy exists.

Canonical permalink format:

- Main VIVE pages: `/vive/www/{path}/`
- Subdomains: `/vive/{subdomain}/{path}/`
- Homepage: `/`
- Legacy paths and `/vive-mirror/...` redirect permanently to the canonical URL.

Vietnamese translations are managed under **Bản dịch VIVE** in WordPress.
Translations are grouped by section and move through Draft, Review, Approved,
and Locked states. Locked translations cannot be overwritten until explicitly
unlocked by an editor.
