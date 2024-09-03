<?php
function AVSHME_admin_styles() {
    wp_enqueue_style('AVSHME_admin_styles', AVSHME_URL.'/src/css/style_dasboard.css?v='.AVSHME_get_version());
}
add_action('admin_enqueue_scripts', 'AVSHME_admin_styles');