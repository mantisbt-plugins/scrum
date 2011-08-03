<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

$statuses = MantisEnum::getValues(config_get("status_enum_string"));

$current_project = helper_get_current_project();
$project_ids = current_user_get_all_accessible_subprojects($current_project);
$project_ids[] = $current_project;

html_page_top(plugin_lang_get("board"));

$bug_table = db_get_table("mantis_bug_table");

$query = "SELECT id FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ") ORDER BY status ASC, priority DESC, id DESC";
$result = db_query_bound($query);

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
<td class="form-title" colspan="<?php echo count($statuses) ?>">Scrum Board Thingy</td>
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

