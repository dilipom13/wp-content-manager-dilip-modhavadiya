=== WP Content Manager Firstname Lastname ===
Contributors: dilip2615
Tags: promo, shortcode, ajax, rest-api, cache, wp-cli, custom-post-type
Requires at least: 5.2
Tested up to: 6.7
Requires PHP: 7.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight plugin to manage "Promo Blocks" in wp-admin and display them on the front-end via shortcode (with optional AJAX), REST API, caching, and WP-CLI cache clear.

== Description ==

WP Content Manager adds a custom post type called **Promo Blocks** that lets admins create promotional content (title, content, featured image) with additional fields:

- CTA Text
- CTA URL
- Display Priority (numeric)
- Expiry Date (YYYYMMDD) (optional)

Front-end output is provided via shortcode:

- `[dynamic_promo]`

The plugin includes:

- Settings page to enable/disable output, set max promos, cache TTL, and enable AJAX loading
- Transient caching for better performance
- REST API endpoint to fetch promos in JSON
- Optional AJAX loading via admin-ajax.php (returns JSON)
- WP-CLI bonus command to clear cache instantly

== Features ==

1. Custom Post Type: Promo Blocks (`promo_block`)
2. Secure meta saving (nonce + capability checks)
3. Shortcode output: `[dynamic_promo]`
4. Caching via transients (TTL in minutes)
5. REST endpoint:
   - `GET /wp-json/dcm/v1/promos`
6. Optional AJAX endpoint (when enabled from settings):
   - `POST wp-admin/admin-ajax.php?action=dcm_get_promos&nonce=...`
7. Bonus: WP-CLI cache clear:
   - `wp dcm cache clear`

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install via ZIP upload.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Go to:
   **Settings → Dynamic Content**
   and configure plugin options.
4. Create promo blocks:
   **Promo Blocks → Add New**
5. Add shortcode on any page/post:
   `[dynamic_promo]`

== Usage ==

= Add promo output in any page/post =
Use shortcode:
[dynamic_promo]

= Settings options =
Go to **Settings → Dynamic Content** and configure:

- Enable/Disable output
- Maximum promo blocks to display
- Cache TTL (minutes)
- Enable AJAX loading (admin-ajax)

= How ordering works =
Promo blocks are ordered by **Display Priority** (ASC). Lower number shows first.

= Expiry Date (YYYYMMDD) =
If Expiry is empty, promo never expires.
If Expiry is set, promo will show only if expiry >= today.

== REST API ==

Endpoint:
GET /wp-json/dcm/v1/promos

Response (example):
{
  "items": [
    {
      "id": 123,
      "title": "Promo title",
      "content": "<p>Promo HTML content</p>",
      "image": "https://example.com/wp-content/uploads/....jpg",
      "cta_text": "Buy now",
      "cta_url": "https://example.com",
      "priority": 1,
      "expiry": "20261231"
    }
  ]
}

== AJAX Loading ==

When "Enable AJAX loading" is turned ON, the shortcode prints a loader container and promos are loaded via admin-ajax.php.

AJAX URL:
wp-admin/admin-ajax.php

Action:
dcm_get_promos

Nonce:
Generated on front-end and verified in the AJAX handler.

== Caching ==

The plugin caches promo data using WordPress transients.
Cache key is based on the "Maximum promo blocks" setting (example: `dcm_promos_3`).

Cache is automatically cleared when a Promo Block is saved or deleted.

== WP-CLI (Bonus) ==

Clear plugin cache from CLI:

wp dcm cache clear

This deletes cached transient entries so the next page load pulls fresh data from the database.

== Frequently Asked Questions ==

= Why don't I see promos on the front-end? =
1. Make sure plugin is enabled in Settings → Dynamic Content
2. Make sure you created at least 1 Promo Block (Published)
3. Add shortcode [dynamic_promo] in a page/post
4. If expiry date is set, ensure it is >= today in YYYYMMDD

= Why is AJAX not calling in Network tab? =
If AJAX is disabled in settings, the plugin uses the REST endpoint instead.
Enable AJAX loading from Settings → Dynamic Content to see admin-ajax.php calls.

= How do I clear cache manually? =
Use WP-CLI:
wp dcm cache clear
or change TTL / update promo blocks to trigger invalidation.

== Screenshots ==

1. Settings page: Dynamic Content configuration.
2. Promo Blocks CPT: Add/Edit screen with meta fields.
3. Front-end output using shortcode.

== Changelog ==

= 1.0.0 =
* Initial release.
* Promo Blocks CPT with meta fields (CTA, priority, expiry).
* Shortcode output with optional AJAX.
* REST API endpoint for promo listing.
* Transient caching + auto invalidation on post changes.
* Bonus WP-CLI cache clear command.

== Upgrade Notice ==

= 1.0.0 =
Initial release.