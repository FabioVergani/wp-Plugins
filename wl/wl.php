<?php
/*
Plugin Name: WL Framework
#Author: Fabio Vergani
*/
if(is_admin()){
 add_action('admin_init',function(){
	remove_action('init','wp_version_check');
	remove_action('admin_notices','update_nag');
	remove_action('admin_head','wp_admin_canonical_url');
	add_filter('admin_title',function($r){return str_replace('&#8212; WordPress','',$r);});
	$f=function(){return true;};
	add_filter('automatic_updater_disabled',$f);
	$f=function(){return '';};
	add_filter('admin_footer_text',$f);
	add_filter('update_footer',$f);
	$f=function(){return false;};
	$t='auto_update_';
	add_filter($t.'core',$f);
	add_filter($t.'plugin',$f);
	add_filter($t.'theme',$f);
	$t='admin_color_scheme_picker';
	remove_action($t,$t);
	unset($t,$f);
	add_action('admin_head',function(){
	 $Content_HelpSideBar='';
	 $o=get_current_screen();
	 $s='dashboard';
	 $t=$s.'_';
	 $id=($o->id);
	 if($id==$s){//ScreenId:dashboard!
		foreach([
		 $t.'quick_press',
		 $t.'recent_drafts',
		 $t.'primary',
		 $t.'secondary'
		] as $v){remove_meta_box($v,$s,'side');};
		foreach([
		 $t.'right_now',
		 $t.'recent_comments',
		 $t.'incoming_links',
		 $t.'plugins'
		] as $v){remove_meta_box($v,$s,'normal');};
		unset($v);
		$s='welcome_panel';
		remove_action($s,'wp_'.$s);
		add_action($s,function(){
		 $m=['<div id="welcome-panel"><style scoped>a.dashicons{vertical-align:middle;width:auto;font:1em/1em sans-serif;margin-left:.4em;}a.dashicons:before{font-family:dashicons;margin-right:.2em;}</style>'];
		 foreach([
			['admin-comments','edit-comments.php?comment_status=moderated','Modera i commenti'],
			['admin-tools','tools.php','Tools'],
			['edit','edit.php?post_status=draft&post_type=post','Drafts']
			/*,
			 ['admin-post','edit.php','article'],
			 ['admin-media','upload.php','media'],
			 ['admin-page','edit.php?post_type=page','page'],
			 ['welcome-widgets-menus','widgets.php','widgets'],
			 ['menu','nav-menus.php','menus']
			*/
		 ] as $v){
			$m[]=(join('',['<a href="',admin_url($v[1]),'" class="dashicons dashicons-',$v[0],'">',$v[2],'</a>']));
		 };
		 unset($v);
		 $m[]='</div>';
		 echo(join('',$m));
		});//end:WelcomePanel
		unset($s);
		$o->remove_help_tabs();
	 }else{//notDashboard!
		$t=($o->get_help_sidebar());
		$t=preg_replace('/<p>(<a.*wordpress\.org.*>Forum.+<\/a>|<strong>Per maggiori informazioni:</strong>)<\/p>/i','',$t);
		$t=join('',[
		 '<p style="font-size:12px;white-space:pre;">',
			'Screen-id: <b>',$id,'</b>',"\r\n",
			'post_type: <b>',$o->post_type,'</b>',
		 '</p>',
		 '<span class="pre-exist">',$t,'</span>'
		]);
		$Content_HelpSideBar=$t;
	 };
	#BackEnd:All
	 $o->set_help_sidebar($Content_HelpSideBar);
	 unset($Content_HelpSideBar);
	#HelpTab:DebugInformations
	 if((wp_get_current_user()->user_login)=='admin'){
		$t=function($a,$b,$c,$d)use($o){$o->add_help_tab(array('id'=>$a,'title'=>$b,'content'=>'<pre>'.($d?var_export($c,true):$c).'</pre>'));};
		$f=function($a,$b,$c)use($t){$t($a,$b,$c,true);};
		if($_GET['gdmp']==1){
		 $f('globals','Globals',$GLOBALS);
		}else{
		 $t('debug','Debug',$_SERVER['PHP_SELF'].'<p>Click2dump: <a href="?gdmp=1">$Globals</a> (wait)</p>');
		};
		$f('wpthequery','WP:THEQUERY',$GLOBALS['wp_the_query']);
		$f('wppost','WP:POST',$GLOBALS['post']);
		$f('request','REQUEST',$_REQUEST);
		$f('post','POST',$_POST);
		$f('get','GET',$_GET);
		unset($f);
	 };
	 unset($t,$o);
	});//end:AdminHead
 });//end:AdminInit
#Menu:RemovePage
 add_action('admin_menu',function(){
	foreach([
	 'tools.php',
	 'index.php',
	 'users.php',
	'upload.php',
	 'plugins.php',
	 'edit-comments.php',
	 'edit.php?post_type=page'
	] as $i){
	 remove_menu_page($i);
	};
 });
#Menu:Customize
 add_action('wp_before_admin_bar_render',function(){
	global $wp_admin_bar;
	$o=$wp_admin_bar;
	foreach([
	 'wp-logo',
	 'comments',
	 'new-content',
	 'updates','site-name'
	] as $i){
	 $o -> remove_menu($i);
	};
	$f=function($r,$s,$title,$href,$target='_self'){return array($r=>$s,'title'=>$title,'href'=>$href,'meta'=>array('target'=>$target));};
	$k='thesite';
	$t=get_site_url();
	$o -> add_menu($f('id',$k,'Sito',$t,'_blank'));
	$t=admin_url();
	$o -> add_menu($f('parent',$k,'Plugin',$t.'/plugins.php'));
	$o -> add_menu($f('parent',$k,'Utenti',$t.'/users.php'));
	$o -> add_menu($f('parent',$k,'Esporta',$t.'/export.php'));
	$o -> add_menu($f('parent',$k,'Importa',$t.'/import.php'));
	$o -> add_menu($f('id','dashboard','Dashboard',$t));
	$o -> add_menu($f('id','media','Media',$t.'/upload.php'));
	$t.='/edit.php?post_type=page';
	$o -> add_menu($f('id','pages','Pagine',$t));
	$o -> add_menu($f('parent','pages','View as tree...',$t.'&page=cms-tpv-page-page/'));
 },0);

};
/*
else{//NonAdmin!
	$page=$GLOBALS['pagenow'];
	if(in_array($page,array('wp-login.php','wp-register.php'))){
	 add_action('login_enqueue_scripts',function(){echo '<style type="text/css">.login h1{display:none}</style>';});
	/ *
	 oppure rimuovilo manualmente in:
	 .\site\wp-login.php
	 .\site\wp-admin\install.php
	 .\site\wp-admin\upgrade.php
	 .\site\wp-admin\setup-config.php
	 .\site\wp-admin\maint\repair.php
	* /
	}else{
	 switch($page){
		case 'pippo':echo("");break;
		//default:echo("");
	 }
	};
	unset($page);
};
#echo('done');
*/
?>
