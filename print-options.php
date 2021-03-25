<?php
### Variables Variables Variables
$base_name = plugin_basename( 'wp-print/print-options.php' );
$base_page = 'admin.php?page=' . $base_name;
$print_settings = array('print_options');


### Form Processing
if( ! empty( $_POST['Submit'] ) ) {
    check_admin_referer( 'wp-print_options' );

    $print_options = array();
    $print_options['post_text']         = ! empty( $_POST['print_post_text'] )  ? addslashes( trim( wp_filter_kses( $_POST['print_post_text'] ) ) ) : '';
    $print_options['page_text']         = ! empty( $_POST['print_page_text'] )  ? addslashes( trim( wp_filter_kses( $_POST['print_page_text'] ) ) ) : '';
    $print_options['comments']          = isset( $_POST['print_comments'] )     ? intval( $_POST['print_comments'] ): 0;
    $print_options['links']             = isset( $_POST['print_links'] )        ? intval( $_POST['print_links'] ) : 1;
    $print_options['images']            = isset( $_POST['print_images'] )       ? intval( $_POST['print_images'] ) : 0;
    $print_options['thumbnail']         = isset( $_POST['print_thumbnail'] )    ? intval( $_POST['print_thumbnail'] ) : 0;
    $print_options['videos']            = isset( $_POST['print_videos'] )       ? intval( $_POST['print_videos'] ) : 1;
    $print_options['disclaimer']        = ! empty( $_POST['print_disclaimer'] ) ? trim( $_POST['print_disclaimer'] ) : '';
    $update_print_queries = array();
    $update_print_text = array();
    $update_print_queries[] = update_option( 'print_options', $print_options );
    $update_print_text[] = __( 'Print Options', 'wp-print' );
    $i = 0;
    $text = '';
    foreach( $update_print_queries as $update_print_query ) {
        if( $update_print_query ) {
            $text .= '<p style="color: green;">' . $update_print_text[$i] . ' ' .__( 'Updated', 'wp-print' ) . '</p>';
        }
        $i++;
    }
    if( empty( $text ) ) {
        $text = '<p style="color: red;">' . __( 'No Print Option Updated', 'wp-print' ) . '</p>';
    }
}

$print_options = get_option( 'print_options' );
if(!empty($text)) { echo '<!-- Last Action --><div id="message" class="updated fade"><p>'.$text.'</p></div>'; } ?>
<form method="post" action="<?php echo admin_url('admin.php?page='.plugin_basename(__FILE__)); ?>">
<?php wp_nonce_field('wp-print_options'); ?>
<div class="wrap">
    <h2><?php _e('Print Options', 'wp-print'); ?></h2>
    <div class="postbox">
    <table class="form-table">
         <tr>
            <th scope="row" valign="top"><?php _e('Print Comments?', 'wp-print'); ?></th>
            <td>
                <select name="print_comments" size="1">
                    <option value="1"<?php selected('1', $print_options['comments']); ?>><?php _e('Yes', 'wp-print'); ?></option>
                    <option value="0"<?php selected('0', $print_options['comments']); ?>><?php _e('No', 'wp-print'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php _e('Print Links?', 'wp-print'); ?></th>
            <td>
                <select name="print_links" size="1">
                    <option value="1"<?php selected('1', $print_options['links']); ?>><?php _e('Yes', 'wp-print'); ?></option>
                    <option value="0"<?php selected('0', $print_options['links']); ?>><?php _e('No', 'wp-print'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php _e('Print Images?', 'wp-print'); ?></th>
            <td>
                <select name="print_images" size="1">
                    <option value="1"<?php selected('1', $print_options['images']); ?>><?php _e('Yes', 'wp-print'); ?></option>
                    <option value="0"<?php selected('0', $print_options['images']); ?>><?php _e('No', 'wp-print'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php _e('Print Thumbnail?', 'wp-print'); ?></th>
            <td>
                <select name="print_thumbnail" size="1">
                    <option value="1"<?php selected('1', $print_options['thumbnail']); ?>><?php _e('Yes', 'wp-print'); ?></option>
                    <option value="0"<?php selected('0', $print_options['thumbnail']); ?>><?php _e('No', 'wp-print'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top"><?php _e('Print Videos?', 'wp-print'); ?></th>
            <td>
                <select name="print_videos" size="1">
                    <option value="1"<?php selected('1', $print_options['videos']); ?>><?php _e('Yes', 'wp-print'); ?></option>
                    <option value="0"<?php selected('0', $print_options['videos']); ?>><?php _e('No', 'wp-print'); ?></option>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row" valign="top">
                <?php _e('Disclaimer/Copyright Text?', 'wp-print'); ?>
            </th>
            <td>
                <textarea rows="2" cols="80" name="print_disclaimer" id="print_template_disclaimer"><?php echo htmlspecialchars(stripslashes($print_options['disclaimer'])); ?></textarea><br /><?php _e('HTML is allowed.', 'wp-print'); ?><br />
            </td>
        </tr>
    </table>

	</div>
    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="<?php _e('Save Changes', 'wp-print'); ?>" />
    </p>
</div>
</form>