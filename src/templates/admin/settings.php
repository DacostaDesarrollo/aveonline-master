<?php

if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!class_exists("AveonlineAPI")) {
    require_once WC_AVEONLINE_SHIPPING_DIR . 'src/aveonline/class-aveonline-api.php';
}

$aveonlineSettings = get_option(WCAveonlineShippingMethod::SETTINGS_KEY);
$aveonlineSettings = empty($aveonlineSettings) ? array() : $aveonlineSettings;

if (isset($_POST["save"])) {
    if (!isset($_POST["wc_aveonline_shipping_nonce"]) || !wp_verify_nonce($_POST['wc_aveonline_shipping_nonce'], 'wc_aveonline_shipping_nonce')) {
        return;
    }

    // authentication settings
    $aveonlineSettings["user"] = sanitize_text_field($_POST["user"]);
    $aveonlineSettings["password"] = sanitize_text_field($_POST["password"]);
    $aveonlineSettings["agent_id"] = sanitize_text_field($_POST["agent_id"]);
    $aveonlineSettings["p_valordeclarado"] = sanitize_text_field($_POST["p_valordeclarado"]);

    // client general info
    $aveonlineSettings["nit"] = sanitize_text_field($_POST["nit"]);

    // TODO: agregar consumidor del endpoint para listar las transportadoras y cargar ese arreglo en la configuración
    // general settings
    $aveonlineSettings["courriers[]"] = sanitize_text_field($_POST["courriers"]);

    // cost settings
    $aveonlineSettings["charge_customer"] = $_POST["charge_customer"] ? true : false;
    $aveonlineSettings["collection_tax"] = sanitize_text_field($_POST["collection_tax"]);
    
    // customer notifications
    $aveonlineSettings["notify_customer"] = $_POST["notify_customer"] ? true : false;

    if (WCAveonlineShippingMethod::SETTINGS_KEY != null) {
        update_option(WCAveonlineShippingMethod::SETTINGS_KEY, $aveonlineSettings);
    }

}

?>
<!-- Credentials to auth through Aveonline API  -->
<div>
    <h2>Credenciales:</h2>
    <p>Credenciales para autenticar las operaciones con el API de Aveonline</p>
</div>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="user">Usuario <span class="woocommerce-help-tip"
                        data-tip="<?=__('Usuario asignado por Aveonline')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="user" id="user" type="text" style=""
                    value="<?=(isset($aveonlineSettings["user"]) ? $aveonlineSettings["user"] : "")?>"
                    class="input-text regular-input" placeholder="">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="password">Contraseña <span class="woocommerce-help-tip"
                        data-tip="<?=__('Contraseña asociada al API key asignado por Aveonline')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="password" id="password" type="password" style=""
                    value="<?=(isset($aveonlineSettings["password"]) ? $aveonlineSettings["password"] : "")?>"
                    class="input-text regular-input" placeholder="">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="agent_id">Id Agente <span class="woocommerce-help-tip"
                        data-tip="<?=__('Id del agente suministrado por Aveonline')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="agent_id" id="agent_id" type="text" style=""
                    value="<?=(isset($aveonlineSettings["agent_id"]) ? $aveonlineSettings["agent_id"] : "")?>"
                    class="input-text regular-input" placeholder="">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;">
                <?=wp_nonce_field("wc_aveonline_shipping_nonce", "wc_aveonline_shipping_nonce")?>
            </td>
        </tr>
    </tbody>
</table>
<div>
    <h2>Generales:</h2>
</div>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="nit">Nit <span class="woocommerce-help-tip"
                        data-tip="<?=__('Nit del cliente registrado en Aveonline')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="nit" id="nit" type="text" style=""
                    value="<?=(isset($aveonlineSettings["nit"]) ? $aveonlineSettings["nit"] : "")?>"
                    class="input-text regular-input" placeholder="">
            </td>
        </tr>
    </tbody>
</table>
<div>
    <h2>Transportadoras:</h2>
</div>
<table class="form-table">
    <tbody>
        <!-- <tr valign="top">
        <td class="forminp">
            <select multiple="" name="woocommerce_specific_ship_to_countries[]" style="width:350px" data-placeholder="Elegir países…" aria-label="País" class="wc-enhanced-select select2-hidden-accessible enhanced" tabindex="-1" aria-hidden="true">
                <option value="AF">Afganistán</option>
                <option value="AL">Albania</option>
                <option value="DE">Alemania</option>
                <option value="ZW">Zimbabue</option>
            </select>
            <span class="select2 select2-container select2-container--default select2-container--below" dir="ltr" style="width: 350px;">
                <span class="selection">
                    <span class="select2-selection select2-selection--multiple" aria-haspopup="true" aria-expanded="false" tabindex="-1">
                        <ul class="select2-selection__rendered" aria-live="polite" aria-relevant="additions removals" aria-atomic="true">
                            <li class="select2-selection__choice" title="Colombia">
                                <span class="select2-selection__choice__remove" role="presentation" aria-hidden="true">×</span>
                                Colombia
                            </li>
                            <li class="select2-search select2-search--inline">
                                <input class="select2-search__field" type="text" tabindex="0" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" role="textbox" aria-autocomplete="list" placeholder="" style="width: 0.75em;">
                            </li>
                        </ul>
                    </span>
                </span>
                <span class="dropdown-wrapper" aria-hidden="true"></span>
            </span>
            <br>
            <a class="select_all button" href="#">Seleccionar todos</a>
            <a class="select_none button" href="#">Borrar selección</a>
        </td>
    </tr> -->
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="courriers">Transportadoras habilitadas <span class="woocommerce-help-tip"
                        data-tip="<?=__('Transportadoras que se habilitan para realizar los envios')?>"></span></label>
            </th>
            <td class="forminp">
                <select name="courriers[]" id="courriers" style="width:350px" multiple=""
                    data-placeholder="Elegir transportadoras…" aria-label="Transportadoras"
                    class="wc-enhanced-select select2-hidden-accessible enhanced" tabindex="-1" aria-hidden="true">
                    <option value="0">Todas</option>
                    <option value="1">Coordinadora</option>
                    <option value="2">Servientrega</option>
                    <option value="3">FedEx</option>
                    <option value="4">Mensajeros Urbanos</option>
                </select>
                <span class="select2 select2-container select2-container--default select2-container--below" dir="ltr"
                    style="width: 350px;">
                    <span class="selection">
                        <span class="select2-selection select2-selection--multiple" aria-haspopup="true"
                            aria-expanded="false" tabindex="-1">
                            <ul class="select2-selection__rendered" aria-live="polite"
                                aria-relevant="additions removals" aria-atomic="true">
                                <li class="select2-search select2-search--inline">
                                    <input class="select2-search__field" type="text" tabindex="0" autocomplete="off"
                                        autocorrect="off" autocapitalize="none" spellcheck="false" role="textbox"
                                        aria-autocomplete="list" style="width: 0.75em;">
                                </li>
                            </ul>
                        </span>
                    </span>
                    <span class="dropdown-wrapper" aria-hidden="true"></span>
                </span>
                <br>
                <a class="select_all button" href="#">Seleccionar todos</a>
                <a class="select_none button" href="#">Borrar selección</a>
            </td>
        </tr>
    </tbody>
</table>
<div>
    <h2>Costo y Notificaciones:</h2>
    <p>Variables relacionadas al cálculo de costos y a la notificación de los usuarios</p>
</div>
<table class="form-table">
    <tbody>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="notify_customer">Notificar clientes <span class="woocommerce-help-tip"
                        data-tip="<?=__('Habilitar esta opción permite que Aveonline notifique a los usuarios el estado de su envío')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="notify_customer" id="notify_customer" type="checkbox"
                    <?=(isset($aveonlineSettings["notify_customer"]) && $aveonlineSettings["notify_customer"] == 1 ? "checked" : "")?>>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="charge_customer">Cargar costo al cliente <span class="woocommerce-help-tip"
                        data-tip="<?=__('Cobar todo el valor del envío al cliente')?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="charge_customer" id="charge_customer" type="checkbox"
                    <?=(isset($aveonlineSettings["charge_customer"]) && $aveonlineSettings["charge_customer"] == 1 ? "checked" : "")?>>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="collection_tax">Cargo por recaudo <span class="woocommerce-help-tip"
                        data-tip="<?= __('Valor extra a cobrar por recaudo') ?>"></span></label>
            </th>
            <td class="forminp forminp-text">
                <input name="collection_tax" id="collection_tax" type="number" style=""
                    value="<?= (isset($aveonlineSettings["collection_tax"]) ? $aveonlineSettings["collection_tax"] : "" ) ?>"
                    class="input-text regular-input" placeholder="">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="collection_tax"><?php echo __("Valor declarado")?>
                    <span class="woocommerce-help-tip" data-tip="<?= __('Porcentaje de valor declarado') ?>">
                    </span>
                </label>
            </th>
            <td class="forminp forminp-text">
                <input name="p_valordeclarado" id="p_valordeclarado" type="number" style=""
                    value="<?= (isset($aveonlineSettings["p_valordeclarado"]) ? $aveonlineSettings["p_valordeclarado"] : "100" ) ?>"
                    class="input-text regular-input" placeholder="" min="0" max="100">
            </td>
        </tr>
        <tr>
            <td colspan="2" style="text-align:center;">
                <?=wp_nonce_field("wc_aveonline_shipping_nonce", "wc_aveonline_shipping_nonce")?>
            </td>
        </tr>
    </tbody>
</table>
<div>


</div>