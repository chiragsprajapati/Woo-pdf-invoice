<?php
/**
 * Plugin Name:       Woo PDF 
 * Plugin URI:        #
 * Description:       Automatically generate and attach customizable PDF Invoices to WooCommerce emails and connect with Dropbox, Google Drive, OneDrive or Egnyte.
 * Version:           1.0
 * Author:            Chirag Prajapati
 * Author URI:        http://glorywebs.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo-pdf
 * Domain Path:       /lang
 * WC requires at least: 2.6.14
 * WC tested up to: 3.4.4
 */


defined( 'ABSPATH' ) or exit;

/**
 *  use WPDF_VERSION.
 */
 
define( 'WPDF_VERSION', '1.0' );


/**
 * Load WooCommerce Woo PDF Invoice plugin.
 */
function _cpwpdf_load_plugin() {
 
	if ( ! defined( 'WPDF_DIR' ) ) {
		define( 'WPDF_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
	}
	if ( ! defined( 'CPWPDF_PLUGIN_BASENAME' ) ) {
			define( 'CPWPDF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		}

	if ( ! defined( 'WPDF_FILE' ) ) {
		define( 'WPDF_FILE', __FILE__ );
	}


	if ( file_exists( WPDF_DIR . '/mpdf/vendor/autoload.php' ) ) {
			require_once WPDF_DIR . '/mpdf/vendor/autoload.php';
	}
	require_once WPDF_DIR . '/pdf-view-function.php';
	
	
}
add_action( 'plugins_loaded', '_cpwpdf_load_plugin', 10 );

add_action( 'admin_init', 'wpdf_admin_pdf_callback' );

 function wpdf_admin_pdf_callback() {
	 
			
			if (isset( $_GET['post'] ) && isset( $_GET['wpdf_action'] ) && isset( $_GET['nonce'] ) ) {
				

			// sanitize data and verify nonce.
			$action = sanitize_key( $_GET['wpdf_action'] );
			$nonce  = sanitize_key( $_GET['nonce'] );
			if ( ! wp_verify_nonce( $nonce, $action ) ) {
				wp_die( 'Invalid request.' );
			}  
			///var_dump($_GET);
	
	
			// validate allowed user roles.
			$user          = wp_get_current_user();
			$allowed_roles = apply_filters( 'wpdf_allowed_roles_to_download_invoice', array(
				'administrator',
				'shop_manager',
			) );

		 	if ( ! array_intersect( $allowed_roles, $user->roles ) && !user_can( $user, 'manage_network_snippets' ) ) {
				wp_die( 'Access denied' );
			}

			$order_id = intval( $_GET['post'] );
		//	var_dump($order_id);
			
			$order = wc_get_order( $order_id );
			///var_dump($order);
			 
			$userdata = $order->get_user();
			$order_data = $order->get_data();
			//print_r($userdata);
			$order_id = $order_data['id'];
			$order_currency = $order_data['currency'];
			$order_total = $order_data['total'];
			$order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');
			$order_customer_id = $order_data['customer_id'];
			$order_billing_first_name = $order_data['billing']['first_name'];
			$order_billing_last_name = $order_data['billing']['last_name'];

			$html = '';
			$html .= "<h1>".$userdata->user_nicename."</h1>";
			  $html .='<style type="text/css">
					.tg  {border-collapse:collapse;border-spacing:0;}
					.tg td{font-family:Arial, sans-serif;font-size:14px;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
					.tg th{font-family:Arial, sans-serif;font-size:14px;font-weight:normal;padding:10px 5px;border-style:solid;border-width:1px;overflow:hidden;word-break:normal;border-color:black;}
					.tg .tg-lqy6{text-align:right;vertical-align:top}
					.tg .tg-0lax{text-align:left;vertical-align:top}
					</style>';  
			$html .='<table class="tg" width="100%">
					  <tr>
						<th class="tg-lqyx">Order ID#</th>
						<th class="tg-0lax">First Name</th>
						<th class="tg-0lax">Last name</th>
						<th class="tg-0lax">Price</th>
						<th class="tg-0lax">Date </th>
					  </tr><';
			 
			$html .='tr>		  
						<td class="tg-0lax">'.$order_id.'</td>
						<td class="tg-0lax">'.$order_billing_first_name.'</td>
						<td class="tg-0lax">'.$order_billing_last_name.'</td>
						<td class="tg-0lax">'.$order_total.' '.$order_currency.'</td>
						<td class="tg-0lax">'.$order_date_created.'</td>
					  </tr>
					</table>';
			
			
			//echo $html;
			  $mpdf = new \Mpdf\Mpdf();
			$mpdf->WriteHTML($html);
			$mpdf->Output(); 
			die(); 
			}
			
		}
	
function wpdf_add_order_export_pdf_column_header( $columns ) {

    $new_columns = array();

    foreach ( $columns as $column_name => $column_info ) {

        $new_columns[ $column_name ] = $column_info;

        if ( 'order_total' === $column_name ) {
            $new_columns['export_pdf'] = __( 'View PDF', 'my-textdomain' );
        }
    }

    return $new_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'wpdf_add_order_export_pdf_column_header', 20 );


function wpdf_add_order_export_pdf_column_content( $column ) {
    global $post;

    if ( 'export_pdf' === $column ) {

         $order    = wc_get_order( $post->ID );
       /* $currency = is_callable( array( $order, 'get_currency' ) ) ? $order->get_currency() : $order->order_currency;
        $profit   = '';
        $cost     = sv_helper_get_order_meta( $order, '_wc_cog_order_total_cost' );
        $total    = (float) $order->get_total();

        // don't check for empty() since cost can be '0'
        if ( '' !== $cost || false !== $cost ) {

            // now we can cast cost since we've ensured it was calculated for the order
            $cost   = (float) $cost;
            $profit = $total - $cost;
        }

        echo wc_price( $profit, array( 'currency' => $currency ) );
		 */
		
		$dd = new Wpdf_WooCommerce_PDF_Invoices;
		
		// display button to view invoice.
		$dd->show_invoice_button( __( 'View', 'woo-pdf' ), $post->ID, 'view', array(
					'class="button grant_access order-page invoice wpi"',
					'target="_blank"',
		) );
    }
}
add_action( 'manage_shop_order_posts_custom_column', 'wpdf_add_order_export_pdf_column_content' );






/* $mpdf = new \Mpdf\Mpdf();
$mpdf->WriteHTML('<h1>Hello world!</h1>');
$mpdf->Output(); */





?>

