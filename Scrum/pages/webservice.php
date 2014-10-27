<?php
	#Test
	#http://localhost/mantis/plugins/Scrum/pages/webservice.php?json={%22bugid%22:1,%22columnstatus%22:20}
	
	require_once("../../../core.php");

	$json=$_GET ['json'];
	$obj = json_decode($json);

	echo $obj->bugid;

	$posts = array($obj);
	header('Content-type: application/json');

	$sql = "UPDATE ".db_get_table('mantis_bug_table')." set status = ".$obj->columnstatus." where id = ".$obj->bugid." ";
	echo $sql;

	db_query_bound($sql);
?>
