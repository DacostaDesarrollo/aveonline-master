<?php

function add_AVSHME_relacion_envio_aveonline_option_page($admin_bar)
{
    global $pagenow;
    $admin_bar->add_menu(
        array(
            'id' => 'relacion_envio_aveonline',
            'title' => 'Relacion de envio Aveonline',
            'href' => get_site_url().'/wp-admin/options-general.php?page=relacion_envio_aveonline'
        )
    );
}

function AVSHME_relacion_envio_aveonline_option_page()
{
    add_options_page(
        'Relacion de envio Aveonline',
        'Relacion de envios Aveonline',
        'manage_options',
        'relacion_envio_aveonline',
        'AVSHME_relacion_envio_aveonline_page'
    );
}

function AVSHME_show_order_by_table_relacion_envia($order_id , $swt = true)
{
    if ($order_id == null)  return;

    $order = wc_get_order($order_id);
    if ($order == null)  return;

    $guias_rotulos = get_post_meta($order_id, 'guias_rotulos', true);
    if ($guias_rotulos == null )  return;
    $estado_recogida = get_post_meta($order_id, 'estado_recogida', true);
    $relacion_envio_generada = get_post_meta( $order_id, 'relacion_envio_generada', true );
    $rutaimpresion = get_post_meta( $order_id, 'rutaimpresion', true );

    ?>
    <tr id="post-<?= $order_id ?>" class="<?= ($estado_recogida == null) ? "no_generado_recogida" : "" ?>
        iedit author-self level-0 post-<?= $order_id ?> type-post status-publish format-standard hentry category-uncategorized">
        <th scope="row" class="check-column" data-children-count="1">
            <label class="screen-reader-text" for="cb-select-<?= $order_id ?>"></label>
            <input id="cb-select-<?= $order_id ?>" order_id="<?= $order_id ?>" 
            type="checkbox" name="post[]" value="<?= $order_id ?>" 
            <?php echo ($relacion_envio_generada == null || $relacion_envio_generada == "" )? '' : 'disabled' ?>>
            <div class="locked-indicator">
                <span class="locked-indicator-icon" aria-hidden="true"></span>
                <span class="screen-reader-text"></span>
            </div>
        </th>
        <td class="title column-title has-row-actions column-primary page-order" data-colname="Title">
            <div class="locked-info"><span class="locked-avatar"></span> <span class="locked-text"></span></div>
            <strong>
                <a class="row-title" href="<?=get_site_url()?>/wp-admin/post.php?post=<?= $order_id ?>&action=edit">
                    #<?= $order_id ?>
                </a>
            </strong>
        </td>

        <td class="author column-guia" data-colname="Guia">
            <a target="_blank" href="<?=$guias_rotulos->rutaguia;?>">
                <?=$guias_rotulos->mensaje;?>
            </a>
        </td>
        <td class="author column-rotulo" data-colname="Rotulo">
            <a target="_blank" href="<?=$guias_rotulos->rotulo;?>">
                <?=$guias_rotulos->numguia;?>
            </a>
        </td>
        <td class="author column-relacion" data-colname="relacion">
            <?php
                if ($rutaimpresion == null || $rutaimpresion == "") {
                    echo "No generada";
                }else{
                    ?>
                    <a href="<?=$rutaimpresion->rutaimpresion?>" target="_blank">
                        <?=$rutaimpresion->relacionenvio?>
                    </a>
                    <?php
                }
            ?>
        </td>
        <td class="author column-estado" data-colname="Estado">
            <?php
            if ($relacion_envio_generada == null || $relacion_envio_generada == "") {
                echo "No generada";
            } else {
                echo "Generada";
            }
            ?>
        </td>
        <td class="date column-date" data-colname="Date">
            <span><?= $order->get_date_created()->format('d-m-y'); ?></span>
        </td>
    </tr>
<?php
}
function AVSHME_relacion_envio_aveonline_page()
{
    $rd_args_nonde = array(
        'meta_key'      => 'enable_recogida',             
        'meta_compare'  => 'NOT EXISTS',  
        'return'        => 'ids',
        'status'        => 'processing',
    );
    $order_none = wc_get_orders($rd_args_nonde);

    $rd_args_total = array(
        'meta_key'      =>  'enable_recogida',             
        'meta_compare'  => 'EXISTS',  
        'return'        => 'ids',
        'status'        => 'processing',
        'exclude'       => $order_none,
    );

    if(isset($_GET["pen"]) && $_GET["pen"]==1){
        $rd_args_total['meta_key'] = "relacion_envio_generada";
        $rd_args_total['meta_compare'] = "NOT EXISTS";
    }else if(isset($_GET["com"]) && $_GET["com"]==1){
        $rd_args_total['meta_key'] = "relacion_envio_generada";
    }
    $customer_orders_total = wc_get_orders($rd_args_total);
    
    $paged = (isset($_GET['paged'])) ? intval($_GET['paged']) : 1;
    $n_page = 10;
    $rd_args = array(
        'meta_key'                  => 'enable_recogida',              
        'meta_compare'              => 'EXISTS',  
        'return'                    => 'ids',
        'fields'                    => 'ids',
        'status'                    => 'processing',
        'nopaging'                  => false,
        'paged'                     => '1',
        'posts_per_page'            => $n_page,
        'posts_per_archive_page'    => $n_page,
        'offset'                    => $n_page * ($paged - 1),
        'exclude'       => $order_none,
    );
    

    if(isset($_GET["pen"]) && $_GET["pen"]==1){
        $rd_args['meta_key'] = "relacion_envio_generada";
        $rd_args['meta_compare'] = "NOT EXISTS";
    }else if(isset($_GET["com"]) && $_GET["com"]==1){
        $rd_args['meta_key'] = "relacion_envio_generada";
    }
    $customer_orders = wc_get_orders($rd_args);
    ?>
    <h2 class="screen-reader-text">Orders</h2>
    
    <style>
        .column-recogida{
            display:none;
        }
        body.load:before,
        body.load:after{
            content:"";
            position: fixed;
            top:0;
            left:0;
            right: 0;
            bottom:0;
            margin:auto;
            z-index: 999999999999;
        }
        body.load:before{
            width:100%;
            height:100%;
            background:#ffffff80;
        }
        body.load:after{
            width:150px;
            height:150px;
            border:10px solid #1d2327;
            border-top-color:transparent;
            animation: ani360 5s infinite;
            border-radius:100%;
        }
        @keyframes ani360{
            to{
                transform: rotateZ(360deg);
            }
        }
    </style>
    <script>
        async function relacion_de_envio() {
            aux_array = document.documentElement.querySelectorAll("[id*='cb-select']:checked:not(#cb-select-all-1)")
            ids = []
            for (index = 0; index < aux_array.length; index++) {
                ids[index] = aux_array[index].value
            }
            
            var myHeaders = new Headers();
            myHeaders.append("Cookie", "__cfduid=d23155ce328a4759efd2b35fde15da2211600376510");

            var formdata = new FormData();

            formdata.append("order_ids", ids);
            formdata.append("relacion_de_envio", 1);
            var requestOptions = {
                method: 'POST',
                headers: myHeaders,
                body: formdata,
                redirect: 'follow'
            };
            document.body.classList.add("load");
            await fetch("<?= plugin_dir_url(__FILE__) ?>class-relacion-envio.php", requestOptions)
                .then(response => response.text())
                .then(result => {
                    console.log(result)
                    window.location.reload()
                })
                .catch(error => console.log('error', error));
        }
    </script>
    <div class="wp-core-ui">
        <p>
            <button onclick="relacion_de_envio()" class="button" style="margin-right:50px">
                Relacion de Envio
            </button>
            <a href="<?=get_admin_url()?>options-general.php?page=relacion_envio_aveonline" class="button">
                All
            </a>
            <a href="<?=get_admin_url()?>options-general.php?page=relacion_envio_aveonline&&pen=1" class="button">
                Pendientes
            </a>
            <a href="<?=get_admin_url()?>options-general.php?page=relacion_envio_aveonline&&com=1" class="button">
                Completadas
            </a>
        </p>
    </div>
    <table class="wp-list-table widefat fixed striped posts">
        <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column" data-children-count="1">
                    <label class="screen-reader-text" for="cb-select-all-1">Select All
                    </label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th scope="col" id="order" class="manage-column column-order column-primary">Orden</th>
                <th scope="col" id="guia" class="manage-column column-guia">Guia</th>
                <th scope="col" id="rotulo" class="manage-column column-rotulo">Rotulo</th>
                <th scope="col" id="rotulo" class="manage-column column-relacion_envio">Relacion de Envio</th>
                <th scope="col" id="estado" class="manage-column column-estado">Estado</th>
                <th scope="col" id="date" class="manage-column column-date">Fecha</th>
            </tr>
        </thead>

        <tbody id="the-list">
            <?php
            for ($i = 0; $i < count($customer_orders); $i++) {
                AVSHME_show_order_by_table_relacion_envia($customer_orders[$i],false);
            }
            ?>
        </tbody>
    </table>
    <div class="tablenav bottom">
        <div class="alignleft actions">
        </div>
        <div class="tablenav-pages">
            <span class="displaying-num"><?= count($customer_orders_total) ?> items</span>
            <span class="pagination-links">
                <?php
                $url_base = get_site_url()."/wp-admin/options-general.php?page=recogida_aveonline&paged=";
                if(isset($_GET["pen"]) && $_GET["pen"]==1){
                    $url_base = get_site_url()."/wp-admin/options-general.php?page=recogida_aveonline&pen=1&paged=";
                }else if(isset($_GET["com"]) && $_GET["com"]==1){
                    $url_base = get_site_url()."/wp-admin/options-general.php?page=recogida_aveonline&com=1&paged=";
                }
                $url_void = "javascript:void(0)";
                $paged_all = ceil(count($customer_orders_total) / $n_page);

                $next = ($paged - 1 > 0) ? $paged - 1 : 1;
                $prev = ($paged + 1 < $paged_all) ? $paged + 1 : $paged_all;

                $url_base_first = $url_base . "1";
                $url_base_prev  = $url_base . $next;
                $url_base_next  = $url_base . $prev;
                $url_base_last  = $url_base . $paged_all;

                if ($paged == 1) {
                    $url_base_first = $url_void;
                    $url_base_prev  = $url_void;
                }
                if ($paged == $paged_all) {
                    $url_base_next  = $url_void;
                    $url_base_last  = $url_void;
                }

                ?>
                <a class="first-page button <?= ($paged == 1) ? "disabled" : ""; ?>" href="<?= $url_base_first ?>">
                    <span class="screen-reader-text">First page</span><span aria-hidden="true">«</span>
                </a>
                <a class="prev-page button <?= ($paged == 1) ? "disabled" : ""; ?>" href="<?= $url_base_prev ?>">
                    <span class="screen-reader-text">Prev page</span><span aria-hidden="true">‹</span>
                </a>
                <span class="screen-reader-text">Current Page</span>
                <span id="table-paging" class="paging-input">
                    <span class="tablenav-paging-text">
                        <?= $paged ?> of <span class="total-pages"><?= $paged_all ?></span>
                    </span>
                </span>
                <a class="next-page button <?= ($paged == $paged_all) ? "disabled" : ""; ?>" href="<?= $url_base_next ?>">
                    <span class="screen-reader-text">Next page</span><span aria-hidden="true">›</span>
                </a>
                <a class="last-page button <?= ($paged == $paged_all) ? "disabled" : ""; ?>" href="<?= $url_base_last ?>">
                    <span class="screen-reader-text">Last page</span><span aria-hidden="true">»</span>
                </a>
            </span>
        </div>
        <br class="clear">
    </div>
    <?php
}
function getGuiaByIdOrder($order_id)
{
    $guias_rotulos = get_post_meta( $order_id, 'guias_rotulos', true );
    update_post_meta( $order_id, 'relacion_envio_generada', true );
    return $guias_rotulos->numguia;
}
if (isset($_POST) && isset($_POST['relacion_de_envio'])) {
    require_once(preg_replace('/wp-content.*$/','',__DIR__).'wp-load.php');
    
    $order_ids = explode(",",$_POST['order_ids']);
    
    $orders = array();
    for ($i=0; $i < count($order_ids); $i++) { 
        $metodo_envio = get_post_meta($order_ids[$i], 'metodo_envio', true);
        //$transportadora = get_post_meta($order_ids[$i], 'transportadora', true);
        
        $order = wc_get_order( $order_ids[$i] );
        foreach ($order->get_items( 'shipping' ) as $item) {
            foreach ($item->get_meta_data() as $data) {
                $e[$data->get_data()["key"]] = json_decode(base64_decode($data->get_data()["value"]),true);
            }
        }
        $request = $e['request'];
        $transportadora    =  $request['idtransportador'];

        $orders[$metodo_envio][$transportadora][] =  $order_ids[$i];
    }
    $settings = AVSHME_get_settings_aveonline();
    $api = new AveonlineAPI($settings);
    foreach ($orders as $key => $value) {
        foreach ($value as $key2 => $value2) {
            $array_guias = array_map("getGuiaByIdOrder",$value2);
            $array_send = array(
                "transportadora"    => $key2,
                "guias"             => implode(',', $array_guias)
            );
            
            $relacion_envio = $api->relacionEnvios($array_send);
            for ($n=0; $n < count($value2); $n++) { 
                update_post_meta( $value2[$n], 'rutaimpresion', $relacion_envio );
            }
        }
    }
    
}
add_action('admin_bar_menu', 'add_AVSHME_relacion_envio_aveonline_option_page', 100);

add_action('admin_menu', 'AVSHME_relacion_envio_aveonline_option_page');
