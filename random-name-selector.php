<?php
    /*
    Plugin Name: Random Name Selector
    Description: A simple plugin to randomly select a user for a prize.
    Version: 0.1
    Author: flatline-84
    */

    // Add a shortcode for displaying the random name selector on a page
    add_shortcode('random_name_selector', 'display_random_name_selector');

    /**
     * Selects a random user and displays it on the webpage.
     */
    function display_random_name_selector() {
        $random_user = get_random_user();

        // Load and include the template file
        ob_start();
        include(plugin_dir_path(__FILE__) . 'rns-template.html.php');
        $html_content = ob_get_clean();

        return $html_content;
    }

    function get_random_user() {
        // Query all users that are only subscribers, so no admins or authors or contributors
        $users = get_users(array('role__in' => array('subscriber')));

        if (empty($users)){
            // make a mock user so we don't need to return null and do more checks
            // If Jimothy wins, you know something is broken.
            $new_user =  new WP_User(1000000);
            $new_user->display_name = "Jimothy Wongingtons";
            return $new_user;
        }

        // Get a random user
        $random_user = $users[array_rand($users)];

        return $random_user;
    }

    /**
     * Loads our CSS and JS into the plugin.
     */
    function enqueue_random_name_selector_styles() {
        wp_register_style( 'random-name-selector-style', plugins_url( '/css/style.css' , __FILE__ ) );
        // wp_register_script( 'custom-gallery', plugins_url( '/js/gallery.js' , __FILE__ ) );    
        wp_enqueue_style( 'random-name-selector-style' );
        // wp_enqueue_script( 'custom-gallery' );
    }

    add_action('wp_enqueue_scripts', 'enqueue_random_name_selector_styles');
?>



