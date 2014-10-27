<?php
	define("IMG_SRC", "/plugins/Scrum/files/img/");

	define("FEATURE_CLASS", "scrumblock-feature");
	define("DEFECT_CLASS", "scrumblock-defect");
	define("IMPROVEMENT_CLASS", "scrumblock-improvement");
	define("TASK_CLASS", "scrumblock-task");	

	define("FEATURE_ESTIMATE_CLASS", "card-estimate-feature");
	define("DEFECT_ESTIMATE_CLASS", "card-estimate-bug");
	define("IMPROVEMENT_ESTIMATE_CLASS", "card-estimate-improvement");
	define("TASK_ESTIMATE_CLASS", "card-estimate-task");

	$FEATURE_IDS = plugin_config_get("bug_category_feature");
	$DEFECT_IDS = plugin_config_get("bug_category_defect");
	$IMPROVEMENT_IDS = plugin_config_get("bug_category_improvement");
	$TASK_IDS = plugin_config_get("bug_category_task");

	define("PRIORITY_LOW", 20);
	define("PRIORITY_HIGH", 40);
	define("PRIORITY_CRITICAL", 50);
	define("PRIORITY_LOW_ICON", plugin_config_get("mantis_home").IMG_SRC."low.png");
	define("PRIORITY_HIGH_ICON", plugin_config_get("mantis_home").IMG_SRC."high.png");
	define("PRIORITY_CRITICAL_ICON", plugin_config_get("mantis_home").IMG_SRC."critical.png");

	function set_scrumblock_color($bug_category_id){
	
		global $FEATURE_IDS, $DEFECT_IDS, $IMPROVEMENT_IDS, $TASK_IDS;

		if (in_array($bug_category_id, $FEATURE_IDS)){ 
			echo FEATURE_CLASS;
		} else if (in_array($bug_category_id, $DEFECT_IDS)) {
			echo DEFECT_CLASS;
		} else if (in_array($bug_category_id, $IMPROVEMENT_IDS)) {
			echo IMPROVEMENT_CLASS;
		} else if (in_array($bug_category_id, $TASK_IDS)) {
            echo TASK_CLASS;
        }
	}

	function print_scrumblock_icon($bug_priority){

		switch ($bug_priority){
			
			case PRIORITY_LOW: echo PRIORITY_LOW_ICON; break;
			case PRIORITY_HIGH: echo PRIORITY_HIGH_ICON; break;
			case PRIORITY_CRITICAL: echo PRIORITY_CRITICAL_ICON; break;
		}
	}

	function calculate_time_diff($version_id, $sprint_length){

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

		return $timeleft_string;
	}

	function set_card_estimate_style($bug_category_id){

		global $FEATURE_IDS, $DEFECT_IDS, $IMPROVEMENT_IDS, $TASK_IDS;

                if (in_array($bug_category_id, $FEATURE_IDS)){
                        echo FEATURE_ESTIMATE_CLASS;
                } else if (in_array($bug_category_id, $DEFECT_IDS)) {
                        echo DEFECT_ESTIMATE_CLASS;
                } else if (in_array($bug_category_id, $IMPROVEMENT_IDS)) {
                        echo IMPROVEMENT_ESTIMATE_CLASS;
                } else if (in_array($bug_category_id, $TASK_IDS)) {
                        echo TASK_ESTIMATE_CLASS;
                } else {
			echo "";
		}
	}

	function get_resolved_count($bug_ids){
		
		foreach ($bug_ids as $bug_id)
		{
		        $bug = bug_get($bug_id);
		        $bugs[$bug->status][] = $bug;

		        $source_count[$bug_id] = $use_source ? count(SourceChangeset::load_by_bug($bug_id)) : "";
		        if ($bug->status >= $resolved_threshold){
	                	$resolved_count++;
        		}
		}
		
		return $resolved_count;
	}

	/*function get_date_filters(){
		
		$date_filters = "";
		$table = plugin_table('project');
		$stmt = " AND target_version IN (SELECT version FROM mantis_project_version_table WHERE id IN (SELECT version_id FROM $table WHERE ";
		$start_date = $_GET["version_start_date"];
		$end_date = $_GET["version_end_date"];
		$has_start = false;

		if ( (isset($start_date) && !empty($start_date)) || (isset($end_date) && !empty($end_date)) ){
			
			$date_filters = $stmt;

			if (isset($start_date) && !empty($start_date)){
			
				$date_filters .= "date_start >= ".mktime(0,0,0, substr($start_date, 5, 2), substr($start_date, 8, 2), substr($start_date, 0, 4))." ";
				$has_start = true;
			}

			if (isset($end_date) && !empty($end_date)){

				if ($has_start){
					$date_filters .= " AND ";
				}
                                $date_filters .= "date_end <= ".mktime(0,0,0, substr($end_date, 5, 2), substr($end_date, 8, 2), substr($end_date, 0, 4))." ";
                        }

			$date_filters .= "))";
		}

		return $date_filters;
	}*/
?>
