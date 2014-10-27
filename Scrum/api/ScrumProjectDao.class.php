<?php

class ScrumProjectDao {
	
	static function getAllProjectVersions(){
	
		$sql = 'SELECT v.id, p.name, v.version 
                        FROM '.db_get_table('mantis_project_table').' p 
                        JOIN '.db_get_table('mantis_project_version_table').' v ON v.project_id = p.id 
                        ORDER BY p.name, v.version ';
		
		return self::toArray(db_query_bound($sql));
	}

	static function getProjectData($version_id){
		
		if (!empty($version_id)){

			$sql = 'SELECT p.date_start, p.date_end 
				FROM '.plugin_table('project').' p 
				WHERE p.version_id = '.$version_id.' ';

			return self::toArray(db_query_bound($sql));
		}
	}

	private static function toArray( $p_db_result ) {

                $result = array();
                while ( $row = db_fetch_array( $p_db_result) ) {
                        $result[] = $row;
                }

                return $result;
        }

	static function saveProject($version_id = "", $date_start = "", $date_end = ""){

		if (!empty($version_id)){

			$sql = 'DELETE FROM '.plugin_table('project').' WHERE version_id = '.$version_id.' ';
			db_query_bound($sql);

			$date_start = (empty($date_start))?date('Y-m-d'):strtotime($date_start);
			$date_end = (empty($date_end))?date('Y-m-d'):strtotime($date_end);
		
			$sql = 'INSERT INTO '.plugin_table("project").'
				VALUES('.$version_id.', '.$date_start.', '.$date_end.') ';

			db_query_bound($sql);
		}
	}
}
