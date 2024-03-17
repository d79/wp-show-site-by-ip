<?php namespace wssbi;

if ( ! class_exists( 'WP_Show_Site_by_IP' ) )
{
	class WP_Show_Site_by_IP
	{
		private $hook;
		private $options;
		private $old_html;

		function __construct () {
			$this->set_options();
			add_action( 'init', array($this, 'textdomain') );
			add_action( 'admin_menu', array($this, 'menu') );
			add_action( 'admin_init', array($this, 'init') );
			add_action( 'admin_enqueue_scripts', array($this, 'scripts') );
			add_action( 'plugins_loaded', array($this, 'check'), 15 );
			add_action( 'plugin_action_links_' . FILE, array($this, 'link2settings') );
			add_action( 'admin_notices', array($this, 'notice') );
			register_activation_hook( FILE, array($this, 'activate') );
			add_action( 'admin_bar_menu', array($this, 'toolbar'), 999 );
			add_action( 'admin_enqueue_scripts', array($this, 'toolbar_styles') );
			add_action( 'wp_enqueue_scripts', array($this, 'toolbar_styles') );
			add_action( 'wssbi_old_html_notice', array($this, 'old_html') );
			$this->old_html = get_option( 'wssbi_html_old' );
			add_action( 'wp_ajax_wssbi_forget_old_html', array($this, 'forget') );
		}

		function textdomain() {
			load_plugin_textdomain( 'wp-show-site-by-ip', false, DIR . 'languages' );
		}

		function menu () {
			$this->hook = add_submenu_page(
				'tools.php',
				_x('Show Site by IP', 'page title', 'wp-show-site-by-ip'),
				_x('Show Site by IP', 'menu title', 'wp-show-site-by-ip'),
				apply_filters( 'wssbi_manage_options', 'manage_options' ),
				'wssbi',
				array($this, 'page')
			);
			add_action( 'load-' . $this->hook, array($this, 'help') );
		}

		function init () {
			register_setting( 'wssbiPage', 'wssbi_settings', array($this, 'save') );
		}

		function set_options () {
			$this->options = wp_parse_args(get_option('wssbi_settings'), array(
				'ips'     => array(),
				'body'    => file_get_contents( DIR . 'parts/temp-page-body.html' ),
				'enabled' => 0,
				'http'    => 503,
				'wordOk'  => 'wpok',
				'wordKo'  => 'wpko',
				'title'   => 'Website temporarily offline',
				'head'    => file_get_contents( DIR . 'parts/temp-page-head.html' )
			));
		}

		function page () {
			extract($this->options);
			$editor = array(
				'editor_height' => 400
			);
			require DIR . 'tpls/settings-page.php';
		}

		function scripts ( $hook ) {
			if ($this->hook != $hook )
				return;
			wp_enqueue_script( 'wssbi-main', URL . 'js/main.js', array('jquery'), VER );
			wp_localize_script( 'wssbi-main', 'wssbiL10n', array(
				'saveAlert' => __( 'The changes you made will be lost if you navigate away from this page.', 'wp-show-site-by-ip' ),
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'confirm_forget' => __( 'Are you sure? The code will be definitely deleted', 'wp-show-site-by-ip' )
			) );
			wp_enqueue_style( 'wssbi-main', URL . 'css/main.css', VER );
			wp_enqueue_script( 'ace-editor', URL . 'lib/ace-1.2.5/src-min-noconflict/ace.js', '1.2.5' );
			wp_enqueue_script( 'tlite', URL . 'lib/tlite-0.0.5/tlite.min.js', '0.0.5' );
			// help pointer
			require DIR . 'inc/help-pointer.php';
			// old html thickbox
			if( $this->old_html ) {
				add_thickbox();
				wp_enqueue_style( 'wssbi-prism', URL . 'lib/prism/prism.css', VER );
				wp_enqueue_script( 'wssbi-prism', URL . 'lib/prism/prism.js', VER );
			}
		}

		function save ( $input ) {
			check_admin_referer( 'wssbi', 'wssbi_field' );
			$input['enabled'] = (isset($_POST['wssbi_settings']['enabled']) && $_POST['wssbi_settings']['enabled']==1) ? 1 : 0;
			$input['ips']     = isset($_POST['wssbi_iplist']) ? $this->_sanitize_ips($_POST['wssbi_iplist']) : $this->options['ips'];
			$input['wordOk']  = urlencode(sanitize_title($input['wordOk']));
			$input['wordOk']  = empty($input['wordOk']) ? 'wpok' : $input['wordOk'];
			$input['wordKo']  = urlencode(sanitize_title($input['wordKo']));
			$input['wordKo']  = empty($input['wordKo']) ? 'wpko' : $input['wordKo'];
			$input['title']   = wp_strip_all_tags($input['title'], true);
			$input['body']    = stripslashes($_POST['wssbieditor']);
			$input['http']    = (int) $input['http'];
			if( ! ($input['http']>100 && $input['http']<600) )
				$input['http'] = 503;
			$ip = $this->ip();
			if( !in_array($ip, $input['ips']) )
				$input['ips'] []= $ip;
			return $input;
		}

		function check () {
			$ip = $this->ip();
			$options =& $this->options;
			if(isset($_GET[$options['wordOk']]) && !$this->_ip_in_ips($ip)) {
				$options['ips'] []= $ip;
			}
			if(isset($_GET[$options['wordKo']]) && $this->_ip_in_ips($ip)) {
				$options['ips'] = array_diff($options['ips'], array($ip));
			}
			update_option( 'wssbi_settings', $options );
			if(!wp_doing_cron() && ($options['enabled'] && !$this->_ip_in_ips($ip))) {
				header('HTTP/1.1 '.$options['http']);
				header('Retry-After: 3600');
				extract($options);
				require DIR . 'parts/temp-page-tpl.php';
				die();
			}
		}

		function ip() {
			return $_SERVER['REMOTE_ADDR'];
		}

		function link2settings( $links ) {
			array_unshift( $links, '<a href="'. get_admin_url(null, 'tools.php?page=wssbi') .'">'.__('Settings').'</a>' );
			return $links;
		}

		function help() {
			require DIR . 'inc/help-screens.php';
		}

		function notice() {
			if( $this->options['enabled'] ) { ?>
				<div class="notice notice-warning">
				<p>
					<?php _e( 'Warning: the filter by IP is enabled. Your users are seeing the temporary page instead of your website.', 'wp-show-site-by-ip' ); ?>
					<a href="<?php menu_page_url('wssbi'); ?>"><span class="dashicons dashicons-admin-generic" style="text-decoration: none"></span></a>
				</p>
				</div> <?php
			}
		}

		function activate() {
			$settings = get_option('wssbi_settings');
			if( is_array($settings) && isset($settings['html']) ) {
				$this->old_html = $settings['html'];
				update_option( 'wssbi_html_old', $settings['html'] );
			}
		}

		function toolbar( $wp_admin_bar ) {
			$status = 'disabled';
			$text = _x('Filter by IP disabled', 'admin toolbar link', 'wp-show-site-by-ip');
			if( $this->options['enabled'] ) {
				$status =  'enabled';
				$text = _x('Filter by IP enabled', 'admin toolbar link', 'wp-show-site-by-ip');
			}
			$args = array(
				'id'    => 'wssbi',
				'title' => '<span class="ab-icon"></span>' . $text,
				'href'  => admin_url('tools.php?page=wssbi'),
				'meta'  => array(
					'class' => "wssbi-toolbar {$status}",
				)
			);
			$wp_admin_bar->add_node( $args );
		}

		function toolbar_styles() {
			wp_add_inline_style( 'admin-bar', '
				#wp-admin-bar-wssbi .ab-icon:before {
					content: "\f528";
					top: 2px;
				}
				#wp-admin-bar-wssbi.enabled .ab-icon:before {
					content: "\f160";
				color: #6ef77e;
				}
				#wp-admin-bar-wssbi.enabled .ab-item {
				color: #6ef77e;
			}
			' );
		}

		function old_html() {
			if( $this->old_html ) {
				require DIR . 'tpls/old-html-notice.php';
				add_action( 'wssbi_after_form', array($this, 'thickbox') );
			}
		}

		function thickbox() {
			$html = $this->old_html;
			require DIR . 'tpls/old-html-thickbox.php';
		}

		function forget() {
			delete_option( 'wssbi_html_old' );
		}

		function _sanitize_ips( $list ) {
			$lines = explode( "\n", $list );
			$lines = array_filter( $lines, 'trim' );
			$lines = array_map( 'trim', $lines );
			return array_filter( $lines, function( $line ) {
				$line = str_replace( '.*', '.1', $line );
				return filter_var( $line, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 );
			} );
		}

		function _ip_in_ips( $ip ) {
			$arr1 = explode('.', $ip);
			foreach ($this->options['ips'] as $addr) {
				$arr2 = explode('.', $addr);
				foreach ($arr2 as $key => $val) {
					if( $val !== '*' && $val !== $arr1[$key] ) {
						break;
					}
					if( $key === 3 ) { // evey single digit matched
						return true;
					}
				}
			}
			return false;
			// return in_array( $ip, $this->options['ips'] );
		}

	} // class end
}