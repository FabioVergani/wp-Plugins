<?php
/*
Plugin Name: WL Framework
#Author: Fabio Vergani
*/
$UrlReferer=$_SERVER['HTTP_REFERER'];
$UrlAdmin=get_admin_url();
$UrlSite=get_site_url();

$etAdminBar=function($f){
 add_action('wp_before_admin_bar_render',function()use(&$f){
	$o=$GLOBALS['wp_admin_bar'];
	foreach(['edit','wp-logo','updates','comments','new-content','site-name','customize'] as $i){$o -> remove_menu($i);};
	$f($o);
 },0);
};

$etArgMenu=function($r,$s,$a,$b,$c='_self'){return array($r=>$s,'title'=>$a,'href'=>$b,'meta'=>array('target'=>$c));};

#Go:
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
		 $m=[
			'<div id="welcome-panel">',
			 '<style scoped>',
				'a.dashicons{vertical-align:middle;width:auto;font:1em/1em sans-serif;margin-left:.4em;}',
				'a.dashicons:before{font-family:dashicons;margin-right:.2em;}',
			 '</style>'
		 ];
		 foreach([
			['admin-comments','edit-comments.php?comment_status=moderated','Modera i commenti'],
			['admin-tools','tools.php','Tools'],
			['edit','edit.php?post_status=draft&post_type=post','Drafts']/*,
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
		$t=preg_replace('/<p>(<a.*wordpress\.org.*>Forum.+<\/a>|<s.*>Per maggiori informazioni:</s.*>)<\/p>/i','',$t);
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
		$t=function($a,$b,$c,$d)use($o){
		 $o->add_help_tab(array('id'=>$a,'title'=>$b,'content'=>'<pre>'.($d?print_r($c,true):$c).'</pre>'));//print_r||var_export
		};
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
 add_filter('hidden_meta_boxes',function($e){return array_merge($e,array('postexcerpt','slugdiv'));});
 add_filter('post_row_actions',function($e){$p='view';$e[$p]=preg_replace('/<a(.*?)>/','<a$1 target=\"_blank\">&nearr;&nbsp;',$e[$p]);return $e;});
 $etAdminBar(function(&$o)use($UrlSite,$UrlAdmin,&$etArgMenu){
	$f=$etArgMenu;
	$k='thesite';
	$t=$UrlSite;
	$o -> add_menu($f('id',$k,'Sito',$t,'_blank'));
	$t=$UrlAdmin;
	$o -> add_menu($f('parent',$k,'Plugin',$t.'/plugins.php'));
	$o -> add_menu($f('parent',$k,'Utenti',$t.'/users.php'));
	$o -> add_menu($f('parent',$k,'Esporta',$t.'/export.php'));
	$o -> add_menu($f('parent',$k,'Importa',$t.'/import.php'));
	$o -> add_menu($f('id','dashboard','Dashboard',$t));
	$o -> add_menu($f('id','media','Media',$t.'/upload.php'));
	$t.='/edit.php?post_type=page';
	$o -> add_menu($f('id','pages','Pagine',$t));
	$o -> add_menu($f('parent','pages','View as tree...',$t.'&page=cms-tpv-page-page/'));
 });
}else{//NonAdmin!
 $etAdminBar(function(&$o)use($UrlAdmin,$UrlReferer,&$etArgMenu){
	$f=$etArgMenu;
	$o -> add_menu($f('id','backtoreferer','Back to Referer',$UrlReferer));
	$o -> add_menu($f('id','backtodashboard','Dashboard',$UrlAdmin));
	$o -> add_menu($f('id','editcurrentpost','Edit',$UrlAdmin.'post.php?post='.get_the_ID().'&action=edit'));
 });
};
unset($UrlReferer,$UrlAdmin,$UrlSite,$etAdminBar);
#FixCustomPostQueryVars(All)
add_action('pre_get_posts',function($q){if($q->is_category||$q->is_tag){$m=&$q->query_vars;$i='post_type';if(empty($m[$i])){$m[$i]='any';};};});
#Done.
?>
