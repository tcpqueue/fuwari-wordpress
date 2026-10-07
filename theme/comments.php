<?php defined('ABSPATH')||exit;if(post_password_required())return;?>
<section id="comments" class="card-base fuwari-comments px-6 md:px-9 py-6 mb-4" aria-label="<?php echo esc_attr(fuwari_t('comments'));?>">
<header class="fuwari-comments-heading"><h2><?php echo fuwari_label('comments');?></h2><span class="fuwari-comments-count"><?php echo (int)get_comments_number();?></span></header>
<?php if(have_comments()):?>
<ol class="fuwari-comment-list"><?php wp_list_comments(['avatar_size'=>0,'style'=>'ol','short_ping'=>true,'callback'=>'fuwari_comment']);?></ol>
<?php the_comments_pagination(['prev_text'=>fuwari_t('olderComments'),'next_text'=>fuwari_t('newerComments'),'screen_reader_text'=>fuwari_t('comments')]);?>
<?php elseif(comments_open()):?><p class="fuwari-comment-empty"><?php echo fuwari_label('commentEmpty');?></p><?php endif;?>
<?php if(comments_open())fuwari_comment_form();else echo '<p class="fuwari-comment-empty">'.fuwari_label('commentClosed').'</p>'; ?>
</section>
