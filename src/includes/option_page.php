<?php
function Aveonline_create_menu() {
	add_menu_page('Aveonline Settings', 'Aveonline', 'administrator', __FILE__, 'Aveonline_settings_page' , plugins_url('../img/aveonline.png', __FILE__) );
	add_submenu_page( __FILE__, 'Recogida', 'Recogida', 'administrator', 'options-general.php?page=recogida_aveonline');
	add_submenu_page( __FILE__, 'Relacion de Envio', 'Relacion de Envio', 'administrator', 'options-general.php?page=relacion_envio_aveonline');
    add_action( 'admin_init', 'register_Aveonline_settings' );
}
add_action('admin_menu', 'Aveonline_create_menu');

function register_Aveonline_settings() {
	//register our settings
	register_setting( 'Aveonline-settings-group', 'new_option_name' );
	register_setting( 'Aveonline-settings-group', 'some_other_option' );
	register_setting( 'Aveonline-settings-group', 'option_etc' );
}

function Aveonline_settings_page(){
    ?>
    <h1 class="title">
        Aveonline
    </h1>
    <div class="content-a">
        <a href="<?=get_admin_url()?>options-general.php?page=recogida_aveonline" class="btnA">
            Recogida
        </a>
        <a href="<?=get_admin_url()?>options-general.php?page=relacion_envio_aveonline" class="btnA">
            Relacion de Envio
        </a>
    </div>
    <style>
        .title{
            font-size: 5rem;
        }
        .content-a{
            display:flex;
        }
        .btnA{
            padding: 20px 40px;
            max-width: 100%;
            border: 3px solid;
            border-radius: 10px;
            margin-right: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            font-size: 20px;
            text-transform: uppercase;
            text-decoration:none;
            background:#fff;
            color: #1d2327;
        }
    </style>
    <?php
}

