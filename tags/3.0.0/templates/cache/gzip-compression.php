<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<div class="wpo_section wpo_group">

	<h3 class="wpo-first-child"><?php _e('Gzip compression settings', 'wp-optimize');?></h3>

	<?php
		if ($wpo_gzip_compression_enabled && false == $wpo_gzip_compression_settings_added) {
			?>
			<div class="wpo-fieldgroup">
				<p><span class="dashicons dashicons-info"></span> <?php _e('Gzip compression has been enabled by something other than WP-Optimize.', 'wp-optimize'); ?></p>
			</div>
			<?php
		}

	?>
	<div class="wpo-fieldgroup">
		<span><?php _e("This option improves the performance of your website and decreases its loading time. When a visitor makes a request, the server compresses the requested page before transfering it leading to smaller filesizes and faster loads.", 'wp-optimize'); ?>
			<?php echo sprintf('<a href="%s" target="_blank">%s</a>', $info_link, __('Follow this link to get more information about Gzip compression.', 'wp_optimize')); ?>
		</span>

		<p id="wpo_gzip_compression_status" class="<?php echo $class_name; ?>">
			<strong class="wpo-enabled"><?php _e('Gzip compression is currently ENABLED.', 'wp-optimize'); ?></strong>
			<strong class="wpo-disabled"><?php _e('Gzip compression is currently DISABLED.', 'wp-optimize'); ?></strong>
			<?php if (!$wp_optimize->is_apache_server() || ($wpo_gzip_compression_enabled && false == $wpo_gzip_compression_settings_added)) : ?>
				<a href="#" class="wpo-refresh-gzip-status" title="<?php esc_attr_e('Press this to see if any changes were made to your GZIP configuration', 'wp-optimize'); ?>"><?php _e('Check status again', 'wp-optimize'); ?> <img class="wpo_spinner display-none" src="<?php echo esc_attr(admin_url('images/spinner-2x.gif')); ?>"></a>
			<?php endif; ?>
		</p>
		<br>
		<?php

		// add gzip compression section only if gzip compression disabled or we added cache settings to .htaccess.
		if (is_wp_error($wpo_gzip_compression_enabled) || false == $wpo_gzip_compression_enabled || $wpo_gzip_compression_settings_added) {

			if ($wp_optimize->is_apache_server()) {
				$button_text = (!is_wp_error($wpo_gzip_compression_enabled) && $wpo_gzip_compression_enabled) ? __('Disable', 'wp-optimize') : __('Enable', 'wp-optimize');
				?>
				<form>
					<button class="button-primary" type="button"
							id="wp_optimize_gzip_compression_enable" data-enable="<?php echo $wpo_gzip_compression_enabled ? '0' : '1'; ?>"><?php echo $button_text; ?></button>
						<img class="wpo_spinner display-none" src="<?php echo esc_attr(admin_url('images/spinner-2x.gif')); ?>"
						width="20" height="20" alt="...">
					<br>
				</form>
			<?php
			} else {
				echo sprintf('<a href="%s" target="_blank">%s</a>', $faq_link, __('Follow this link to read the article about how to enable Gzip compression with your server software.', 'wp_optimize'));
			}
		}
	?>
	</div>

	<div id="wpo_gzip_compression_error_message">
		<?php
		if (is_wp_error($wpo_gzip_compression_enabled)) {
			echo htmlspecialchars($wpo_gzip_compression_enabled->get_error_message());
		}
		?>
	</div>
	<pre id="wpo_gzip_compression_output" style="display: none;"></pre>

</div>
