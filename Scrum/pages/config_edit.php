<?php
# Copyright (C) 2017/2018 Frank Bültge
# Licensed under the MIT license

auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

/**
 * Get the board_columns values.
 */
$t_field_col    = gpc_get_string_array( 'board_columns_col' );
$t_field_status = gpc_get_string_array( 'board_columns_status' );
// Build array for 'board_columns' => array().
$t_board_columns = array(
	$t_field_col[ 0 ] => array_map( 'intval', explode( ',', $t_field_status[ 0 ] ) ),
	$t_field_col[ 1 ] => array_map( 'intval', explode( ',', $t_field_status[ 1 ] ) ),
	$t_field_col[ 2 ] => array_map( 'intval', explode( ',', $t_field_status[ 2 ] ) ),
);
$scrum = new ScrumPlugin( __FILE__ );
// Set new value, if different.
$scrum->config_set( 'board_columns', $t_board_columns );

/**
 * Get the board_severity_colors values.
 */
$t_field_colors = gpc_get_string( 'board_severity_colors' );
// Build array for 'board_severity_colors'.
$t_field_colors = (array) json_decode( '{' . $t_field_colors . '}' );
$tmp_field_colors = array();
foreach ( $t_field_colors as $status => $color ) {
	$tmp_field_colors[ $status ] = $color;
}
// Set new value, if different.
$scrum->config_set( 'board_severity_colors', $tmp_field_colors );

/**
 * Get the board_resolution_colors values.
 */
$t_resolution_colors    = gpc_get_string( 'board_resolution_colors' );
// Build array for 'board_resolution_colors'.
$t_resolution_colors = (array) json_decode( '{' . $t_resolution_colors . '}' );
$tmp_resolution_colors = array();
foreach ( $t_resolution_colors as $status => $color ) {
	$tmp_resolution_colors[ $status ] = $color;
}
// Set new value, if different.
$scrum->config_set( 'board_resolution_colors', $tmp_resolution_colors );

/**
 * Get the board_sprint_length.
 */
$t_sprint_length = gpc_get_int( 'board_sprint_length' );
// Set new value, if different.
$scrum->config_set( 'board_sprint_length', $t_sprint_length );

/**
 * Get show_empty_status and set the new value.
 * Set always to false, if is not in $_POST.
 *
 */
if ( isset( $_POST[ 'show_empty_status' ] ) ) {
	$t_show_empty_status = gpc_get_bool( 'show_empty_status' );
	// Set new value, if different.
	$scrum->config_set( 'show_empty_status', $t_show_empty_status );
} else {
	$scrum->config_set( 'show_empty_status', false );
}

/**
 * Get the token for expire the filter criteria.
 */
$t_token_expiry = gpc_get_int( 'token_expiry' );
// Set new value, if different.
$scrum->config_set( 'token_expiry', $t_token_expiry );

print_successful_redirect( plugin_page( 'config_page', true ) );