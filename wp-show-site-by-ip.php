<?php namespace wssbi;
/**
 * Plugin Name:       WP Show Site by IP
 * Plugin URI:        https://wordpress.org/plugins/wp-show-site-by-ip/
 * Description:       Hide the website to unknown IPs and show a temporary and fully customizable page instead.
 * Version:           2.4.0
 * Author:            Dario Candelù
 * Author URI:        https://sputnikweb.it
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-show-site-by-ip
 * Domain Path:       /languages
 * Requires at least: 3.0.1
 * Tested up to:      6.4.3
 * Requires PHP:      5.3
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

if ( ! defined( 'ABSPATH' ) ) { exit; /* Exit if accessed directly */ }

define( 'wssbi\VER', '2.4.0' );
define( 'wssbi\DIR', __DIR__ . '/' );
define( 'wssbi\INC', DIR . 'inc/' );
define( 'wssbi\FILE', __FILE__ );
define( 'wssbi\URL', plugin_dir_url( FILE ) );


add_action( 'plugins_loaded', 'wssbi\init' );
function init() {
	require INC . 'wp-show-site-by-ip.class.php';
	new WP_Show_Site_by_IP;
}