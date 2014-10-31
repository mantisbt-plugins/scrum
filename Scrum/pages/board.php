<script src="plugins/Scrum/files/functions.js" type="text/javascript"></script>
<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license
# Tweaked by Ralph Rassweiler (2014)

require_once("icon_api.php");
require_once("functions.inc.php");

$current_project = helper_get_current_project();
$project_ids = current_user_get_all_accessible_subprojects($current_project);
$project_ids[] = $current_project;

$timeleft_percent = 0;

$bug_table = db_get_table("mantis_bug_table");
$version_table = db_get_table("mantis_project_version_table");
$hide_obsolete = plugin_config_get("hide_obsolete_versions")?" AND v.obsolete = false ":"";

# Fetch list of target versions in use for the given projects
$query = "SELECT DISTINCT v.version, b.target_version, v.date_order, v.released FROM {$version_table} v JOIN {$bug_table} b ON b.target_version= v.version WHERE v.project_id IN (".join(", ", $project_ids). ") ".$hide_obsolete." ORDER BY v.released, v.date_order DESC";

//echo $query;

$result = db_query_bound($query);

$versions = array();
$version_info = array();
while ($row = db_fetch_array($result))
{
	if ($row["version"])
	{
		$versions[] = $row["version"];
		$version_info[$row["version"]]["released"] = $row["released"];
	}
}

# Get the selected target version
$versions_by_project = array();
$token_versions_by_project = token_get_value(ScrumPlugin::TOKEN_SCRUM_VERSION);
if( !is_null( $token_versions_by_project ) ) {
	$versions_by_project = unserialize( $token_versions_by_project );
}

if ( gpc_isset("version") )
{
	$target_version = gpc_get_string("version", "");
}
else
{
	if ( array_key_exists( $current_project, $versions_by_project) )
	{
		$target_version = $versions_by_project[ $current_project ];
	}
}

if (!in_array($target_version, $versions))
{
	$target_version = "";
}

$versions_by_project[ $current_project ] = $target_version;
$t_res = token_set( ScrumPlugin::TOKEN_SCRUM_VERSION, serialize( $versions_by_project ), plugin_config_get('token_expiry') );

# Fetch list of categories in use for the given projects
$params = array();
$query = "SELECT DISTINCT category_id FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ") ";

if ($target_version)
{
	$query .= "AND target_version=" . db_param();
	$params[] = $target_version;
}

$result = db_query_bound($query, $params);
$categories = array();
$category_ids = array();
while ($row = db_fetch_array($result))
{
	if ($row["category_id"])
	{
		$category_id = $row["category_id"];
		$category_ids[] = $category_id;
		$category_name = category_full_name($category_id, false);

		if (isset($categories[$category_name]))
		{
			$categories[$category_name][] = $category_id;
		}
		else
		{
			$categories[$category_name] = array($category_id);
		}
	}
}

# Get the selected category
$categories_by_project = array();
$token_categories_by_project = token_get_value(ScrumPlugin::TOKEN_SCRUM_CATEGORY);

if ( !is_null( $token_categories_by_project ) )
{
	$categories_by_project = unserialize( $token_categories_by_project );
}

if ( gpc_isset("category") )
{
	$category = gpc_get_string("category", "");
} else
{
	if ( array_key_exists( $current_project, $categories_by_project) )
	{
		$category = $categories_by_project[ $current_project ];
	}
}

if (isset($categories[$category]))
{
	$category_ids = $categories[$category];
}

//print_r($category_ids);

$categories_by_project[ $current_project ] = $category;
token_set( ScrumPlugin::TOKEN_SCRUM_CATEGORY, serialize( $categories_by_project), plugin_config_get('token_expiry') );

# Retrieve all bugs with the matching target version
//$date_filter = get_date_filters();
$params = array();
$query = "SELECT id FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ") ";
//echo $query;

if ($target_version)
{
	$query .= " AND target_version=" . db_param();
	$params[] = $target_version;
}
if ($category_name)
{
	$query .= " AND category_id IN (" . join(", ", $category_ids) . ")";
}

$query .= " ORDER BY status ASC, priority DESC, category_id, id";
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
$sevcolors = plugin_config_get("board_severity_colors");
$rescolors = plugin_config_get("board_resolution_colors");
$sprint_length = plugin_config_get("sprint_length");

$resolved_count = 0;
$bug_count = 0;

$use_source = plugin_is_loaded("Source");
$resolved_threshold = config_get("bug_resolved_status_threshold");

#Count resolved bugs
foreach ($bug_ids as $bug_id)
{
	$bug = bug_get($bug_id);
	$bugs[$bug->status][] = $bug;

	$source_count[$bug_id] = $use_source ? count(SourceChangeset::load_by_bug($bug_id)) : "";
	if ($bug->status >= $resolved_threshold)
	{
		$resolved_count++;
	}
}

#Calculate resolved percent
$resolved_percent = calculate_resolved_percent($bug_ids, $resolved_count);

#Calculate time diff
$timeleft_string = calculate_time_left($target_version, $project_ids, $sprint_length);

html_page_top(plugin_lang_get("board"));

?>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("scrumboard.css") ?>"/>

<br/>
<table class="scrumboard" style="width: 100%" align="center" cellspacing="0">

<?php include("board_filters.php"); ?>

<tr>
<td colspan="<?php echo count($columns) ?>">
<div class="scrumbar">
<?php if ($resolved_percent > 50): ?>
<span class="bar" style="width: <?php echo $resolved_percent ?>%"><?php echo "{$resolved_count}/{$bug_count} ({$resolved_percent}%)" ?></span>
<?php else: ?>
<span class="bar" style="width: <?php echo $resolved_percent ?>%">&nbsp;</span><span><?php echo "{$resolved_count}/{$bug_count} ({$resolved_percent}%)" ?></span>
<?php endif ?>
</div>

<?php if ($target_version): ?>
<div class="scrumbar">
<?php if ($timeleft_percent > 50): ?>
<span class="bar" style="width: <?php echo $timeleft_percent ?>%"><?php echo $timeleft_string ?></span>
<?php else: ?>
<span class="bar" style="width: <?php echo $timeleft_percent ?>%">&nbsp;</span><span><?php echo $timeleft_string ?></span>
<?php endif ?>
</div>
<?php endif ?>

</td>
</tr>

<tr class="row-category">

<?php 
//WARNING!!!! Must allow only ONE STATUS per column because of the drag-n-drop funcionallity

$bug_count = 0;

foreach ($columns as $column => $statuses): 
	if (! empty($bugs) && !empty($statuses)){
		if (array_key_exists($statuses[0], $bugs)){
			$bug_count = count($bugs[$statuses[0]]);
		}
	}
?>
<td style="color: #0a6283; background: rgba(255,255,255,.6);"><?php echo strtoupper($column); if (array_key_exists($statuses[0], $bugs) && $bugs[$statuses[0]] != null) echo " (".$bug_count.")"; else { echo " (0)"; } ?></th>
<?php endforeach ?>

</tr>

<tr class="row-1">

<?php 
	include("board_content.php");
?>

</tr>
</table>

<?php
html_page_bottom();