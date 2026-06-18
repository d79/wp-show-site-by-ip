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
				'ips'                   => array(),
				'url_whitelist_strings' => array(),
				'body'                  => file_get_contents( DIR . 'parts/temp-page-body.html' ),
				'enabled'               => 0,
				'http'                  => 503,
				'wordOk'                => 'wpok',
				'wordKo'                => 'wpko',
				'title'                 => 'Website temporarily offline',
				'head'                  => file_get_contents( DIR . 'parts/temp-page-head.html' )
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
            'confirm_forget' => __( 'Are you sure? The code will be definitely deleted', 'wp-show-site-by-ip' ),
            'forget_old_html_nonce' => wp_create_nonce( 'wssbi_forget_old_html' )
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
			$input['ips']     = isset($_POST['wssbi_iplist']) ? $this->sanitize_ip_rules($_POST['wssbi_iplist']) : $this->options['ips'];
			$input['url_whitelist_strings'] = isset($_POST['wssbi_url_whitelist_strings']) ? $this->sanitize_url_whitelist_strings($_POST['wssbi_url_whitelist_strings']) : $this->options['url_whitelist_strings'];
			$input['wordOk']  = urlencode(sanitize_title($input['wordOk']));
			$input['wordOk']  = empty($input['wordOk']) ? 'wpok' : $input['wordOk'];
			$input['wordKo']  = urlencode(sanitize_title($input['wordKo']));
			$input['wordKo']  = empty($input['wordKo']) ? 'wpko' : $input['wordKo'];
			$input['title']   = wp_strip_all_tags($input['title'], true);
			$input['body']    = stripslashes($_POST['wssbieditor']);
			$input['http']    = (int) $input['http'];
			if( ! ($input['http']>100 && $input['http']<600) )
				$input['http'] = 503;
			$ip = $this->get_client_ip();
			if( $ip && !in_array($ip, $input['ips'], true) ) {
				$input['ips'] []= $ip;
				$input['ips'] = $this->deduplicate_ip_rules( $input['ips'] );
			}
			return $input;
		}

		function check () {
			$ip = $this->get_client_ip();
			$request_uri = $this->get_request_uri();
			$options =& $this->options;
			$options_modified = false;
			if( isset($_GET[$options['wordOk']]) && $ip && !$this->has_ip_access($ip, $options['ips']) ) {
				$options['ips'] []= $ip;
				$options['ips'] = $this->deduplicate_ip_rules( $options['ips'] );
				$options_modified = true;
			}
			if( isset($_GET[$options['wordKo']]) && $ip && in_array($ip, $options['ips'], true) ) {
				$options['ips'] = array_diff($options['ips'], array($ip));
				$options['ips'] = array_values( $options['ips'] );
				$options_modified = true;
			}
			if( $options_modified ) {
				update_option( 'wssbi_settings', $options );
			}
			$show_temp_page = !wp_doing_cron() && $options['enabled'] && !$this->request_matches_url_whitelist($request_uri, $options['url_whitelist_strings']) && !$this->has_ip_access($ip, $options['ips']) && !$this->should_bypass_filter();
			$show_temp_page = apply_filters( 'wssbi_show_temp_page', $show_temp_page, $ip, $options );
			if( $show_temp_page ) {
				header('HTTP/1.1 '.$options['http']);
				header('Retry-After: 3600');
				extract($options);
				require DIR . 'parts/temp-page-tpl.php';
				die();
			}
		}

		function ip() {
			return $this->get_client_ip();
		}

		function get_client_ip() {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : null;
			$ip = is_string( $ip ) ? trim( $ip ) : null;
			$ip = apply_filters( 'wssbi_client_ip', $ip, $_SERVER );
			if( ! is_string( $ip ) ) {
				return null;
			}
			return $this->normalize_ip( trim( $ip ) );
		}

		function get_request_uri() {
			$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : null;
			if( ! is_string( $request_uri ) ) {
				return null;
			}
			$request_uri = trim( $request_uri );
			return '' === $request_uri ? null : $request_uri;
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
			if( ! current_user_can( apply_filters( 'wssbi_manage_options', 'manage_options' ) ) ) {
				wp_send_json_error();
			}
			if( ! check_ajax_referer( 'wssbi_forget_old_html', 'nonce', false ) ) {
				wp_send_json_error();
			}
			delete_option( 'wssbi_html_old' );
			wp_send_json_success();
		}

		function sanitize_ip_rules( $list ) {
			$rules = array();
			foreach ( $this->get_config_lines( $list ) as $line ) {
				$rule = $this->sanitize_ip_rule( $line );
				if( null !== $rule ) {
					$rules []= $rule;
				}
			}
			$rules = $this->deduplicate_entries( $rules );
			return apply_filters( 'wssbi_ip_rules', $rules );
		}

		function _sanitize_ips( $list ) {
			return $this->sanitize_ip_rules( $list );
		}

		function sanitize_url_whitelist_strings( $list ) {
			$strings = $this->deduplicate_entries( $this->get_config_lines( $list ) );
			return $strings;
		}

		function has_ip_access( $ip, $rules = null ) {
			if( ! $ip ) {
				return false;
			}
			$normalized_ip = $this->normalize_ip( $ip );
			if( ! $normalized_ip ) {
				return false;
			}
			if( null === $rules ) {
				$rules = $this->options['ips'];
			}
			foreach ( $rules as $rule ) {
				if( $this->ip_matches_rule( $normalized_ip, $rule ) ) {
					return true;
				}
			}
			return false;
		}

		function _ip_in_ips( $ip ) {
			return $this->has_ip_access( $ip );
		}

		function request_matches_url_whitelist( $request_uri, $rules = null ) {
			if( ! is_string( $request_uri ) || '' === $request_uri ) {
				return false;
			}
			if( null === $rules ) {
				$rules = $this->options['url_whitelist_strings'];
			}
			foreach ( $rules as $rule ) {
				if( '' !== $rule && false !== strpos( $request_uri, $rule ) ) {
					return true;
				}
			}
			return false;
		}

		function ip_matches_rule( $ip, $rule ) {
			$normalized_ip = $this->normalize_ip( $ip );
			$normalized_rule = $this->sanitize_ip_rule( $rule );
			$matches = false;

			if( $normalized_ip && $normalized_rule ) {
				if( false === strpos( $normalized_rule, '*' ) ) {
					$matches = ( $normalized_ip === $normalized_rule );
				} else {
					$ip_version = $this->get_ip_version( $normalized_ip );
					$rule_version = $this->get_rule_version( $normalized_rule );
					if( $ip_version && $ip_version === $rule_version ) {
						$ip_segments = $this->get_ip_segments( $normalized_ip, $ip_version );
						$rule_segments = $this->get_rule_segments( $normalized_rule, $rule_version );
						$matches = ( count( $ip_segments ) === count( $rule_segments ) );
						foreach ( $rule_segments as $index => $segment ) {
							if( ! $matches ) {
								break;
							}
							if( '*' !== $segment && $segment !== $ip_segments[ $index ] ) {
								$matches = false;
							}
						}
					}
				}
			}

			return (bool) apply_filters( 'wssbi_ip_rule_matches', $matches, $normalized_ip, $normalized_rule );
		}

		function sanitize_ip_rule( $rule ) {
			if( ! is_string( $rule ) ) {
				return null;
			}
			$rule = strtolower( trim( $rule ) );
			if( '' === $rule ) {
				return null;
			}
			if( false === strpos( $rule, '*' ) ) {
				return $this->normalize_ip( $rule );
			}
			if( false !== strpos( $rule, '.' ) && false === strpos( $rule, ':' ) ) {
				return $this->normalize_ipv4_rule( $rule );
			}
			if( false !== strpos( $rule, ':' ) && false === strpos( $rule, '.' ) ) {
				return $this->normalize_ipv6_rule( $rule );
			}
			return null;
		}

		function normalize_ip( $ip ) {
			if( ! is_string( $ip ) ) {
				return null;
			}
			$ip = trim( $ip );
			// Loopback addresses such as 127.0.0.1 and ::1 are valid whitelist entries.
			if( '' === $ip || ! filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return null;
			}
			if( false !== strpos( $ip, ':' ) ) {
				$binary = @inet_pton( $ip );
				if( false === $binary ) {
					return null;
				}
				return strtolower( inet_ntop( $binary ) );
			}
			return $ip;
		}

		function normalize_ipv4_rule( $rule ) {
			$segments = explode( '.', $rule );
			if( 4 !== count( $segments ) ) {
				return null;
			}
			$normalized = array();
			foreach ( $segments as $segment ) {
				if( '*' === $segment ) {
					$normalized []= '*';
					continue;
				}
				if( '' === $segment || ! ctype_digit( $segment ) ) {
					return null;
				}
				$value = (int) $segment;
				if( $value < 0 || $value > 255 ) {
					return null;
				}
				$normalized []= (string) $value;
			}
			return implode( '.', $normalized );
		}

		function normalize_ipv6_rule( $rule ) {
			if( preg_match( '/[^0-9a-f:\*]/', $rule ) ) {
				return null;
			}
			if( substr_count( $rule, '::' ) > 1 ) {
				return null;
			}
			$double_colon_pos = strpos( $rule, '::' );
			if( false !== $double_colon_pos ) {
				$left_part = substr( $rule, 0, $double_colon_pos );
				$right_part = substr( $rule, $double_colon_pos + 2 );
				$left = '' === $left_part ? array() : explode( ':', $left_part );
				$right = '' === $right_part ? array() : explode( ':', $right_part );
			} else {
				$left = explode( ':', $rule );
				$right = array();
			}

			$left = $this->normalize_ipv6_rule_groups( $left );
			$right = $this->normalize_ipv6_rule_groups( $right );
			if( null === $left || null === $right ) {
				return null;
			}

			$total_groups = count( $left ) + count( $right );
			if( false === $double_colon_pos ) {
				if( 8 !== $total_groups ) {
					return null;
				}
				$groups = array_merge( $left, $right );
			} else {
				if( $total_groups >= 8 ) {
					return null;
				}
				$groups = array_merge( $left, array_fill( 0, 8 - $total_groups, '0' ), $right );
			}

			return implode( ':', $groups );
		}

		function normalize_ipv6_rule_groups( $groups ) {
			$normalized = array();
			foreach ( $groups as $group ) {
				if( '' === $group ) {
					return null;
				}
				if( '*' === $group ) {
					$normalized []= '*';
					continue;
				}
				if( ! preg_match( '/^[0-9a-f]{1,4}$/', $group ) ) {
					return null;
				}
				$normalized []= strtolower( dechex( hexdec( $group ) ) );
			}
			return $normalized;
		}

		function get_ip_version( $ip ) {
			if( false !== strpos( $ip, ':' ) ) {
				return 6;
			}
			if( false !== strpos( $ip, '.' ) ) {
				return 4;
			}
			return null;
		}

		function get_rule_version( $rule ) {
			return $this->get_ip_version( $rule );
		}

		function get_ip_segments( $ip, $version ) {
			if( 4 === $version ) {
				return explode( '.', $ip );
			}
			return $this->expand_ipv6_to_groups( $ip );
		}

		function get_rule_segments( $rule, $version ) {
			if( 4 === $version ) {
				return explode( '.', $rule );
			}
			return explode( ':', $rule );
		}

		function expand_ipv6_to_groups( $ip ) {
			$binary = @inet_pton( $ip );
			if( false === $binary ) {
				return array();
			}
			$hex = unpack( 'H*', $binary );
			$groups = str_split( $hex[1], 4 );
			return array_map( array( $this, 'normalize_ipv6_group' ), $groups );
		}

		function normalize_ipv6_group( $group ) {
			$group = strtolower( ltrim( $group, '0' ) );
			return '' === $group ? '0' : $group;
		}

		function get_config_lines( $list ) {
			$lines = explode( "\n", (string) $list );
			$normalized = array();
			foreach ( $lines as $line ) {
				$line = trim( $line );
				if( '' === $line || $this->is_comment_line( $line ) ) {
					continue;
				}
				$normalized []= $line;
			}
			return $normalized;
		}

		function is_comment_line( $line ) {
			return 0 === strpos( $line, '#' );
		}

		function deduplicate_entries( $rules ) {
			$deduplicated = array();
			foreach ( $rules as $rule ) {
				if( ! in_array( $rule, $deduplicated, true ) ) {
					$deduplicated []= $rule;
				}
			}
			return array_values( $deduplicated );
		}

		function deduplicate_ip_rules( $rules ) {
			return $this->deduplicate_entries( $rules );
		}

		function should_bypass_filter() {
			$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
			$path = parse_url($uri, PHP_URL_PATH);

			if (!is_string($path) || $path === '') {
				return false;
			}

			$path = '/' . ltrim(rawurldecode($path), '/');

			if (is_admin()) {
				return true;
			}

			if (function_exists('wp_doing_ajax') && wp_doing_ajax()) {
				return true;
			}

			if (function_exists('wp_doing_cron') && wp_doing_cron()) {
				return true;
			}

			$allowed_exact_paths = [
				'/robots.txt',
				'/favicon.ico',
				'/ads.txt',
				'/app-ads.txt',
				'/browserconfig.xml',
				'/site.webmanifest',
				'/manifest.json',
				'/.well-known/security.txt',
			];

			if (in_array($path, $allowed_exact_paths, true)) {
				return true;
			}

			$allowed_path_prefixes = [
				'/.well-known/acme-challenge/',
				'/wp-json/',
			];

			foreach ($allowed_path_prefixes as $prefix) {
				if (strpos($path, $prefix) === 0) {
					return true;
				}
			}

			$allowed_patterns = [
				'#^/wp-sitemap.*\.xml$#i',
				'#^/sitemap(_index)?\.xml$#i',
				'#^/[a-z0-9_\-]+-sitemap.*\.xml$#i',
			];

			foreach ($allowed_patterns as $pattern) {
				if (preg_match($pattern, $path)) {
					return true;
				}
			}

			$allowed_static_extensions = [
				'css',
				'js',
				'mjs',
				'map',
				'jpg',
				'jpeg',
				'png',
				'gif',
				'webp',
				'avif',
				'svg',
				'ico',
				'woff',
				'woff2',
				'ttf',
				'otf',
				'eot',
				'pdf',
				'txt',
				'xml',
				'json',
				'webmanifest',
			];

			$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

			if ($extension && in_array($extension, $allowed_static_extensions, true)) {
				return true;
			}

			return false;
		}

	} // class end
}
