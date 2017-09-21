<?php
if(defined('WP_UNINSTALL_PLUGIN')){
 //function uninstallplugin(){}
 if(is_multisite()){
	$m=function_exists('get_sites')?get_sites():wp_get_sites();
	if(0<sizeof($m)){
	 foreach($m as $o){
		$x=class_exists('WP_Site')?$o->blog_id:$o['blog_id'];
		switch_to_blog($x);
		//uninstallplugin();
	 };
	};
	restore_current_blog();
 }else{
	//uninstallplugin();
 };
}else{
 exit;
};
