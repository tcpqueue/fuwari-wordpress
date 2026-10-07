<?php
defined('ABSPATH')||exit;
add_action('init',function(){
 add_rewrite_rule('^archive/?$','index.php?fuwari_archive=1','top');
 register_post_meta('post','_fuwari_language',['single'=>true,'type'=>'string','show_in_rest'=>true,'sanitize_callback'=>'sanitize_text_field','auth_callback'=>fn()=>current_user_can('edit_posts')]);
 register_post_meta('post','_fuwari_description',['single'=>true,'type'=>'string','show_in_rest'=>true,'sanitize_callback'=>'sanitize_textarea_field','auth_callback'=>fn()=>current_user_can('edit_posts')]);
});
add_filter('query_vars',fn($vars)=>array_merge($vars,['fuwari_archive']));
add_filter('document_title_parts',function($parts){if(get_query_var('fuwari_archive'))$parts['title']=fuwari_t('archive');elseif(is_home()){if(fuwari_option('subtitle'))$parts['tagline']=fuwari_option('subtitle');else unset($parts['tagline']);}return $parts;});
add_action('add_meta_boxes',function(){add_meta_box('fuwari-post','Fuwari','fuwari_post_box','post','side');});
function fuwari_post_box($post){wp_nonce_field('fuwari_post','fuwari_post_nonce');echo '<p><label>'.esc_html(fuwari_admin_text('文章语言（不翻译正文）','Article language (content stays unchanged)')).'<input class="widefat" name="fuwari_language" value="'.esc_attr(get_post_meta($post->ID,'_fuwari_language',true)).'" placeholder="zh-CN / en"></label></p><p><label>'.esc_html(fuwari_admin_text('首页摘要','Homepage description')).'<textarea class="widefat" name="fuwari_description">'.esc_textarea(get_post_meta($post->ID,'_fuwari_description',true)).'</textarea></label></p>';}
add_action('save_post_post',function($id){
 if(wp_is_post_revision($id)||!current_user_can('edit_post',$id)||empty($_POST['fuwari_post_nonce'])||!wp_verify_nonce($_POST['fuwari_post_nonce'],'fuwari_post'))return;
 update_post_meta($id,'_fuwari_language',sanitize_text_field(wp_unslash($_POST['fuwari_language']??'')));
 update_post_meta($id,'_fuwari_description',sanitize_textarea_field(wp_unslash($_POST['fuwari_description']??'')));
});
function fuwari_reading($post=null){
 $post=get_post($post);if(post_password_required($post))return ['words'=>0,'minutes'=>0];$text=wp_strip_all_tags(strip_shortcodes($post->post_content));
 preg_match_all('/[\p{Han}\p{Hiragana}\p{Katakana}]|[\p{L}\p{N}]+(?:[\x{0027}’-][\p{L}\p{N}]+)*/u',$text,$matches);
 $count=count($matches[0]);return ['words'=>$count,'minutes'=>max(1,(int)ceil($count/200))];
}
function fuwari_description($post){if(post_password_required($post))return fuwari_t('protectedPost');$custom=get_post_meta($post->ID,'_fuwari_description',true);return $custom?:wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_excerpt?:$post->post_content)),35,'…');}
function fuwari_metadata($post,$class='mb-4',$hide_update=false,$hide_mobile_tags=false){
 echo '<div class="flex flex-wrap items-center gap-4 gap-x-4 gap-y-2 '.esc_attr($class).'">';
 $dates=[['material-symbols:calendar-today-outline-rounded',get_the_date('Y-m-d',$post),get_the_date('c',$post)]];
 if(!$hide_update&&get_the_modified_date('Y-m-d',$post)!==get_the_date('Y-m-d',$post))$dates[]=['material-symbols:edit-calendar-outline-rounded',get_the_modified_date('Y-m-d',$post),get_the_modified_date('c',$post)];
 foreach($dates as [$icon,$date,$iso])echo '<div class="flex items-center"><span class="meta-icon">'.fuwari_icon($icon,'text-xl').'</span><time class="text-50 text-sm font-medium" datetime="'.esc_attr($iso).'">'.esc_html($date).'</time></div>';
 $link_class='link-lg transition text-50 text-sm font-medium hover:text-[var(--primary)] whitespace-nowrap';
 $cats=get_the_category($post->ID);echo '<div class="flex items-center"><span class="meta-icon">'.fuwari_icon('material-symbols:book-2-outline-rounded','text-xl').'</span><div class="flex flex-row flex-nowrap items-center">';
 foreach($cats as $i=>$cat){if($i)echo '<span class="mx-1.5 text-sm text-30">/</span>';echo '<a class="'.$link_class.'" href="'.esc_url(home_url('/archive/?category='.rawurlencode($cat->slug))).'">'.esc_html($cat->name).'</a>';}if(!$cats)echo fuwari_label('uncategorized');echo '</div></div>';
 $tags=get_the_tags($post->ID);echo '<div class="'.($hide_mobile_tags?'hidden md:flex':'flex').' items-center"><span class="meta-icon">'.fuwari_icon('material-symbols:tag-rounded','text-xl').'</span><div class="flex flex-row flex-nowrap items-center">';
 foreach($tags?:[] as $i=>$tag){if($i)echo '<span class="mx-1.5 text-sm text-30">/</span>';echo '<a class="'.$link_class.'" href="'.esc_url(home_url('/archive/?tag='.rawurlencode($tag->slug))).'">'.esc_html($tag->name).'</a>';}if(!$tags)echo '<span class="text-50 text-sm font-medium">'.fuwari_label('noTags').'</span>';echo '</div></div></div>';
}
add_action('rest_api_init',function(){register_rest_route('fuwari/v1','/search',['methods'=>'GET','permission_callback'=>'__return_true','callback'=>function($request){
 $term=mb_substr(sanitize_text_field($request->get_param('q')??''),0,80);if(mb_strlen($term)<1)return rest_ensure_response([]);
 $query=new WP_Query(['s'=>$term,'post_type'=>['post','page'],'post_status'=>'publish','posts_per_page'=>8,'no_found_rows'=>true,'ignore_sticky_posts'=>true]);
 $results=[];foreach($query->posts as $post){if($post->post_password)continue;$results[]=['title'=>get_the_title($post),'url'=>get_permalink($post),'excerpt'=>wp_trim_words(wp_strip_all_tags(strip_shortcodes($post->post_content)),35,'…')];}
 return rest_ensure_response($results);
}]);});
add_shortcode('fuwari_note',function($atts,$content=''){
 $atts=shortcode_atts(['type'=>'note','title'=>''],$atts);$type=in_array($atts['type'],['note','tip','important','warning','caution'],true)?$atts['type']:'note';
 return '<blockquote class="admonition bdm-'.$type.'"><span class="bdm-title">'.esc_html($atts['title']?:strtoupper($type)).'</span>'.wpautop(wp_kses_post(do_shortcode($content))).'</blockquote>';
});
add_shortcode('fuwari_github',function($atts){
 $repo=trim($atts['repo']??'');if(!preg_match('~^[\w.-]+/[\w.-]+$~',$repo))return '';
 $key='fuwari_gh_'.md5($repo);$data=get_transient($key);
 if($data===false&&fuwari_option('github_cache')){
  $response=wp_safe_remote_get('https://api.github.com/repos/'.$repo,['timeout'=>4,'headers'=>['Accept'=>'application/vnd.github+json']]);
  if(!is_wp_error($response)&&wp_remote_retrieve_response_code($response)===200){$raw=json_decode(wp_remote_retrieve_body($response),true);$data=['description'=>sanitize_text_field($raw['description']??''),'stars'=>(int)($raw['stargazers_count']??0),'language'=>sanitize_text_field($raw['language']??'')];set_transient($key,$data,DAY_IN_SECONDS);}
  else{set_transient($key,[],10*MINUTE_IN_SECONDS);$data=[];}
 }
 return '<a class="fuwari-github-card" href="https://github.com/'.esc_attr($repo).'" target="_blank" rel="noopener"><strong>'.fuwari_icon('fa6-brands:github').' '.esc_html($repo).'</strong><p>'.esc_html($data['description']??'').'</p><span>★ '.(int)($data['stars']??0).' · '.esc_html($data['language']??'GitHub').'</span></a>';
});
add_action('enqueue_block_editor_assets',function(){wp_enqueue_script('fuwari-blocks',get_template_directory_uri().'/assets/blocks.js',['wp-blocks','wp-element','wp-block-editor','wp-components','wp-i18n'],FUWARI_VERSION,true);wp_localize_script('fuwari-blocks','FuwariEditor',['code'=>fuwari_admin_text('Fuwari 代码','Fuwari Code'),'language'=>fuwari_admin_text('语言','Language'),'filename'=>fuwari_admin_text('文件名','Filename'),'lines'=>fuwari_admin_text('显示行号','Line numbers'),'note'=>fuwari_admin_text('Fuwari 提示框','Fuwari Note'),'type'=>fuwari_admin_text('类型','Type'),'title'=>fuwari_admin_text('标题','Title')]);});
