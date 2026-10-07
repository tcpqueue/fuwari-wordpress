<?php
defined('ABSPATH') || exit;
function fuwari_defaults() {
    return [
        'ui_language'=>'zh_CN','admin_language'=>'auto','subtitle'=>'','profile_name'=>get_bloginfo('name'),'profile_bio'=>'',
        'avatar'=>'','social_links'=>"GitHub|fa6-brands:github|https://github.com/saicaca/fuwari",
        'banner_enable'=>1,'banner_image'=>'','banner_position'=>'center','banner_home_height'=>65,'banner_height'=>35,
        'banner_credit_enable'=>0,'banner_credit'=>'','banner_credit_url'=>'',
        'hue'=>250,'theme_mode'=>'system','visitor_color'=>1,'visitor_language'=>1,'page_size'=>8,'toc_enable'=>1,'toc_depth'=>2,
        'categories_enable'=>1,'tags_enable'=>1,'license_enable'=>1,'license_name'=>'CC BY-NC-SA 4.0','license_url'=>'https://creativecommons.org/licenses/by-nc-sa/4.0/',
        'comments_enable'=>0,'transitions_enable'=>1,'lightbox_enable'=>1,'math_enable'=>1,'code_enable'=>1,
        'font_chinese'=>'system','font_chinese_url'=>'','font_english'=>'roboto','font_english_url'=>'','font_code'=>'jetbrains','font_code_url'=>'',
        'asset_css'=>'local','asset_js'=>'local','asset_fonts'=>'local','cdn_base'=>'','custom_css'=>'','github_cache'=>1,
        'updates_repo'=>'tcpqueue/fuwari-wordpress','updates_manifest'=>'','updates_auto'=>0,
    ];
}
function fuwari_option($key) { $settings=get_option('fuwari_settings', []); return $settings[$key] ?? (fuwari_defaults()[$key] ?? ''); }
function fuwari_admin_en() { $lang=fuwari_option('admin_language'); return $lang==='en' || ($lang==='auto' && str_starts_with(get_user_locale(),'en')); }
function fuwari_admin_text($zh,$en) { return fuwari_admin_en() ? $en : $zh; }
function fuwari_fields() {
    return [
      'general'=>[
        ['ui_language','界面默认语言','Default interface language','select',['zh_CN'=>'简体中文','en'=>'English']],
        ['admin_language','主题后台语言','Theme settings language','select',['auto'=>'跟随管理员 / Follow user','zh_CN'=>'简体中文','en'=>'English']],
        ['subtitle','站点副标题','Site subtitle','text'],['profile_name','个人名称','Profile name','text'],['profile_bio','个人简介','Profile bio','textarea'],
        ['avatar','头像','Avatar','media'],['social_links','社交链接（每行 名称|图标|网址）','Social links: Name|icon|URL per line','textarea'],
      ],
      'banner'=>[
        ['banner_enable','启用横幅','Enable banner','checkbox'],['banner_image','横幅图片','Banner image','media'],
        ['banner_position','图片裁切位置','Image position','select',['top'=>'顶部 / Top','center'=>'居中 / Center','bottom'=>'底部 / Bottom']],
        ['banner_home_height','首页高度（vh）','Homepage height (vh)','number'],['banner_height','内页高度（vh）','Inner-page height (vh)','number'],
        ['banner_credit_enable','显示图片来源','Show image attribution','checkbox'],['banner_credit','来源文字','Attribution text','text'],['banner_credit_url','来源链接','Attribution URL','url'],
      ],
      'display'=>[
        ['hue','主题色相（0–360）','Theme hue (0–360)','number'],['theme_mode','默认明暗模式','Default color mode','select',['system'=>'跟随系统 / System','light'=>'亮色 / Light','dark'=>'暗色 / Dark']],
        ['visitor_color','允许访客修改主题色','Allow visitor color selection','checkbox'],['visitor_language','显示界面语言切换','Show interface language selection','checkbox'],
        ['page_size','每页文章数','Posts per page','number'],['categories_enable','显示分类模块','Show categories','checkbox'],['tags_enable','显示标签模块','Show tags','checkbox'],
        ['toc_enable','显示文章目录','Show table of contents','checkbox'],['toc_depth','目录标题深度（1–3）','TOC depth (1–3)','number'],
        ['license_enable','显示文章版权信息','Show post license','checkbox'],['license_name','版权协议名称','License name','text'],['license_url','版权协议链接','License URL','url'],
        ['comments_enable','启用评论区域','Enable comments section','checkbox'],['transitions_enable','启用页面过渡','Enable page transitions','checkbox'],
        ['lightbox_enable','启用图片灯箱','Enable image lightbox','checkbox'],['math_enable','启用数学公式','Enable math rendering','checkbox'],['code_enable','启用代码高亮与复制','Enable code highlighting and copy','checkbox'],
      ],
      'fonts'=>[
        ['font_chinese','中文字体','Chinese font','select',['system'=>'跟随系统字体 / System fonts','fangsong'=>'仿宋_GB2312','custom'=>'自定义本地字体 / Custom']],
        ['font_chinese_url','中文字体文件（WOFF2/WOFF）','Chinese font file (WOFF2/WOFF)','font'],
        ['font_english','英文和数字字体','English and numerals','select',['roboto'=>'Roboto','system'=>'System','custom'=>'Custom']],
        ['font_english_url','英文字体文件','English font file','font'],
        ['font_code','代码字体','Code font','select',['jetbrains'=>'JetBrains Mono','system'=>'System monospace','custom'=>'Custom']],['font_code_url','代码字体文件','Code font file','font'],
      ],
      'resources'=>[
        ['asset_css','CSS 加载方式','CSS delivery','select',['local'=>'本地 / Local','cdn'=>'CDN']],
        ['asset_js','JS 加载方式','JavaScript delivery','select',['local'=>'本地 / Local','cdn'=>'CDN']],
        ['asset_fonts','原版字体加载方式','Bundled font delivery','select',['local'=>'本地 / Local','cdn'=>'CDN']],
        ['cdn_base','主题资源 CDN 根地址','Theme assets CDN base URL','url'],['github_cache','启用 GitHub 卡片服务端缓存','Cache GitHub cards on the server','checkbox'],
      ],
      'advanced'=>[['custom_css','自定义 CSS','Custom CSS','code']],
      'updates'=>[['updates_repo','GitHub 更新仓库（owner/repo）','GitHub update repository (owner/repo)','text'],['updates_manifest','自定义更新清单地址（HTTPS）','Custom update manifest URL (HTTPS)','url'],['updates_auto','允许 WordPress 自动更新此主题','Allow automatic theme updates','checkbox']],
    ];
}
function fuwari_sanitize_settings($input) {
    $saved=get_option('fuwari_settings', []); $clean=is_array($saved)?$saved:[];
    if(!is_array($input))return $clean;
    $defaults=fuwari_defaults();
    foreach (fuwari_fields() as $fields) foreach ($fields as $field) {
        [$key,$zh,$en,$type]=$field;
        if (!array_key_exists($key,$input)) continue;
        $value=is_scalar($input[$key]) ? (string)$input[$key] : '';
        if ($type==='select') $clean[$key]=array_key_exists($value,$field[4]) ? $value : $defaults[$key];
        elseif ($type==='checkbox') $clean[$key]=(int)!empty($value);
        elseif ($type==='number') {
            $limits=['hue'=>[0,360],'page_size'=>[1,50],'toc_depth'=>[1,3],'banner_home_height'=>[20,100],'banner_height'=>[15,90]];
            [$min,$max]=$limits[$key] ?? [1,100]; $clean[$key]=max($min,min($max,(int)$value));
        } elseif (in_array($type,['url','media','font'],true)) $clean[$key]=esc_url_raw($value,['http','https']);
        elseif ($type==='code') $clean[$key]=str_replace(['</style','<script'],['',''],wp_strip_all_tags($value));
        elseif ($type==='textarea') $clean[$key]=sanitize_textarea_field($value);
        else $clean[$key]=sanitize_text_field($value);
    }
    if (!empty($clean['updates_repo']) && !preg_match('~^[\w.-]+/[\w.-]+$~',$clean['updates_repo'])) $clean['updates_repo']='';
    foreach (['updates_manifest','cdn_base'] as $key) if (!empty($clean[$key]) && !str_starts_with($clean[$key],'https://')) $clean[$key]='';
    if(isset($clean['banner_height'],$clean['banner_home_height']))$clean['banner_home_height']=max($clean['banner_height'],$clean['banner_home_height']);
    return $clean;
}
add_action('admin_init', function () { register_setting('fuwari_settings_group','fuwari_settings',['type'=>'array','sanitize_callback'=>'fuwari_sanitize_settings']); });
add_action('admin_menu', function () { add_theme_page('Fuwari','Fuwari','manage_options','fuwari-settings','fuwari_settings_page'); });
add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook!=='appearance_page_fuwari-settings') return;
    wp_enqueue_media();
    wp_enqueue_style('fuwari-admin',get_template_directory_uri().'/assets/admin.css',[],FUWARI_VERSION);
    wp_enqueue_script('fuwari-admin',get_template_directory_uri().'/assets/admin.js',[],FUWARI_VERSION,true);
});
function fuwari_settings_page() {
    if (!current_user_can('manage_options')) return;
    $tabs=['general'=>['基本与个人信息','General & profile'],'banner'=>['横幅','Banner'],'display'=>['显示与文章','Display & posts'],'fonts'=>['字体','Fonts'],'resources'=>['资源','Resources'],'advanced'=>['自定义样式','Custom CSS'],'updates'=>['升级与备份','Updates & backup']];
    $tab=sanitize_key($_GET['tab']??'general'); if(!isset($tabs[$tab]))$tab='general';
    echo '<div class="wrap fuwari-admin"><h1>Fuwari <span>v'.esc_html(FUWARI_VERSION).'</span></h1><p>'.esc_html(fuwari_admin_text('主题设置保存于数据库，升级主题时保留。','Settings are stored in the database and retained during theme upgrades.')).'</p>';
    settings_errors();
    $notices=['backup'=>['当前主题已备份。','The current theme has been backed up.'],'rollback'=>['主题版本已恢复，字体与设置已保留。','The selected theme version has been restored. Fonts and settings are retained.']];
    $notice=sanitize_key($_GET['fuwari_notice']??'');if(isset($notices[$notice]))echo '<div class="notice notice-success is-dismissible"><p>'.esc_html(fuwari_admin_text(...$notices[$notice])).'</p></div>';
    echo '<nav class="nav-tab-wrapper">'; foreach($tabs as $id=>$labels) echo '<a class="nav-tab '.($tab===$id?'nav-tab-active':'').'" href="'.esc_url(admin_url('themes.php?page=fuwari-settings&tab='.$id)).'">'.esc_html(fuwari_admin_text(...$labels)).'</a>'; echo '</nav>';
    echo '<form action="options.php" method="post">'; settings_fields('fuwari_settings_group');
    echo '<table class="form-table" role="presentation">';
    foreach(fuwari_fields()[$tab] as $field) {
        [$key,$zh,$en,$type]=$field; $value=fuwari_option($key); $id='fuwari-'.$key; $name='fuwari_settings['.$key.']';
        echo '<tr><th><label for="'.esc_attr($id).'">'.esc_html(fuwari_admin_text($zh,$en)).'</label></th><td>';
        if($type==='checkbox') echo '<input type="hidden" name="'.esc_attr($name).'" value="0"><input type="checkbox" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="1" '.checked($value,1,false).'>';
        elseif($type==='select') { echo '<select id="'.esc_attr($id).'" name="'.esc_attr($name).'">'; foreach($field[4] as $v=>$label) echo '<option value="'.esc_attr($v).'" '.selected($value,$v,false).'>'.esc_html($label).'</option>'; echo '</select>'; }
        elseif(in_array($type,['textarea','code'],true)) echo '<textarea class="large-text '.($type==='code'?'code':'').'" rows="'.($type==='code'?12:4).'" id="'.esc_attr($id).'" name="'.esc_attr($name).'">'.esc_textarea($value).'</textarea>';
        else {
            echo '<input class="regular-text" type="'.($type==='number'?'number':($type==='url'?'url':'text')).'" id="'.esc_attr($id).'" name="'.esc_attr($name).'" value="'.esc_attr($value).'">';
            if(in_array($type,['media','font'],true))echo ' <button type="button" class="button fuwari-media" data-target="'.esc_attr($id).'" data-kind="'.esc_attr($type).'">'.esc_html(fuwari_admin_text('选择或上传','Select or upload')).'</button>';
            if($type==='media' && $value) echo '<div><img class="fuwari-preview" src="'.esc_url($value).'" alt=""></div>';
        }
        echo '</td></tr>';
    }
    echo '</table>';
    if($tab==='fonts') echo '<p class="description">'.esc_html(fuwari_admin_text('中文默认跟随系统字体：苹果优先苹方，Windows 优先微软雅黑，其他系统使用可用的中文无衬线字体，无需下载中文字体。仿宋或自定义模式可选择 WOFF2/WOFF 文件；媒体库字体在主题升级后保留。英文和代码分别配置。','Chinese defaults to system fonts: PingFang on Apple devices, Microsoft YaHei on Windows, and available CJK sans-serif fonts elsewhere, with no Chinese font download. FangSong and custom modes accept WOFF2/WOFF files; uploaded fonts are retained across theme upgrades. English and code fonts are configured independently.')).'</p>';
    if($tab==='resources') echo '<p class="description">'.esc_html(fuwari_admin_text('CDN 目录须与主题 assets 目录内容一致；加载失败时回退到本地。图标使用内嵌 SVG，自定义字体保留所选媒体地址。','Mirror the complete theme assets directory on the CDN. Failed assets fall back to local files. Icons are inline SVG; custom fonts retain their selected media URLs.')).'</p>';
    submit_button(fuwari_admin_text('保存设置','Save settings')); echo '</form>';
    echo '<p><a class="button" href="'.esc_url(home_url('/')).'" target="_blank">'.esc_html(fuwari_admin_text('查看网站','View site')).'</a></p>';
    if($tab==='updates') {
        echo '<section class="fuwari-tools"><h2>'.esc_html(fuwari_admin_text('主题升级与设置备份','Theme updates & settings backup')).'</h2><p>'.esc_html(fuwari_admin_text('在线升级需要配置本主题的发布仓库或更新清单；原 Fuwari Astro 仓库不能直接用作 WordPress 更新包。','Configure this WordPress theme’s release repository or manifest for online updates. The upstream Astro repository is not a WordPress update package.')).'</p>';
        echo '<a class="button" href="'.esc_url(admin_url('theme-install.php?upload')).'">'.esc_html(fuwari_admin_text('上传 ZIP 升级','Upgrade with ZIP')).'</a> ';
        echo '<a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=fuwari_check_updates'),'fuwari_check_updates')).'">'.esc_html(fuwari_admin_text('检查更新','Check for updates')).'</a> ';
        echo '<a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=fuwari_export'),'fuwari_export')).'">'.esc_html(fuwari_admin_text('导出设置','Export settings')).'</a>';
        echo ' <a class="button" href="'.esc_url(wp_nonce_url(admin_url('admin-post.php?action=fuwari_backup_theme'),'fuwari_backup_theme')).'">'.esc_html(fuwari_admin_text('备份当前主题','Back up current theme')).'</a>';
        $backups=glob(WP_CONTENT_DIR.'/fuwari-backups/fuwari-wp-*.zip')?:[];rsort($backups);
        if($backups){echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="fuwari_rollback_theme">';wp_nonce_field('fuwari_rollback_theme');echo '<p><select name="backup">';foreach($backups as $file)echo '<option>'.esc_html(basename($file)).'</option>';echo '</select> ';submit_button(fuwari_admin_text('回退到所选版本','Restore selected version'),'secondary','submit',false);echo '</p></form>';}
        echo '<form method="post" enctype="multipart/form-data" action="'.esc_url(admin_url('admin-post.php')).'"><input type="hidden" name="action" value="fuwari_import">'; wp_nonce_field('fuwari_import'); echo '<p><input type="file" name="settings" accept=".json" required> ';submit_button(fuwari_admin_text('导入设置','Import settings'),'secondary','submit',false); echo '</p></form></section>';
    }
    echo '</div>';
}
add_action('admin_post_fuwari_export',function(){
    if(!current_user_can('manage_options'))wp_die('Forbidden',403);check_admin_referer('fuwari_export');
    nocache_headers();header('Content-Type: application/json; charset=utf-8');header('Content-Disposition: attachment; filename="fuwari-settings.json"');
    echo wp_json_encode(['schema'=>1,'version'=>FUWARI_VERSION,'settings'=>array_merge(fuwari_defaults(),get_option('fuwari_settings',[]))],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
});
add_action('admin_post_fuwari_import',function(){
    if(!current_user_can('manage_options'))wp_die('Forbidden',403);check_admin_referer('fuwari_import');
    if(empty($_FILES['settings']['tmp_name']) || $_FILES['settings']['size']>102400)wp_die('Invalid file');
    $data=json_decode(file_get_contents($_FILES['settings']['tmp_name']),true);
    if(($data['schema']??0)!==1 || !is_array($data['settings']??null))wp_die('Invalid settings');
    update_option('fuwari_settings',fuwari_sanitize_settings($data['settings']));
    wp_safe_redirect(admin_url('themes.php?page=fuwari-settings&tab=updates&settings-updated=true'));exit;
});
