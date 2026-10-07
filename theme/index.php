<?php defined('ABSPATH')||exit;get_header();if(get_query_var('fuwari_archive')){get_template_part('parts/archive');}else{?>
<?php if(is_search()||is_archive()):?><div class="card-base px-6 py-4 mb-4"><h1 class="text-2xl font-bold"><?php if(is_search())echo esc_html(fuwari_t('search').': '.get_search_query());else the_archive_title();?></h1></div><?php endif;?>
<div class="transition flex flex-col rounded-[var(--radius-large)] bg-[var(--card-bg)] py-1 md:py-0 md:bg-transparent md:gap-4 mb-4">
<?php if(have_posts()):while(have_posts()):the_post();get_template_part('parts/post-card');endwhile;else:?><div class="card-base p-8"><?php echo fuwari_label('empty');?></div><?php endif;?></div>
<?php $pagination=paginate_links(['type'=>'array','prev_text'=>'‹','next_text'=>'›']);if($pagination):?><nav class="fuwari-pagination flex gap-3 justify-center mb-4" aria-label="Pagination"><?php foreach($pagination as $item)echo $item;?></nav><?php endif;?>
<?php }get_footer();?>
