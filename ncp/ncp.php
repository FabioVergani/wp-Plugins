<?php
/*
Plugin Name: WL Content: News plugin
#Author: Fabio Vergani
Version:
*/
$i='news';
$n='News';
$k=array('news-category','Categoria della Notizia');
#Init:
add_action('init',function()use($i,$n,$k){
 register_post_type($i,
	array(
	 'labels'=>array(
	 'name'=>$n,
	 'singular_name'=>$n,
	 'add_new'=>'Crea',
	 'add_new_item'=>'Aggiungi una nuova notizia',
	 'all_items' =>':All',
	 'edit_item' => 'Modifica, oppure',
	 'view_item'=>'Preview',
	 'search_items'=>':Search',
	 'not_found'=>'No '.$n.' found',
	 'not_found_in_trash'=>'No '.$n.' found (in Trash)',
	 'menu_name'=>$n,
	 'parent_item_colon'=>''
	),
	'supports'=>array('title','editor','thumbnail','excerpt'),//more:'custom-fields'
	'public'=>true,'publicly_queryable'=>true,'query_var'=>true,
	'show_ui'=>true,
	'show_in_menu'=>false,
	'has_archive'=>true,
	'hierarchical'=>false,
	'exclude_from_search'=>false,
	'rewrite'=>array('slug'=>$i,'with_front'=>false),
	'capability_type'=>'post',
  )
 );
 register_taxonomy($k[0],array($i),array('label'=>$k[1],'hierarchical'=>true,'show_admin_column'=>true,'show_ui'=>true,'rewrite'=>false));
});//EndInit
#Setup:
if(is_admin()){
 add_action('admin_menu',function()use($i,$n){add_menu_page($i,$n,'manage_options','edit.php?post_type='.$i,null,plugins_url('ncp.png',__FILE__),2);});
 add_action('current_screen',function($o)use($i,$n,$k){
	$x=$k[0];
	//CustomHelp:
	$o->add_help_tab(array(
	 'id'=>'ncp-help',
	 'title'=>'CustomPost: '.$n,
	 'content'=>join('',[
		'<p style="font-size:12px;white-space:pre;">',
		 'Per accedere all\'ultima notizia linkare "get_site_url()/'.$i.'/updated"',
		'</p>',
	 ])
	));
	if(($o>id)=='edit-'.$i){
	//CustomRow:
	 add_filter('post_row_actions',function($o){
		$p='view';
		$o[$p]=preg_replace('/<a(.*?)>/','<a$1 target=\"_blank\">&nearr;&nbsp;',$o[$p]);
		return $o;
	 });
	//TaxDropDown:
	 add_action('restrict_manage_posts',function()use($x){
		$s=$_GET[$x];
		$m=array('<select name="'.$x.'" id="'.$x.'" class="postform"><option value="">Tutti i tipi di notizia</option>');
		foreach(get_terms($x) as $o){$m[]='<option value="'.($o->slug).'"'.(($s==($o->slug))?' selected="selected">':'>').($o->name).' (<i>' . $o->count .'</i>)</option>';};
		$m[]='</select>';
		echo join('',$m);
	 });
	 //...
	 add_filter('manage_edit-'.$i.'_sortable_columns',function($a)use($x){$p='taxonomy-'.$x;$a[$p]=$p;return $a;});
	 add_filter('hidden_meta_boxes',function($hidden){return array_merge($hidden,array('postexcerpt','slugdiv'));});
	};
 });
}else{//NonAdmin!
 add_action('pre_get_posts',function($q)use($i){
	$m=&$q->query_vars;
	if($m['post_type']==$i && $m[$i]=='updated'){
	 $m=array('post_type'=>$i,'post_status'=>'publish','orderby'=>'date','posts_per_page'=>1);
	 wp_redirect(get_permalink(get_posts($m)[0]->ID ),200);
	 exit;
	};
 });
};//Endif:IsAdmin
unset($k,$i,$n);
#Done.


?>
