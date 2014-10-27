<?php
	require_once( 'core.php' );

	access_ensure_global_level( plugin_config_get( 'scrum_project_threshold' ) );
	html_page_top( plugin_lang_get( 'scrum_project' ) );
	print_manage_menu( plugin_page('scrum_project') );

	$date_start = "";
	$date_end = "";
	$selected_version = "";

	if (isset($_POST["Salvar"])){

		ScrumProjectDao::saveProject($_POST['scrum_project_version'], $_POST['date_start'], $_POST['date_end']);
	}

	if (isset($_POST["scrum_project_version"])){
		
		$project_data = ScrumProjectDao::getProjectData($_POST["scrum_project_version"]);
		$date_start = date('Y-m-d', $project_data[0]['date_start']);
		$date_end = date('Y-m-d', $project_data[0]['date_end']);
		$selected_version = $_POST["scrum_project_version"];
	}

	$project_versions = ScrumProjectDao::getAllProjectVersions();
?>

<h1 align="center"><?php echo plugin_lang_get( 'scrum_project' ) ?></h1>
<form method="post">
<table border="1" align="center" width="50%">
	<tr>
		<td><?php echo plugin_lang_get('label_project_version'); ?></td>
		<td>
		<select name="scrum_project_version" id="scrum_project_version" onchange="this.form.submit();">
			<option value=""></option>
		<?php
			foreach($project_versions as $pv){
				
				$selected=($selected_version==$pv['id'])?"selected":"";
		?>
			<option value="<?php echo $pv['id']; ?>" <?php echo $selected; ?>><?php echo $pv['name']." - ".$pv['version']; ?></option>
		<?php
			}
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td><?php echo plugin_lang_get('label_date_start'); ?></td>
		<td><input type="date" name="date_start" id="date_start" value="<?php echo $date_start; ?>"></td>
	</tr>
	<tr>
                <td><?php echo plugin_lang_get('label_date_end'); ?></td>
                <td><input type="date" name="date_end" id="date_end" value="<?php echo $date_end; ?>"></td>
        </tr>
	<tr>
		<td colspan="2"><input type="submit" value="Salvar" name="Salvar"></td>
	</tr>
</table>
</form>
<?php
	html_page_bottom();
?>
