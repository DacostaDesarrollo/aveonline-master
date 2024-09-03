<?php 
/**
* AVSHME_add_function_order_change funcion para detectar el cambio de estado de pedidos para ejecutar peticiones al api de aveonline
*
* @access public
* @return void
*/
function AVSHME_add_function_order_change($order_id) {
    AVSHME_generate_guia($order_id);
}
add_action('woocommerce_order_status_aveonline',   'AVSHME_add_function_order_change' , 10, 1);  
function AVSHME_generate_guia($order_id){
    global $wpdb, $woocommerce, $current_user;
    $order = wc_get_order( $order_id );
    $settings = AVSHME_get_settings_aveonline();
    $api = new AveonlineAPI($settings);

    $order_data = $order->get_data();
    $e = array();
    foreach ($order->get_items( 'shipping' ) as $item) {
        foreach ($item->get_meta_data() as $data) {
            $e[$data->get_data()["key"]] = json_decode(base64_decode($data->get_data()["value"]),true);
        }
    }
    if(!isset($e["request"])){
        return;
    }
    $r = $api->AVSHME_generate_guia($e["request"], $order);
    if($r->status == "ok"){
        $guia = $r->resultado->guia;
        AVSHME_addLogAveonline($guia);
        update_post_meta( $order_id, 'enable_recogida', true);
        update_post_meta( $order_id, 'guias_rotulos', $guia);
        // update_post_meta( $order_id, 'paquete_final', $e["request"]['paquete_final'] );
        $respond = $api->system_update_guia(array(
            'numguia'   => $guia->numguia,
            'order_id'  => $order_id,
            'transportadora_id'=>$e["request"]['idtransportador']
        ));
    }else{
        update_post_meta( $order_id, 'error_guia', json_encode($r));
    }
}
//show
add_action( 'woocommerce_admin_order_data_after_billing_address', 'AVSHME_order_pdf', 10, 1 );
function AVSHME_order_pdf( $order ) {
	$order_id = $order->get_id();
	$guias_rotulos = get_post_meta( $order_id, 'guias_rotulos', true );
	$enable_recogida = get_post_meta( $order_id, 'enable_recogida', true );
    $error_guia = get_post_meta( $order_id, 'error_guia',true);
    
    if($error_guia){
        $error_guia = json_decode($error_guia,true);
        ?>
        <div style="color:red;">
            <strong>Ocurrio un Error:</strong>
            <br>
            <?=$error_guia["message"]?>
        </div>
        <?php

    }
	if ( $guias_rotulos ) {
        ?>
        <strong>Guia:</strong>
        <br>
        <a target="_blank" href="<?=$guias_rotulos->rutaguia;?>">
            <?=$guias_rotulos->mensaje;?>
        </a>
        <br>
        <strong>Rotulo:</strong>
        <br>
        <a target="_blank" href="<?=$guias_rotulos->rotulo;?>">
            <?=$guias_rotulos->numguia;?>
        </a>
        <?php
    }
    if($enable_recogida){
        ?>
        <br>
        <strong>Listo para generar Recogida</strong>
        <?php
    }
}
