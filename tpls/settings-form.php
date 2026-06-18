<form action="options.php" method="post" id="wssbi-form">

	<?php wp_nonce_field( 'wssbi', 'wssbi_field' ); ?>

	<?php settings_fields( 'wssbiPage' ); ?>

	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Enable filter', 'wp-show-site-by-ip'); ?></th>
			<td>
				<div class="cmn-switch">
					<input id="wssbi_enabled" name="wssbi_settings[enabled]" class="cmn-toggle cmn-toggle-round-flat" type="checkbox" value="1" <?php checked( $enabled, 1 ); ?>>
					<label for="wssbi_enabled"></label>
				</div>
				<p class="description"><?php _e('Enable or disable the IP filter', 'wp-show-site-by-ip'); ?> <span class="wssbi-help-tip" title="<?php _e("Don't worry, your IP will be automatically added to the whitelist", 'wp-show-site-by-ip'); ?>"></span></p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('HTTP Status', 'wp-show-site-by-ip'); ?></th>
			<td>
				<p class="description">
					<label for="wssbi_settings[http]">
						<input type="text" name="wssbi_settings[http]" maxlength="3" size="3" value="<?php echo esc_attr( $http ); ?>">
						<?php
						printf(
							__('Choose the %sHTTP Status code%s for the temporary page', 'wp-show-site-by-ip'),
							'<a href="https://en.wikipedia.org/wiki/List_of_HTTP_status_codes" target="_blank">',
							'</a>'
						);
						?>
						<span class="wssbi-help-tip" title="<?php _ex('Default is 503: &quot;The server is currently unavailable (because it is overloaded or down for maintenance)&quot;.', 'HTTP status', 'wp-show-site-by-ip'); ?>"></span>
					</label>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('String OK', 'wp-show-site-by-ip'); ?></th>
			<td>
				<label for="wssbi_settings[wordOk]">
					<input type="text" name="wssbi_settings[wordOk]" value="<?php echo esc_attr( $wordOk ); ?>">
				</label>
				<p class="description" style="display: inline">
					<?php _e('String to append to the URL to see the website', 'wp-show-site-by-ip'); ?>
					<span class="wssbi-help-tip" title="<?php echo esc_attr( sprintf( _x('(e.g. %s)', 'URL example for string ok', 'wp-show-site-by-ip'), sprintf('%s?%s', get_site_url(), $wordOk) ) ); ?>"></span>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('String KO', 'wp-show-site-by-ip'); ?></th>
			<td>
				<label for="wssbi_settings[wordKo]">
					<input type="text" name="wssbi_settings[wordKo]" value="<?php echo esc_attr( $wordKo ); ?>">
				</label>
				<p class="description" style="display: inline">
					<?php _e('String to append to the URL to see the temporary page', 'wp-show-site-by-ip'); ?>
					<span class="wssbi-help-tip" title="<?php echo esc_attr( sprintf( _x('(e.g. %s)', 'URL example for string ko', 'wp-show-site-by-ip'), sprintf('%s?%s', get_site_url(), $wordKo) ) ); ?>"></span>
				</p>
				<p class="description"><?php _e('This removes only exact IP entries; wildcard rules must be edited manually.', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
	</table>

	<table class="form-table hidden">

		<?php do_action( 'wssbi_old_html_notice' ); ?>

		<tr valign="top">
			<td>
				<h4><?php _ex('Page title', 'Temporary page settings', 'wp-show-site-by-ip'); ?></h4>
				<label for="wssbi_settings[title]">
					<input type="text" name="wssbi_settings[title]" value="<?php echo esc_attr( $title ); ?>" class="large-text">
				</label>
				<p class="description"><?php _e('Title tag of the temporary page', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<h4><?php _e('Body content', 'wp-show-site-by-ip'); ?></h4>
				<?php wp_editor( $body, 'wssbieditor', $editor ); ?>
				<p class="description"><?php _e('HTML body content of the temporary page', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<h4><?php _e('Styles & scripts', 'wp-show-site-by-ip'); ?></h4>
				<textarea id="wssbi_head_textarea" name="wssbi_settings[head]" style="display:none"><?php echo esc_textarea( $head ); ?></textarea>
				<div id="wssbi_head_wrap">
					<div id="wssbi_head"><?php echo esc_html($head); ?></div>
				</div>
				<p class="description"><?php _e('Styles and scripts for the temporary page', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
	</table>

	<table class="form-table hidden">
		<tr valign="top">
			<td>
				<h4><?php _e('IPs list', 'wp-show-site-by-ip'); ?></h4>
				<p style="margin: -0.5em 0 1.5em"><?php _e('Here you can manually edit the list of authorized IPs', 'wp-show-site-by-ip'); ?> <span class="wssbi-help-tip" title="<?php _e('Your current IP will not be removed', 'wp-show-site-by-ip'); ?>"></span></p>
				<textarea id="wssbi_iplist_textarea" name="wssbi_iplist" style="display:none"><?php echo esc_textarea( $ip_list_text ); ?></textarea>
				<div id="wssbi_iplist_wrap">
					<div id="wssbi_iplist"><?php echo esc_html( $ip_list_text ); ?></div>
				</div>
				<p class="description"><?php _e('Insert one IP rule per line', 'wp-show-site-by-ip'); ?><br><?php _e('Supported: IPv4, IPv6, loopback addresses such as <code>127.0.0.1</code> and <code>::1</code>, and full-segment wildcards (e.g. <code>123.123.123.*</code> or <code>2001:db8:*:*:*:*:*:*</code>)', 'wp-show-site-by-ip'); ?><br><?php _e('In the IP list, comments can start a line or follow a rule after <code>#</code> (e.g. <code>123.123.123.* # office LAN</code>).', 'wp-show-site-by-ip'); ?><br><?php _e('The plugin currently checks only <code>REMOTE_ADDR</code> and does not automatically trust proxy/CDN headers.', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
		<tr valign="top">
			<td>
				<h4><?php _e('URL whitelist', 'wp-show-site-by-ip'); ?></h4>
				<p style="margin: -0.5em 0 1.5em"><?php _e('Here you can allow requests whose URL contains one of the following strings.', 'wp-show-site-by-ip'); ?></p>
				<textarea id="wssbi_url_whitelist_textarea" name="wssbi_url_whitelist_strings" style="display:none"><?php echo esc_textarea( join("\n", $url_whitelist_strings) ); ?></textarea>
				<div id="wssbi_url_whitelist_wrap">
					<div id="wssbi_url_whitelist"><?php echo esc_html( join("\n", $url_whitelist_strings) ); ?></div>
				</div>
				<p class="description"><?php _e('Insert one string per line. Each string is searched literally inside path and query of the current URL.', 'wp-show-site-by-ip'); ?><br><?php _e('Matching a string bypasses the temporary page only for the current request and does not save the visitor IP.', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>

</form>

<script>
	jQuery(document).ready(function($) {

		$('#wssbieditor, #wssbi_head_textarea, #wssbi_iplist_textarea, #wssbi_url_whitelist_textarea').on('keyup', function(e) {
			window.wssbi_form_changed = true;
		});

	});
</script>

<?php do_action( 'wssbi_after_form' ); ?>
