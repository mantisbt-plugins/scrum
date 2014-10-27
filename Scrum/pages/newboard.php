<script src="plugins/Scrum/files/functions.js" type="text/javascript"></script>
<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

# Tweaked by Ralph Rassweiler (2014)

//error_reporting(1);

require_once("icon_api.php");
require_once("functions.inc.php");

$current_project = helper_get_current_project();
$project_ids = current_user_get_all_accessible_subprojects($current_project);
$project_ids[] = $current_project;

$resolved_threshold = config_get("bug_resolved_status_threshold");

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
$params = array();
$query = "SELECT id FROM {$bug_table} WHERE project_id IN (" . join(", ", $project_ids) . ")";

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

$use_source = plugin_is_loaded("Source");
$resolved_count = get_resolved_count($bug_ids);

$bug_count = count($bug_ids);
if ($bug_count > 0)
{
	$resolved_percent = floor(100 * $resolved_count / $bug_count);
}
else
{
	$resolved_percent = 0;
}

if ($target_version)
{
	foreach($project_ids as $project_id)
	{
		$version_id = version_get_id($target_version, $project_id, true);
		if ($version_id !== false)
		{
			break;
		}
	}

	$timeleft_string = calculate_time_diff($version_id, $sprint_length);
}

html_page_top(plugin_lang_get("board"));

?>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("scrumboard.css") ?>"/>

<br/>
<table class="scrumboard" style="width: 2400px" align="center" cellspacing="0">

<tr>
<td class="form-title" colspan="<?php echo count($columns) ?>">
<?php echo plugin_lang_get("board") ?>
<form action="<?php echo plugin_page("board") ?>" method="get" name="scrum_form">
<input type="hidden" name="page" value="Scrum/board"/>
<select name="version">
<option value=""><?php echo plugin_lang_get("all") ?></option>
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
<option value=""><?php echo plugin_lang_get("all") ?></option>
<?php foreach (array_keys($categories) as $category_name): ?>
<option value="<?php echo $category_name ?>" <?php if ($category == $category_name) echo 'selected="selected"' ?>><?php echo $category_name ?></option>
<?php endforeach ?>
</select>
<input type="submit" value="Go"/>
</form>
</td>
</tr>

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
foreach ($columns as $column => $statuses): 
	$bug_count = count($bugs[$statuses[0]]);
?>
<td style="color: #0a6283; background: rgba(255,255,255,.6);"><?php echo strtoupper($column); if ($bugs[$statuses[0]] != null) echo " (".$bug_count.")"; else { echo " (0)"; } ?></th>
<?php endforeach ?>

</tr>

<tr class="row-1">

<?php foreach ($columns as $column => $statuses): 
	$bug_count = count($bugs[$statuses[0]]);
?>
<td id="scrumcolumn_<?php echo $column; ?>" class="scrumcolumn" ondrop="drop(event)" ondragover="allowDrop(event)" columnstatus="<?php echo $statuses[0]; ?>" style="max-width: <?php echo ($bug_count > 3)?(40*$bug_count):300; ?>">
<?php $first = true; foreach ($statuses as $status): ?>
<?php if (isset($bugs[$status]) || plugin_config_get("show_empty_status")): ?>
<?php if ($first): $first = false; else: ?>
<hr/>
<?php endif ?>
<?php if (isset($bugs[$status])) foreach ($bugs[$status] as $bug):
$sevcolor = $sevcolors[$bug->severity];
$rescolor = $rescolors[$bug->resolution];
?>

<div class="scrumblock <?php set_scrumblock_color($bug->category_id); ?>" id="scrumblock_<?php echo $bug->id; ?>"  draggable="true" ondragstart="drag(event)" bugid="<?php echo $bug->id; ?>">
<!--<p class="priority"><?php //print_status_icon($bug->priority) ?></p>-->
<p class="bugid"></p>
<p class="commits"><?php echo $source_count[$bug->id] ?></p>
<p class="category">
<?php if ($bug->project_id != $current_project) {
	$project_name = project_get_name($bug->project_id);
	echo "<span class=\"project\">{$project_name}</span> - ";
}
//echo category_full_name($bug->category_id, false) 
echo print_bug_link($bug->id) ?>
</p>
<div class="summary"><?php //echo print_bug_link($bug->id) ?><?php echo $bug->summary ?>
</div>
<!-- <p class="severity" style="background: <?php //echo $sevcolor ?>" title="Severity: <?php //echo get_enum_element("severity", $bug->severity) ?>"></p>-->
<!-- <p class="resolution" style="background: <?php //echo $rescolor ?>" title="Resolution: <?php //echo get_enum_element("resolution", $bug->resolution) ?>"></p>-->
<div class="handler">
	<?php if ($bug->handler_id > 0){ ?>
		<?php $emailHash = md5( strtolower( trim( user_get_email($bug->handler_id) ) ) ); ?>
		<img style="vertical-align: inherit" src="http://www.gravatar.com/avatar/<?php echo $emailHash; ?>?s=28&d=mm" />
	<?php } ?>
</div>
<?php 
	$estimate = 0;
	$bug_data = ScrumBugDao::getBugData($bug->id); 
	if ($bug_data != null){ $estimate = $bug_data[0]["estimate"]; }
?>
<?php if ($estimate > 0){ ?> 
	<div class="card-estimate <?php set_card_estimate_style($bug->category_id); ?>"><?php echo $estimate; ?></div>
<?php } ?>
<div class="priority"><img src="<?php print_scrumblock_icon($bug->priority); ?>"></div>
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
