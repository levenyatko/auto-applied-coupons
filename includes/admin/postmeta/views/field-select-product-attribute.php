<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	?>

<div class="options_group wc-auto-coupons-field">
	<p class="form-field">
		<label for="<?php echo esc_attr($field_name); ?>"><?php echo esc_html($field_label); ?></label>
		<select id="<?php echo esc_attr($field_name); ?>"
		        name="<?php echo esc_attr($field_name); ?>[]"
		        style="width: 50%;"
		        class="wc-enhanced-select"
		        multiple="multiple"
		        data-placeholder="<?php esc_attr_e( 'No product attributes', 'wcac' ); ?>"
		>
			<?php
				if ( ! empty( $options ) ) {
					foreach ( $options as $attribute_id => $attribute_data ) {
						?>
							<option title="<?php echo esc_attr($attribute_data['title']); ?>"
							        value="<?php echo esc_attr($attribute_id); ?>"
								    <?php selected( in_array( (string) $attribute_id, $selected_values, true ), true); ?>
							>
								<?php echo esc_attr(esc_html($attribute_data['label'])); ?>
							</option>
						<?php
					}
				}
			?>
		</select>
	</p>
</div>
