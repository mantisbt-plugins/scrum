<tr>
<td class="form-title" colspan="<?php echo count($columns) ?>">
<?php echo "Filtrar"; ?>
<form action="<?php echo plugin_page("board") ?>" method="get" name="scrum_form">
<input type="hidden" name="page" value="Scrum/board"/>
<select name="version">
<option value=""><?php echo "Todas as versões"; ?></option>
<?php $released_versions_group = false; ?>
<?php foreach ($versions as $version): ?>
<?php $version_text = string_display_line($version);
if ($version_info[$version_text]["released"] == 1 && !$released_versions_group){ echo "<optgroup label=\"Released\">"; $released_versions_group = true; }
?>
<option value="<?php echo string_attribute($version) ?>" <?php if ($version == $target_version) echo 'selected="selected"' ?>><?php echo $version_text; ?></option>
<?php endforeach ?>
<?php if ($released_versions_group){ echo "</optgroup>"; } ?>
</select>
<select name="category">
<option value=""><?php echo "Todos os tipos"; ?></option>
<?php foreach (array_keys($categories) as $category_name): ?>
<option value="<?php echo $category_name ?>" <?php if ($category == $category_name) echo 'selected="selected"' ?>><?php echo $category_name ?></option>
<?php endforeach ?>
</select>
<input type="submit" value="Ok"/>
</form>
</td>
</tr>
