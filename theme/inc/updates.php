<?php
defined('ABSPATH')||exit;
function fuwari_update_information($force=false){
 $source=fuwari_option('updates_manifest')?:fuwari_option('updates_repo');if(!$source)return null;
 $key='fuwari_update_'.md5($source);if(!$force){$cached=get_transient($key);if($cached!==false)return $cached?:null;}
 $url=fuwari_option('updates_manifest')?:'https://api.github.com/repos/'.fuwari_option('updates_repo').'/releases/latest';
 $response=wp_safe_remote_get($url,['timeout'=>8,'headers'=>['Accept'=>'application/vnd.github+json']]);$info=null;
 if(!is_wp_error($response)&&wp_remote_retrieve_response_code($response)===200){
  $data=json_decode(wp_remote_retrieve_body($response),true);
  if(!is_array($data)){set_transient($key,[],HOUR_IN_SECONDS);return null;}
  if(fuwari_option('updates_manifest')) $info=['version'=>$data['version']??'','package'=>$data['package']??'','url'=>$data['url']??'','requires'=>$data['requires']??'6.9','requires_php'=>$data['requires_php']??'8.3','sha256'=>$data['sha256']??''];
  else {foreach($data['assets']??[] as $asset)if(preg_match('/^fuwari-wp(?:-[0-9.]+)?[.]zip$/',$asset['name']??'')){$info=['version'=>ltrim($data['tag_name']??'','v'),'package'=>$asset['browser_download_url']??'','url'=>$data['html_url']??'','requires'=>'6.9','requires_php'=>'8.3','sha256'=>''];break;}}
  if($info&&(!preg_match('/^\d+[.]\d+[.]\d+(?:[-+][A-Za-z0-9.-]+)?$/',$info['version'])||!str_starts_with($info['package'],'https://')||!wp_http_validate_url($info['package'])))$info=null;
  if($info&&!empty($info['sha256'])&&!preg_match('/^[a-f0-9]{64}$/i',$info['sha256']))$info=null;
 }
 set_transient($key,$info?:[],HOUR_IN_SECONDS);return $info;
}
add_filter('pre_set_site_transient_update_themes',function($transient){
 if(!is_object($transient))return $transient;$info=fuwari_update_information();if(!$info)return $transient;
 $slug=get_template();$data=['theme'=>$slug,'new_version'=>$info['version'],'url'=>$info['url'],'package'=>$info['package'],'requires'=>$info['requires'],'requires_php'=>$info['requires_php']];
 if(version_compare($info['version'],FUWARI_VERSION,'>'))$transient->response[$slug]=$data;else $transient->no_update[$slug]=$data;return $transient;
});
add_filter('auto_update_theme',function($update,$item){return ($item->theme??'')===get_template()?(bool)fuwari_option('updates_auto'):$update;},10,2);
add_action('admin_post_fuwari_check_updates',function(){
 if(!current_user_can('update_themes'))wp_die('Forbidden',403);check_admin_referer('fuwari_check_updates');
 fuwari_update_information(true);delete_site_transient('update_themes');wp_update_themes();wp_safe_redirect(admin_url('update-core.php'));exit;
});
add_filter('upgrader_pre_download',function($reply,$package,$upgrader,$hook_extra){
 $info=fuwari_update_information();if(!$info||empty($info['sha256'])||$package!==$info['package'])return $reply;
 require_once ABSPATH.'wp-admin/includes/file.php';$file=download_url($package);if(is_wp_error($file))return $file;
 if(!hash_equals(strtolower($info['sha256']),hash_file('sha256',$file))){wp_delete_file($file);return new WP_Error('fuwari_checksum','Theme package checksum mismatch.');}return $file;
},10,4);
function fuwari_backup_theme(){
 if(!class_exists('ZipArchive'))return new WP_Error('fuwari_zip','ZIP extension is required.');
 $folder=WP_CONTENT_DIR.'/fuwari-backups';if(!wp_mkdir_p($folder))return new WP_Error('fuwari_backup','Cannot create theme backup folder.');
 if(!file_exists($folder.'/index.php'))file_put_contents($folder.'/index.php',"<?php http_response_code(403); exit;\n");
 $file=$folder.'/fuwari-wp-'.FUWARI_VERSION.'-'.gmdate('YmdHis').'.zip';$zip=new ZipArchive();if($zip->open($file,ZipArchive::CREATE)!==true)return new WP_Error('fuwari_backup','Cannot create theme backup.');
 $root=get_template_directory();$iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root,FilesystemIterator::SKIP_DOTS));
 foreach($iterator as $entry)if($entry->isFile()&&!$entry->isLink())$zip->addFile($entry->getPathname(),'fuwari-wp/'.substr($entry->getPathname(),strlen($root)+1));
 $zip->close();return $file;
}
add_filter('upgrader_pre_install',function($result,$extra){if(($extra['theme']??'')===get_template()&&!is_wp_error($result)){$backup=fuwari_backup_theme();if(is_wp_error($backup))return $backup;}return $result;},10,2);
add_filter('upgrader_source_selection',function($source,$remote_source,$upgrader,$extra){
 if(is_wp_error($source)||($extra['type']??'')!=='theme'||($extra['action']??'')!=='install'||basename(untrailingslashit($source))!==get_template())return $source;
 $backup=fuwari_backup_theme();return is_wp_error($backup)?$backup:$source;
},20,4);
add_action('admin_post_fuwari_backup_theme',function(){
 if(!current_user_can('update_themes'))wp_die('Forbidden',403);check_admin_referer('fuwari_backup_theme');$file=fuwari_backup_theme();if(is_wp_error($file))wp_die($file->get_error_message());wp_safe_redirect(admin_url('themes.php?page=fuwari-settings&tab=updates&fuwari_notice=backup'));exit;
});
add_action('admin_post_fuwari_rollback_theme',function(){
 if(!current_user_can('update_themes'))wp_die('Forbidden',403);check_admin_referer('fuwari_rollback_theme');
 $name=basename(sanitize_file_name(wp_unslash($_POST['backup']??'')));if(!preg_match('/^fuwari-wp-[0-9.]+-[0-9]{14}[.]zip$/',$name))wp_die('Invalid backup');
 $file=WP_CONTENT_DIR.'/fuwari-backups/'.$name;if(!is_file($file))wp_die('Backup not found');
 require_once ABSPATH.'wp-admin/includes/class-wp-upgrader.php';require_once ABSPATH.'wp-admin/includes/file.php';
 $upgrader=new Theme_Upgrader(new Automatic_Upgrader_Skin());$result=$upgrader->install($file,['overwrite_package'=>true]);if(is_wp_error($result))wp_die(esc_html($result->get_error_message()));if(!$result)wp_die(esc_html($upgrader->skin->get_errors()->get_error_message()?:'Rollback failed'));
 wp_safe_redirect(admin_url('themes.php?page=fuwari-settings&tab=updates&fuwari_notice=rollback'));exit;
});
