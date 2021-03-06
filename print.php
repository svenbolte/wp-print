<?php
/*
 * WordPress Plugin: wpprint
 * Copyright (c) 2012 Lester "GaMerZ" Chan
 * File Written By:
 * - Lester "GaMerZ" Chan
 * File Information:
 * - Process Printing Page
 * - wp-content/plugins/wpprint/print.php
 */


### Variables
$links_text = '';

### Actions
add_action('init', 'print_content');

### Filters
add_filter('wp_title', 'print_pagetitle');
add_filter('comments_template', 'print_template_comments');

### Print Options
$print_options = get_option('print_options');

### PDF-Ausgabe, wenn ?pdfout=2
if (isset($_GET['pdfout'])) {
  $ppc = sanitize_text_field($_GET['pdfout']);
  if ( $ppc=='2' ) {
	include(plugin_dir_path( __FILE__ ) . 'print-pdf.php');
  }
}

### Load Print Post/Page Template from stylesheet dir (child theme)
if(file_exists(get_stylesheet_directory().'/print-posts.php')) {
	include(get_stylesheet_directory().'/print-posts.php');
### Then try template dir (parent theme) 
} elseif(file_exists(get_template_directory().'/print-posts.php')) {
	include(get_template_directory().'/print-posts.php');
### Fall back to default template in plugin dir	
} else {
	include(WP_PLUGIN_DIR.'/wpprint/print-posts.php');
}