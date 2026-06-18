# Manual Test Checklist

Use this checklist after changes to runtime filtering, settings persistence, IP rules, URL whitelist, bypasses, or the temporary page editors.

## Settings Save

- Save settings with the filter disabled and verify the public site remains visible.
- Save settings with the filter enabled and verify admin notices appear.
- Save the temporary page title with quotes and special characters.
- Save `head` with CSS and JavaScript and verify the code is preserved.
- Save `body` with HTML, quotes, and backslashes and verify the code is preserved.
- Save a custom HTTP status in the valid range and verify it persists.
- Save an invalid HTTP status and verify it falls back to `503`.

## IP Rules

- Save exact IPv4 and verify access from that IP.
- Save exact IPv6 and verify it is normalized and preserved.
- Save loopback entries such as `127.0.0.1` and `::1`.
- Save full-segment wildcard rules such as `123.123.123.*`.
- Save comment lines beginning with `#` and verify they are ignored.
- Save invalid IP rules and verify they are discarded.

## Access Strings

- With the filter enabled, visit `?wpok` and verify the current exact IP is added when not already authorized.
- With the filter enabled, visit `?wpko` and verify only the exact current IP entry is removed.
- Verify wildcard rules continue to grant access after `?wpko`.
- With `wordOk` set to `wpok` and the filter enabled, verify the warning is visible and dismissible.
- Customize `wordOk` and verify the warning no longer appears.

## URL Whitelist

- Save one URL whitelist string per line and verify they persist after reload.
- Visit a URL containing a configured whitelist string and verify the temporary page is bypassed.
- Visit a URL without matching whitelist strings and verify normal filtering behavior.
- Add comment lines beginning with `#` and verify they are ignored.

## Technical Bypasses

- With the filter enabled and no matching IP access, verify WordPress admin pages show the temporary page.
- Visit `?wpok`, then verify WordPress admin pages are accessible from the authorized IP.
- With the filter enabled and no matching IP access, verify these requests bypass the temporary page:
  - Ajax requests.
  - WP Cron.
  - `/wp-json/`.
  - sitemap XML paths.
  - static assets such as CSS, JS, images, fonts, PDF, TXT, XML and JSON.
  - `/.well-known/acme-challenge/`.
- Temporarily add custom filters for bypass exact paths, prefixes, patterns, extensions and final decision, then verify each filter can change the decision.

## Old HTML Migration Notice

- When `wssbi_html_old` exists, verify the notice appears on the settings page.
- Click "View old code" and verify the Thickbox opens.
- Click "Forget old code" with a valid session and verify the notice is removed.
- Verify the Ajax action fails without a valid nonce or without the required capability.
