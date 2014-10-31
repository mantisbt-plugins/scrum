<?php

class ScrumBugDao{
	
	static function getBugData($bug_id){
		
                if (!empty($bug_id)){

                        $sql = 'SELECT b.estimate 
                                FROM '.plugin_table('bug_data').' b 
                                WHERE b.bug_id = '.$bug_id.' ';

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

	static function saveBug($bug_id = 0, $estimate = 0){

                if ($bug_id > 0){

                        $sql = 'DELETE FROM '.plugin_table('bug_data').' WHERE bug_id = '.$bug_id.' ';
                        db_query_bound($sql);

                        $sql = 'INSERT INTO '.plugin_table("bug_data").'
                                VALUES('.$bug_id.', '.$estimate.') ';
                        db_query_bound($sql);
                }
        }
}
