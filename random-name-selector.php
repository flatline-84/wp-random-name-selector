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
    $rns_db_version = '1.0';
    global $rns_db_name;
    $rns_db_name = 'random_name_selector';


    // Include shortcode functions
    include_once(plugin_dir_path(__FILE__) . 'rns-page.php');

    // Include admin functions
    include_once(plugin_dir_path(__FILE__) . 'rns-admin.php');

?>