<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

$current_project = helper_get_current_project();
$project_ids = current_user_get_all_accessible_subprojects($current_project);
$project_ids[] = $current_project;

html_page_top(plugin_lang_get("board"));

$bug_table = db_get_table("mantis_bug_table");

# Fetch list of target versions in use for the given projects
$query = "SELECT DISTINCT target_version FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ") ORDER BY target_version DESC";
$result = db_query_bound($query);

$versions = array();
while ($row = db_fetch_array($result))
{
	if ($row["target_version"])
	{
		$versions[] = $row["target_version"];
	}
}

# Get the selected target version
$target_version = gpc_get_string("version", "");
if (!in_array($target_version, $versions))
{
	$target_version = "";
}

# Retrieve all bugs with the matching target version
$params = array();
$query = "SELECT id FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ") ";

if ($target_version)
{
	$query .= "AND target_version=" . db_param();
	$params[] = $target_version;
}

$query .= " ORDER BY status ASC, priority DESC, id DESC";
$result = db_query_bound($query, $params);

$bug_ids = array();
while ($row = db_fetch_array($result))
{
	$bug_ids[] = $row["id"];
}

bug_cache_array_rows($bug_ids);
$bugs = array();
$status = array();
$columns = plugin_config_get("board_columns");

foreach ($bug_ids as $bug_id)
{
	$bug = bug_get($bug_id);
	$bugs[$bug->status][] = $bug;
}

?>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("scrumboard.css") ?>"/>

<br/>
<table class="width100 scrumboard" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="<?php echo count($columns) ?>">
<?php echo plugin_lang_get("board") ?>
<form action="<?php echo plugin_page("board") ?>" method="get">
<input type="hidden" name="page" value="Scrum/board"/>
<select name="version">
<option value=""><?php echo plugin_lang_get("all") ?></option>
<?php foreach ($versions as $version): ?>
<option value="<?php echo string_attribute($version) ?>" <?php if ($version == $target_version) echo 'selected="selected"' ?>><?php echo string_display_line($version) ?></option>
<?php endforeach ?>
</select>
<input type="submit" value="Go"/>
</form>
</td>
</tr>

<tr class="row-category">

<?php foreach ($columns as $column => $statuses): ?>
<td><?php echo $column ?></th>
<?php endforeach ?>

</tr>

<tr class="row-1">

<?php foreach ($columns as $column => $statuses): ?>
<td class="scrumcolumn">
<?php $first = true; foreach ($statuses as $status): ?>
<?php if (isset($bugs[$status]) || plugin_config_get("show_empty_status")): ?>
<?php if ($first): $first = false; else: ?>
<hr/>
<?php endif ?>
<?php $status_name = get_enum_element("status", $status); if ($status_name != $column): ?>
<p class="scrumstatus"><?php echo get_enum_element("status", $status) ?></p>
<?php endif ?>
<?php if (isset($bugs[$status])) foreach ($bugs[$status] as $bug): ?>

<div class="scrumblock">
<p class="bugid"><?php echo print_bug_link($bug->id) ?></p>
<p class="commits"></p>
<p class="category"><?php echo category_full_name($bug->category_id, false) ?></p>
<p class="summary"><?php echo $bug->summary ?></p>
</div>

<?php endforeach ?>
<?php endif ?>
<?php endforeach ?>
</td>
<?php endforeach ?>

</tr>
</table>

<?php
html_page_bottom();

