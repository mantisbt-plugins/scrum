<?php

# Copyright (c) 2014-2018 Damien Regad
# Licensed under the MIT license

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'config_title' ) );

print_manage_menu();
?>

	<br>
	<form action="<?php echo plugin_page( 'config_edit' ) ?>" method="post">
		<?php echo form_security_field( 'plugin_Scrum_config' ) ?>
		<table class="width100" align="center" style="word-break: break-all;">

			<tr>
				<td class="form-title" colspan="2"><?php echo plugin_lang_get( 'config_title' ) ?></td>
			</tr>

			<!-- Board columns -->
			<?php $t_field = 'board_columns'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
						<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
						<span class="small"><?php echo plugin_lang_get( $t_field . '_info' ) ?></span>
					</label>
				</td>
				<td>
					<table>
						<tr>
							<td class="bold"><?php echo plugin_lang_get( 'board_columns_name' ); ?></td>
							<td class="bold"><?php echo lang_get( 'status' ); ?></td>
						</tr>
						<?php
						$t_config = plugin_config_get( $t_field );
						foreach ( $t_config as $t_key => $t_value ) {
							echo '<tr>';
							echo '<td><input id="' . $t_field . '" name="' . $t_field . '_col[]" size="10" value="' . $t_key . '"></td>';
							echo '<td><input name="' . $t_field . '_status[]" size="10" value="'
								 . implode( ',', $t_value ) . '"></td>';
							echo '</tr>';
						}
						?>
					</table>
					<span class="small"><?php
						printf( plugin_lang_get( 'reference_enum' ),
							lang_get( 'status' ),
							string_attribute( config_get( 'status_enum_string' ) )
						);
						?></span>
				</td>
			</tr>

			<!-- Board Severity Colors -->
			<?php $t_field = 'board_severity_colors'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
					<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
					<span class="small"><?php
						printf(
							plugin_lang_get( 'colors_info' ),
							lang_get( 'severity' )
						); ?></span>
					</label>
				</td>
				<td>
						<textarea id="<?php echo $t_field; ?>" name="<?php echo $t_field; ?>" rows="2" cols="95"><?php
							$t_config = plugin_config_get( $t_field );
							$t_config = json_encode( $t_config );
							echo trim( $t_config, '\{\}' ); ?>
						</textarea>
					<br>
					<span class="small"><?php
						printf(
							plugin_lang_get( 'reference_enum' ),
							lang_get( 'severity' ),
							string_attribute( config_get( 'severity_enum_string' ) )
						); ?>
					</span>
				</td>
			</tr>

			<!-- Board Resolution Colors -->
			<?php $t_field = 'board_resolution_colors'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
					<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
					<span class="small"><?php
						printf(
							plugin_lang_get( 'colors_info' ),
							lang_get( 'resolution' )
						); ?>
					</span>
					</label>
				</td>
				<td>
					<textarea id="<?php echo $t_field; ?>" name="<?php echo $t_field; ?>" rows="2" cols="95"><?php
					$t_config  = plugin_config_get( $t_field );
					$t_config = json_encode( $t_config );
					echo trim( $t_config, '\{\}' );
					?>
					</textarea>
					<br>
					<span class="small"><?php
						printf( plugin_lang_get( 'reference_enum' ),
							lang_get( 'resolution' ),
							string_attribute( config_get( 'resolution_enum_string' ) )
						);
						?></span>
				</td>
			</tr>

			<!-- Sprint Length -->
			<?php $t_field = 'board_sprint_length'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
						<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
						<span class="small"><?php echo plugin_lang_get( $t_field . '_info' ) ?></span>
					</label>
				</td>
				<td>
					<input id="<?php echo $t_field; ?>" name="<?php echo $t_field; ?>" size="5" value="<?php
					echo plugin_config_get( $t_field ); ?>">
				</td>
			</tr>

			<!-- Show Empty Status -->
			<?php $t_field = 'show_empty_status'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
						<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
						<span class="small"><?php echo plugin_lang_get( $t_field . '_info' ) ?></span>
					</label>
				</td>
				<td>
					<input id="<?php echo $t_field; ?>" name="<?php echo $t_field; ?>" type="checkbox" <?php
					check_checked( plugin_config_get( $t_field ) ); ?>>
				</td>
			</tr>

			<!-- Token Expiry -->
			<?php $t_field = 'token_expiry'; ?>
			<tr <?php echo helper_alternate_class() ?>>
				<td class="category">
					<label for="<?php echo $t_field; ?>">
						<?php echo plugin_lang_get( $t_field . '_label' ) ?><br>
						<span class="small"><?php echo plugin_lang_get( $t_field . '_info' ) ?></span>
					</label>
				</td>
				<td>
					<input id="<?php echo $t_field; ?>" name="<?php echo $t_field; ?>" size="5" value="<?php
					echo plugin_config_get( $t_field ); ?>">
				</td>
			</tr>

			<tr>
				<td class="center" colspan="2">
					<input type="submit" value="<?php echo plugin_lang_get( 'action_update' ) ?>" />
				</td>
			</tr>

		</table>
	</form>

<?php
html_page_bottom();
