<?php
defined('ABSPATH') || exit;
define('FUWARI_VERSION', '1.0.1');
foreach (['options', 'frontend', 'content', 'updates'] as $module) require_once __DIR__ . '/inc/' . $module . '.php';
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form','comment-form','comment-list','gallery','caption','style','script']);
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('editor-styles');
    add_editor_style('assets/editor.css');
    register_nav_menus(['primary'=>'Main navigation']);
});
add_action('pre_get_posts', function ($query) {
    if (!is_admin() && $query->is_main_query() && $query->is_home()) $query->set('posts_per_page', (int) fuwari_option('page_size'));
});
add_filter('upload_mimes', function ($mimes) {
    if (current_user_can('manage_options')) { $mimes['woff2']='font/woff2'; $mimes['woff']='font/woff'; }
    return $mimes;
});
add_filter('wp_check_filetype_and_ext', function ($data, $file, $filename, $mimes) {
    if (current_user_can('manage_options') && preg_match('/[.](woff2?)$/i', $filename, $m)) {
        $signature = file_get_contents($file, false, null, 0, 4);
        if (in_array($signature, ['wOFF','wOF2'], true)) $data = ['ext'=>strtolower($m[1]),'type'=>'font/'.strtolower($m[1]),'proper_filename'=>false];
    }
    return $data;
}, 10, 4);
add_action('after_switch_theme', function () { flush_rewrite_rules(false); });
