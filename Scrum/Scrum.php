<?php

# Copyright (c) 2011 John Reese
# Licensed under the MIT license

//error_reporting(E_ALL);

class ScrumPlugin extends MantisPlugin
{
	const TOKEN_SCRUM_VERSION = 101;
	const TOKEN_SCRUM_CATEGORY = 102;
	
	public function register(){
		$this->name = plugin_lang_get("title");
		$this->description = plugin_lang_get("description");

		$this->version = "0.3";
		$this->requires = array(
			"MantisCore" => "1.2.6",
		);
		$this->uses = array(
			"Source" => "0.16",
		);

		$this->author = "Ralph Rassweiler";
		$this->contact = "ralphrass@gmail.com";
		$this->url = "http://ralphrass.wordpress.com";
	}

	public function config(){
		return array(

			#Mantis absolute path
			"mantis_home" => "http://localhost/mantis/",

			#$g_status_enum_string = '10:new,20:feedback,30:acknowledged,40:confirmed,50:assigned,60:testing,80:resolved,90:closed';
			"board_columns" => array(
				"To Do" => array(10),
				"Returned" => array(20),
				//"Confirmed" => array(40),
				"Doing" => array(50),
				"Done" => array(80),
				//"Ready for inspection" => array(60),
				//"Inspecting" => array(70),
				"Good to Go" => array(90),
			),

			#$g_severity_enum_string = '10:feature,20:trivial,30:text,40:tweak,50:minor,60:major,70:crash,80:block';
			"board_severity_colors" => array(
				10 => "green",
				20 => "green",
				30 => "green",
				40 => "green",
				50 => "gray",
				60 => "gray",
				70 => "orange",
				80 => "red",
			),

			#$g_resolution_enum_string = '10:open,20:fixed,30:reopened,40:unable to duplicate,50:not fixable,60:duplicate,70:not a bug,80:suspended,90:wont fix';
			"board_resolution_colors" => array(
				10 => "orange",
				20 => "green",
				30 => "red",
				40 => "gray",
				50 => "gray",
				60 => "gray",
				70 => "gray",
				80 => "gray",
				90 => "gray",
			),
			
			#Set the categories ID's wich represent features, improvements, tasks and bugs
			"bug_category_feature" => array(2, 24, 25),

			"bug_category_improvement" => array(3, 26, 27, 51, 52, 53),
	
			"bug_category_task" => array(4, 28, 29),

			"bug_category_defect" => array(5, 9, 22, 23),

			"bug_complexities" => array(
				10 => "low",
				20 => "average",
				30 => "high"
			),

			"token_expiry" => 2592000,  # 30 days,
			"sprint_length" => 1209600, # 14 days (14 * 24 * 60 * 60)
			"show_empty_status" => OFF,
			"scrum_project_threshold" => ADMINISTRATOR,

			#Exclude versions set as obsolete from the filters
			"hide_obsolete_versions" => true
		);
	}

	public function schema(){
		return array(
	
			array("CreateTableSQL", array(plugin_table("project"), "
                                        version_id I NOTNULL UNSIGNED PRIMARY,
                                        date_start I UNSIGNED,
                                        date_end I UNSIGNED
                             ")),
		
			/*array("CreateIndexSQL",
                                        array("idx_scrum_to_project",plugin_table("project"),"project_id")
                             ),*/
		
			/*array("CreateTableSQL", array(plugin_table("complexity"), "
                                        id I NOTNULL UNSIGNED PRIMARY AUTOINCREMENT,
					description C(128) NOTNULL
                             ")),*/
			
			array("CreateTableSQL", array(plugin_table("bug_data"), "
                                        bug_id I NOTNULL UNSIGNED PRIMARY,
                                        estimate I UNSIGNED,
                                        bug_complexity_id I UNSIGNED
                                ")),

			/*array("CreateIndexSQL",
                                        array("idx_scrum_to_bug",plugin_table("bug_data"),"bug_complexity_id")
                             ),*/
		);
	}

	public function menu_manage($event, $user_id) {

                if (access_has_global_level(plugin_config_get("scrum_project_threshold"))) {

			$links = array();

                        $page = plugin_page("scrum_project");
                        $label = plugin_lang_get("scrum_project");
			$link = '<a href="' . string_html_specialchars( $page ) . '">' . $label . '</a>';
			$links[] = $link;

                        return $links;
                }
        }

	public function init(){		
		require_once 'api/ScrumProjectDao.class.php';
		require_once 'api/ScrumBugDao.class.php';
	}

	public function hooks(){
		return array(
			"EVENT_MENU_MAIN" => "menu",
			"EVENT_MENU_MANAGE" => "menu_manage",
			"EVENT_REPORT_BUG_FORM" => "prepare_bug_report",
                        "EVENT_UPDATE_BUG_FORM" => "prepare_bug_update",
                        "EVENT_UPDATE_BUG_STATUS_FORM" => "prepare_bug_status_update",
			"EVENT_UPDATE_BUG" => "save_bug",
                        "EVENT_REPORT_BUG" => "save_bug",
			"EVENT_VIEW_BUG_DETAILS" => "view_bug_details",
		);
	}

	public function menu($event){
		$links = array();
		$links[] = '<a href="' . plugin_page("board") . '">' . plugin_lang_get("board") . '</a>';

		return $links;
	}

	public function prepare_bug_report($event, $project_id){
		
		$this->prepare_bug_report_internal(true);
	}

	public function prepare_bug_update($event, $bug_id){

		$this->prepare_bug_report_internal(false, $bug_id);
	}

	public function prepare_bug_status_update( $event, $bug_id ) {

		$this->prepare_bug_report_internal(true, $bug_id);
	}

	private function prepare_bug_report_internal($verticalLayout, $bug_id = 0){
		
		$bug_estimate = 0;
		$class = helper_alternate_class();
		$class2 = helper_alternate_class();

		if ($bug_id){
			
			$bug = bug_get( $bug_id );
			$bug_data = ScrumBugDao::getBugData($bug_id);

			if ($bug_data){
			
				$bug_estimate = $bug_data[0]['estimate'];
				$bug_complexity = $bug_data[0]['bug_complexity_id'];
			}
		}

		$bug_estimate_label = plugin_lang_get('bug_estimate');
		$bug_input = $this->getBugInput($bug_estimate);

		$complexity_label = plugin_lang_get('complexity_description');
                $complexity_select = $this->getComplexitySelect($bug_complexity);

		if ($verticalLayout){
			$row = "<tr ".$class.">
					<td class=\"category\">".$bug_estimate_label."</td>
					<td colspan=4>".$bug_input."</td>
				</tr>";
			$row .= "<tr ".$class.">
					<td class=\"category\">".$complexity_label."</td>
					<td colspan=4>".$complexity_select."</td>
				</tr>";
		} else {
	
			$row = "<tr ".$class.">
					<td class=\"category\">".$bug_estimate_label."</td>
					<td>".$bug_input."</td>
					<td class=\"category\">".$complexity_label."</td>
					<td colspan=3>".$complexity_select."</td>
				</tr>";
		}

		echo $row;
	}

	private function getBugInput($estimate = 0, $disable=false) {

		$disabled=($disable)?"disabled":"";
		$input = '<input type="text" name="scrum_plugin_estimate" id="scrum_plugin_estimate" value="' . 
				$estimate . '" size="5" maxlength="5" '.$disabled.'>';
                return $input;
        }

	private function getComplexitySelect($selected_complexity = 0){
		
		$options = "<option value=\"0\"></option>";

		foreach (plugin_config_get('bug_complexities') as $key => $description){
			$selected = ($key == $selected_complexity)?"selected":"";
			$options .= "<option value=\"".$key."\" ".$selected.">".$description."</option>";
		}

		$select = "<select name=\"scrum_plugin_complexity\" id=\"scrum_plugin_complexity\">";
		$select .= $options;
		$select .= "</select>";

		return $select;
	}

	public function save_bug($p_event, $p_bug_data, $p_bug_id){
		
		$estimate = gpc_get_int('scrum_plugin_estimate', null);
		$complexity = gpc_get_int('scrum_plugin_complexity', null);

		if ($estimate > 0){
			ScrumBugDao::saveBug($p_bug_id, $estimate, $complexity);
		}
	}

	public function view_bug_details($p_event, $p_bug_id){

		$class = helper_alternate_class();

		$bug_estimate = "";
		$bug_complexity = "";

		if ($p_bug_id){

                        $bug = bug_get( $p_bug_id );
                        $bug_data = ScrumBugDao::getBugData($p_bug_id);
	                if ($bug_data){
                                $bug_estimate = $bug_data[0]['estimate'];
				$bug_complexity = $bug_data[0]['bug_complexity_id'];

				if ($bug_complexity > 0){
					$complexities = plugin_config_get('bug_complexities');
					$bug_complexity = $complexities[$bug_complexity];
				}
			}
		}
	
		$bug_estimate_label = plugin_lang_get('bug_estimate');
		$bug_complexity_label = plugin_lang_get('complexity_description');
                //$bug_input = $this->getBugInput($bug_estimate, true);

                $row = "
                        <tr ".$class.">
                                <td class=\"category\">".$bug_estimate_label."</td>
                                <td>".$bug_estimate."</td>
				<td class=\"category\">".$bug_complexity_label."</td>
				<td colspan=3>".$bug_complexity."</td>
                        </tr>
                ";

                echo $row;
	}
}
?>