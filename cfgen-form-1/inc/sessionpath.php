<?php
$cfgenwp_sessionpath = ''; // <=== Update the value of this variable with the proper session path if required


// Don't modify anything below this line
$cfgenwp_phpconfig_sessionsavepath = @session_save_path();
$cfgenwp_phpconfig_sessionsavehandler = ini_get('session.save_handler');


// MANAGING TCP AND MEMCACHE
if($cfgenwp_phpconfig_sessionsavepath
	&& !in_array($cfgenwp_phpconfig_sessionsavehandler, array('memcache', 'memcached'))
	&& !@file_exists($cfgenwp_phpconfig_sessionsavepath)
	){

	$cfgen_session_save_path = array(
									$cfgenwp_sessionpath,
									sys_get_temp_dir(),
									// ipower / fatcow / ipage:
									$_SERVER['DOCUMENT_ROOT'].'/cgi-bin/tmp',
									);

	foreach($cfgen_session_save_path as $cfgen_session_save_path_v){
	
		if($cfgen_session_save_path_v && @file_exists($cfgen_session_save_path_v)){
		
			@session_save_path($cfgen_session_save_path_v);
			// echo '<p><strong>'.$cfgen_session_save_path_v.'</strong> exist</p>';
			break;
			
		} else{
			//echo '<p><strong>'.$cfgen_session_save_path_v.'</strong> does not exist</p>';
		}
	}
}

session_start();
?>