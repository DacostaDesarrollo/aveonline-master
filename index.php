<?php
/**
 * @package WoocommerceAveOnlineShipping
 */
/*
Plugin Name: Aveonline Shipping
Plugin URI: https://gitlab.com/franciscoblancojn/aveonline
Description: Integración de woocommerce con los servicios de envío de Aveonline.
Version: 2.3.6
Author: Startsco
Author URI: https://startscoinc.com/es/
License: GPL2+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wc-aveonline-shipping
*/

if (!function_exists( 'is_plugin_active' ))
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://gitlab.com/franciscoblancojn/aveonline',
	__FILE__,
	'elios'
);
$myUpdateChecker->setAuthentication('MqiAAZzo9WNBAGzgwc3L');
$myUpdateChecker->setBranch('master');


//AVSHME_
define("AVSHME_LOG",false);
define("AVSHME_DIR",plugin_dir_path( __FILE__ ));
define("AVSHME_URL",plugin_dir_url(__FILE__));

if ( is_callable('curl_init') && 
	function_exists('curl_init') && 
	function_exists('curl_close') && 
	function_exists('curl_exec') && 
	function_exists('curl_setopt_array')
){
    if(
        is_plugin_active( 'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php' ) || 
        is_plugin_active( 'wc-departamentos-y-ciudades-colombia/main.php' ) ||
        is_plugin_active( 'departamentos-y-ciudades-colombia/main.php' ) || 
        is_plugin_active( 'wc-departamentos-y-ciudades-colombia/departamentos-y-ciudades-de-colombia-para-woocommerce.php' ) || 
        is_plugin_active( 'departamentos-y-ciudades-colombia/departamentos-y-ciudades-de-colombia-para-woocommerce.php' ) 
     ){
        function AVSHME_log_dycc() {
            ?>
            <div class="notice notice-error is-dismissible">
                <p>
                Aveonline Shipping no es compatible con "Departamentos y ciudades colombia", desactivelo para el funcionamiento de Aveonline
                </p>
            </div>
            <?php
        }
        add_action( 'admin_notices', 'AVSHME_log_dycc' );
    }else{
        function AVSHME_get_version() {
            $plugin_data = get_plugin_data( __FILE__ );
            $plugin_version = $plugin_data['Version'];
            return $plugin_version;
        }
        require_once plugin_dir_path( __FILE__ ) . 'departamentos-y-ciudades-de-colombia-para-woocommerce/departamentos-y-ciudades-de-colombia-para-woocommerce.php';
        require_once plugin_dir_path( __FILE__ ) . 'src/includes/class-admin.php';
    }
}else{
    function AVSHME_log_dependencia() {
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
            Aveonline Shipping requiere "Curl"
            </p>
        </div>
        <?php
    }
    add_action( 'admin_notices', 'AVSHME_log_dependencia' );
}

