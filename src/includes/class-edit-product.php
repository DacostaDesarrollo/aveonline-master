<?php
// custom_valor_declarado_aveonline
function AVSHME_woocommerce_custom_valor_declarado()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_valor_declarado',
            'placeholder' => 'Valor declarado',
            'label' => __('Valor_declarado', 'woocommerce'),
            'desc_tip' => 'true',
            'type' => 'number',
            'min' => '0',
            'required' => true
        )
    );
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'AVSHME_woocommerce_custom_valor_declarado', 10, 1);
// Save Fields
function AVSHME_woocommerce_custom_valor_declarado_save($post_id)
{
    $AVSHME_woocommerce_custom_valor_declarado = $_POST['_custom_valor_declarado'];
    if (!empty($AVSHME_woocommerce_custom_valor_declarado)){
        update_post_meta($post_id, '_custom_valor_declarado', esc_attr($AVSHME_woocommerce_custom_valor_declarado));
    }else{
        //wc_add_notice( 'Valor declarado requerido', 'error' );
    }
}
add_action('woocommerce_process_product_meta', 'AVSHME_woocommerce_custom_valor_declarado_save', 10, 1);

function AVSHME_woocommerce_custom_valor_declarado_varibale( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input( 
        array(
            'id' => '_custom_valor_declarado[' . $loop . ']',
            'placeholder' => 'Valor declarado',
            'label' => __('Valor_declarado', 'woocommerce'),
            'desc_tip' => 'true',
            'type' => 'number',
            'min' => '0',
            'required' => true,
            'value' => get_post_meta( $variation->ID, '_custom_valor_declarado', true )
        ) 
    );
}
add_action( 'woocommerce_variation_options_pricing', 'AVSHME_woocommerce_custom_valor_declarado_varibale', 10, 3 );
 
function AVSHME_woocommerce_custom_valor_declarado_save_variable( $variation_id, $i ) {
   $custom_field = $_POST['_custom_valor_declarado'][$i];
   if ( isset( $custom_field ) ) update_post_meta( $variation_id, '_custom_valor_declarado', esc_attr( $custom_field ) );
}
add_action( 'woocommerce_save_product_variation', 'AVSHME_woocommerce_custom_valor_declarado_save_variable', 10, 2 );
 
