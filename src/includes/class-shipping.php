<?php

function aveonline_shipping_method() {
    //if (class_exists( 'WC_aveonline_Shipping_Method' ) )return;
   
    class WC_aveonline_Shipping_Method extends WC_Shipping_Method {
        public function __construct( $instance_id = 0) {

            //parent::__construct( $instance_id );
            $this->instance_id        = absint( $instance_id );
            $this->id                   = 'wc_aveonline_shipping';
            $this->method_title         = __( 'Aveonline Shipping' );
            $this->method_description   = __( 'Servicios especializados en logística' );
            
            $this->title                = __( 'Aveonline Shipping' );
            //$this->debug = false;

            $this->availability = 'including';
            $this->countries = array(
                'CO'  // Colombia
            );

            $this->supports = array(
                'settings',
                'shipping-zones',
                'instance-settings',
            );
            $this->reques_config_api();
            // Load the settings API
            $this->init_settings();
            $this->init_form_fields();
            // Save settings in admin if you have any defined
            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            
        }
        function reques_config_api()
        {
            $this->classUser = "";
            $this->classPassword = "";
            $api = new AveonlineAPI($this->settings);
            if(isset($this->settings['user']) && isset($this->settings['password'])){
                $r = $api->autenticarusuario();
                if($r->status == 'ok'){
                    $cuentas =  $r->cuentas;
                    for ($i=0; $i < count( $cuentas); $i++) { 
                        $option_cuenta[$cuentas[$i]->usuarios[0]->id] =  $cuentas[$i]->servicio;
                    }
                    
                    $this->select_cuenta  =   array(
                        'id'    => 'select_cuenta',
                        'type'  => 'select',
                        'title' => __( 'Seleccione Cuenta'),
                        'options'   => $option_cuenta,
                    );
                }else{
                    $this->select_cuenta  =   array(
                        'id'    => 'select_cuenta',
                        'type'  => 'text_info',
                        'title' => $r->message,
                        'class' => 'error'
                    );
                    $this->classUser = "error";
                    $this->classPassword = "error";
                }
                if(isset($this->settings['select_cuenta'])){
                    $r = $api->agentes();
                    
                    if($r->status == 'ok'){
                        $agentes =  $r->agentes;
                        for ($i=0; $i < count( $agentes); $i++) { 
                            $option_agentes[$agentes[$i]->id."_".$agentes[$i]->idciudad] =  $agentes[$i]->nombre." ".$agentes[$i]->idciudad;
                        }
                        $this->select_agentes = array(
                            'id'    => 'select_agentes',
                            'type'  => 'select',
                            'title' => __( 'Seleccione Agentes'),
                            'options'   => $option_agentes,
                        );
                    }else{
                        $this->select_agentes = array(
                            'id'    => 'select_agentes',
                            'type'  => 'text_info',
                            'title' => $r->message,
                            'class' => 'error'
                        );
                    }
                }else{
                    $this->select_agentes = array(
                        'id'    => 'select_agentes',
                        'type'  => 'text_info',
                        'title' => "Cuenta Necesaria",
                        'class' => 'error'
                    );
                }
            }else{
                $this->select_cuenta  =   array(
                    'id'    => 'select_cuenta',
                    'type'  => 'text_info',
                    'title' => "Usuario o clave necesarios",
                );
                $this->select_agentes = array(
                    'id'    => 'select_agentes',
                    'type'  => 'text_info',
                    'title' => "Usuario o clave necesarios",
                );
            }
        }
        //Fields for the settings page
        function init_form_fields() {
            $this->reques_config_api();
            
            $option_cuenta = array(
                ''  => "Seleccione Cuenta"    
            );
            $option_agentes = array(
                ''  => "Seleccione Agente"    
            );
            $this->form_fields = array(
                'style' => array(
                    'id'    => 'style',
                    'type'  => 'style',
                ),
                'tag_Configuraciones' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Configuraciones'),
                ),
                'enabled' => array(
                    'title' => __( 'Habilitar/Deshabilitar' ),
                    'type' => 'checkbox',
                    'desc_tip' => __( 'Habilitar/Deshabilitar' ),
                    'default' => 'yes',
                ),
                'tag_api' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'API KEY'),
                ),
                'user' => array(
                    'title' => __( 'Usuario' ),
                    'type' => 'text',
                    'desc_tip' => __( 'Registered user in Aveonline' ),
                    'default' => '',
                    "class"    => $this->classUser
                ),
                'password' => array(
                    'title' => __( 'Contraseña' ),
                    'type' => 'password',
                    'desc_tip' => __( 'Password in API Aveonline' ),
                    'default' => '',
                    "class"    => $this->classPassword
                ),
                'tag_Remitente' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Remitente'),
                ),
                'dsnitre' => array(
                    'title' => __( 'NIT Remitente' ),
                    'type' => 'text',
                    'desc_tip' => __( 'NIT Remitente in Aveonline' ),
                    'default' => '',
                ),
                'dsdirre' => array(
                    'title' => __( 'Direccion Remitente' ),
                    'type' => 'text',
                    'desc_tip' => __( 'Direccion Remitente in Aveonline' ),
                    'default' => '',
                ),
                'dstelre' => array(
                    'title' => __( 'Teléfono Remitente' ),
                    'type' => 'tel',
                    'desc_tip' => __( 'Teléfono remitente in Aveonline' ),
                    'default' => '',
                ),
                'dscelularre' => array(
                    'title' => __( 'Celular Remitente' ),
                    'type' => 'tel',
                    'desc_tip' => __( 'Celular remitente in Aveonline' ),
                    'default' => '',
                ),
                'dscorreopre' => array(
                    'title' => __( 'Correo Remitente' ),
                    'type' => 'email',
                    'desc_tip' => __( 'Correo remitente in Aveonline' ),
                    'default' => '',
                ),
                'tag_cuenta' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Cuenta'),
                ),
                'select_cuenta' => $this->select_cuenta,
                'tag_agentes' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Agentes'),
                ),
                'select_agentes' => $this->select_agentes,
                'tag_valorMinimo' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Valor Minimo'),
                ),
                'valorMinimo' => array(
                    'title' => __( 'Habilitar/Deshabilitar' ),
                    'type' => 'checkbox',
                    'desc_tip' => __( 'Habilitar/Deshabilitar' ),
                    'default' => 'no',
                ),
                'tag_envioGratis' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Envio Gratis'),
                ),
                'envioGratis' => array(
                    'title' => __( 'Habilitar/Deshabilitar' ),
                    'type' => 'checkbox',
                    'desc_tip' => __( 'Habilitar/Deshabilitar' ),
                    'default' => 'no',
                ),
                'minValueEnvioGratis' => array(
                    'title' => __( 'Minimo acumulado en el carrito para envio Gratis' ),
                    'type' => 'number',
                    'default' => 0,
                    'custom_attributes' => array(
                                    'step'  => 'any',
                                    'min'   => '0'
                                ) 
                ),
                'tag_fijarFlete' => array(
                    'id'    => 'tag',
                    'type'  => 'tag',
                    'title' => __( 'Fijar Flete'),
                ),
                'fijarFlete' => array(
                    'title' => __( 'Habilitar/Deshabilitar' ),
                    'type' => 'checkbox',
                    'desc_tip' => __( 'Habilitar/Deshabilitar' ),
                    'default' => 'no',
                ),
                'fleteFijo' => array(
                    'title' => __( 'Flete Fijo para envios' ),
                    'type' => 'number',
                    'default' => 0,
                    'custom_attributes' => array(
                                    'step'  => 'any',
                                    'min'   => '0'
                                ) 
                ),
            );
        }
        public function isEnvioGratis($price = 0)
        {
            $minValueEnvioGratis = $this->settings['minValueEnvioGratis'];
            if($minValueEnvioGratis == '' || $minValueEnvioGratis == null){
                $minValueEnvioGratis = -1;
            }
            return $this->settings['envioGratis'] != 'no' && $minValueEnvioGratis <= floatval($price);
        }
        /**
         * Custon field tag
         */
        public function generate_text_info_html( $key, $data ) { 
            $defaults = array(
                'class'             => '',
                'css'               => '',
                'custom_attributes' => array(),
                'desc_tip'          => false,
                'description'       => '',
                'title'             => '',
            );

            $data = wp_parse_args( $data, $defaults );
            ob_start();
            ?>
            <tr class="<?=$data["class"]?>">
                <td>
                    <?php echo wp_kses_post( $data['title'] ); ?>
                </td>
                <td></td>
            </tr>
            <?php
            return ob_get_clean();
        }
        /**
         * Custon field tag
         */
        public function generate_tag_html( $key, $data ) { 
            $defaults = array(
                'class'             => 'button-secondary',
                'css'               => '',
                'custom_attributes' => array(),
                'desc_tip'          => false,
                'description'       => '',
                'title'             => '',
            );

            $data = wp_parse_args( $data, $defaults );
            ob_start();
            ?>
            <tr class="tag_amazing">
                <td>
                    <?php echo wp_kses_post( $data['title'] ); ?>
                </td>
                <td></td>
            </tr>
            <?php
            return ob_get_clean();
        }
        /**
         * Custon field style
         */
        public function generate_style_html( $key, $data ) { 
            if($_POST["refres"] == "1"){
                ?>
                <script>
                    const refres = () => {
                        document.querySelector('[value="Save changes"]').click()
                    }
                    window.addEventListener("load",refres)
                </script>
                <style>
                    .form-table{
                        display:none;
                    }
                </style>
                <?php
                return;
            }else{
                ?>
                <input type="hidden" name="refres" value="1">
                <?php
            }
            ?>
            <style>
                .tag_amazing{
                    background-color: #23282d;
                    color: #fff;
                    width: 100%;
                    box-shadow: -50px 0 #23282d, 50px 0 #23282d;
                }
                .tag_amazing.tag_amazing *{
                    font-size: 30px;
                    font-weight: 700;
                    color: #fff;
                    padding: 5px 0;
                }
                input.error,tr.error{
                    background: #e93030;
                    color: white;
                    border: #e93030;
                    font-weight: 600;
                }
            </style>
            <?php
        }
        public function generate_table_package_html( $key, $data ) { 
            $field    = $this->plugin_id . $this->id . '_' . $key;
            $defaults = array(
                'class'             => 'button-secondary',
                'css'               => '',
                'custom_attributes' => array(),
                'desc_tip'          => false,
                'description'       => '',
                'title'             => '',
            );

            $data = wp_parse_args( $data, $defaults );
            ob_start();
            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $field ); ?>">
                        <?php echo wp_kses_post( $data['title'] ); ?>
                    </label>
                    <?php echo $this->get_tooltip_html( $data ); ?>
                </th>
                <td class="forminp">
                    <fieldset>
                        <legend class="screen-reader-text">
                            <span>
                                <?php echo wp_kses_post( $data['title'] ); ?>
                            </span>
                        </legend>
                        <button 
                            class="<?php echo esc_attr( $data['class'] ); ?>" 
                            type="button" name="<?php echo esc_attr( $field ); ?>" 
                            id="table_package_btn" 
                            style="<?php echo esc_attr( $data['css'] ); ?>" 
                            <?php echo $this->get_custom_attribute_html( $data ); ?>>
                            <?php echo __('Add package');?>
                        </button>
                        <?php echo $this->get_description_html( $data ); ?>
                    </fieldset>
                    <input 
                    type="hidden"
                    name="woocommerce_wc_aveonline_shipping_table_package" 
                    id="woocommerce_wc_aveonline_shipping_table_package"
                    value='<?=(isset($this->settings['table_package']))?$this->settings['table_package']:"[]";?>' 
                    />
                </td>
            </tr>
            <tr id="table_package">
            </tr>
            <script>
                input = document.getElementById('woocommerce_wc_aveonline_shipping_table_package')
                btn = document.getElementById('table_package_btn')
                table = document.getElementById('table_package')
                n = 0
                var data 
                function add_tr(data = null){
                    cR = `
                        type="number"
                        min="1"
                        style="width: 30%;"
                        required
                    ` 
                    e = document.createElement("tr");
                    e.id=`package_${n}`
                    e.style = `
                        width: 100%;
                        min-width: 700px;
                        display: block;
                    `
                    e.innerHTML = `
                        <td>
                            <input 
                            id="Length_${n}"    
                            name="Length"
                            placeholder="Length"
                            ${cR}
                            ${(data!=null)?'value="'+data.length+'"':""}
                            />

                            <input 
                            id="Width_${n}"    
                            name="Width"
                            placeholder="Width"
                            ${cR}
                            ${(data!=null)?'value="'+data.width+'"':""}
                            />

                            <input 
                            id="Height_${n}"    
                            name="Height"
                            placeholder="Height"
                            ${cR}
                            ${(data!=null)?'value="'+data.height+'"':""}
                            />
                            cm
                        </td>
                        <td>
                            <button
                                id="delete_${n}"
                                id_delete="package_${n}"
                            >
                                Delete
                            </button>
                        </td>
                    `
                    table.appendChild(e)
                    d = document.getElementById(`delete_${n}`)
                    d.onclick = function(event){
                        event.preventDefault()
                        id = this.getAttribute('id_delete')
                        ele = document.getElementById(id)
                        ele.outerHTML = ""
                        sabe_table_package()
                    }
                    l = document.getElementById(`Length_${n}`)
                    w = document.getElementById(`Width_${n}`)
                    h = document.getElementById(`Height_${n}`)
                    change_input(l)
                    change_input(w)
                    change_input(h)
                    n++
                }
                function load_data(){
                    if(input.value == ""){
                        input.value = "{}"
                    }
                    data = JSON.parse(input.value)
                    for (let i = 0; i < data.length; i++) {
                        add_tr(data[i])
                    }
                }
                load_data()
                function sabe_table_package(){
                    data = []
                    l = document.documentElement.querySelectorAll('[id*="Length_"]')
                    w = document.documentElement.querySelectorAll('[id*="Width_"]')
                    h = document.documentElement.querySelectorAll('[id*="Height_"]')
                    for (let i = 0; i < l.length; i++) {
                        data[i] = {
                            length: l[i].value,
                            width: w[i].value,
                            height: h[i].value,
                        }
                    }
                    input.value = JSON.stringify(data)
                }
                function change_input(e){
                    e.onchange = function(){
                        sabe_table_package()
                    }
                }
                btn.onclick = function(){
                    add_tr()
                }
            </script>
            <?php
            return ob_get_clean();
        }
        public function add_rate_request($r  ,$request, $envioGratis = false )
        {
            //verifit request
            if($r->status == "ok"){
                $paymentContraentrega = new WC_Contraentrega();
                $activeContraentrega = $paymentContraentrega->enabled == "yes";
                //for cotizaciones
                foreach ($r->cotizaciones as $key => $value) {
                    //verifict price
                    if($value->total!="000"){
                        //load title and id
                        $titleContraentrega = "";
                        $idContraentrega = "wc_contraentrega_";
                        if( $value->contraentrega == "true"){
                            $titleContraentrega = " ";
                            $idContraentrega .= "on";
                        }else{
                            $idContraentrega .= "off";

                            $request['contraentrega'] = 0;
                            $request['valorrecaudo'] = 0;
                        }

                        $request['idtransportador'] = $value->codTransportadora;
                        $request['envioGratis'] = $envioGratis ? 1 : 0;
                        if($activeContraentrega == true || $value->contraentrega != "true"){
                            $t = $this->settings['fijarFlete'] == 'yes' ? floatval($this->settings['fleteFijo']): $value->total;
                            $this->add_rate( 
                                array(
                                    'id'      => $value->codTransportadora . $idContraentrega,
                                    'label'   => $titleContraentrega.$value->nombreTransportadora,
                                    'cost'    => $envioGratis ? 0 : $t,
                                    //add meta dat
                                    'meta_data' => array(
                                        "request"      => base64_encode(json_encode($request)),
                                    ),
                                )
                            );
                        }
                    }
                }
            }
        }
        public function calculate_shipping( $package = array()) {
            
            if(!is_checkout()){
                return;
            }
            wp_enqueue_script( 'jquery' );
            //load api
            $api = new AveonlineAPI($this->settings);
            //performat destination
            $destino = AVSHME_reajuste_code_aveonline(strtoupper($package["destination"]["city"]." (".$package["destination"]["state"].")"));
            if(AVSHME_get_code_aveonline($destino) == null)return;
            $productos = [];
            //recorre products
            foreach ($package["contents"] as $clave => $valor) {
                if($valor['variation_id']!=0){
                    $valor["product_id"] = $valor['variation_id'];
                }
                
                $_product           = wc_get_product($valor["product_id"]);
                $vendor_id          = get_post_field( 'post_author', $valor["product_id"] );
                $vendor_data        = get_user_meta( $vendor_id , 'wcfmmp_profile_settings', true );
                $vendor_custom_info = (array) get_user_meta( $vendor_id, 'wcfmvm_custom_infos', true );
                
                $_valor_declarado 	= get_post_meta($valor["product_id"],'_custom_valor_declarado' , true);
                if(0==floatval($_valor_declarado)){
                    $_valor_declarado = $_product->get_price();
                }
                
                $discount = $valor['line_total'] / $valor['line_subtotal'];
                //seguro producto valor sobre escrito por el de formotos
                $seguro = isset( $vendor_data['security_products'] ) ? (float) esc_attr( $vendor_data['security_products'] ) : (float) '0.10';
                $_valor_declarado = calculoseguro($seguro, $_valor_declarado);

                $productos[] = array(
                    "alto"              => $_product->get_height(),
                    "largo"             => $_product->get_length(),
                    "ancho"             => $_product->get_width(), 
                    "peso"              => $_product->get_weight(), 
                    "unidades"          => floatval($valor["quantity"]), 
                    "valorDeclarado"    => floatval($_valor_declarado) * $discount, 
                );
            }
            $contraentregaPayment = 0;
            $gateways = WC()->payment_gateways->get_available_payment_gateways();
            if( $gateways ) {
                foreach( $gateways as $gateway ) {
                    if( $gateway->enabled == 'yes' && $gateway->title == 'Contraentrega Aveonline') {
                        $contraentregaPayment = 1;
                    }
                }
            }
            $envioGratis = $this->isEnvioGratis($package['contents_cost']);
            
            
            if (empty($vendor_data)) {
                # code...
                return;
            }

            $origen = AVSHME_reajuste_code_aveonline(strtoupper($vendor_data["address"]['city']." (".$vendor_data["address"]['state'].")"));
            
            //generate request
            $request = array(
                "token"                 => $api->get_token(),
                "origen"                => $origen,
                "destinos"              => $destino,
                "contraentrega"         => 1,
                "contraentregaPayment"  => $contraentregaPayment,
                "valorrecaudo"          => $package['contents_cost'],
                "idasumecosto"          => 1,
                "productos"             => $productos,
                "envioGratis"           => $envioGratis,
                
            );

            //requeste api
            $r = $api->cotisar($request);
            //add rates
            $this->add_rate_request($r , $request,$envioGratis);
        }
    }
    
    //add your shipping method to WooCommers list of Shipping methods
}
add_action( 'woocommerce_shipping_init', 'aveonline_shipping_method' );

function AVSHME_add_aveonline_shipping_method( $methods ) {
    $methods['wc_aveonline_shipping'] = 'WC_aveonline_Shipping_Method';
    return $methods;
}
add_filter( 'woocommerce_shipping_methods', 'AVSHME_add_aveonline_shipping_method' );