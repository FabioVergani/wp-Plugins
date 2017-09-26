<?php
/*
Plugin Name: !TinyMce Admin Init
*/
if(defined('ABSPATH')){
 if(is_admin()){
	add_filter('tiny_mce_before_init',function($o){
		$o['forced_root_block']='';
		$o['force_br_newlines']=true;
		$o['force_p_newlines']=false;
		return $o;
	});
 };
}else{
 die;
};