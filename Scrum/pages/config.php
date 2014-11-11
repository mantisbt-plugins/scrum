<?php

# Copyright (c) 2014 Damien Regad
# Licensed under the MIT license

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

html_page_top( plugin_lang_get( 'config_title' ) );

//form_security_validate( 'plugin_Scrum_config' );

//var_dump($_POST);
# Retrieve form data

$f_board_columns_col = gpc_get_string_array( 'board_columns_col' );
$f_board_columns_status = gpc_get_string_array( 'board_columns_status' );
$f_board_severity_colors = gpc_get_string( 'board_severity_colors' );
$f_board_resolution_colors = gpc_get_string( 'board_resolution_colors' );
$f_sprint_length = gpc_get_int( 'sprint_length', 14 * ScrumPlugin::DURATION_DAY );
$f_show_empty_status = gpc_get_bool( 'show_empty_status', OFF );
$f_token_expiry = gpc_get_int( 'token_expiry', 30 * ScrumPlugin::DURATION_DAY );


# Process Board Columns
foreach( $f_board_columns_status as &$t_status ) {
	$t_status = explode( ',', $t_status );
}
$t_board_columns = array_combine( $f_board_columns_col, $f_board_columns_status );

# Update config settings
plugin_config_set( 'board_columns', $t_board_columns );
plugin_config_set( 
	'board_severity_colors', 
	MantisEnum::getAssocArrayIndexedByValues( $f_board_severity_colors ) 
);
plugin_config_set( 
	'board_resolution_colors', 
	MantisEnum::getAssocArrayIndexedByValues( $f_board_resolution_colors ) 
);
plugin_config_set( 'sprint_length', $f_sprint_length );
plugin_config_set( 'show_empty_status', $f_show_empty_status );
plugin_config_set( 'token_expiry', $f_token_expiry );

//form_security_purge( 'plugin_Scrum_config' );

//print_successful_redirect( plugin_page( 'config_page', true ) );
