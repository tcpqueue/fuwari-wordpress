<?php
defined('ABSPATH') || exit;
function fuwari_language() { $lang=sanitize_key($_COOKIE['fuwari_lang']??''); return in_array($lang,['en','zh_cn'],true)?($lang==='en'?'en':'zh_CN'):fuwari_option('ui_language'); }
function fuwari_dictionary() {
 return [
  'home'=>['主页','Home'],'archive'=>['归档','Archive'],'about'=>['关于','About'],'search'=>['搜索','Search'],
  'categories'=>['分类','Categories'],'tags'=>['标签','Tags'],'recentPosts'=>['最新文章','Recent Posts'],'comments'=>['评论','Comments'],
  'uncategorized'=>['未分类','Uncategorized'],'noTags'=>['无标签','No Tags'],'themeColor'=>['主题色','Theme Color'],
  'light'=>['亮色','Light'],'dark'=>['暗色','Dark'],'system'=>['跟随系统','System'],'language'=>['界面语言','Interface language'],
  'word'=>['字','word'],'words'=>['字','words'],'minute'=>['分钟','minute'],'minutes'=>['分钟','minutes'],'author'=>['作者','Author'],'publishedAt'=>['发布于','Published at'],
  'license'=>['许可协议','License'],'allPosts'=>['全部文章','All posts'],'post'=>['篇文章','post'],'posts'=>['篇文章','posts'],
  'noResults'=>['没有找到相关文章','No matching posts'],'loading'=>['搜索中…','Searching…'],'reset'=>['恢复默认','Reset to default'],
  'previous'=>['上一篇','Previous post'],'next'=>['下一篇','Next post'],'toc'=>['文章目录','Contents'],'notFound'=>['页面未找到','Page not found'],
  'notFoundText'=>['这个页面可能已移动或不存在。','This page may have moved or does not exist.'],'empty'=>['暂无文章','No posts yet'],
  'backToTop'=>['返回顶部','Back to top'],'displaySettings'=>['显示设置','Display settings'],'menu'=>['菜单','Menu'],'colorMode'=>['明暗模式','Color mode'],
  'copyCode'=>['复制代码','Copy code'],'protectedPost'=>['这篇文章需要密码才能阅读。','This post is password protected.'],
  'leaveComment'=>['发表评论','Leave a comment'],'replyTo'=>['回复','Reply to'],'cancelReply'=>['取消回复','Cancel reply'],'postComment'=>['提交评论','Post comment'],
  'commentText'=>['评论内容','Comment'],'commentName'=>['称呼','Name'],'commentEmail'=>['邮箱','Email'],'commentWebsite'=>['网站（可选）','Website (optional)'],
  'commentPrivacy'=>['邮箱不会公开，标有 * 的项目必填。','Your email will not be published. Fields marked * are required.'],
  'commentCookies'=>['在此浏览器保存称呼、邮箱和网站。','Save my name, email and website in this browser.'],
  'commentPending'=>['评论正在等待审核。','Your comment is awaiting moderation.'],'commentClosed'=>['评论已关闭。','Comments are closed.'],
  'commentEmpty'=>['还没有评论，欢迎分享你的想法。','No comments yet. Share your thoughts below.'],'commentAuthor'=>['作者','Author'],
  'commentReply'=>['回复','Reply'],'commentEdit'=>['编辑','Edit'],'olderComments'=>['较早评论','Older comments'],'newerComments'=>['较新评论','Newer comments'],
  'loggedIn'=>['已登录','Logged in as'],'editProfile'=>['编辑资料','Edit profile'],'logout'=>['退出登录','Log out'],'login'=>['登录后发表评论','Log in to leave a comment'],
  'captchaLabel'=>['算术验证码','Math verification'],'captchaRefresh'=>['换一道题','New question'],
  'captchaHelp'=>['请输入计算结果，题目 15 分钟内有效。','Enter the result. This question is valid for 15 minutes.'],
  'captchaExpired'=>['验证码已过期或无效，请返回评论表单换一道题后重试。','This question has expired or is invalid. Go back to the comment form and request a new question.'],
  'captchaIncorrect'=>['验证码答案不正确，请返回修改答案后重试。','The answer is incorrect. Go back and correct your answer.'],
  'captchaRefreshError'=>['暂时无法换题，请重试或重新加载页面。','Unable to load a new question. Try again or reload the page.'],
 ];
}
function fuwari_t($key) { $dict=fuwari_dictionary(); return $dict[$key][fuwari_language()==='en'?1:0]??$key; }
function fuwari_label($key) { return '<span data-i18n="'.esc_attr($key).'">'.esc_html(fuwari_t($key)).'</span>'; }
function fuwari_asset($path,$type='css',$local=false) {
 $url=get_template_directory_uri().'/assets/'.ltrim($path,'/');
 if(!$local && fuwari_option('asset_'.$type)==='cdn' && fuwari_option('cdn_base')) $url=rtrim(fuwari_option('cdn_base'),'/').'/'.ltrim($path,'/');
 return $url;
}
function fuwari_asset_version($path){$file=get_template_directory().'/assets/'.$path;return FUWARI_VERSION.'.'.(is_file($file)?filemtime($file):'0');}
function fuwari_entry($source='frontend/app.js'){static $manifest=null;if($manifest===null)$manifest=json_decode(@file_get_contents(get_template_directory().'/assets/bundle/manifest.json'),true)?:[];return $manifest[$source]??[];}
function fuwari_icon($name,$class='') {
 static $icons=null; if($icons===null) $icons=json_decode(file_get_contents(get_template_directory().'/assets/icons.json'),true)?:[];
 $icon=$icons[$name]??$icons['material-symbols:chevron-right-rounded']??null; if(!$icon)return '';
 return '<svg aria-hidden="true" class="fuwari-icon '.esc_attr($class).'" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 '.(int)$icon['width'].' '.(int)$icon['height'].'" fill="currentColor">'.$icon['body'].'</svg>';
}
function fuwari_navigation() {
 $links=[['name'=>fuwari_t('home'),'key'=>'home','url'=>home_url('/')],['name'=>fuwari_t('archive'),'key'=>'archive','url'=>home_url('/archive/')],['name'=>fuwari_t('about'),'key'=>'about','url'=>home_url('/about/')],['name'=>'GitHub','url'=>'https://github.com/saicaca/fuwari','external'=>true]];
 $locations=get_nav_menu_locations();
 if(!empty($locations['primary'])) { $links=[]; foreach(wp_get_nav_menu_items($locations['primary'])?:[] as $item) $links[]=['name'=>$item->title,'url'=>$item->url,'external'=>$item->target==='_blank']; }
 return $links;
}
add_action('wp_enqueue_scripts',function(){
 if(is_singular()&&comments_open()&&get_option('thread_comments')&&fuwari_option('comments_enable'))wp_enqueue_script('comment-reply');
 foreach(['upstream.css'=>'css','theme.css'=>'css'] as $file=>$type) wp_enqueue_style('fuwari-'.str_replace('.','-',$file),fuwari_asset($file,$type),[],fuwari_asset_version($file));
 $fonts=file_get_contents(get_template_directory().'/assets/fonts.css');
 $fonts=preg_replace_callback('~url\(upstream/([^)]*)\)\s*format\(["\x27]([^"\x27]*)["\x27]\)~',function($m){$remote=fuwari_asset('upstream/'.$m[1],'fonts');$local=fuwari_asset('upstream/'.$m[1],'fonts',true);$value='url('.wp_json_encode($remote).') format("'.$m[2].'")';if($remote!==$local)$value.=',url('.wp_json_encode($local).') format("'.$m[2].'")';return $value;},$fonts);
 wp_add_inline_style('fuwari-upstream-css',$fonts);
 $entry=fuwari_entry();
 foreach($entry['css']??[] as $i=>$css)wp_enqueue_style('fuwari-runtime-'.$i,fuwari_asset('bundle/'.$css,'css'),[],FUWARI_VERSION);
 wp_enqueue_script('fuwari-runtime',fuwari_asset('bundle/'.($entry['file']??'app.js'),'js'),[],FUWARI_VERSION,true);
 wp_add_inline_script('fuwari-runtime','window.FuwariConfig='.wp_json_encode([
   'api'=>rest_url('fuwari/v1/search'),'base'=>home_url('/'),'lang'=>fuwari_language(),'dictionary'=>fuwari_dictionary(),'commentCaptchaUrl'=>admin_url('admin-ajax.php'),
   'hue'=>(int)fuwari_option('hue'),'mode'=>fuwari_option('theme_mode'),'banner'=>(bool)fuwari_option('banner_enable'),
   'homeHeight'=>(int)fuwari_option('banner_home_height'),'innerHeight'=>(int)fuwari_option('banner_height'),
   'transitions'=>(bool)fuwari_option('transitions_enable')&&!is_singular(),'lightbox'=>(bool)fuwari_option('lightbox_enable'),
   'math'=>(bool)fuwari_option('math_enable'),'code'=>(bool)fuwari_option('code_enable'),'tocDepth'=>(int)fuwari_option('toc_depth'),
 ],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';','before');
 remove_action('wp_head','print_emoji_detection_script',7);remove_action('wp_print_styles','print_emoji_styles');remove_action('wp_enqueue_scripts','wp_enqueue_emoji_styles');
});
add_filter('script_loader_tag',function($tag,$handle,$src){
 if($handle==='fuwari-editor')return str_replace('id="fuwari-editor-js"','type="module" id="fuwari-editor-js"',$tag);
 if($handle!=='fuwari-runtime')return $tag;
 $local=fuwari_asset('bundle/'.(fuwari_entry()['file']??'app.js'),'js',true);
 return str_replace('id="fuwari-runtime-js"','type="module" id="fuwari-runtime-js" data-local="'.esc_url($local).'"'.(strtok($src,'?')!==$local?' onerror="if(!this.dataset.retried){this.dataset.retried=1;this.src=this.dataset.local;}"':''),$tag);
},10,3);
add_filter('style_loader_tag',function($tag,$handle,$href){
 if(!str_starts_with($handle,'fuwari-') || !fuwari_option('cdn_base') || !str_starts_with($href,rtrim(fuwari_option('cdn_base'),'/')))return $tag;
 $local=str_replace(rtrim(fuwari_option('cdn_base'),'/'),get_template_directory_uri().'/assets',$href);
 return str_replace('/>','data-local="'.esc_url($local).'" onerror="this.onerror=null;this.href=this.dataset.local;" />',$tag);
},10,3);
function fuwari_typography() {
 $english=fuwari_option('font_english');$chinese=fuwari_option('font_chinese');$code=fuwari_option('font_code');
 $fontcss='';$faces=['chinese'=>'Fuwari Chinese','english'=>'Fuwari English','code'=>'Fuwari Code'];
 $choices=['chinese'=>$chinese,'english'=>$english,'code'=>$code];
 foreach($faces as $kind=>$family) {
  $url=fuwari_option('font_'.$kind.'_url');
  if(!$url || ($choices[$kind]!=='custom' && !($kind==='chinese' && $chinese==='fangsong')))continue;
  $fontcss.='@font-face{font-family:"'.$family.'";src:url('.wp_json_encode(esc_url_raw($url)).') format("'.(str_ends_with(parse_url($url,PHP_URL_PATH)??'','.woff')?'woff':'woff2').'");font-display:swap;font-style:normal;font-weight:400;}';
 }
 $sans=$english==='roboto'?'"Roboto"':($english==='custom'?'"Fuwari English"':'system-ui');
 if($chinese==='fangsong')$sans.=',"Fuwari Chinese","仿宋_GB2312","FangSong_GB2312","FangSong","仿宋"';
 elseif($chinese==='custom')$sans.=',"Fuwari Chinese"';
 $sans.=',"PingFang SC","Hiragino Sans GB","Microsoft YaHei","Noto Sans CJK SC","Noto Sans SC",system-ui,sans-serif';
 $mono=$code==='jetbrains'?'"JetBrains Mono Variable"':($code==='custom'?'"Fuwari Code"':'ui-monospace');$mono.=',ui-monospace,monospace';
 return ['faces'=>$fontcss,'sans'=>$sans,'mono'=>$mono];
}
function fuwari_head_config() {
 $hue=(int)fuwari_option('hue');$mode=fuwari_option('theme_mode');$home_height=(int)fuwari_option('banner_home_height');$inner=(int)fuwari_option('banner_height');
 $extend=max(0,$home_height-$inner);$pos=fuwari_option('banner_position');$offset=$pos==='top'?$extend:($pos==='bottom'?0:$extend/2);
 echo '<script>(function(){try{var m=localStorage.getItem("theme")||'.wp_json_encode($mode).';document.documentElement.classList.toggle("dark",m==="dark"||((m==="system"||m==="auto")&&matchMedia("(prefers-color-scheme: dark)").matches));var h=localStorage.getItem("hue");h=h!==null&&Number(h)>=0&&Number(h)<=360?Number(h):'.$hue.';document.documentElement.style.setProperty("--hue",h);var e=Math.floor(innerHeight*'.$extend.'/100);document.documentElement.style.setProperty("--banner-height-extend",(e-e%4)+"px");}catch(e){}})();</script>';
 ['faces'=>$fontcss,'sans'=>$sans,'mono'=>$mono]=fuwari_typography();
 echo '<style id="fuwari-config">'.$fontcss.':root{--hue:'.$hue.';--page-width:75rem;--banner-height-home:'.$home_height.'vh;--banner-height:'.$inner.'vh;--banner-offset:'.$offset.'vh;--fuwari-sans:'.$sans.';--fuwari-code:'.$mono.';}html,body{font-family:var(--fuwari-sans)}.custom-md code,.custom-md pre,.expressive-code .code{font-family:var(--fuwari-code)!important}';
 echo fuwari_option('custom_css').'</style>';
}
add_action('wp_head',function(){
 if(!is_singular())return;
 $post=get_queried_object();$description=wp_strip_all_tags(get_the_excerpt($post));
 echo '<meta name="description" content="'.esc_attr($description).'"><meta property="og:title" content="'.esc_attr(get_the_title($post)).'"><meta property="og:url" content="'.esc_url(get_permalink($post)).'"><meta property="og:type" content="article">';
 $schema=['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>get_the_title($post),'datePublished'=>get_the_date('c',$post),'dateModified'=>get_the_modified_date('c',$post),'author'=>['@type'=>'Person','name'=>get_the_author_meta('display_name',$post->post_author)],'url'=>get_permalink($post)];
 echo '<script type="application/ld+json">'.wp_json_encode($schema,JSON_HEX_TAG|JSON_HEX_AMP).'</script>';
});
