<?php if (!defined('WPO_VERSION')) die('No direct access allowed'); ?>

<table id="optimizations_list" class="widefat">
	<thead>
		<tr>
			<th></th>
			<th><?php _e('Optimization', 'wp-optimize'); ?></th>
			<th><?php _e('Notes', 'wp-optimize'); ?></th>
			<th></th>
	<!--		<th></th>-->
		</tr>
	</thead>
	<tbody>
	<?php
	$optimizations = $optimizer->sort_optimizations($optimizer->get_optimizations());
	$hidden_in_optimizations_list = apply_filters('wpo_hidden_in_optimizations_list', array('images'));

	foreach ($optimizations as $id => $optimization) {
		// If we don't want to show optimization on the first tab.
		if (in_array($id, $hidden_in_optimizations_list)) continue;
		// This is an array, with attributes dom_id, activated, settings_label, info; all values are strings.
		$use_ajax = defined('WP_OPTIMIZE_DEBUG_OPTIMIZATIONS') && WP_OPTIMIZE_DEBUG_OPTIMIZATIONS ? false : true;
		$html = $optimization->get_settings_html($use_ajax);

		$optimize_table_list_disabled = '';
		$optimize_table_list_data_disabled = '';

		// Check if the DOM is optimize-db to generate a list of tables.
		if ('optimize-db' == $html['dom_id']) {
			$table_list = $optimizer->get_table_information();

			// Make sure that optimization_table_inno_db is set.
			if ($table_list['inno_db_tables'] > 0 && 0 == $table_list['is_optimizable']) {
				$optimize_table_list_disabled .= 'disabled';
				$optimize_table_list_data_disabled = 'data-disabled="1"';
				$html['activated'] = '';
			}
		}
		?>
		<tr class="wp-optimize-settings wp-optimize-settings-<?php echo $html['dom_id']; ?>" id="wp-optimize-settings-<?php echo $html['dom_id']; ?>" data-optimization_id="<?php echo esc_attr($id); ?>" data-optimization_run_sort_order="<?php echo $optimization->get_run_sort_order(); ?>" >
		<?php
		if (!empty($html['settings_label'])) {
			?>

			<td class="wp-optimize-settings-optimization-checkbox">
				<input name="<?php echo $html['dom_id']; ?>" id="optimization_checkbox_<?php echo $id; ?>" class="optimization_checkbox" type="checkbox" value="true" <?php if ($html['activated']) echo 'checked="checked"'; ?> <?php echo $optimize_table_list_data_disabled; ?> <?php echo $optimize_table_list_disabled; ?> >

				<img id="optimization_spinner_<?php echo $id; ?>" class="optimization_spinner display-none" src="<?php echo esc_attr(admin_url('images/spinner-2x.gif')); ?>" alt="...">
				</td>

				<td>
				<label for="optimization_checkbox_<?php echo $id; ?>"><?php echo $html['settings_label']; ?></label>

				</td>

				<td id="optimization_info_<?php echo $id; ?>" class="wp-optimize-settings-optimization-info">
					<?php
					if ($use_ajax && array_key_exists('support_ajax_get_info', $html) && $html['support_ajax_get_info']) {
						$last_output = $optimization->get_last_output();
						if ($last_output) {
							echo join('<br>', $last_output);
						} else {
							echo '<span class="wp-optimize-optimization-info-ajax" data-id="'.$id.'">...</span>';
						}
					} else {
						echo join('<br>', $html['info']);
					}
					?>
					</td>

					<td class="wp-optimize-settings-optimization-run">
					<button id="optimization_button_<?php echo $id; ?>_big" class="button button-secondary wp-optimize-settings-optimization-run-button show_on_default_sizes optimization_button_<?php echo $id; ?>" type="button" <?php echo $optimize_table_list_data_disabled; ?> <?php echo $optimize_table_list_disabled; ?> ><?php _e('Run optimization', 'wp-optimize'); ?></button>

					<button id="optimization_button_<?php echo $id; ?>_small" class="button button-secondary wp-optimize-settings-optimization-run-button show_on_mobile_sizes optimization_button_<?php echo $id; ?>" type="button" <?php echo $optimize_table_list_data_disabled; ?> <?php echo $optimize_table_list_disabled; ?> ><?php _e('Go', 'wp-optimize'); ?></button>

					</td>

		<?php } ?>
			</tr>
	<?php } ?>
	</tbody>
</table>
