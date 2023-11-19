<?php
    /*
    Plugin Name: Random Name Selector
    Description: A simple plugin to randomly select a user for a prize.
    Version: 0.1
    Author: flatline-84
    */

    /****************************
     * GLOBAL VARS
    *****************************/
    global $rns_db_version;
    $rns_db_version = '1.1';
    global $rns_db_name;
    $rns_db_name = 'random_name_selector';

    // We need this so DB migrations work when included from another file.
    define('RNS_PLUGIN_FILE_URL', __FILE__);

    // Include shortcode functions
    include_once(plugin_dir_path(__FILE__) . 'rns-page.php');

    // Include admin functions
    include_once(plugin_dir_path(__FILE__) . 'rns-admin.php');

    // Include endpoint to return a name
    include_once(plugin_dir_path(__FILE__) . 'rns-endpoint.php');
    
    // DO NOT ADD EMPTY LINES AFTER THE PHP CLOSING BRACE OR THE PLUGIN INSTALLER WILL KILL YOUR FAMILY.
?>