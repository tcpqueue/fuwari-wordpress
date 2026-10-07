<?php
defined('ABSPATH') || exit;

function fuwari_captcha_create($post_id) {
    $a=random_int(0,100);
    $operator=random_int(0,1)?'+':'-';
    $b=random_int(0,$operator==='+'?100-$a:$a);
    $payload=base64_encode(wp_json_encode(['post'=>(int)$post_id,'a'=>$a,'b'=>$b,'op'=>$operator,'time'=>time(),'nonce'=>bin2hex(random_bytes(16))]));
    return ['question'=>$a.' '.($operator==='-'?'−':'+').' '.$b.' = ?','token'=>$payload.'.'.hash_hmac('sha256',$payload,wp_salt('nonce'))];
}

function fuwari_captcha_validate($post_id,$token,$answer) {
    $expired=new WP_Error('fuwari_captcha_expired',fuwari_t('captchaExpired'),['status'=>400]);
    if(!is_string($token)||strlen($token)>1024||!preg_match('/^([A-Za-z0-9+\/=]+)\.([a-f0-9]{64})$/D',$token,$parts))return $expired;
    if(!hash_equals(hash_hmac('sha256',$parts[1],wp_salt('nonce')),$parts[2]))return $expired;
    $data=json_decode(base64_decode($parts[1],true)?:'',true);
    if(!is_array($data)||($data['post']??0)!==(int)$post_id||!is_int($data['time']??null)||$data['time']>time()||time()-$data['time']>=15*MINUTE_IN_SECONDS)return $expired;
    if(!is_int($data['a']??null)||!is_int($data['b']??null)||$data['a']<0||$data['a']>100||$data['b']<0||$data['b']>100||!in_array($data['op']??'',['+','-'],true))return $expired;
    $expected=$data['op']==='+'?$data['a']+$data['b']:$data['a']-$data['b'];
    if($expected<0||$expected>100||get_transient('fuwari_captcha_used_'.md5($token)))return $expired;
    if(!is_scalar($answer)||!preg_match('/^\d{1,3}$/D',trim((string)$answer))||(int)$answer!==$expected)return new WP_Error('fuwari_captcha_answer',fuwari_t('captchaIncorrect'),['status'=>400]);
    return true;
}

function fuwari_captcha_required($comment) {
    return fuwari_option('comment_captcha_enable')&&in_array($comment['comment_type']??'',['','comment'],true)&&!(is_admin()&&current_user_can('moderate_comments'));
}

function fuwari_captcha_request() {
    return $GLOBALS['fuwari_captcha_rest_fields']??[
        'token'=>wp_unslash($_POST['fuwari_captcha_token']??''),
        'answer'=>wp_unslash($_POST['fuwari_captcha_answer']??''),
    ];
}

add_filter('pre_comment_approved',function($approved,$comment){
    if(is_wp_error($approved)||!fuwari_captcha_required($comment))return $approved;
    $fields=fuwari_captcha_request();
    $result=fuwari_captcha_validate($comment['comment_post_ID'],$fields['token'],$fields['answer']);
    return is_wp_error($result)?$result:$approved;
},99,2);

add_action('wp_insert_comment',function($id,$comment){
    $comment=(array)$comment;
    if(!fuwari_captcha_required($comment))return;
    $fields=fuwari_captcha_request();
    if(fuwari_captcha_validate($comment['comment_post_ID'],$fields['token'],$fields['answer'])===true)set_transient('fuwari_captcha_used_'.md5($fields['token']),1,15*MINUTE_IN_SECONDS);
},10,2);

add_filter('rest_request_before_callbacks',function($response,$handler,$request){
    unset($GLOBALS['fuwari_captcha_rest_fields']);
    if($request->get_route()==='/wp/v2/comments'&&$request->get_method()==='POST')$GLOBALS['fuwari_captcha_rest_fields']=['token'=>$request->get_param('fuwari_captcha_token'),'answer'=>$request->get_param('fuwari_captcha_answer')];
    return $response;
},10,3);

add_filter('rest_pre_insert_comment',function($comment,$request){
    if(is_wp_error($comment)||$request->get_method()!=='POST'||$request->get_param('id')||!fuwari_captcha_required((array)$comment))return $comment;
    $fields=['token'=>$request->get_param('fuwari_captcha_token'),'answer'=>$request->get_param('fuwari_captcha_answer')];
    $data=(array)$comment;
    $result=fuwari_captcha_validate($data['comment_post_ID'],$fields['token'],$fields['answer']);
    if(is_wp_error($result))return $result;
    $GLOBALS['fuwari_captcha_rest_fields']=$fields;
    return $comment;
},10,2);
add_filter('rest_request_after_callbacks',function($response){unset($GLOBALS['fuwari_captcha_rest_fields']);return $response;});

function fuwari_captcha_ajax() {
    nocache_headers();
    $post_id=absint($_POST['post_id']??0);$post=get_post($post_id);
    if(!fuwari_option('comments_enable')||!fuwari_option('comment_captcha_enable')||!$post||!comments_open($post_id)||post_password_required($post)||($post->post_status!=='publish'&&!current_user_can('edit_post',$post_id)))wp_send_json_error(['message'=>fuwari_t('captchaRefreshError')],400);
    wp_send_json_success(fuwari_captcha_create($post_id));
}
add_action('wp_ajax_fuwari_comment_captcha','fuwari_captcha_ajax');
add_action('wp_ajax_nopriv_fuwari_comment_captcha','fuwari_captcha_ajax');

function fuwari_captcha_field() {
    if(!fuwari_option('comment_captcha_enable'))return '';
    $post_id=get_the_ID();$challenge=fuwari_captcha_create($post_id);
    return '<div class="comment-form-captcha" data-fuwari-captcha data-post-id="'.(int)$post_id.'"><label for="fuwari-captcha-answer">'.fuwari_label('captchaLabel').' *</label><div class="fuwari-captcha-row"><span class="fuwari-captcha-question" aria-live="polite">'.esc_html($challenge['question']).'</span><input id="fuwari-captcha-answer" name="fuwari_captcha_answer" type="text" inputmode="numeric" pattern="[0-9]{1,3}" maxlength="3" autocomplete="off" required aria-required="true" aria-describedby="fuwari-captcha-help"><button class="btn-regular fuwari-captcha-refresh" type="button" hidden>'.fuwari_label('captchaRefresh').'</button></div><input type="hidden" name="fuwari_captcha_token" value="'.esc_attr($challenge['token']).'"><p id="fuwari-captcha-help" class="fuwari-captcha-help">'.fuwari_label('captchaHelp').'</p><p class="fuwari-captcha-status" role="status"></p></div>';
}
