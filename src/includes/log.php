<?php
if(AVSHME_LOG){
    function add_AVSHME_logAveonline_aveonline_option_page($admin_bar)
    {
        global $pagenow;
        $admin_bar->add_menu(
            array(
                'id' => 'logAveonline_aveonline',
                'title' => 'logAveonline',
                'href' => get_site_url().'/wp-admin/options-general.php?page=logAveonline_aveonline'
            )
        );
    }

    function AVSHME_logAveonline_aveonline_option_page()
    {
        add_options_page(
            'Log Aveonline',
            'Log Aveonline',
            'manage_options',
            'logAveonline_aveonline',
            'AVSHME_logAveonline_aveonline_page'
        );
    }

    function AVSHME_logAveonline_aveonline_page()
    {
        ?>
        <h1>
            Solo se guardan las 10 peticiones
        </h1>
        <pre>
            <?php var_dump(array_reverse(json_decode(get_option("AVSHME_log"))));?>
        </pre>
        <?php
    }
    add_action('admin_bar_menu', 'add_AVSHME_logAveonline_aveonline_option_page', 100);

    add_action('admin_menu', 'AVSHME_logAveonline_aveonline_option_page');
}
