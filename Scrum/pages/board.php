<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

require_once("icon_api.php");

$current_project = helper_get_current_project();
$project_ids = current_user_get_all_accessible_subprojects($current_project);
$project_ids[] = $current_project;

$resolved_threshold = config_get("bug_resolved_status_threshold");

$bug_table = db_get_table("mantis_bug_table");
$version_table = db_get_table("mantis_project_version_table");
$tag_table = db_get_table("mantis_bug_tag_table");

# Fetch list of target versions in use for the given projects
$query = "SELECT DISTINCT v.date_order, v.version, b.target_version
	FROM {$version_table} v
	JOIN {$bug_table} b ON b.target_version= v.version
	WHERE v.project_id IN (".join(", ", $project_ids). ")
	ORDER BY v.date_order DESC";

$result = db_query_bound($query);

$versions = array();
while ($row = db_fetch_array($result))
{
	if ($row["version"])
	{
		$versions[] = $row["version"];
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

$columns = plugin_config_get("board_columns");
$sevcolors = plugin_config_get("board_severity_colors");
$rescolors = plugin_config_get("board_resolution_colors");
$sprint_length = plugin_config_get("sprint_length");

# Retrieve all statuses to display on the board
$statuses = array();
foreach($columns as $col)
{
	$statuses = array_merge($statuses, $col);
}

$categories_by_project[ $current_project ] = $category;
token_set( ScrumPlugin::TOKEN_SCRUM_CATEGORY, serialize( $categories_by_project), plugin_config_get('token_expiry') );

#Get selected Tag
$tag = -1;
$tags_by_project = array();
$token_tags_by_project = token_get_value(ScrumPlugin::TOKEN_SCRUM_TAG);

if ( !is_null( $token_tags_by_project ) )
{
	$tags_by_project = unserialize( $token_tags_by_project );
}

if ( gpc_isset("tag") )
{
	$tag = gpc_get_string("tag", "");
} else
{
	if ( array_key_exists( $current_project, $tags_by_project) )
	{
		$tag = $tags_by_project[ $current_project ];
	}
}

$tags_by_project[ $current_project ] = $tag;
token_set( ScrumPlugin::TOKEN_SCRUM_TAG, serialize( $tags_by_project), plugin_config_get('token_expiry') );


# Retrieve all bugs with the matching target version, categories and tag
$params = array();
$query = "SELECT id FROM {$bug_table} b ";

if ($tag > 0) {
  $query .= " JOIN {$tag_table} t ON t.bug_id=b.id AND t.tag_id=" . db_param();
  $params[] = $tag;
}

$query .= " WHERE b.project_id IN (" . join(", ", $project_ids) . ")";

if ($target_version)
{
	$query .= " AND b.target_version=" . db_param();
	$params[] = $target_version;
}
if ($category_name)
{
	$query .= " AND b.category_id IN (" . join(", ", $category_ids) . ")";
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

$rem_work = array();
$est_work = array();
$act_work = array();
$columns = plugin_config_get("board_columns");
$sevcolors = plugin_config_get("board_severity_colors");
$rescolors = plugin_config_get("board_resolution_colors");
$sprint_length = plugin_config_get("sprint_length");

$use_source = plugin_is_loaded("Source");
$resolved_count = 0;

$rem_work_total = 0;
$est_work_total = 0;
$act_work_total = 0;
foreach ($bug_ids as $bug_id)
{
	$bug = bug_get($bug_id);
	$bugs[$bug->status][] = $bug;

	$source_count[$bug_id] = $use_source ? count(SourceChangeset::load_by_bug($bug_id)) : "";
	if ($bug->status >= $resolved_threshold)
	{
		$resolved_count++;
	}
	$t_field_id = custom_field_get_id_from_name("Rem.Work");
	$rem_work[$bug_id] = custom_field_get_value($t_field_id, $bug_id);
	$t_field_id = custom_field_get_id_from_name("Est.Work");
	$est_work[$bug_id] = custom_field_get_value($t_field_id, $bug_id);
	$t_field_id = custom_field_get_id_from_name("Act.Work");
	$act_work[$bug_id] = custom_field_get_value($t_field_id, $bug_id);
# Patch: BS  24-1-2013 Remaining work is only counted for issues not solved yet, and if remaining work is zero and not resolved than est.work is used
	if ($bug->status < $resolved_threshold)
	{
		$rem_work_total += $rem_work[$bug_id] ;
    if ($rem_work[$bug_id] <= 0)
    {
      $rem_work_total += $est_work[$bug_id];
    }
	}
	$est_work_total += $est_work[$bug_id];
	$act_work_total += $act_work[$bug_id];
}

# Patch: BS 2-1-2013 Solved division by zero
if ($est_work_total > 0)
{
  $workleft_percent = floor(100 - 100 * $rem_work_total / $est_work_total);
}
else
{
  $workleft_percent = 100;
}
 

$bug_count = count($bug_ids);
if ($bug_count > 0)
{
	$resolved_percent = floor(100 * $resolved_count / $bug_count);

	$bug_percentage_by_column = array();
	foreach ($columns as $column => $statuses)
	{
		$bug_count_for_column = 0;

		foreach ($statuses as $l_status)
		{
			if (array_key_exists($l_status, $bugs))
			{
				$bug_count_for_column += count($bugs[$l_status]);
			}
		}

		$bug_percentage_by_column[$column] = $bug_count_for_column / $bug_count * 100;
	}
}
else
{
	$resolved_percent = 0;
	$bug_percentage_by_column = 100 / count($columns);
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

	$version = version_get($version_id);
	$version_date = $version->date_order;
	$now = time();

	$time_diff = $version_date - $now;
	$time_hours = floor($time_diff / 3600);
	$time_days = floor($time_diff / 86400);
	$time_weeks = floor($time_diff / 604800);

	$timeleft_percent = min(100, 100 - floor(100 * $time_diff / $sprint_length));

	if ($time_diff <= 0)
	{
		$timeleft_string = plugin_lang_get("time_up");
	}
	else if ($time_weeks > 1)
	{
		$timeleft_string = $time_weeks . plugin_lang_get("time_weeks");
	}
	else if ($time_days > 1)
	{
		$timeleft_string = $time_days . plugin_lang_get("time_days");
	}
	else if ($time_hours > 1)
	{
		$timeleft_string = $time_hours . plugin_lang_get("time_hours");
	}
}

$rem_work_total = round($rem_work_total);
$est_work_total = round($est_work_total);
$act_work_total = round($act_work_total);

html_page_top(plugin_lang_get("board"));

?>

<link rel="stylesheet" type="text/css" href="<?php echo plugin_file("scrumboard.css") ?>"/>

<br/>
<table class="width100 scrumboard" align="center" cellspacing="1">

<tr>
  <td class="form-title" colspan="<?php echo count($columns) ?>"><?php echo plugin_lang_get("board") ?>
  <form action="<?php echo plugin_page("board") ?>" method="get">
    <input type="hidden" name="page" value="Scrum/board"/>
    <select name="version">
      <option value=""><?php echo plugin_lang_get("all") ?></option>
        <?php foreach ($versions as $version): ?>
          <option value="<?php echo string_attribute($version) ?>" <?php if ($version == $target_version) echo 'selected="selected"' ?>><?php echo string_display_line($version) ?></option>
        <?php endforeach ?>
    </select>
    <select name="category">
      <option value=""><?php echo plugin_lang_get("all") ?></option>
      <?php foreach (array_keys($categories) as $category_name): ?>
        <option value="<?php echo $category_name ?>" <?php if ($category == $category_name) echo 'selected="selected"' ?>><?php echo $category_name ?></option>
      <?php endforeach ?>
		</select>
		<select name="tag" id="tag">
			<?php print_tag_option_list();?>

# Following line needs modificatio of print_api.php of mantis
#			<?php print_tag_option_list(0, $tag);?>

    </select>
    <input type="submit" value="Go"/>
  </form>
</td>
</tr>

<tr>
<td colspan="<?php echo count($columns) ?>">
<div class="scrumbar">
<?php if ($resolved_percent > 50): ?>
<span class="bar" style="width: <?php echo $resolved_percent ?>%"><?php echo "Task count: {$resolved_count} / {$bug_count} ({$resolved_percent}%)" ?></span>
<?php else: ?>
<span class="bar" style="width: <?php echo $resolved_percent ?>%">&nbsp;</span><span><?php echo "Task count: {$resolved_count} / {$bug_count} ({$resolved_percent}%)" ?></span>
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

<tr>
<td colspan="<?php echo count($columns) ?>">
<div class="scrumbar">
<?php if ($workleft_percent > 50): ?>
<span class="bar" title="Est/Act/Rem" style="width: <?php echo $workleft_percent ?>%"><?php echo "Work left: {$est_work_total} / {$act_work_total} / {$rem_work_total} ({$workleft_percent}%)" ?></span>
<?php else: ?>
<span class="bar" title="Est/Act/Rem" style="width: <?php echo $workleft_percent ?>%">&nbsp;</span><span><?php echo "Work left: {$est_work_total} / {$act_work_total} / {$rem_work_total} ({$workleft_percent}%)" ?></span>
<?php endif ?>
</div>

</td>
</tr>

<tr class="row-category">

<?php foreach ($columns as $column => $statuses): ?>
<td><?php echo $column ?></td>
<?php endforeach ?>

</tr>

<tr class="row-1">

<?php foreach ($columns as $column => $statuses): ?>
<td class="scrumcolumn" width="<?php
	echo ($bug_count > 0) ? $bug_percentage_by_column[$column] : $bug_percentage_by_column; ?>%">
<?php $first = true; foreach ($statuses as $status): ?>
<?php if (isset($bugs[$status]) || plugin_config_get("show_empty_status")): ?>
<?php if ($first): $first = false; else: ?>
<hr/>
<?php endif ?>
<?php $status_name = get_enum_element("status", $status); if ($status_name != $column): ?>
<p class="scrumstatus"><?php echo get_enum_element("status", $status) ?></p>
<?php endif ?>
<?php if (isset($bugs[$status])) foreach ($bugs[$status] as $bug):
$sevcolor = $sevcolors[$bug->severity];
$rescolor = $rescolors[$bug->resolution];
?>

<div class="scrumblock">

<p class="priority"><?php print_status_icon($bug->priority) ?></p>
<p class="bugid"></p>
<p class="commits" title="Est/Act/Rem"><?php echo $est_work[$bug->id] ?>/<?php echo $act_work[$bug->id] ?>/<?php echo $rem_work[$bug->id] ?></p>

<?php if ($bug->status >= 80) {
      echo "<p class=\"category_finished\">"; 
      } else {

      echo "<p class=\"category\">"; 
      }
?>
<?php if ($bug->project_id != $current_project) {
	$project_name = project_get_name($bug->project_id);
	echo "<span class=\"project\">{$project_name}</span> - ";
}
echo category_full_name($bug->category_id, false) ?>
</p>
<p class="summary"><?php echo bug_format_summary( $bug->id, SUMMARY_FIELD ); ?></p>
<p class="severity" style="background: <?php echo $sevcolor ?>" title="Severity: <?php echo get_enum_element("severity", $bug->severity) ?>"></p>
<p class="resolution" style="background: <?php echo $rescolor ?>" title="Resolution: <?php echo get_enum_element("resolution", $bug->resolution) ?>"></p>
<p class="handler"><?php echo $bug->handler_id > 0 ? user_get_name($bug->handler_id) : "" ?></p>
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

