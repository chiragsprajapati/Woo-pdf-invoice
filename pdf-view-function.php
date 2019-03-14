<?php
/**
 * Final WooCommerce PDF Invoices Class.
 *
 * Processes several hooks and filter callbacks.
 *
 * @author      Bas Elbers
 * @category    Class
 * @package     BE_WooCommerce_PDF_Invoices/Class
 * @version     1.0.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Wpdf_WooCommerce_PDF_Invoices' ) ) {

	
	/**
	 * Implements main function for attaching invoice to email and show invoice buttons.
	 */
	class Wpdf_WooCommerce_PDF_Invoices {
		
	/**
		 * Display invoice button html.
		 *
		 * @param string $title title attribute of button.
		 * @param int    $order_id WC_ORDER id.
		 * @param string $action action create, view or cancel.
		 * @param array  $attributes additional attributes.
		 */
		public function show_invoice_button( $title, $order_id, $action, $attributes = array() ) {
			$url = wp_nonce_url( add_query_arg( array(
				'post'         => $order_id,
				'action'       => 'edit',
				'wpdf_action' => $action,
			), admin_url( 'post.php' ) ), $action, 'nonce' );

			$url        = apply_filters( 'wpdf_invoice_url', $url, $order_id, $action );
			$attr_title = $title . ' ' . __( 'PDF Invoice', 'woocommerce-pdf-invoices' );

			printf( '<a href="%1$s" title="%2$s" %3$s>%4$s</a>', $url, $attr_title, join( ' ', $attributes ), $title );
		}
		
		
		public function admin_init_hooks() {
			add_action( 'admin_init', array( $this, 'admin_pdf_callback' ) );
		}
		
		/**
		 * Check if request is PDF action.
		 *
		 * @return bool
		 */
		private static function is_pdf_request() {
			return ( isset( $_GET['post'] ) && isset( $_GET['bewpi_action'] ) && isset( $_GET['nonce'] ) );
		}
		
		/**
		 * Admin pdf actions callback.
		 * Within admin by default only administrator and shop managers have permission to view, create, cancel invoice.
		 */
	

		
	}	
		
}


 

?>

