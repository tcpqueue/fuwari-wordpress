<?php
defined('ABSPATH')||exit;
function fuwari_comment($comment,$args,$depth){
    $GLOBALS['comment']=$comment;
    $name=get_comment_author($comment);
    $initial=mb_substr($name,0,1);
    echo '<li ';comment_class('fuwari-comment',$comment);echo ' id="comment-'.(int)$comment->comment_ID.'">';
    echo '<article id="div-comment-'.(int)$comment->comment_ID.'" class="fuwari-comment-body" data-comment-name="'.esc_attr($name).'"><header class="fuwari-comment-header"><span class="fuwari-comment-avatar" aria-hidden="true">'.esc_html($initial).'</span><div class="fuwari-comment-meta"><div class="fuwari-comment-author">';
    $url=get_comment_author_url($comment);
    if($url)echo '<a data-no-swup href="'.esc_url($url).'" rel="external nofollow ugc" target="_blank">'.esc_html($name).'</a>';else echo esc_html($name);
    if($comment->user_id&&(int)$comment->user_id===(int)get_post_field('post_author',$comment->comment_post_ID))echo '<span class="fuwari-comment-badge">'.fuwari_label('commentAuthor').'</span>';
    echo '</div><a data-no-swup class="fuwari-comment-date" href="'.esc_url(get_comment_link($comment)).'"><time datetime="'.esc_attr(get_comment_date('c',$comment)).'">'.esc_html(get_comment_date('', $comment).' '.get_comment_time('H:i')).'</time></a></div></header>';
    if($comment->comment_approved==='0')echo '<p class="fuwari-comment-pending">'.fuwari_label('commentPending').'</p>';
    echo '<div class="custom-md fuwari-comment-content">';comment_text($comment);echo '</div><footer class="fuwari-comment-actions">';
    comment_reply_link(array_merge($args,['add_below'=>'div-comment','depth'=>$depth,'max_depth'=>$args['max_depth'],'reply_text'=>fuwari_label('commentReply'),'reply_to_text'=>fuwari_t('replyTo').' %s']),$comment);
    edit_comment_link(fuwari_label('commentEdit'));
    echo '</footer></article>';
}
function fuwari_comment_form(){
    $commenter=wp_get_current_commenter();$required=(bool)get_option('require_name_email');$mark=$required?' *':'';
    $attrs=$required?' required aria-required="true"':'';
    $fields=[];
    foreach(['author'=>['commentName','text',$commenter['comment_author']],'email'=>['commentEmail','email',$commenter['comment_author_email']],'url'=>['commentWebsite','url',$commenter['comment_author_url']]] as $key=>[$label,$type,$value]){
        $fields[$key]='<p class="comment-form-'.$key.'"><label for="'.$key.'">'.fuwari_label($label).($key==='url'?'':$mark).'</label><input id="'.$key.'" name="'.$key.'" type="'.$type.'" value="'.esc_attr($value).'" '.($key==='url'?'':$attrs).' autocomplete="'.(['author'=>'name','email'=>'email','url'=>'url'][$key]).'"></p>';
    }
    if(get_option('show_comments_cookies_opt_in'))$fields['cookies']='<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes" '.checked(!empty($commenter['comment_author_email']),true,false).'><label for="wp-comment-cookies-consent">'.fuwari_label('commentCookies').'</label></p>';
    comment_form([
        'fields'=>$fields,'title_reply'=>esc_html(fuwari_t('leaveComment')),'title_reply_to'=>esc_html(fuwari_t('replyTo')).' %s',
        'cancel_reply_link'=>fuwari_label('cancelReply'),'label_submit'=>fuwari_t('postComment'),'class_submit'=>'submit btn-regular',
        'comment_notes_before'=>'<p class="comment-notes">'.fuwari_label('commentPrivacy').'</p>','comment_notes_after'=>'',
        'logged_in_as'=>'<p class="logged-in-as">'.fuwari_label('loggedIn').' <strong>'.esc_html(wp_get_current_user()->display_name).'</strong> · <a data-no-swup href="'.esc_url(get_edit_profile_url()).'">'.fuwari_label('editProfile').'</a> · <a data-no-swup href="'.esc_url(wp_logout_url(get_permalink())).'">'.fuwari_label('logout').'</a></p>',
        'must_log_in'=>'<p class="must-log-in"><a data-no-swup href="'.esc_url(wp_login_url(get_permalink())).'">'.fuwari_label('login').'</a></p>',
        'comment_field'=>'<p class="comment-form-comment"><label for="comment">'.fuwari_label('commentText').' *</label><textarea id="comment" name="comment" rows="5" required aria-required="true" maxlength="65525"></textarea></p>'.fuwari_captcha_field(),
        'submit_button'=>'<button name="%1$s" type="submit" id="%2$s" class="%3$s">'.fuwari_label('postComment').'</button>',
    ]);
}
