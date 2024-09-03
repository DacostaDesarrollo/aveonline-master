<?php
//cedula
//add
add_action( 'woocommerce_before_order_notes', 'AVSHME_cedula_checkout' , 10, 1);  
function AVSHME_cedula_checkout( $checkout ) { 
   $current_user = wp_get_current_user();
   $cedula = $current_user->cedula;
   woocommerce_form_field( 'cedula', array(        
      'type' => 'text',               
      'label' => __('Cedula'),     
      'required' => true,        
      'default' => $cedula,        
   ), $checkout->get_value( 'cedula' ) ); 
}
//valiadate
add_action( 'woocommerce_checkout_process', 'validate_AVSHME_cedula_checkout' , 10, 1);
function validate_AVSHME_cedula_checkout() {    
    if ( ! $_POST['cedula'] ) {
        wc_add_notice( 'Por favor ingrese la Cedula  ', 'error' );
        return;
    }
}
//save
add_action( 'woocommerce_checkout_update_order_meta', 'save_AVSHME_cedula_checkout' , 10, 1);
function save_AVSHME_cedula_checkout( $order_id ) { 
    if ( $_POST['cedula'] ) update_post_meta( $order_id, '_cedula', esc_attr( $_POST['cedula'] ) );
}
//show
add_action( 'woocommerce_admin_order_data_after_billing_address', 'show_AVSHME_cedula_checkout', 10, 1 );
function show_AVSHME_cedula_checkout( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, '_cedula', true ) ) echo '<p><strong>Cedula:</strong> ' . get_post_meta( $order_id, '_cedula', true ) . '</p>';
}