<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<table id="optimizations_list" class="wp-list-table widefat striped">
	<thead>
		<tr>
			<td class="check-column"><input id="select_all_optimizations" type="checkbox"></td>
			<th><?php esc_html_e('Optimization', 'wp-optimize'); ?></th>
			<th></th>
	<!--		<th></th>-->
		</tr>
	</thead>
	<tbody>
	<?php
	$optimizations = $optimizer->sort_optimizations($optimizer->get_optimizations());

	foreach ($optimizations as $id => $optimization) {
		if ('optimizetables' == $id && false === $does_server_allows_table_optimization) continue;
		// If we don't want to show optimization on the first tab.
		if (false === $optimization->display_in_optimizations_list()) continue;
		// This is an array, with attributes dom_id, activated, settings_label, info; all values are strings.
		$use_ajax = defined('WP_OPTIMIZE_DEBUG_OPTIMIZATIONS') && WP_OPTIMIZE_DEBUG_OPTIMIZATIONS ? false : true;
		$html = $optimization->get_settings_html($use_ajax);

		// Check if the DOM is optimize-db to generate a list of tables.
		if ('optimize-db' == $html['dom_id']) {
			$table_list = $optimizer->get_table_information();

			// Make sure that optimization_table_inno_db is set.
			if ($table_list['inno_db_tables'] > 0 && 0 == $table_list['is_optimizable'] && 0 == $table_list['non_inno_db_tables']) {
				$html['activated'] = '';
			}
		}

		$sensitive_items = array(
			'clean-transient',
			'clean-pingbacks',
			'clean-trackbacks',
			'clean-postmeta',
			'clean-orphandata',
			'clean-commentmeta',
			'clean-usermeta',
		);

		?>
		<tr class="wp-optimize-settings wp-optimize-settings-<?php echo esc_attr($html['dom_id']); ?><?php echo (in_array($html['dom_id'], $sensitive_items)) ? ' wp-optimize-setting-is-sensitive' : ''; ?>" id="wp-optimize-settings-<?php echo esc_attr($html['dom_id']); ?>" data-optimization_id="<?php echo esc_attr($id); ?>" data-optimization_run_sort_order="<?php echo esc_attr($optimization->get_run_sort_order()); ?>" >
		<?php
		if (!empty($html['settings_label'])) {
			?>

			<th class="wp-optimize-settings-optimization-checkbox check-column">
				<input name="<?php echo esc_attr($html['dom_id']); ?>" id="optimization_checkbox_<?php echo esc_attr($id); ?>" class="optimization_checkbox" type="checkbox" value="true" <?php echo (!empty($html['activated'])) ? 'checked="checked"' : 'data-disabled="1" disabled'; ?> >

				<img id="optimization_spinner_<?php echo esc_attr($id); ?>" class="optimization_spinner display-none" src="<?php echo esc_url(admin_url('images/spinner-2x.gif')); ?>" alt="...">
			</th>


			<td>
				<label for="optimization_checkbox_<?php echo esc_attr($id); ?>"><?php echo esc_html($html['settings_label']); ?></label>
				<div class="wp-optimize-settings-optimization-info" id="optimization_info_<?php echo esc_attr($id); ?>">
				<?php
				if ($use_ajax && array_key_exists('support_ajax_get_info', $html) && $html['support_ajax_get_info']) {
					$last_output = $optimization->get_last_output();
					if ($last_output) {
						echo join('<br>', $last_output);
					} else {
						echo '<span class="wp-optimize-optimization-info-ajax" data-id="'.esc_attr($id).'">...</span>';
					}
				} else {
					echo join('<br>', $html['info']);
				}
				?>
				</div>
			</td>

			<td class="wp-optimize-settings-optimization-run">
				<button id="optimization_button_<?php echo esc_attr($id); ?>_big" class="button button-secondary wp-optimize-settings-optimization-run-button show_on_default_sizes optimization_button_<?php echo esc_attr($id); ?>" type="button" <?php echo ('' == $html['activated']) ? 'data-disabled="1" disabled' : ''; ?> ><?php esc_html_e('Run optimization', 'wp-optimize'); ?></button>

				<button id="optimization_button_<?php echo esc_attr($id); ?>_small" class="button button-secondary wp-optimize-settings-optimization-run-button show_on_mobile_sizes optimization_button_<?php echo esc_attr($id); ?>" type="button" <?php echo ('' == $html['activated']) ? 'data-disabled="1" disabled' : ''; ?> ><?php esc_html_e('Go', 'wp-optimize'); ?></button>
			</td>

		<?php } ?>
		</tr>
	<?php } ?>
	</tbody>
</table>