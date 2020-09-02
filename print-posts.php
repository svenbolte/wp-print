<?php
/*
 * WordPress Plugin: WP-Print
 * Copyright (c) 2012 Lester "GaMerZ" Chan MOD PB 2020
 *
 * File Written By:
 * - Lester "GaMerZ" Chan
 * - http://lesterchan.net
 *
 * File Information:
 * - Printer Friendly Post/Page Template
 * - wp-content/plugins/wp-print/print-posts.php
 */

global $wp, $post

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title><?php bloginfo('name'); ?> <?php wp_title(); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
	<meta name="Robots" content="noindex, nofollow" />
	<?php if(@file_exists(get_stylesheet_directory().'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php elseif(@file_exists(get_template_directory().'/print-css.css')): ?>
		<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/print-css.css" type="text/css" media="screen, print" />
	<?php else: ?>
		<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css.css'); ?>" type="text/css" media="screen, print" />
	<?php endif; ?>
	<?php if ( is_rtl() ) : ?>
		<?php if(@file_exists(get_stylesheet_directory().'/print-css-rtl.css')): ?>
			<link rel="stylesheet" href="<?php echo get_stylesheet_directory_uri(); ?>/print-css-rtl.css" type="text/css" media="screen, print" />
		<?php else: ?>
			<link rel="stylesheet" href="<?php echo plugins_url('wp-print/print-css-rtl.css'); ?>" type="text/css" media="screen, print" />
		<?php endif; ?>
	<?php endif; ?>
	<link rel="canonical" href="<?php the_permalink(); ?>" />
</head>
<body>

<main role="main" class="center">

	<?php if (have_posts()): ?>

		<header class="entry-header">
			<span class="hat">
			<?php
			if ( function_exists( 'the_custom_logo' ) ) {
				//   the_custom_logo();
				$custom_logo_id = get_theme_mod( 'custom_logo' );
				$logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
				if ( isset($custom_logo_id) && !empty($custom_logo_id) ) {
					?>
					<a title="<?php bloginfo( 'description' ); ?>" class="site-logo" href="<?php echo esc_url( get_site_url('/') ) ?>" rel="home">
					<img style="width:220px;max-width:220px" title="<?php bloginfo( 'name' ); ?>" alt="<?php bloginfo( 'name' ); ?>" src="<?php echo $logo[0];  ?>">
					</a>
					<?php
				} else {
					?>
					<p class="site-title">
					<a title="Zur Startseite" href="<?php echo esc_url( get_site_url('/') ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a>
					</p>
					<?php
					}
			} elseif ( $logo = get_theme_mod( 'logo-upload', false ) ) {
			?>
				<a title="<?php bloginfo( 'description' ); ?>" class="site-logo" href="<?php echo esc_url( get_site_url('/') ) ?>" rel="home">
					<img style="width:220px;max-width:220px" title="<?php bloginfo( 'name' ); ?>" alt="<?php bloginfo( 'name' ); ?>" src="<?php esc_url( $logo ) ?>">
				</a>
			<?php
			} 
			?>
			</span>
		<div style="text-align: center;font-size:1.3em"><?php  echo bloginfo( 'name' ); ?>
		</div>
		<p style="text-align: center">
		<?php echo bloginfo( 'url' ) ?>	&nbsp; 
		<?php echo bloginfo( 'description' ) .' &nbsp; ' . stripslashes($print_options['disclaimer']);  ?><br>
		<span id="print-link">
			<?php
			echo '<a href="#Print" onclick="window.print();return false;" title="' . __('Click here to print.', 'wp-print') . '">Drucken</a> &nbsp; ';
			echo '<a href="'.add_query_arg( array('pdfout'=>1), $wp->request ).'" title="Offline PDF-Zeitung">PDF-Offline-Zeitung</a> &nbsp; ';
			echo '<a href="'.add_query_arg( array('pdfout'=>2,'print'=>1), $wp->request ).'" title="Ansicht als PDF herunterladen">diese Posts als PDF</a> ';
			?>
		</span>
		</p>
		</header>
			
<div id="posts-container">
	
			<?php while (have_posts()): the_post(); ?>
		<article class="penguin-post">
			<h1 class="entry-title">
				<?php the_title(); ?>
			</h1>
			<span class="entry-date">
				<?php _e('Posted By', 'wp-print'); ?> 
				<cite><?php the_author(); ?></cite> 
				<?php _e('On', 'wp-print'); ?> 
				<?php 
				// Datum kurz und Langanzeige	
				$diff = time() - get_post_time( 'U', false, $post, true );
				echo get_post_time( 'l, d.m.Y H:i:s', false, $post, true ) . " (" . ago(get_post_time( 'U, d.m.Y H:i:s', false, $post, true )) . ') ';
				$diffmod=round(( get_the_modified_time( 'U', false, $post, true ) - get_post_time( 'U', false, $post, true ) ) /3600/24);
				if ( $diffmod > 0 ) {	
					echo " &nbsp; aktualisiert " . get_the_modified_time( 'l, d.m.Y H:i:s', false, $post, true ) . " (" . ago(get_the_modified_time( 'U, d.m.Y H:i:s', false, $post, true )) . ")";
				}				
				echo "<br>Kategorien: <strong>";
				print_categories();
				echo "</strong> &nbsp; ";
				echo " &nbsp; Lesezeit: " . theme_slug_reading_time($post->ID) . " &nbsp; &nbsp; ";	
				// Kommentare ----------------------------------------------------------------
				if (  ! post_password_required() && ( comments_open() ) ) :
					comments_popup_link( __( 'Leave a comment', 'penguin' ), __( '1 Comment', 'penguin' ), __( '% Comments', 'penguin' ) ); 	
				endif;
				?>
			</span>
			
		<?php if(print_can('thumbnail')): ?>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="thumbnail">
					<?php the_post_thumbnail('medium'); ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<div class="entry-content">
			<?php
			print_content();
			?>
		</div>

	<div class="comments">
		<?php if(print_can('comments')): ?>
			<?php comments_template(); ?>
		<?php endif; ?>
	</div>
	
	</article> 

	<div style="border:1px solid #e1e1e1;margin-bottom:25px">
		<p>
			<?php _e('URL to article', 'wp-print'); ?>: 
			<strong dir="ltr">
				<?php the_permalink(); ?>
			</strong>
		</p>
		
		<?php if(print_can('links')): ?>
			<p><?php print_links(); ?></p>
		<?php endif; ?>
	
	</div>
	
	<?php endwhile; ?>
</div>


	<footer class="footer">
		<?php else: ?>
			<p>
				<?php _e('No posts matched your criteria.', 'wp-print'); ?>
			</p>
		<?php endif; ?>
	</footer>

</main>

</body>
</html>
