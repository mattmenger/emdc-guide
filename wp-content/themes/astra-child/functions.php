<?php
/**
 * Astra Child Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra Child
 * @since 1.0.0
 */

/**
 * Define Constants
 */
define( 'CHILD_THEME_ASTRA_CHILD_VERSION', '1.0.0' );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'astra-child-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_ASTRA_CHILD_VERSION, 'all' );

}

/**
 * Our intialization code
 */
function child_init() {
	register_post_type('guide_resource', array(
		'has_archive'		=>	true,
		'label'				=>	__('Resources'),
		'singular_label'	=>	__('Resource'),
		'description'		=>	__('Scripture engagement resources to support your ministry.'),
		'public' 			=>	true,
		'show_ui'			=>	true,
		'capability_type'	=>	'post',
		'hierarchical'		=>	false,
		'menu_icon'			=>	'dashicons-admin-media',
		'menu_position'		=>	20,
		'rewrite' 			=> 	array('slug' => 'resources'),
		'query_var'			=>	false,
		'taxonomies'		=>	array('post_tag','category'),
		'supports'			=>	array('title', 'editor', 'thumbnail'),
        'show_in_rest' 		=> 	true
	));
}
/**
 * @link https://wpdevelopment.courses/articles/custom-spacing-settings/
 */
function child_enable_gutenberg_custom_spacing() {
	add_theme_support( 'custom-spacing' );
}
/**
 * Fixes the sort order on Archive page
 * https://wordpress.stackexchange.com/a/39818
 */
function child_blog_change_sort_order($query) {
	if(is_archive()) {
		$query->set( 'order', 'ASC' );
		$query->set( 'orderby', 'title' );
	}
};
/**
 * Change the archive title
 *
 * @link https://wordpress.stackexchange.com/a/175903
 */
function child_get_archive_title($title) {
	if (is_post_type_archive('guide_resource')) {
		$title = 'Resources';
	}
	return $title;
}

add_action( 'after_setup_theme', 'child_enable_gutenberg_custom_spacing' );
add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );
add_action( 'init', 'child_init' );
add_action( 'pre_get_posts', 'child_blog_change_sort_order' );
add_filter( 'get_the_archive_title', 'child_get_archive_title' );
