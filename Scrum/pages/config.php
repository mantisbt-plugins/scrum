<?php
# MantisBT - a php based bugtracking system
# Copyright (C) 2002 - 2013  MantisBT Team - mantisbt-dev@lists.sourceforge.net
# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

auth_reauthenticate( );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'title' ) );

print_manage_menu( );

?>

<br />
<form action="<?php echo plugin_page( 'config_edit' )?>" method="post">
<?php echo form_security_field( 'plugin_scrum_config_update' ) ?>
<table align="center" class="width50" cellspacing="1">

<tr>
	<td class="form-title" colspan="3">
		<?php echo plugin_lang_get( 'title' ) . ': ' . plugin_lang_get( 'config' )?>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category" width=30%>
		<?php echo plugin_lang_get( 'sprint_length' )?>
	</td>
	<td colspan=2>
			<input name="plugin_scrum_sprint_length" size="30" maxlength="30" value="<?php echo plugin_config_get( 'sprint_length' )  ?>"/>
      <br><span class="small"><?php echo plugin_lang_get( 'sprint_length_help' )  ?></span>
	</td>
 </tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'Token_expiry' )?>
	</td>
	<td colspan=2>
			<input name="plugin_scrum_token_expiry" size="30" maxlength="30" value="<?php echo plugin_config_get( 'token_expiry' )  ?>"/>
      <br><span class="small"><?php echo plugin_lang_get( 'token_expiry_help' )  ?></span>
	</td>
</tr>

<tr <?php echo helper_alternate_class( )?>>
	<td class="category">
		<?php echo plugin_lang_get( 'show_empty_status' )?>
	</td>
	<td class="center" width="20%">
		<label><input type="radio" name="plugin_scrum_show_empty_status" value="1" <?php echo( ON == plugin_config_get( 'show_empty_status' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo plugin_lang_get( 'enabled' )?></label>
	</td>
	<td class="center" width="20%">
		<label><input type="radio" name="plugin_scrum_show_empty_status" value="0" <?php echo( OFF == plugin_config_get( 'show_empty_status' ) ) ? 'checked="checked" ' : ''?>/>
			<?php echo plugin_lang_get( 'disabled' )?></label>
	</td>
</tr>

<tr>
	<td class="center" colspan="3">
		<input type="submit" class="button" value="<?php echo lang_get( 'change_configuration' )?>" />
	</td>
</tr>

</table>
</form>

<?php
html_page_bottom();