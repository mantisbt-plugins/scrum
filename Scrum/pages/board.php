<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

$statuses = MantisEnum::getValues(config_get("status_enum_string"));

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

foreach ($bug_ids as $bug_id)
{
	$bug = bug_get($bug_id);
	$bugs[$bug->status][] = $bug;
}

?>

<br/>
<table class="width100" align="center" cellspacing="1">

<tr>
<td class="form-title" colspan="<?php echo count($statuses) ?>">
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

<?php foreach ($statuses as $status): ?>
<td><?php echo get_enum_element("status", $status) ?></th>
<?php endforeach ?>

</tr>

<tr class="row-1">

<?php foreach ($statuses as $status): ?>
<td style="vertical-align: top">
<?php if (isset($bugs[$status])) foreach ($bugs[$status] as $bug): ?>
<p><?php echo $bug->summary ?></p>
<?php endforeach ?>
</td>
<?php endforeach ?>

</tr>
</table>

<?php
html_page_bottom();

