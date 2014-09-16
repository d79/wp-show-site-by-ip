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
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
			add_action( 'plugins_loaded', array($this, 'check') );
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

		function page () {
			$options = get_option('wssbi_settings');
		?>

			<div class="wrap">

				<h2><?php _e('Show Site by IP', 'wssbi'); ?></h2>

				<form action="options.php" method="post">				
					
					<?php settings_fields( 'wssbiPage' ); ?>

					<table class="form-table">
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
			myCodeMirror.setSize("100%", 500);
			</script>
		
		<?php
		}

		function scripts ( $hook ) {
			if ('tools_page_wssbi' != $hook )
				return;
			$cdn = '//cdn.jsdelivr.net/codemirror/4.5.0/';
			wp_enqueue_style( 'codemirror', $cdn.'codemirror.css' );
			wp_enqueue_script( 'codemirror-js', $cdn.'codemirror.min.js' );
			wp_enqueue_script( 'codemirror-xml', $cdn.'mode/xml/xml.js' );
			wp_enqueue_script( 'codemirror-cssjs', $cdn.'mode/css/css.js' );
			wp_enqueue_script( 'codemirror-javascript', $cdn.'mode/javascript/javascript.js' );
			wp_enqueue_script( 'codemirror-htmlmixed', $cdn.'mode/htmlmixed/htmlmixed.js' );
		}

		function save ( $input ) {
			$options = wp_parse_args(get_option('wssbi_settings'), array( 'ips' => array() ));
			$input['ips'] = $options['ips'];
			return $input;
		}

		function check () {
			$ip = $_SERVER['REMOTE_ADDR'];
			$options = wp_parse_args(
				get_option('wssbi_settings'),
				array(
					'ips'  => array(),
					'html' => ''
				)
			);
			if(isset($_GET['wpok']) && !in_array($ip, $options['ips']))
				$options['ips'] []= $ip;
			if(isset($_GET['wpko']) && in_array($ip, $options['ips'])){
				$key = array_search($ip, $options['ips']);
				if($key!==false)
					unset($options['ips'][$key]);
			}
			update_option( 'wssbi_settings', $options );
			if(!in_array($ip, $options['ips'])) {
				echo $options['html'];
				die();
			}
		}

	}
}

if(class_exists('WP_Show_Site_by_IP')) {
	// instantiate the plugin class
	new WP_Show_Site_by_IP();
}
