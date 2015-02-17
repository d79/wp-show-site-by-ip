<?php
/*
Plugin Name: WP Show Site by IP
Plugin URI: https://wordpress.org/plugins/wp-show-site-by-ip/
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
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
			add_action( 'plugins_loaded', array($this, 'check') );
			add_action( 'plugin_action_links_' . plugin_basename(__FILE__), array($this, 'link2settings') );
		}

		function menu () {
			add_submenu_page(
				'tools.php',
				_x('Show Site by IP', 'page title', 'wssbi'),
				_x('Show Site by IP', 'menu title', 'wssbi'),
				'manage_options',
				'wssbi',
				array($this, 'page')
			);
		}

		function init () {
			register_setting( 'wssbiPage', 'wssbi_settings', array($this, 'save') );
		}

		function defaults () {
			return wp_parse_args(get_option('wssbi_settings'), array(
				'ips' => array(),
				'html' => '',
				'enabled' => ''
			));
		}

		function page () {
			$options = $this->defaults();
		?>

			<div class="wrap">

				<h2><?php _e('Show Site by IP', 'wssbi'); ?></h2>

				<div class="update-nag" style="display:block">
					<h4><?php _e('Warning: once enabled the IP filter you could not be able to see your website!', 'wssbi'); ?></h4>
					<p><?php printf(__('To allow the access to your website from your internet connection add the string <code>?wpok</code> to the website URL, like this: <br> <code>http://%s?wpok</code>', 'wssbi'), $_SERVER['SERVER_NAME']); ?></p>
					<p><?php printf(__('To remove your IP from the whitelist afterwards (and then go back to see the temporary page instead of your website) add the string <code>?wpko</code> to the website URL, like this: <br> <code>http://%s?wpko</code>', 'wssbi'), $_SERVER['SERVER_NAME']); ?></p>
				</div>

				<form action="options.php" method="post">				
					
					<?php settings_fields( 'wssbiPage' ); ?>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Enable', 'wssbi'); ?></th>
							<td>
								<label for="wssbi_settings[enabled]">
									<input type="checkbox" name="wssbi_settings[enabled]" value="1" <?php checked( $options['enabled'], 1 ); ?> />
									<?php _e('Enable or disable the IP filter', 'wssbi'); ?>
								</label>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('HTML', 'wssbi'); ?></th>
							<td>
								<textarea cols="50" rows="20" id="wssbi_html" name="wssbi_settings[html]" class="large-text code"><?php echo $options['html']; ?></textarea>
								<p class="description"><?php _e('Full HTML content of the temporary page', 'wssbi'); ?></p>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>

				</form>
			</div>
			<script>
			var myCodeMirror = CodeMirror.fromTextArea(document.getElementById("wssbi_html"), {
				lineNumbers: true,
				mode: "htmlmixed"
			});
			myCodeMirror.setSize("90%", 500);
			</script>
		
		<?php
		}

		function scripts ( $hook ) {
			if ('tools_page_wssbi' != $hook )
				return;
			$path = 'lib/codemirror-4.5/';
			wp_enqueue_style( 'codemirror', plugins_url( $path.'codemirror.min.css', __FILE__ ) );
			wp_enqueue_script( 'codemirror-js', plugins_url( $path.'codemirror.min.js', __FILE__ ) );
			wp_enqueue_script( 'codemirror-xml', plugins_url( $path.'mode/xml.min.js', __FILE__ ) );
			wp_enqueue_script( 'codemirror-cssjs', plugins_url( $path.'mode/css.min.js', __FILE__ ) );
			wp_enqueue_script( 'codemirror-javascript', plugins_url( $path.'mode/javascript.min.js', __FILE__ ) );
			wp_enqueue_script( 'codemirror-htmlmixed', plugins_url( $path.'mode/htmlmixed.min.js', __FILE__ ) );
		}

		function save ( $input ) {
			$options = $this->defaults();
			$input['ips'] = $options['ips'];
			return $input;
		}

		function check () {
			$ip = $_SERVER['REMOTE_ADDR'];
			$options = $this->defaults();
			if(isset($_GET['wpok']) && !in_array($ip, $options['ips']))
				$options['ips'] []= $ip;
			if(isset($_GET['wpko']) && in_array($ip, $options['ips'])){
				$key = array_search($ip, $options['ips']);
				if($key!==false)
					unset($options['ips'][$key]);
			}
			update_option( 'wssbi_settings', $options );
			if($options['enabled'] && !in_array($ip, $options['ips'])) {
				echo $options['html'];
				die();
			}
		}

		function link2settings( $links ) {
			array_unshift( $links, '<a href="'. get_admin_url(null, 'tools.php?page=wssbi') .'">'.__('Settings').'</a>' );
			return $links;
		}

	} // class end

	// instantiate the plugin class
	new WP_Show_Site_by_IP();

}
