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
 * - Printer Friendly Post/Page Template for PDF Output
 * - wp-content/plugins/wp-print/print-pdf.php
 */

output_pdf2();

function output_pdf2() {
	if (have_posts()):
		global $pdf;
		$pdf->SetAutoPageBreak(true,30);
        $title_line_height = 10;
        $content_line_height = 8;
        $pdf->AddPage();
		while (have_posts()): the_post();
			if($pdf->GetY() > 220) { $pdf->AddPage(); }
            $pdf->Ln(10);
            $pdf->SetFont( 'Arial', '', 18 );
            $pdf->Write($title_line_height, utf8_decode(get_the_title()));
            $pdf->SetFont( 'Arial', '', 10 );
			// Datum und Kategorie
            $pdf->Ln(8);
            $categorie = get_categories();
            $pdf->WriteHTML(' Kategorie: ' . $categorie[0]->cat_name);
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
				$first_img = '';
				ob_start();
				ob_end_clean();
				$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i',  get_the_content() , $matches);
				$first_img = $matches [1] [0];
				// if(empty($first_img)){ $first_img = ''; }
				if( !empty( $first_img ) ) {
					$pdf->Ln(8);
					$pdf->Cell( 40, 40, $pdf->InlineImage($first_img, $pdf->GetX(), $pdf->GetY(), 100), 0, 0, 'L', false );
				}		
			}	
			
            // Post Content
			$content = '';
			if(post_password_required($post)) {
				$content = 'Inhalt nur für Abonnenten';
			} else {
				$content = get_the_content();
			}	
            $pdf->Ln(8);
            $pdf->SetFont( 'Arial', '', 11 );
            $pdf->WriteHTML(utf8_decode($content));
        endwhile;
    endif;

    $pdf->Output('I','wp-pdf-output.pdf');
    exit;
}

?>