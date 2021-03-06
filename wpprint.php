<?php
/*
Plugin Name: WP Print
Description: Show posts and pages in a newspaper Style Print View. A PDF downloadable file of a post may be created
Author: Lester 'GaMerZ' Chan und PBMod
Plugin URI: https://github.com/svenbolte/wpprint
Author URI: https://github.com/svenbolte/
Text Domain: wpprint
Domain Path: /
Version: 9.2.58.1.10
Stable tag: 9.2.58.1.10
Requires at least: 5.1
Tested up to: 5.7.2
Requires PHP: 7.4
*/

### Create Text Domain For Translations
add_action( 'plugins_loaded', 'print_textdomain' );
function print_textdomain() {
	load_plugin_textdomain( 'wpprint', false, plugin_basename( dirname( __FILE__ ) ) . '/' );
}

### Function: Print Option Menu
add_action('admin_menu', 'print_menu');
function print_menu() {
	add_options_page(__('Print', 'wpprint'), __('Print', 'wpprint'), 'manage_options', 'wpprint/print-options.php') ;
}

### Function: Add htaccess Rewrite Endpoint - this handles all the rules
add_action( 'init', 'wp_print_endpoint' );
function wp_print_endpoint() {
	add_rewrite_endpoint( 'print', EP_PERMALINK | EP_PAGES );
}

### Function: Print Public Variables
add_filter('query_vars', 'print_variables');
function print_variables($public_query_vars) {
	$public_query_vars[] = 'print';
	return $public_query_vars;
}


### Function: Short Code For DO NOT PRINT Content
add_shortcode('donotprint', 'print_donotprint_shortcode');
function print_donotprint_shortcode($atts, $content = null) {
	return do_shortcode($content);
}
function print_donotprint_shortcode2($atts, $content = null) {
	return;
}

### Function: Print Content
function print_content($display = true) {
	global $links_text, $link_number, $max_link_number, $matched_links,  $pages, $multipage, $numpages, $post;
	if (!isset($matched_links)) {
		$matched_links = array();
	}
	$content = '';
	if(post_password_required()) {
		$content = get_the_password_form();
	} else {
		if($multipage) {
			for($page = 0; $page < $numpages; $page++) {
				$content .= $pages[$page];
			}
		} else {
			$content = $pages[0];
		}
		if(function_exists('email_rewrite')) {
			remove_shortcode('donotemail');
			add_shortcode('donotemail', 'email_donotemail_shortcode2');
		}
		remove_shortcode('donotprint');
		add_shortcode('donotprint', 'print_donotprint_shortcode2');
		remove_shortcode('print_link');
		add_shortcode('print_link', 'print_link_shortcode2');
		$content = apply_filters('the_content', $content);
		$content = str_replace(']]>', ']]&gt;', $content);
		if(!print_can('images')) {
			$content = remove_image($content);
		}
		if(!print_can('videos')) {
			$content = remove_video($content);
		}
		if(print_can('links')) {
			$links_text='';
			preg_match_all('/<a(.+?)href=[\"\'](.+?)[\"\'](.*?)>(.+?)<\/a>/', $content, $matches);
			for ($i=0; $i < count($matches[0]); $i++) {
				$link_match = $matches[0][$i];
				$link_url = $matches[2][$i];
				if(substr($link_url, 0, 2) == '//') {
					$link_url = (is_ssl() ? 'https:' : 'http:') . $link_url;
				} elseif(stristr($link_url, 'https://')) {
					$link_url =(strtolower(substr($link_url,0,8)) != 'https://') ?get_option('home') . $link_url : $link_url;
				} else if(stristr($link_url, 'mailto:')) {
					$link_url =(strtolower(substr($link_url,0,7)) != 'mailto:') ?get_option('home') . $link_url : $link_url;
				} else if($link_url[0] == '#') {
					$link_url = $link_url;
				} else {
					$link_url =(strtolower(substr($link_url,0,7)) != 'http://') ?get_option('home') . $link_url : $link_url;
				}
				$link_text = $matches[4][$i];
				$new_link = true;
				$link_url_hash = md5($link_url);
				if (!isset($matched_links[$link_url_hash])) {
					$link_number = ++$max_link_number;
					$matched_links[$link_url_hash] = $link_number;
				} else {
					$new_link = false;
					$link_number = $matched_links[$link_url_hash];
				}
				$content = str_replace_one($link_match, "<a href=\"$link_url\" rel=\"external\">".$link_text.'</a> <sup>['.number_format_i18n($link_number).']</sup>', $content);
				if ($new_link) {
					if(preg_match('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/',$link_text)) {
						$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.__('Image', 'wpprint').': <b><span dir="ltr">'.$link_url.'</span></b></p>';
					} else {
						$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.$link_text.': <b><span dir="ltr">'.$link_url.'</span></b></p>';
					}
				}
			}
		}
	}
	if($display) {
		echo $content;
	} else {
		return $content;
	}
}

### Function: Print Categories
function print_categories($before = '', $after = '') {
	$temp_cat = strip_tags(get_the_category_list(','));
	$temp_cat = explode(', ', $temp_cat);
	$temp_cat = implode($after.__(',', 'wpprint').' '.$before, $temp_cat);
	echo $before.$temp_cat.$after;
}

### Function: Print Comments Content
function print_comments_content($display = true) {
	global $links_text, $link_number, $max_link_number, $matched_links;
	if (!isset($matched_links)) {
		$matched_links = array();
	}
	$content  = get_comment_text();
	$content = apply_filters('comment_text', $content);
	if(!print_can('images')) {
		$content = remove_image($content);
	}
	if(!print_can('videos')) {
		$content = remove_video($content);
	}
	if(print_can('links')) {
		preg_match_all('/<a(.+?)href=[\"\'](.+?)[\"\'](.*?)>(.+?)<\/a>/', $content, $matches);
		for ($i=0; $i < count($matches[0]); $i++) {
			$link_match = $matches[0][$i];
			$link_url = $matches[2][$i];
			if(stristr($link_url, 'https://')) {
				 $link_url =(strtolower(substr($link_url,0,8)) != 'https://') ?get_option('home') . $link_url : $link_url;
			} else if(stristr($link_url, 'mailto:')) {
				$link_url =(strtolower(substr($link_url,0,7)) != 'mailto:') ?get_option('home') . $link_url : $link_url;
			} else if($link_url[0] == '#') {
				$link_url = $link_url;
			} else {
				$link_url =(strtolower(substr($link_url,0,7)) != 'http://') ?get_option('home') . $link_url : $link_url;
			}
			$new_link = true;
			$link_url_hash = md5($link_url);
			if (!isset($matched_links[$link_url_hash])) {
				$link_number = ++$max_link_number;
				$matched_links[$link_url_hash] = $link_number;
			} else {
				$new_link = false;
				$link_number = $matched_links[$link_url_hash];
			}
			$content = str_replace_one($link_match, "<a href=\"$link_url\" rel=\"external\">".$link_text.'</a> <sup>['.number_format_i18n($link_number).']</sup>', $content);
			if ($new_link) {
				if(preg_match('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/',$link_text)) {
					$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.__('Image', 'wpprint').': <b><span dir="ltr">'.$link_url.'</span></b></p>';
				} else {
					$links_text .= '<p style="margin: 2px 0;">['.number_format_i18n($link_number).'] '.$link_text.': <b><span dir="ltr">'.$link_url.'</span></b></p>';
				}
			}
		}
	}
	if($display) {
		echo $content;
	} else {
		return $content;
	}
}

### Function: Print Comments
function print_comments_number() {
	global $post;
	$comment_status = $post->comment_status;
	if($comment_status == 'open') {
		$num_comments = get_comments_number();
		if($num_comments == 0) {
			$comment_text = __('No Comments', 'wpprint');
		} else {
			$comment_text = sprintf(_n('%s Comment', '%s Comments', $num_comments, 'wpprint'), number_format_i18n($num_comments));
		}
	} else {
		$comment_text = __('Comments Disabled', 'wpprint');
	}
	if(post_password_required()) {
		_e('Comments Hidden', 'wpprint');
	} else {
		echo $comment_text;
	}
}

### Function: Print Links
function print_links($text_links = '') {
	global $links_text;
	if(empty($text_links)) {
		$text_links = __('URLs in this post:', 'wpprint');
	}
	if(!empty($links_text)) {
		echo $text_links.$links_text;
	}
}

### Function: Load wpprint
add_action('template_redirect', 'wp_print', 5);
function wp_print() {
	global $wp_query;
	if( array_key_exists( 'print' , $wp_query->query_vars ) ) {
		include(WP_PLUGIN_DIR.'/wpprint/print.php');
		exit();
	}
}

### Function: Add Print Comments Template
function print_template_comments() {
	if(file_exists(get_stylesheet_directory().'/print-comments.php')) {
		$file = get_stylesheet_directory().'/print-comments.php';
	} else {
		$file = WP_PLUGIN_DIR.'/wpprint/print-comments.php';
	}
	return $file;
}

### Function: Print Page Title
function print_pagetitle($page_title) {
	$page_title .= ' &raquo; '.__('Print', 'wpprint');
	return $page_title;
}

### Function: Can Print?
function print_can($type) {
	$print_options = get_option( 'print_options' );
	if ( isset( $print_options[$type] ) ) {
		return (int) $print_options[$type];
	}

	return 0;
}

### Function: Remove Image From Text
function remove_image($content) {
	$content= preg_replace('/<img(.+?)src=[\"\'](.+?)[\"\'](.*?)>/', '',$content);
	return $content;
}

### Function: Remove Video From Text
function remove_video( $content ) {
	$content= preg_replace( '/<object[^>]*?>.*?<\/object>/', '',$content );
	$content= preg_replace( '/<embed[^>]*?>.*?<\/embed>/', '',$content );
	$content= preg_replace( '/<iframe[^>]*?>.*?<\/iframe>/', '',$content );
	return $content;
}

### Function: Replace One Time Only
function str_replace_one($search, $replace, $content){
	if ($pos = strpos($content, $search)) {
		return substr($content, 0, $pos).$replace.substr($content, $pos+strlen($search));
	} else {
		return $content;
	}
}

### Function PDF Output newest 10 Posts/Pages
include(plugin_dir_path( __FILE__ ) . 'pdfhelper.php');
$pdf = new PDF_HTML();

if (isset($_GET['pdfout'])) {
  $ppc = sanitize_text_field($_GET['pdfout']);
  if ( $ppc=='1' ) {    output_pdf(); }
}

function catch_that_image($post) {
  $first_img = '';
  ob_start();
  ob_end_clean();
  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
  if(empty($first_img)){ $first_img = ''; } else { $first_img = $matches [1] [0]; }
  return $first_img;
}

function output_pdf() {
	$excludecat = get_category_by_slug( 'softwareversionen' );
    $laargs = array(
    'posts_per_page'   => 20,
    'orderby'          => 'date',
    'order'            => 'DESC',
    'category__not_in' => $excludecat 
	);
	$posts = get_posts( $laargs );

    if( ! empty( $posts ) ) {
		global $pdf;
		$pdf->SetAutoPageBreak(true,30);
        $title_line_height = 10;
        $content_line_height = 8;
        $pdf->AddPage();
        foreach( $posts as $post ) {
			if($pdf->GetY() > 220) { $pdf->AddPage(); }
            $pdf->Ln(10);
            $pdf->SetFont( 'Arial', '', 18 );
            $pdf->Write($title_line_height, utf8_decode($post->post_title));
            $pdf->SetFont( 'Arial', '', 10 );
			// Datum und Kategorie
            $pdf->Ln(8);
            $categorie = get_the_category($post); 
            if (!empty($categorie)) $pdf->WriteHTML('Kategorie: ' . $categorie[0]->cat_name);
			date_default_timezone_set('Europe/Berlin');
			$cdate = get_post_time( get_option( 'date_format' ), false, $post, true );
			$mdate = get_post_modified_time( get_option( 'date_format' ), false, $post, true );
            $pdf->WriteHTML(' erstellt ' . $cdate . ' aktualisiert ' . $mdate);
			// Reading time
			$content = get_post_field( 'post_content', $post );
			$content = strip_tags( strip_shortcodes( $content ) );
			$word_count = str_word_count( $content );
			$reading_time = ceil( $word_count / 275 );
			$s = ceil($word_count / 275 * 60);
			$reading_time = " Lesezeit: ";
			if ($s < 60) {
				$reading_time .= sprintf('%02d', $s%60). " Sek";	
			} else {
				$reading_time .= sprintf('%02d:%02d', ($s/60%60), $s%60) . " Min";	
			}			
			$reading_time .= " (" . $word_count . " Wörter) ";
            $pdf->WriteHTML(utf8_decode($reading_time));

            // Image
			if( !post_password_required($post) ) {
				$imgatt = catch_that_image($post);
				if( ! empty( $imgatt ) ) {
					$pdf->Ln(8);
					$pdf->Cell( 40, 40, $pdf->InlineImage($imgatt, $pdf->GetX(), $pdf->GetY(), 100), 0, 0, 'L', false );
				}		
			}	
            // Post Content
			$content = '';
			if(post_password_required($post)) {
				$content = 'Inhalt nur für Abonnenten';
			} else {
				$content = $post->post_content;
			}	
            $pdf->Ln(8);
            $pdf->SetFont( 'Arial', '', 11 );
            $pdf->WriteHTML(utf8_decode($content));
        }
    }
    $pdf->Output('I','wp-pdf-output.pdf');
    exit;
}


### Function: Activate Plugin
register_activation_hook( __FILE__, 'print_activation' );
function print_activation( $network_wide ) {
	// Add Options
	$option_name = 'print_options';
	$option = array(
		'post_text'   => __('Print This Post', 'wpprint'),
		'page_text'   => __('Print This Page', 'wpprint'),
		'comments'    => 0,
		'links'       => 1,
		'images'      => 1,
		'thumbnail'   => 0,
		'videos'      => 0,
		'disclaimer'  => sprintf(__('Copyright &copy; %s %s. All rights reserved.', 'wpprint'), date('Y'), get_option('blogname'))
	);

	if ( is_multisite() && $network_wide ) {
		$ms_sites = function_exists( 'get_sites' ) ? get_sites() : wp_get_sites();
		if( 0 < count( $ms_sites ) ) {
			foreach ( $ms_sites as $ms_site ) {
				$blog_id = isset( $ms_site['blog_id'] ) ? $ms_site['blog_id'] : $ms_site->blog_id;
				switch_to_blog( $blog_id );
				add_option( $option_name, $option );
				print_activate();
			}
		}
		restore_current_blog();
	} else {
		add_option( $option_name, $option );
		print_activate();
	}
}

function print_activate() {
	flush_rewrite_rules();
}
?>
