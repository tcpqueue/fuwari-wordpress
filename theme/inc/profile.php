<?php
defined('ABSPATH') || exit;
function fuwari_media_value($value) {
    $value=trim($value);
    if($value==='')return '';
    if(preg_match('~^https?://~i',$value))return esc_url_raw($value,['http','https']);
    if(preg_match('~[\x00-\x1f\\\\]|(^|/)\.\.(/|$)|^[a-z][a-z0-9+.-]*:|^//~i',$value))return '';
    $root=rtrim(wp_normalize_path(ABSPATH),'/').'/';
    if(str_starts_with($value,$root))$value=substr($value,strlen($root));
    elseif(preg_match('~^/(www|home|var|etc|root|usr|tmp|opt|proc|sys|dev)/~',$value))return '';
    return '/'.ltrim(sanitize_text_field($value),'/');
}
function fuwari_media_url($value){
    $value=fuwari_media_value($value);
    return preg_match('~^https?://~i',$value)?$value:($value?home_url($value):'');
}
function fuwari_profile_name(){
    $source=fuwari_option('profile_name_source');
    if($source==='custom')return fuwari_option('profile_name')?:get_bloginfo('name');
    $user=get_userdata((int)fuwari_option('profile_user'));
    if(!$user){$users=get_users(['role'=>'administrator','number'=>1,'orderby'=>'ID','order'=>'ASC']);$user=$users[0]??null;}
    if(!$user)return fuwari_option('profile_name')?:get_bloginfo('name');
    return $source==='username'?$user->user_login:(get_user_meta($user->ID,'nickname',true)?:$user->display_name);
}
function fuwari_social_platforms(){
    return ['fa6-brands:github'=>'GitHub','fa6-brands:telegram'=>'Telegram','fa6-brands:x-twitter'=>'X','fa6-brands:bilibili'=>'Bilibili','fa6-brands:weibo'=>'Weibo','fa6-brands:qq'=>'QQ','fa6-brands:discord'=>'Discord','fa6-brands:youtube'=>'YouTube','fa6-brands:instagram'=>'Instagram','fa6-brands:facebook'=>'Facebook','fa6-brands:linkedin'=>'LinkedIn','fa6-brands:mastodon'=>'Mastodon','fa6-solid:rss'=>'RSS','fa6-solid:envelope'=>'Email','material-symbols:language'=>'Website'];
}
function fuwari_social_rows($value){
    if(is_array($value))return array_slice($value,0,30);
    $rows=[];foreach(explode("\n",(string)$value) as $line){$parts=explode('|',$line,3);if(count($parts)===3)$rows[]=['name'=>trim($parts[0]),'icon'=>trim($parts[1]),'url'=>trim($parts[2])];}
    return array_slice($rows,0,30);
}
function fuwari_sanitize_social($value){
    $rows=[];$platforms=fuwari_social_platforms();
    $icons=json_decode(file_get_contents(get_template_directory().'/assets/icons.json'),true)?:[];
    foreach(fuwari_social_rows($value) as $row){
        if(!is_array($row))continue;
        $url=esc_url_raw(trim(is_scalar($row['url']??null)?(string)$row['url']:''),['http','https','mailto']);
        if(!$url)continue;
        $icon=sanitize_text_field(is_scalar($row['icon']??null)?$row['icon']:'');
        if(!isset($icons[$icon]))$icon='material-symbols:language';
        $name=str_replace('|','',sanitize_text_field(is_scalar($row['name']??null)?$row['name']:''));
        $rows[]=($name?:($platforms[$icon]??'Website')).'|'.$icon.'|'.$url;
    }
    return implode("\n",$rows);
}
function fuwari_social_row_field($name,$index,$row){
    $platforms=fuwari_social_platforms();$icon=$row['icon']??'fa6-brands:github';
    if(!isset($platforms[$icon]))$platforms[$icon]=$icon;
    echo '<div class="fuwari-social-row"><label><span>'.esc_html(fuwari_admin_text('平台','Platform')).'</span><select name="'.esc_attr($name.'['.$index.'][icon]').'">';
    foreach($platforms as $key=>$label)echo '<option value="'.esc_attr($key).'" '.selected($icon,$key,false).'>'.esc_html($label).'</option>';
    echo '</select></label><label><span>'.esc_html(fuwari_admin_text('显示名称（可选）','Label (optional)')).'</span><input type="text" name="'.esc_attr($name.'['.$index.'][name]').'" value="'.esc_attr($row['name']??'').'"></label><label class="fuwari-social-url"><span>'.esc_html(fuwari_admin_text('网址（留空不显示）','URL (empty hides link)')).'</span><input type="text" name="'.esc_attr($name.'['.$index.'][url]').'" value="'.esc_attr($row['url']??'').'" placeholder="https://…"></label><button type="button" class="button fuwari-social-remove">'.esc_html(fuwari_admin_text('删除','Remove')).'</button></div>';
}
function fuwari_social_field($name,$value){
    echo '<div class="fuwari-social-editor"><input type="hidden" name="'.esc_attr($name).'" value=""><div class="fuwari-social-rows">';
    foreach(fuwari_social_rows($value) as $index=>$row)fuwari_social_row_field($name,$index,$row);
    echo '</div><template class="fuwari-social-template">';fuwari_social_row_field($name,'__INDEX__',[]);
    echo '</template><button type="button" class="button fuwari-social-add">'.esc_html(fuwari_admin_text('添加社交链接','Add social link')).'</button><p class="description">'.esc_html(fuwari_admin_text('可添加多个平台或同一平台的多个账号，按列表顺序显示；未填写网址的条目不显示。邮箱使用 mailto:you@example.com。','Add multiple platforms or accounts in display order. Rows without URLs are hidden. Use mailto:you@example.com for email.')).'</p></div>';
}
