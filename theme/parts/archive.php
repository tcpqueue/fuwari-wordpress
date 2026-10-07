<?php
defined('ABSPATH')||exit;
$args=['post_type'=>'post','post_status'=>'publish','posts_per_page'=>-1,'orderby'=>'date','order'=>'DESC'];
if(!empty($_GET['category'])&&is_string($_GET['category']))$args['category_name']=sanitize_title(wp_unslash($_GET['category']));
if(!empty($_GET['tag'])&&is_string($_GET['tag']))$args['tag']=sanitize_title(wp_unslash($_GET['tag']));
$archive=new WP_Query($args);$groups=[];
foreach($archive->posts as $entry)$groups[get_the_date('Y',$entry)][]=$entry;
?>
<section class="card-base fuwari-archive" aria-label="<?php echo esc_attr(fuwari_t('archive'));?>">
<?php foreach($groups as $year=>$entries):?>
<div class="fuwari-archive-year"><div class="fuwari-archive-date text-2xl font-bold text-75"><?php echo esc_html($year);?></div><div class="fuwari-archive-track"><div class="fuwari-year-dot"></div></div><div class="text-50"><?php echo count($entries).' '.fuwari_label(count($entries)===1?'post':'posts');?></div></div>
<?php foreach($entries as $entry):$tags=get_the_tags($entry->ID);?>
<a data-no-swup class="fuwari-archive-row group btn-plain" href="<?php echo esc_url(get_permalink($entry));?>"><time class="fuwari-archive-date text-sm text-50"><?php echo esc_html(get_the_date('m-d',$entry));?></time><div class="fuwari-archive-track"><div class="fuwari-archive-dot"></div></div><div class="fuwari-archive-title text-75 font-bold"><?php echo esc_html(get_the_title($entry));?></div><div class="fuwari-archive-tags text-sm text-30"><?php echo esc_html(implode(' ',array_map(fn($tag)=>'#'.$tag->name,$tags?:[])));?></div></a>
<?php endforeach;endforeach;if(!$groups)echo '<p>'.fuwari_label('empty').'</p>';?></section>
