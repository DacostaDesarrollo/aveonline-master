<?php


//state_guia
//show
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'AVSHME_show_state_guia_order', 10, 1 );
function AVSHME_show_state_guia_order( $order ) {    
    $order_id = $order->get_id();
    $e = get_post_meta( $order_id, 'state_guia', true );
    if ( $e ) {
        echo "State Guia <hr>";
        for ($i=0; $i < count($e) ; $i++) { 
            if(isset($e[$i]["status"]) && $e[$i]["status"] == "ok"){
                $estado = $e[$i]["estado"];
                for ($j=0; $j < count($estado); $j++) { 
                    echo "<p>";
                    echo $estado[$j]["nombre_estado"];
                    echo "<br>";
                    echo $estado[$j]["fecha"];
                    echo "</p>";
                }
                echo "<hr>";
            }
        }
        echo "<hr>";
    }
}