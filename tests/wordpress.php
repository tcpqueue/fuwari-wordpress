<?php
// Run with wp eval-file tests/wordpress.php on a disposable WordPress test fixture.
function fuwari_check($condition,$message){if(!$condition)WP_CLI::error($message);WP_CLI::log('PASS '.$message);}
foreach(['code','admonition','math','github'] as $name){$block=WP_Block_Type_Registry::get_instance()->get_registered('fuwari/'.$name);fuwari_check($block&&$block->api_version===3,'Block registration: '.$name);}
foreach(['article','callout','columns','card'] as $name)fuwari_check(WP_Block_Patterns_Registry::get_instance()->is_registered('fuwari/'.$name),'Pattern: '.$name);
$settings=wp_get_global_settings();fuwari_check(count($settings['typography']['fontFamilies']['theme']??[])===3,'Configured editor font families');
$manifest=json_decode(file_get_contents(get_template_directory().'/assets/bundle/manifest.json'),true);
foreach(['frontend/app.js','frontend/editor.js'] as $entry)fuwari_check(is_file(get_template_directory().'/assets/bundle/'.$manifest[$entry]['file']),'Built entry: '.$entry);
fuwari_check(fuwari_media_value('/wp-content/uploads/avatar.png')==='/wp-content/uploads/avatar.png','Site-relative avatar path');
fuwari_check(fuwari_media_value(ABSPATH.'wp-content/uploads/avatar.png')==='/wp-content/uploads/avatar.png','Site-root server avatar path');
fuwari_check(fuwari_media_value('/etc/passwd')===''&&fuwari_media_value('../avatar.png')===''&&fuwari_media_value('javascript:alert(1)')==='','Avatar URL traversal and protocol checks');
$social=fuwari_sanitize_social([['name'=>'GitHub','icon'=>'fa6-brands:github','url'=>'https://github.com/saicaca/fuwari'],['icon'=>'fa6-brands:telegram','url'=>''],['icon'=>'fa6-brands:x-twitter','url'=>'https://x.com/example'],['icon'=>'invalid','url'=>'javascript:alert(1)']]);
fuwari_check(count(fuwari_social_rows($social))===2&&str_contains($social,'X|fa6-brands:x-twitter'),'Multiple social links and empty-row filtering');
$old='<!-- wp:fuwari/code {"content":"echo 1;","language":"php","filename":"test.php"} --><pre class="wp-block-fuwari-code" data-fuwari-language="php" data-fuwari-filename="test.php" data-fuwari-lines="1"><code class="language-php">echo 1;</code></pre><!-- /wp:fuwari/code -->';
fuwari_check(str_contains(do_blocks($old),'data-fuwari-filename="test.php"'),'Legacy code markup remains readable');
fuwari_check(str_contains(do_shortcode('[fuwari_note type="tip" title="Example"]Text[/fuwari_note]'),'bdm-tip'),'Legacy note shortcode');
fuwari_check(str_contains(do_shortcode('[fuwari_github repo="saicaca/fuwari"]'),'https://github.com/saicaca/fuwari'),'GitHub shortcode');
$github=do_blocks('<!-- wp:fuwari/github {"repo":"saicaca/fuwari","anchor":"repository"} /-->');
fuwari_check(str_contains($github,'fuwari-github-card')&&str_contains($github,'id="repository"'),'Dynamic repository block and anchor');
$user=get_users(['role'=>'administrator','number'=>1]);wp_set_current_user($user[0]->ID);
foreach(['post','page'] as $type){
    $id=wp_insert_post(['post_type'=>$type,'post_title'=>'Fuwari integration fixture','post_status'=>'draft']);
    $route=$type==='post'?'posts':'pages';$request=new WP_REST_Request('POST','/wp/v2/'.$route.'/'.$id);
    $request->set_param('meta',['_fuwari_language'=>'en','_fuwari_description'=>'Editor metadata fixture']);
    $response=rest_do_request($request);
    fuwari_check($response->get_status()===200&&get_post_meta($id,'_fuwari_language',true)==='en'&&get_post_meta($id,'_fuwari_description',true)==='Editor metadata fixture','REST metadata saves for '.$type);
    wp_trash_post($id);
}
$open=get_posts(['post_status'=>'publish','numberposts'=>1,'comment_status'=>'open']);
if($open){
    $original=get_option('fuwari_settings',[]);$captcha_enabled=false;
    $force_settings=function()use($original,&$captcha_enabled){return array_merge($original,['comment_captcha_enable'=>(int)$captcha_enabled]);};
    add_filter('pre_option_fuwari_settings',$force_settings);
    add_filter('comment_flood_filter','__return_false',100);
    add_filter('notify_moderator','__return_false');add_filter('notify_post_author','__return_false');
    wp_set_current_user(0);
    $comment=wp_handle_comment_submission(wp_slash(['comment_post_ID'=>$open[0]->ID,'author'=>'Fuwari integration fixture','email'=>'fuwari-fixture@example.com','comment'=>'Fuwari comment submission fixture.']));
    fuwari_check($comment instanceof WP_Comment,'Native visitor comment submission');
    if(get_option('comment_moderation')||get_option('comment_previously_approved'))fuwari_check($comment->comment_approved==='0','Visitor comment follows moderation settings');
    wp_trash_comment($comment->comment_ID);
    $captcha_enabled=true;$post_id=$open[0]->ID;
    $types=[];$within_bounds=true;
    for($i=0;$i<200;$i++){
        $challenge=fuwari_captcha_create($post_id);$data=json_decode(base64_decode(explode('.',$challenge['token'])[0]),true);
        $answer=$data['op']==='+'?$data['a']+$data['b']:$data['a']-$data['b'];$types[$data['op']]=true;
        $within_bounds=$within_bounds&&$data['a']>=0&&$data['a']<=100&&$data['b']>=0&&$data['b']<=100&&$answer>=0&&$answer<=100;
    }
    fuwari_check($within_bounds&&count($types)===2,'CAPTCHA addition/subtraction and results remain within 0–100');
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,'',''))&&is_wp_error(fuwari_captcha_validate($post_id,[],'1')),'CAPTCHA rejects missing or malformed tokens');
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,$challenge['token'],(string)($answer+1))),'CAPTCHA rejects incorrect answers');
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id+1,$challenge['token'],(string)$answer)),'CAPTCHA cannot be reused on a different post');
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,$challenge['token'].'0',(string)$answer)),'CAPTCHA rejects a modified signature');
    $data['time']=time()-16*MINUTE_IN_SECONDS;$payload=base64_encode(wp_json_encode($data));$expired=$payload.'.'.hash_hmac('sha256',$payload,wp_salt('nonce'));
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,$expired,(string)$answer)),'CAPTCHA rejects expired questions');
    foreach([0,100] as $boundary){$data['a']=$boundary;$data['b']=0;$data['op']='+';$data['time']=time();$payload=base64_encode(wp_json_encode($data));$token=$payload.'.'.hash_hmac('sha256',$payload,wp_salt('nonce'));fuwari_check(fuwari_captcha_validate($post_id,$token,(string)$boundary)===true,'CAPTCHA accepts boundary answer '.$boundary);}
    $_POST=[];
    $rejected=wp_handle_comment_submission(wp_slash(['comment_post_ID'=>$post_id,'author'=>'Fuwari integration fixture','email'=>'fuwari-fixture@example.com','comment'=>'Comment without required verification.']));
    fuwari_check(is_wp_error($rejected)&&$rejected->get_error_code()==='fuwari_captcha_expired','Native comment submission rejects missing CAPTCHA');
    $_POST=['fuwari_captcha_token'=>$challenge['token'],'fuwari_captcha_answer'=>(string)$answer];
    $verified=wp_handle_comment_submission(wp_slash(['comment_post_ID'=>$post_id,'author'=>'Fuwari integration fixture','email'=>'fuwari-fixture@example.com','comment'=>'Comment with valid verification.']));
    fuwari_check($verified instanceof WP_Comment,'Native comment submission accepts a valid CAPTCHA');
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,$challenge['token'],(string)$answer)),'Submitted CAPTCHA cannot be reused');
    wp_trash_comment($verified->comment_ID);$_POST=[];
    wp_set_current_user($user[0]->ID);
    $request=new WP_REST_Request('POST','/wp/v2/comments');$request->set_param('post',$post_id);$request->set_param('content','REST verification fixture.');
    $response=rest_do_request($request);
    fuwari_check($response->get_status()===400&&($response->get_data()['code']??'')==='fuwari_captcha_expired','REST comment creation cannot bypass CAPTCHA');
    $rest_challenge=fuwari_captcha_create($post_id);$rest_data=json_decode(base64_decode(explode('.',$rest_challenge['token'])[0]),true);$rest_answer=$rest_data['op']==='+'?$rest_data['a']+$rest_data['b']:$rest_data['a']-$rest_data['b'];
    $request->set_param('fuwari_captcha_token',$rest_challenge['token']);$request->set_param('fuwari_captcha_answer',(string)$rest_answer);
    $response=rest_do_request($request);
    if($response->get_status()!==201)WP_CLI::log('REST CAPTCHA status '.$response->get_status().' '.wp_json_encode($response->get_data()));
    fuwari_check($response->get_status()===201,'REST comment creation accepts valid CAPTCHA fields');
    wp_trash_comment($response->get_data()['id']);
    fuwari_check(is_wp_error(fuwari_captcha_validate($post_id,$rest_challenge['token'],(string)$rest_answer)),'REST submission consumes its CAPTCHA');
    remove_filter('pre_option_fuwari_settings',$force_settings);
    remove_filter('comment_flood_filter','__return_false',100);
    remove_filter('notify_moderator','__return_false');remove_filter('notify_post_author','__return_false');
}
WP_CLI::success('WordPress integration checks passed.');
