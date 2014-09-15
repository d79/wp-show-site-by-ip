<?php
/*
Plugin Name: WP Show Site by IP
Plugin URI: https://github.com/d79/wp-show-site-by-ip
Description: Hide the website to unknown IPs and show a temporary page instead
Version: 1.0
Author: Dario Candelù
Author URI: http://www.spaziosputnik.it
License: GPL2

Text Domain: wssbi
*/
/*
Copyright 2013 Dario Candelù
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
if ( ! defined( 'ABSPATH' ) ) { exit; /* Exit if accessed directly */ }


if ( ! class_exists( 'WP_Show_Site_by_IP' ) )
{
	class WP_Show_Site_by_IP
	{

		function __construct () {
			add_action( 'admin_menu', array($this, 'menu') );
			add_action( 'admin_init', array($this, 'init') );
		}

		function menu () {
			add_submenu_page( 'tools.php', 'Show Site by IP', 'Show Site by IP', 'manage_options', 'wp_show_site_by_ip', array($this, 'page') );
		}

		function init () {
			register_setting( 'wssbiPage', 'wssbi_settings', array($this, 'sanitize') );
		}

		function page () {
			$options = get_option('wssbi_settings');
		?>

			<div class="wrap">

				<h2><?php _e('Show Site by IP', 'wssbi'); ?></h2>

				<form action="options.php" method="post">				
					
					<?php settings_fields( 'wssbiPage' ); ?>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Title', 'wssbi'); ?></th>
							<td>
								<input type="text" name="wssbi_settings[title]" value="<?php echo $options['title']; ?>" class="regular-text" />
								<p class="description"><?php _e('Title of the temporary page', 'wssbi'); ?></p>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('HTML', 'wssbi'); ?></th>
							<td>
								<textarea cols="50" rows="20" name="wssbi_settings[html]" class="large-text code"><?php echo $options['html']; ?></textarea>
								<p class="description"><?php _e('HTML content of the temporary page', 'wssbi'); ?></p>
							</td>
						</tr>
					</table>	

					<?php submit_button(); ?>

				</form>
			</div>
		
		<?php
		}

		function sanitize ( $input ) {
			return $input;
		}

	}
}

if(class_exists('WP_Show_Site_by_IP')) {
	// instantiate the plugin class
	new WP_Show_Site_by_IP();
}
