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

		global $timeleft_percent;

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

	function calculate_resolved_percent($bug_ids, $resolved_count){

		global $bug_count;

		$bug_count = count($bug_ids);

		if ($bug_count > 0)
		{
			$resolved_percent = floor(100 * $resolved_count / $bug_count);
		}
		else
		{
			$resolved_percent = 0;
		}

		return $resolved_percent;
	}

	function calculate_time_left($target_version, $project_ids, $sprint_length){

		$timeleft_string = "";

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

		return $timeleft_string;
	}
?>