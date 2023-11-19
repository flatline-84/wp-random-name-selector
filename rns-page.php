<?php

    /*****************************
     * Random Name Selector Page
     *****************************/
    // Add a shortcode for displaying the random name selector on a page
    add_shortcode('random_name_selector', 'display_random_name_selector');

    /*
     * Selects a random user and displays it on the webpage.
     */
    function display_random_name_selector() {

        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        // Safely load the current user.
        $current_user = wp_get_current_user();
        if ( ! ( $current_user instanceof WP_User ) ) {
            return;
        }

        // Retrieve the CSV file option
        $rns_csv_file = get_option('selected_csv_id');
        
        // Load and include the template file
        ob_start();
        include(plugin_dir_path(__FILE__) . 'rns-template.html.php');
        $html_content = ob_get_clean();
        
        return $html_content;
    }

    /*
     * Loads our CSS and JS into the plugin.
     */
    function enqueue_random_name_selector_styles() {
        wp_register_style( 'random-name-selector-style', plugins_url( '/css/style.css' , __FILE__ ) );
        wp_register_script( 'rns-js', plugins_url( '/js/rns.js' , __FILE__ ) );
        wp_enqueue_style( 'random-name-selector-style' );
        wp_enqueue_script( 'rns-js' );

            // Localize the script with the nonce
        wp_localize_script('rns-js', 'wp_data', array(
            'nonce' => wp_create_nonce('wp_rest'),
            'api_url' => esc_url_raw(rest_url('/rns/v1/winner')),
        ));
    }

    add_action('wp_enqueue_scripts', 'enqueue_random_name_selector_styles');
?>