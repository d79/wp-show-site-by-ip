# WP Show Site by IP

Hide your Wordpress website to unknown IPs and replace it with a HTML page. Useful for developers to work online in private (e.g. maintenance).

### Why it's useful
Sometimes you need to work directly on your online Wordpress installation, like when you are developing or refining a theme for your site, and you don't want others to see your work in progress, but at the same time you need to be able to see it from whatever place you find yourself!!!

### What it does
This plugin allows you very easily to load a temporary HTML page for your website visitors, but it filters the IP of your connection and let you see your website and work on it.
When the filter is enabled, unauthorized visitors see the temporary page on the public site and on `/wp-admin/`; authorize your IP before opening the WordPress admin area.

Even better you don't have to find out your IP and then save it on some sort of whitelist, you can simply add the string `wpok` to you url to automatically obtain the permission to see your website, without even knowing your IP!!!

That makes it easier to work on your website from many different places (e.g. if you work on it with other people from different locations) or if you have an internet connection with dynamic IP (everytime it changes you can easily add the new one).

### How it works
After installed this plugin, you'll find the submenu *Show Site by IP* on your website Dashboard, under the *Tools* menu.
That link open the plugin configuration page, where you can insert the full HTML of your temporary page and, when you are good to go, enable the IP filter.

The temporary page fields can contain custom HTML, CSS and JavaScript, so they should be edited only by trusted administrators.

**Your IP will be automatically added to the whitelist in order for you to continue to use your website.**

The whitelist accepts one rule per line and supports IPv4, IPv6 and full-segment wildcards such as `123.123.123.*` or `2001:db8:*:*:*:*:*:*`.

Loopback addresses such as `127.0.0.1` and `::1` are valid whitelist entries too.

In the IP whitelist, comments can start a line or follow a rule after `#`, for example `# office` or `123.123.123.* # office LAN`.

For security, the plugin currently checks only `REMOTE_ADDR` and does not automatically trust proxy or CDN headers such as `X-Forwarded-For`.

You can also configure a URL whitelist made of one string per line: when the current request URL contains one of those strings, the website is shown for that request only, without saving the visitor IP.

To allow the access to your website from an internet connection add the string `?wpok` to the website URL, like this:

* `http://www.your-site.com?wpok`

The default `wpok` string is convenient but predictable. For better privacy, customize it in the plugin settings.

Wildcard rules must be edited manually. To remove your exact IP from the whitelist afterwards (and then go back to see the temporary page instead of your website) add the string `?wpko` to the website URL, like this:

* `http://www.your-site.com?wpko`

That's it. =)
