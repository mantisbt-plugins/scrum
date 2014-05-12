<?php

# authenticate
auth_reauthenticate();
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

# Read results
form_security_validate( 'plugin_scrum_config_update' );
#$f_reporter_select_threshold = gpc_get_int( 'plugin_customreporter_threshold', DEVELOPER );
$f_scrum_token_expiry = gpc_get_int( 'plugin_scrum_token_expiry', 2592000);
$f_scrum_sprint_length = gpc_get_int( 'plugin_scrum_sprint_length', 2419200);
$f_scrum_show_empty_status = gpc_get_int( 'plugin_scrum_show_empty_status', OFF);

# update results
plugin_config_set( 'token_expiry', $f_scrum_token_expiry );
plugin_config_set( 'sprint_length', $f_scrum_sprint_length );
plugin_config_set( 'show_empty_status', $f_scrum_show_empty_status );

form_security_purge( 'plugin_scrum_config_update' );

# redirect
print_successful_redirect( plugin_page( 'config', true ) );
