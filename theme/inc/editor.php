<?php
defined('ABSPATH') || exit;
add_action('init', function () {
    foreach (['code','admonition','math'] as $name) register_block_type(get_template_directory().'/blocks/'.$name);
    register_block_type('fuwari/github', [
        'api_version'=>3, 'title'=>'Fuwari GitHub', 'category'=>'embed',
        'attributes'=>['repo'=>['type'=>'string','default'=>'']],
        'supports'=>['html'=>false,'anchor'=>true,'align'=>['wide','full'],'spacing'=>['margin'=>true]],
        'render_callback'=>function ($attributes) {
            $card=fuwari_github_card($attributes);
            return $card ? '<div '.get_block_wrapper_attributes(!empty($attributes['anchor'])?['id'=>$attributes['anchor']]:[]).'>'.$card.'</div>' : '';
        },
    ]);
    foreach ([
        ['core/paragraph','fuwari-lead','导语','Lead paragraph'],
        ['core/quote','fuwari-callout','突出引用','Callout quote'],
        ['core/image','fuwari-framed','图片边框','Framed image'],
        ['core/group','fuwari-card','内容卡片','Content card'],
        ['core/table','fuwari-compact','紧凑表格','Compact table'],
        ['core/separator','fuwari-accent','主题色分隔线','Accent separator'],
    ] as [$block,$name,$zh,$en]) register_block_style($block,['name'=>$name,'label'=>fuwari_admin_text($zh,$en)]);
    register_block_pattern_category('fuwari',['label'=>'Fuwari']);
    $paragraph='<!-- wp:paragraph --><p>'.esc_html(fuwari_admin_text('在这里填写正文。','Write your content here.')).'</p><!-- /wp:paragraph -->';
    $heading='<!-- wp:heading --><h2 class="wp-block-heading">'.esc_html(fuwari_admin_text('文章小节','Article section')).'</h2><!-- /wp:heading -->';
    $note='<!-- wp:fuwari/admonition {"type":"tip","title":"'.esc_attr(fuwari_admin_text('提示','Tip')).'"} --><blockquote class="wp-block-fuwari-admonition admonition bdm-tip"><span class="bdm-title">'.esc_html(fuwari_admin_text('提示','Tip')).'</span>'.$paragraph.'</blockquote><!-- /wp:fuwari/admonition -->';
    foreach ([
        ['article','文章起步','Article starter','<!-- wp:paragraph {"className":"is-style-fuwari-lead"} --><p class="is-style-fuwari-lead">'.esc_html(fuwari_admin_text('用一段简短的导语介绍这篇文章。','Introduce this article with a short lead paragraph.')).'</p><!-- /wp:paragraph -->'.$heading.$paragraph.$note.$heading.$paragraph],
        ['callout','提示与正文','Callout and text',$note.$paragraph],
        ['columns','双栏内容','Two columns','<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column">'.$heading.$paragraph.'</div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column">'.$heading.$paragraph.'</div><!-- /wp:column --></div><!-- /wp:columns -->'],
        ['card','内容卡片','Content card','<!-- wp:group {"className":"is-style-fuwari-card","layout":{"type":"constrained"}} --><div class="wp-block-group is-style-fuwari-card">'.$heading.$paragraph.'</div><!-- /wp:group -->'],
    ] as [$slug,$zh,$en,$content]) register_block_pattern('fuwari/'.$slug,['title'=>fuwari_admin_text($zh,$en),'categories'=>['fuwari'],'content'=>$content,'description'=>fuwari_admin_text('可直接修改的 Fuwari 文章区块。','Editable Fuwari content blocks.')]);
});
add_filter('wp_theme_json_data_theme', function ($data) {
    $fonts=fuwari_typography();
    $settings=json_decode(file_get_contents(get_template_directory().'/theme.json'),true)['settings'];
    $names=['primary'=>['主题色','Theme color'],'soft'=>['柔和背景','Soft background'],'ink'=>['墨色','Ink'],'white'=>['白色','White'],'blue'=>['蓝色','Blue'],'green'=>['绿色','Green'],'amber'=>['琥珀色','Amber'],'red'=>['红色','Red'],'theme-soft'=>['主题渐变','Theme gradient'],'small'=>['小号','Small'],'medium'=>['正文','Body'],'large'=>['大号','Large'],'x-large'=>['标题','Heading'],'xx-large'=>['展示','Display']];
    foreach([['color','palette'],['color','gradients'],['typography','fontSizes']] as [$group,$key])foreach($settings[$group][$key] as &$preset)if(isset($names[$preset['slug']]))$preset['name']=fuwari_admin_text(...$names[$preset['slug']]);unset($preset);
    $settings['shadow']['presets'][0]['name']=fuwari_admin_text('柔和阴影','Soft shadow');
    $settings['typography']['fontFamilies']=[
        ['slug'=>'body','name'=>fuwari_admin_text('主题字体','Theme font'),'fontFamily'=>$fonts['sans']],
        ['slug'=>'system','name'=>fuwari_admin_text('系统字体','System fonts'),'fontFamily'=>'system-ui,"PingFang SC","Microsoft YaHei",sans-serif'],
        ['slug'=>'mono','name'=>fuwari_admin_text('代码字体','Code font'),'fontFamily'=>$fonts['mono']],
    ];
    return $data->update_with(['version'=>3,'settings'=>$settings]);
});
add_action('enqueue_block_assets', function () {
    wp_enqueue_style('fuwari-content',fuwari_asset('content.css','css'),is_admin()?[]:['fuwari-theme-css'],fuwari_asset_version('content.css'));
    if (!is_admin()) return;
    foreach (fuwari_entry('frontend/editor.js')['css']??[] as $i=>$css) wp_enqueue_style('fuwari-editor-bundle-'.$i,fuwari_asset('bundle/'.$css,'css',true),[],FUWARI_VERSION);
    $fontcss=file_get_contents(get_template_directory().'/assets/fonts.css');
    $fontcss=preg_replace_callback('~url\(upstream/([^)]*)\)~',fn($match)=>'url('.wp_json_encode(fuwari_asset('upstream/'.$match[1],'fonts',true)).')',$fontcss);
    $fonts=fuwari_typography();
    wp_add_inline_style('fuwari-content',$fontcss.$fonts['faces'].'.editor-styles-wrapper{--hue:'.(int)fuwari_option('hue').';--fuwari-sans:'.$fonts['sans'].';--fuwari-code:'.$fonts['mono'].';}');
});
add_action('enqueue_block_editor_assets', function () {
    $entry=fuwari_entry('frontend/editor.js');
    wp_enqueue_script('fuwari-editor',fuwari_asset('bundle/'.($entry['file']??'editor.js'),'js',true),[
        'wp-blocks','wp-element','wp-block-editor','wp-components','wp-i18n','wp-data','wp-plugins','wp-editor','wp-edit-post','wp-server-side-render','wp-html-entities',
    ],FUWARI_VERSION,true);
    $labels=[
        'code'=>['Fuwari 代码','Fuwari Code'],'language'=>['代码语言','Code language'],'filename'=>['文件名','Filename'],'lines'=>['显示行号','Show line numbers'],
        'note'=>['Fuwari 提示框','Fuwari Note'],'type'=>['提示类型','Callout type'],'title'=>['标题','Title'],'preview'=>['显示预览','Show preview'],'source'=>['源代码','Source code'],
        'math'=>['Fuwari 公式','Fuwari Math'],'latex'=>['LaTeX 公式','LaTeX formula'],'display'=>['独立公式','Display equation'],'github'=>['Fuwari GitHub 卡片','Fuwari GitHub card'],
        'repo'=>['仓库（owner/repo）','Repository (owner/repo)'],'repoHelp'=>['输入公开仓库，例如 saicaca/fuwari。','Enter a public repository, such as saicaca/fuwari.'],
        'repoInvalid'=>['请填写 owner/repo 格式的仓库名称。','Enter a repository in owner/repo format.'],'postSettings'=>['Fuwari 文章设置','Fuwari post settings'],
        'postLanguage'=>['正文语言','Content language'],'original'=>['保持原语言','Keep original language'],'description'=>['首页摘要','Homepage description'],
        'descriptionHelp'=>['只修改首页卡片摘要，不修改正文。','Only changes the homepage card excerpt, not the article content.'],
        'canvasMode'=>['编辑区明暗预览','Editor color preview'],'theme'=>['跟随主题','Follow theme'],'light'=>['亮色','Light'],'dark'=>['暗色','Dark'],
        'noteType'=>['普通提示','Note'],'tipType'=>['技巧','Tip'],'importantType'=>['重要','Important'],'warningType'=>['警告','Warning'],'cautionType'=>['注意','Caution'],
        'loading'=>['正在生成预览…','Generating preview…'],'emptyCode'=>['输入代码后显示预览。','Enter code to see a preview.'],
    ];
    $labels=array_map(fn($pair)=>fuwari_admin_text(...$pair),$labels);
    wp_add_inline_script('fuwari-editor','window.FuwariEditor='.wp_json_encode(['labels'=>$labels,'mode'=>fuwari_option('theme_mode')],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT).';','before');
});
