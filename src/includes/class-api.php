<?php

function AVSHME_log_error_Curl_file_get_contents() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p>
        Aveonline Shipping detecto que no esta funcionando  "curl" y "file_get_contents"
        </p>
    </div>
    <?php
}
/**
 * AveonlineAPI class
 *
 * Handless Aveonline API calls and authorization.
 *
 */
function load_AveonlineAPI()
{
    if(class_exists("AveonlineAPI"))return;
    class AveonlineAPI {
        private $API_URL_AUTHENTICATE   = 'https://app.aveonline.co/api/comunes/v1.0/autenticarusuario.php';
        private $API_URL_AGENTE         = "https://app.aveonline.co/api/comunes/v1.0/agentes.php";
        private $API_URL_CITY           = "https://app.aveonline.co/api/box/v1.0/ciudad.php";
        private $API_URL_QUOTE          = "https://app.aveonline.co/api/nal/v1.0/generarGuiaTransporteNacional.php";
        private $API_URL_UPDATE_GUIA    = "https://app.aveonline.co/api/nal/v1.0/plugins/wordpress.php";
        private $APY_URL_ST             = "https://apiave.startscoinc.com/".((AVSHME_LOG)?"dev":"app")."/";


        private $URL_UPDATE_GUIA        = 'action-update-guia.php';

        public function __construct($settings)
        {
            $this->settings = $settings;
        }

        private function request2($json_data , $url)
        {
            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header' => "Content-type: application/json\r\n" .
                                "Accept: application/json\r\n" .
                                "Connection: close\r\n" .
                                "Content-length: " . strlen($json_data) . "\r\n",
                    'protocol_version' => 1.1,
                    'content' => $json_data
                )
            );

            $context  = stream_context_create($opts);

            $response = file_get_contents($url, false, $context);
            if($response == false){
                add_action( 'admin_notices', 'AVSHME_log_error_Curl_file_get_contents' );
            }
            AVSHME_addLogAveonline(array(
                "type"=>"api resquest2",
                "url"=>$url,
                "send"=>json_decode($json_data),
                "respond_json_decode"=>json_decode($response),
                "respond"=>$response,
            ));
            return json_decode($response);
        }
        public function request($json , $url)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_VERBOSE => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            $response = curl_exec($curl);
            $error = curl_error($curl);
            curl_close($curl);

            if($response == false){
                AVSHME_addLogAveonline(array(
                    "type"=>"api resquest error",
                    "url"=>$url,
                    "send"=>json_decode($json),
                    "error"=>$error,
                ));
                return $this->request2($json , $url);
            }else{
                AVSHME_addLogAveonline(array(
                    "type"=>"api resquest",
                    "url"=>$url,
                    "send"=>json_decode($json),
                    "respond_json_decode"=>json_decode($response),
                    "respond"=>$response,
                ));
                return json_decode($response);
            }

        }
        public function autenticarusuario()
        {
            $json_body = '
                {
                    "tipo":"auth",
                    "usuario":"' .$this->settings['user']. '",
                    "clave":"' .$this->settings['password']. '"
                }
            ';
            return $this->request($json_body , $this->API_URL_AUTHENTICATE);
        }
        public function get_token()
        {
            $r = $this->autenticarusuario();
            if($r->status == 'ok'){
                return $r->token;
            }
            return null;
        }
        public function agentes()
        {
            $json_body = '
                {
                    "tipo":"listarAgentesPorEmpresaAuth",
                    "token":"' . $this->get_token(). '",
                    "idempresa":"' . $this->settings['select_cuenta'] . '"
                }
            ';
            return $this->request($json_body , $this->API_URL_AGENTE);
        }
        public function cotisar($data = array())
        {
            $json_body = array(
                "tipo"                  => "cotizarDoble",
                "access"                => "",
                "token"                 => $data["token"],
                "idempresa"             => $this->settings['select_cuenta'],
                "origen"                => $data['origen'],
                "destino"               => $data["destinos"],
                "idasumecosto"          => $data["idasumecosto"],
                "contraentrega"         => $data["contraentrega"],
                "contraentregaPayment"  => $data["contraentregaPayment"],
                "valorrecaudo"          => $data["valorrecaudo"],
                "productos"             => $data["productos"],
                "valorMinimo"           => ($this->settings['valorMinimo'] == "yes")?1:0
            );
            
            $json_body = json_encode($json_body);
            return $this->request($json_body , $this->API_URL_QUOTE);
        }
        public function AVSHME_generate_guia($data , $order)
        {
            $order_id = $order->get_id();
            $productos = [];
            $dscontenido = "";
            foreach ( $order->get_items() as $item_id => $item ) {
                $product_id         = $item->get_product_id();
                $variation_id       = $item->get_variation_id();
                $subtotal           = $item->get_subtotal();
                //obtener información del aliado
                $vendor_id = get_post_field( 'post_author', $product_id );
                $vendor_data = get_user_meta( $vendor_id , 'wcfmmp_profile_settings', true );
                $vendor_custom_info = (array) get_user_meta( $vendor_id, 'wcfmvm_custom_infos', true );
                $total              = $item->get_total();
                
                if($variation_id!=0){
                    $product_id = $variation_id;
                }
                $_product           = wc_get_product($product_id);
                
                $_valor_declarado 	= get_post_meta($product_id,'_custom_valor_declarado' , true);
                if(0==floatval($_valor_declarado)){
                    $_valor_declarado = $_product->get_price();
                }
                
                $discount = $total / $subtotal;

                //seguro producto valor sobre escrito por el de formotos
                $seguro = isset( $vendor_data['security_products'] ) ? (float) esc_attr( $vendor_data['security_products'] ) : (float) '0.10';
                $_valor_declarado = calculoseguro($seguro, $_valor_declarado);

                $productos[] = array(
                    "alto"              => $_product->get_height(),
                    "largo"             => $_product->get_length(),
                    "ancho"             => $_product->get_width(), 
                    "peso"              => $_product->get_weight(), 
                    "unidades"          => $item->get_quantity(),
                    "nombre"            => $item->get_name(),
                    "ref"               => $_product->get_sku(),
                    "urlProducto"       => $_product->get_reviews_allowed(),
                    "valorDeclarado"    => floatval($_valor_declarado) * $discount
                );
                $dscontenido .= $item->get_name().",";
            }


            $origen = AVSHME_reajuste_code_aveonline(strtoupper($vendor_data["address"]['city']." (".$vendor_data["address"]['state'].")"));

            $json_body = array(
                "tipo"              => "generarGuia2",
                "token"             => $this->get_token(),
                "idempresa"         => $this->settings['select_cuenta'],
                "codigo"            => "",
                "dsclavex"          => "",
                "plugin"            => "wordpress",

                "origen"            => $origen,
                "dsdirre"           => "{$vendor_data["address"]['street_1']} - {$vendor_data["address"]['street_2']}",
                "dsbarrioo"         => "",

                "destino"           => $data['destinos'],
                "dsdir"             => $order->get_billing_address_1()." ".$order->get_billing_address_2(),
                "dsbarrio"          => "",

                "dsnitre"           => $this->settings['dsnitre'],
                "dstelre"           => $this->settings['dstelre'],
                "dscelularre"       => $vendor_custom_info["telefono-de-contacto-para-clientes"],
                "dscorreopre"       => $this->settings['dscorreopre'],

                "dsnit"             => get_post_meta( $order_id, '_cedula', true ),
                "dsnombre"          => $order->get_billing_first_name(),
                "dsnombrecompleto"  => $order->get_formatted_billing_full_name(),
                "dscorreop"         => $order->get_billing_email(),
                "dstel"             => $order->get_billing_phone(),
                "dscelular"         => $order->get_billing_phone(),

                "idtransportador"   => $data['idtransportador'],

                "unidades"          => 1,
                "productos"         => $productos,

                "dscontenido"       => $dscontenido,
                "dscom"             => $order->get_customer_note(),

                "idasumecosto"      => $data['contraentrega'],
                "contraentrega"     => $data['contraentrega'],
                "valorrecaudo"      => $data['valorrecaudo'],

                "idagente"          => explode('_',$this->settings['select_agentes'])[0],
                
                "dsreferencia"      => "",
                "dsordendecompra"   => "",
                "bloquegenerarguia" => "1",
                "relacion_envios"   => "1",
                "enviarcorreos"     => "1",
                "guiahija"          => "",
                "accesoila"         => "",
                "cartaporte"        => "",
                "valorMinimo"       => ($this->settings['valorMinimo'] == "yes")?1:0,
                "envioGratis"       => $data['envioGratis'],
            );
            
            $json_body = json_encode($json_body);

            $r = $this->request($json_body , $this->API_URL_QUOTE);
            $json_S = '{
                "shop" : "'.get_bloginfo( 'name' ).'",
                "send" : '.$json_body.',
                "respond" : '.json_encode($r).'
            }';
            // $this->request($json_S , $this->APY_URL_ST."guias");

            $json_order_products = array();
            foreach ( $order->get_items() as $item_id => $item ) {
                $product_id = $item->get_product_id();
                $name = $item->get_name();
                $quantity = $item->get_quantity();
                $subtotal = $item->get_subtotal();
                $total = $item->get_total();
                $json_order_products[] = array(
                    "product_id"    => $product_id,
                    "name"          => $name,
                    "quantity"      => $quantity,
                    "subtotal"      => $subtotal,
                    "total"         => $total,
                );
            }
            $json_order_products = json_encode($json_order_products);
            $json_order = '{
                "shop" : "'.get_bloginfo( 'name' ).'",
                "order_id" : "'.$order_id.'",
                "view"  : "'.$order->get_view_order_url().'",
                "status"  : "'.$order->get_status().'",
                "user_id"  : "'.$order->get_user_id().'",
                "billing_first_name"  : "'.$order->get_billing_first_name().'" ,
                "billing_last_name"  : "'.$order->get_billing_last_name().'",
                "billing_address_1"   : "'.$order->get_billing_address_1()." ".$order->get_billing_address_2().'",
                "billing_city"   : "'.$order->get_billing_city().'",
                "billing_state"  : "'.$order->get_billing_state().'",
                "billing_country"    : "'.$order->get_billing_country().'",
                "billing_email"   : "'.$order->get_billing_email().'",
                "billing_phone"   : "'.$order->get_billing_phone().'",
                "shipping_method"  : "'.$order->get_shipping_method().'",
                "total"  : "'.$order->get_total().'",
                "discount_total"  : "'.$order->get_discount_total().'",
                "products" : '.$json_order_products.'
            }';
            // $this->request($json_order , $this->APY_URL_ST."ordenes");
            
            return $r;
        }
        public function generarRecogida($data)
        {
            $json_body = array(
                "tipo"              => "generarRecogida2",
                "token"             => $this->get_token(),
                "idempresa"         => $this->settings['select_cuenta'],
                "idagente"          => explode('_',$this->settings['select_agentes'])[0],
                "dscom"             => $data['dscom'],
                "guias"             => $data['guias']
            );
            $json_body = json_encode($json_body);

            $r = $this->request($json_body , $this->API_URL_QUOTE);

            $json_S = '{
                "shop" : "'.get_bloginfo( 'name' ).'",
                "send" : '.$json_body.',
                "respond" : '.json_encode($r).'
            }';
            // $this->request($json_S , $this->APY_URL_ST."recogidas");
           
            return $r;
        }
        public function system_update_guia($data)
        {
            $json_body = '
            {
                "tipo" : "guardarPedidos",
                "ruta":"'.              plugin_dir_url( __FILE__ ).$this->URL_UPDATE_GUIA.'",
                "guia":"'.              $data["numguia"].'",
                "pedido_id":"'.         $data["order_id"].'",
                "cliente_id" : "'.      $this->settings['select_cuenta'].'",
                "transportadora_id": "'.$data["transportadora_id"].'"
            }
            ';
            return $this->request($json_body , $this->API_URL_UPDATE_GUIA);
        }
        public function relacionEnvios($data)
        {
            $json_body = '
            {
                "tipo" : "relacionEnvios",
                "token":"'.                 $this->get_token() .'",
                "idempresa":"'.             $this->settings['select_cuenta'].'",
                "transportadora":"'.        $data["transportadora"].'",
                "guias" : "'.               $data['guias'].'"
            }
            ';
            $r = $this->request($json_body , $this->API_URL_QUOTE);
            $json_S = '{
                "shop" : "'.get_bloginfo( 'name' ).'",
                "send" : '.$json_body.',
                "respond" : '.json_encode($r).'
            }';
            // $this->request($json_S , $this->APY_URL_ST."relaciones");
            return $r;
        }
    }
}
load_AveonlineAPI();
