<?php
$bug_count = 0;

foreach ($columns as $column => $statuses): 
	if (! empty($bugs) && !empty($statuses)){
		if (array_key_exists($statuses[0], $bugs)){
			$bug_count = count($bugs[$statuses[0]]);
		}
	}
?>
<td id="scrumcolumn_<?php echo $column; ?>" class="scrumcolumn" ondrop="drop(event)" ondragover="allowDrop(event)" columnstatus="<?php echo $statuses[0]; ?>" >
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
<p class="priority"><?php //print_status_icon($bug->priority) ?></p>
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

<?php endforeach //bugs ?>
<?php endif ?>
<?php endforeach //statuses ?>
</td>
<?php endforeach //columns ?>