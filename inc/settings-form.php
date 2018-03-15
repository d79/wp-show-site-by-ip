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
						<input type="text" name="wssbi_settings[http]" maxlength="3" size="3" value="<?php echo $http; ?>">
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
					<input type="text" name="wssbi_settings[wordOk]" value="<?php echo $wordOk; ?>">
				</label>
				<p class="description" style="display: inline">
					<?php _e('String to append to the URL to see the website', 'wp-show-site-by-ip'); ?>
					<span class="wssbi-help-tip" title="<?php printf(_x('(e.g. %s)', 'URL example for string ok', 'wp-show-site-by-ip'), sprintf('%s?%s', get_site_url(), $wordOk)); ?>"></span>
				</p>
			</td>
		</tr>
		<tr valign="top">
			<th scope="row"><?php _e('String KO', 'wp-show-site-by-ip'); ?></th>
			<td>
				<label for="wssbi_settings[wordKo]">
					<input type="text" name="wssbi_settings[wordKo]" value="<?php echo $wordKo; ?>">
				</label>
				<p class="description" style="display: inline">
					<?php _e('String to append to the URL to see the temporary page', 'wp-show-site-by-ip'); ?>
					<span class="wssbi-help-tip" title="<?php printf(_x('(e.g. %s)', 'URL example for string ko', 'wp-show-site-by-ip'), sprintf('%s?%s', get_site_url(), $wordKo)); ?>"></span>
				</p>
			</td>
		</tr>
	</table>

	<table class="form-table hidden">

		<?php do_action( 'wssbi_old_html_notice' ); ?>

		<tr valign="top">
			<td>
				<h4><?php _ex('Page title', 'Temporary page settings', 'wp-show-site-by-ip'); ?></h4>
				<label for="wssbi_settings[title]">
					<input type="text" name="wssbi_settings[title]" value="<?php echo $title; ?>" class="large-text">
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
				<textarea id="wssbi_head_textarea" name="wssbi_settings[head]" style="display:none"><?php echo $head; ?></textarea>
				<div id="wssbi_head_wrap">
					<div id="wssbi_head"><?php echo esc_html($head); ?></div>
				</div>
				<p class="description"><?php _e('Styles and scripts for the temporary page', 'wp-show-site-by-ip'); ?></p>
			</td>
		</tr>
	</table>

	<?php submit_button(); ?>

</form>

<script>
	jQuery(document).ready(function($) {

		$('#wssbieditor, #wssbi_head_textarea').on('keyup', function(e) {
			window.wssbi_form_changed = true;
		});

	});
</script>

<?php do_action( 'wssbi_after_form' ); ?>