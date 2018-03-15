<div class="wrap">

	<h2><?php _e('Show Site by IP', 'wp-show-site-by-ip'); ?></h2>

	<div id="sidebar-col">
		
		<?php require plugin_dir_path( __FILE__ ) . '/settings-sidebar.php'; ?>

	</div>
	
	<div id="main-col">

		<h3><?php _e('Settings', 'wp-show-site-by-ip'); ?></h3>

		<?php settings_errors(); ?>

		<h3 class="nav-tab-wrapper">
			<a class="nav-tab" href="#general"> <?php _e('General settings', 'wp-show-site-by-ip'); ?> </a>
			<a class="nav-tab" href="#page"> <?php _e('Temporary page', 'wp-show-site-by-ip') ?> </a>
		</h3>

		<?php require plugin_dir_path( __FILE__ ) . '/settings-form.php'; ?>

	</div>
	
</div>
