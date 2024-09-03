<?php

function AVSHME_add_Contraentrega($methods)
{
    $methods[] = 'WC_Contraentrega';
    return $methods;
}
add_filter('woocommerce_payment_gateways', 'AVSHME_add_Contraentrega');
function AVSHME_woocommerce_Contraentrega_gateway()
{
	if (!class_exists('WC_Payment_Gateway')) return;

	class WC_Contraentrega extends WC_Payment_Gateway
	{
        /**
 		 * Class constructor, more about it in Step 3
 		 */
 		public function __construct() {

            $this->id = 'contraentrega'; // payment gateway plugin ID
            
            //$this->icon = plugin_dir_url( __FILE__ )."../img/c21.svg";
            $this->has_fields = false; // in case you need a custom credit card form
            $this->method_title = 'Pago Contraentrega';
            $this->title = 'Pago Contraentrega';
            $this->method_description = 'Description of Contraentrega payment gateway'; // will be displayed on the options page
         
            // gateways can support subscriptions, refunds, saved payment methods,
            // but in this tutorial we begin with simple payments
            $this->supports = array(
                'products'
            );
         
         
            // Load the settings.
            $this->init_settings();
            // Method with all the options fields
            $this->init_form_fields();
            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
         
            // We need custom JavaScript to obtain a token
            //add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );
         
            // You can also register a webhook here
            // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        }

		/**
		 * Funcion que define los campos que iran en el formulario en la configuracion
		 * de la pasarela de Contraentrega
		 *
		 * @access public
		 * @return void
		 */
		function init_form_fields()
		{
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __( 'Enable/Disable', 'woocommerce' ),
                    'type' => 'checkbox',
                    'label' => __( 'Enable', 'woocommerce' ),
                    'default' => isset($this->enabled)?$this->enabled:'yes'
                ),
                'title' => array(
                    'title' => __( 'Title', 'woocommerce' ),
                    'type' => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                    'default' => isset($this->title)?$this->title:__( 'Pago Contraentrega', 'woocommerce' ),
                    'desc_tip'      => true,
                ),
                'description' => array(
                    'title' => __( 'Customer Message', 'woocommerce' ),
                    'type' => 'textarea',
                    'default' => __("Pago a Destino, de esta forma usted paga al momento de recibir el pedido")
                )
            );
        }
        /*
        * We're processing the payments here, everything about it is in Step 5
        */
        public function process_payment( $order_id ) {
            global $woocommerce;
            $order = new WC_Order( $order_id );
        
            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status('processing', __( 'Awaiting cheque payment', 'woocommerce' ));
        
            // Remove cart
            $woocommerce->cart->empty_cart();
        
            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            );
        }
    }

}
add_action('plugins_loaded', 'AVSHME_woocommerce_Contraentrega_gateway', 0);

add_filter( 'woocommerce_available_payment_gateways', 'AVSHME_desactivar_wc_payment_gateway' );

function AVSHME_desactivar_wc_payment_gateway( $available_gateways ) {
    if ( ! is_object( WC()->cart ) ) {
       return $available_gateways;
    }

    try {
        $ave = new WC_aveonline_Shipping_Method();
        $fijarFlete = $ave->get_option( 'fijarFlete' );

        if ( $fijarFlete == "yes" ) {
            unset( $available_gateways['contraentrega'] );
        }
    } catch (\Throwable $th) {
        unset( $available_gateways['contraentrega'] );
    }
    return $available_gateways;
}