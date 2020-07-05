<?php

if (isset($_GET['pdfout'])) {
  $ppc = $_GET['pdfout'];
  if ( $ppc='1' ) {    output_pdf(); }
}

function output_pdf() {
	include(WP_PLUGIN_DIR.'/wp-print/pdfhelper.php');
	$pdf = new PDF_HTML();
    $posts = get_posts( 'posts_per_page=5' );

    if( ! empty( $posts ) ) {
        global $pdf;
        $title_line_height = 10;
        $content_line_height = 8;

        $pdf->AddPage();
        $pdf->SetFont( 'Arial', '', 42 );
        $pdf->Write(20, 'Atomic Smash FPDF Tutorial');

        foreach( $posts as $post ) {

            $pdf->AddPage();
            $pdf->SetFont( 'Arial', '', 22 );
            $pdf->Write($title_line_height, $post->post_title);

            // Add a line break
            $pdf->Ln(15);

            // Image
            $page_width = $pdf->GetPageWidth() - 20;
            $max_image_width = $page_width;

            $image = get_the_post_thumbnail_url( $post->ID );
            if( ! empty( $image ) ) {
                $pdf->Image( $image, null, null, 100 );
            }
            
            // Post Content
            $pdf->Ln(10);
            $pdf->SetFont( 'Arial', '', 12 );
            $pdf->WriteHTML($post->post_content);
        }
    }

    $pdf->Output('D','atomic_smash_fpdf_tutorial.pdf');
    exit;
}

?>