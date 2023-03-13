<?php

$screen = get_current_screen();

$screen->add_help_tab( array(
	'id'       => 'wssbi-default',
	'title'    => __( 'Plugin guide', 'wp-show-site-by-ip' ),
	'content'  => sprintf(
		'
		<h1> %s </h1>
		<p> %s </p>
		<h3> %s </h3>
		<p> %s </p>
		<h3> %s </h3>
		<p> %s </p>
		<p> %s </p>
		<p> %s </p>
		<h3> %s </h3>
		<p> %s <br> %s </p>
		<p><strong> %s </strong></p>
		<p> %s </p>
		<p><code>http://www.your-site.com?wpok</code></p>
		<p> %s </p>
		<p><code>http://www.your-site.com?wpko</code></p>
		<p> %s </p>
		',
		__( 'Plugin guide', 'wp-show-site-by-ip' ),
		__( 'Hide your Wordpress website to unknown IPs and replace it with a HTML page. Useful for developers to work online in private (e.g. maintenance).', 'wp-show-site-by-ip' ),
		__( "Why it's useful", 'wp-show-site-by-ip' ),
		__( "Sometimes you need to work directly on your online Wordpress installation, like when you are developing or refining a theme for your site, and you don't want others to see your work in progress, but at the same time you need to be able to see it from whatever place you find yourself!!!", 'wp-show-site-by-ip' ),
		__( "What it does", 'wp-show-site-by-ip' ),
		__( "This plugin allows you very easily to load a temporary HTML page for your website visitors, but it filters the IP of your connection and let you see your website and work on it.", 'wp-show-site-by-ip' ),
		__( "Even better you don't have to find out your IP and then save it on some sort of whitelist, you can simply add the string <code>wpok</code> to you url to automatically obtain the permission to see your website, without even knowing your IP!!!", 'wp-show-site-by-ip' ),
		__( "That makes it easier to work on your website from many different places (e.g. if you work on it with other people from different locations) or if you have an internet connection with dynamic IP (everytime it changes you can easily add the new one).", 'wp-show-site-by-ip' ),
		__( "How it works", 'wp-show-site-by-ip' ),
		__( "After installed this plugin, you'll find the submenu <b>Show Site by IP</b> on your website Dashboard, under the <b>Tools</b> menu.", 'wp-show-site-by-ip' ),
		__( "That link open the plugin configuration page, where you can insert the full HTML of your temporary page and, when you are good to go, enable the IP filter.", 'wp-show-site-by-ip' ),
		__( "Your IP will be automatically added to the whitelist in order for you to continue to use your website.", 'wp-show-site-by-ip' ),
		__( "To allow access to your website from an internet connection add the string <code>?wpok</code> to the website URL, like this:", 'wp-show-site-by-ip' ),
		__( "To remove your IP from the whitelist afterwards (and then go back to see the temporary page instead of your website) add the string <code>?wpko</code> to the website URL, like this:", 'wp-show-site-by-ip' ),
		__( "That's it. 🙂", 'wp-show-site-by-ip' )
	)
));

$screen->add_help_tab( array(
	'id'       => 'wssbi-settings',
	'title'    => __( 'Settings', 'wp-show-site-by-ip' ),
	'content'  => '
		<h1>'.__('Settings', 'wp-show-site-by-ip').'</h1>
		<p>
			<strong>'.__('Enable filter', 'wp-show-site-by-ip').'</strong><br>
			'.__('Activate or deactivate the IP filter: when enabled everyone but who gained access to the whitelist of IP addresses will see the temporary page.', 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('HTTP Status', 'wp-show-site-by-ip').'</strong><br>
			'.__('Choose the  Hypertext Transfer Protocol (HTTP) response status code for the temporary page. This is useful mainly for communicating the temporary status of the website to the search engines.', 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('String OK', 'wp-show-site-by-ip').'</strong><br>
			'.__("It's the string this plugin will look for in the URL to grant access to the website: if found, the IP from which the user is connecting will be added to the whitelist, allowing him to see the website.", 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('String KO', 'wp-show-site-by-ip').'</strong><br>
			'.__("It's the string this plugin will look for in the URL to deny access to the website: if found, the IP from which the user is connecting will be removed from the whitelist, and then the temporary page will be shown.", 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('Page title', 'wp-show-site-by-ip').'</strong><br>
			'.__("It's the content of the title tag of the temporary page.", 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('Body content', 'wp-show-site-by-ip').'</strong><br>
			'.__("It's the HTML content of the body tag of the temporary page.", 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('Styles & scripts', 'wp-show-site-by-ip').'</strong><br>
			'.__("This code will be loaded inside the head tag of the temporary page, and it allows to add some styles (CSS) and scripts (Javascript) to the page, even from external resources.", 'wp-show-site-by-ip').'
		</p>
		<p>
			<strong>'.__('IPs list', 'wp-show-site-by-ip').'</strong><br>
			'.__("It's the list of authorized IPs and can be manually edited.", 'wp-show-site-by-ip').'
		</p>
	'
));

$screen->set_help_sidebar(
	'<p><strong>' . __( 'Need support?', 'wp-show-site-by-ip' ) . '</strong></p>' .
	'<p><a href="https://wordpress.org/support/plugin/wp-show-site-by-ip" target="_blank">' . __( 'Plugin Support Forum', 'wp-show-site-by-ip' ) . '</a></p>'
);