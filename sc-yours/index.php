<?php
/*
Plugin Name: !Sc Yours
//Fabio Vergani
*/
if(defined('ABSPATH')){
	if(is_admin()){
		function init_SHCDV(){
			$db=&$GLOBALS['wpdb'];
			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			$k=($db->prefix).'SHCDV';
			$db->query('DROP TABLE IF EXISTS '.$k);
			dbDelta('CREATE TABLE '.$k.' ( id mediumint(9) NOT NULL AUTO_INCREMENT, slug varchar(100) NOT NULL, data text, disabled bit default 0, UNIQUE KEY id (id)) '.($db->get_charset_collate()).';');
		}
		function sc_user_defined_page(){
			if(current_user_can('manage_options')){
				$db=&$GLOBALS['wpdb'];
				$k=($db->prefix).'SHCDV';
				$j=isset($_GET[$x='id'])?$_GET[$x]:false;
				$z=admin_url('admin.php?page=sc-user-defined');
				echo '<div class="wrap"><script>window.shorcotesadminpage="',$z,'";</script>';
				$redirect='<script>setTimeout(function(){window.location.href=window.shorcotesadminpage;},400);</script>';
				if(isset($_GET[$x='action'])){
					$action=$_GET[$x];
					if($action!=='save'){
						if($action!=='delete'){
							if($action!=='erase'){
								$slug=$data='';
								$disableoptions='<option value="0" selected>No</option><option value="1">Yes</option>';
								if(!empty($j) && !empty($o=$db->get_row($db->prepare('SELECT slug, data, disabled FROM '.$k.' where id=%d',$j)))){
									if($o->disabled==='1'){$disableoptions='<option value="1" selected>Yes</option><option value="0">No</option>';};
									$slug=$o->slug;
									$data=$o->data;
								};
								echo '<form method="post" action="',$z.'&action=save','">';
								if($action==='edit'){
									echo '<p style="color:#aeaeb4;">sc slug="<b style="color:#12206a;">',$slug,'</b>"</p><input type="hidden" id="slug" name="slug" value="',$slug,'"><input type="hidden" id="existing-id" name="existing-id" value="',$j,'">';
									$x='Save Shortcode';
								}elseif($action==='add'){
									echo '<p>Slug:</p><input type="text" id="slug" name="slug" value="',$slug,'" required style="width:100%;margin:0 0 .6em 0;"><input type="hidden" id="existing-id" name="existing-id" value="">';
									$x='Add Shortcode';
								};
								add_filter('wp_default_editor',function(){return'html';});
								wp_editor($data,'data',array('textarea_name'=>'data','wpautop'=>false));
								echo '<p style="text-align:right;font-size:.8em;color:#9f9f9f;">note: |%nome%| get [sc slug="" nome="valore"]</p><input type="submit" name="submit" id="submit" class="button button-primary" value="',$x,'"><p>Disabled?</p><select id="is-disabled" name="is-disabled">',$disableoptions,'</select>';
							}else{//erase-all
								init_SHCDV();
								echo '<div class="updated"><p>Erased</p></div>',$redirect;
							};
						}else{//deleting
							if(is_numeric($j)){
								if($db->delete($k,array('id'=>$j))!==false){
									echo '<div class="updated"><p>Shortcode has been deleted</p></div>',$redirect;
								}else{
									echo '<div class="error"><p>There was an error deleting your shortcode: id:',$j,'</p></div>';
								};
							}else{
								echo '<div class="error"><p>There was an error deleting your shortcode. id:',$j,'</p></div>';
							};
						};
					}else{//saving
						$slug=isset($_POST[$x='slug'])?sanitize_title($_POST[$x]):'';
						if($j==false && empty($slug)){
							echo '<div class="error"><p>No slug e id.</p></div>',$redirect;
						}else{
							$n=$slug;
							$insert=true;
							$result=false;
							$m=array('data'=>isset($_POST[$x='data'])?esc_html(stripslashes($_POST[$x])):'','disabled'=>(isset($_POST[$x='is-disabled'])?($disabled=($_POST[$x]==='1')?'1':0):0));
							if(isset($_POST[$x='existing-id'])){
								if(is_numeric($x=$_POST[$x])){
									if(is_null($db->get_var($db->prepare('SELECT slug FROM '.$k.' where id=%d',$x)))){
										//
									}else{
										$result=$db->update($k,$m,array('id'=>$x),array('%s','%d'),array('%d'));
										$insert=false;
									};
								};
							};
							if($insert){
								$x=0;
								while(empty($slug)?true:(($db->get_var($db->prepare('SELECT count(slug) FROM '.$k.' where slug=%s',$slug)))!=0)){$slug=$n.'_'.++$x;};
								$m['slug']=$slug;
								//print_r($m);
								$result=$db->insert($k,$m,array('%s','%d','%s'));
							};
							if($result!==false){
								echo '<div class="updated"><p>Shortcode has been ',$insert?'sav':'updat','ed</p></div>',$redirect;
							}else{
								echo '<div class="error"><p>There was an error saving your shortcode.',$slug,'</p></div>';
							};
						};
					};
				}else{
					echo '<a class="button-primary" style="margin: 0 0 1em 0;" href="',$z.'&action=add','">Add a new Shortcode</a>';
					$x=$db->get_results('SELECT * FROM '.$k.' order by id asc');
					if(empty($x)!==true){
						echo '<script>ysc0=function(x,y){location.href=shorcotesadminpage+"&id="+x+"&action="+y;};ysc1=function(x){ysc0(x,"edit")};ysc2=function(x){if(confirm("delete "+x+"?")){ysc0(x,"delete");};};</script><table class="widefat" id="yourshortcode"><tbody><tr><style scoped>.widefat th{color:silver;background:#414353;}</style><th width="180px">[sc slug=""]</th><th>Content / Value</th><th width="5%">Disabled</th><th width="5%"><u onclick="if(confirm(\'erase all?\')){if(confirm(\'really?!\')){ysc0(\'\',\'erase\');};};">All</u></th></tr>';
						foreach($x as $o){$y=$o->id;echo '<tr><td><a onclick="ysc1(\'',$y,'\')">',$o->slug,'</a></td><td>',$o->data,'</td><td>',($o->disabled)==='1'?'Yes':'No','</td><td><a onclick="ysc2(\'',$y,'\')">Delete</a></td>';};
						echo '</tbody></table>';
					};
				};
				echo '</div>';
			}else{
				wp_die('You do not have sufficient permissions to access this page.');
			};
		}

		add_action('admin_menu',function(){
			$x='sc-main-menu';
			add_menu_page('ShortcodeOptions','Shortcodes','manage_options',$x,'scuser_defined_page','dashicons-editor-kitchensink');
			add_submenu_page($x,'Registered_Shortcodes','Registered','manage_options','sc-registeredshortcodes',function(){
				$o=&$GLOBALS['shortcode_tags'];
				$m=['wp_caption','bar','caption','gallery','playlist','audio','video','embed'];//ingnored
				$o=array_filter($o,function ($x) use ($m){return in_array($x,$m)!==true;},ARRAY_FILTER_USE_KEY);
				echo '<div class="wrap"><h3>Registered Shortcodes</h3><ul>';
				foreach($o as $x=>$function){echo '<li>',$x,'</li>';};
				echo '</ul><hr /><ul>';
				foreach($m as $x){echo '<li>',$x,'</li>';};
				echo '</ul></div>';
			});
			add_submenu_page($x,'Your_Shortcodes','Yours','manage_options','sc-user-defined','sc_user_defined_page');
			$GLOBALS['submenu']['sc-main-menu'][0]=null;//hide first duplicate print_r($GLOBALS['submenu']);
		});
	};

	add_shortcode('sc',function($x){
		$db=&$GLOBALS['wpdb'];
		$a=array('slug'=>false,'format'=>false,'redirect'=>false);
		$y=array();
		if(!empty($x)){foreach($x as $n=>$v){if(!isset($a[$n])){$y[$n]=$v;};};};
		$a=shortcode_atts($a,$x);
		if(empty($y)){$y=false;};
		$i=$a['slug'];
		if(empty($i)){
			return '';
		}else{
			$s=$db->get_var($db->prepare('SELECT data FROM '.($db->prefix).'SHCDV where slug=%s and disabled <> 1',$i));
			if(empty($s)){
				$s='';
			}else{
				$s=do_shortcode(stripslashes($s));
				if($y!==false){
					foreach($y as $n=>$v){
						$s=str_replace('|%'.$n.'%|',$v,$s);
					};
				};

			};
			return $s;
		};
	});

	register_activation_hook(__FILE__,'init_SHCDV');
}else{
 die;
};
