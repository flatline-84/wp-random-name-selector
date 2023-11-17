<?php
    /*
    Plugin Name: Random Name Selector
    Description: A simple plugin to randomly select a user for a prize.
    Version: 0.1
    Author: flatline-84
    */

    /****************************
     * ADMIN SETUP
    *****************************/
    global $rns_db_version;
    $rns_db_version = '1.0';
    global $rns_db_name;
    $rns_db_name = 'random_name_selector';

    function rns_db_install() {
        global $wpdb;
        global $rns_db_version;
        global $rns_db_name;

        $table_name = $wpdb->prefix . $rns_db_name;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            username varchar(255) NOT NULL,
            userid int NOT NULL,
            email varchar(255) NOT NULL,
            admin varchar(255) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        add_option( 'rns_db_version', $rns_db_version );
    }

    register_activation_hook(__FILE__, 'rns_db_install');

    // Add a menu page for the plugin settings
    function custom_rns_menu() {
        add_menu_page(
            'Random Name Selector',     // page title
            'Random Name Selector',     // menu title
            'manage_options',           // capability (use manage_options for admins)
            'rns-settings',             // menu slug
            'rns_settings',        // callback function
            'dashicons-admin-generic',  // menu icon
            30                          // menu position
        );
    }
    add_action('admin_menu', 'custom_rns_menu');

    function rns_settings() {
        // Register settings
        // register_setting('rns_settings_page', 'rns_options', 'rns_options_validate');
    
        // Add sections and fields
        add_settings_section('rns_main', 'Main Settings', 'rns_section_text', 'rns-settings');
        add_settings_field('rns_field', 'Database Data', 'rns_field_output', 'rns-settings', 'rns_main');

        // Make sure to update this part
        do_settings_sections('rns-settings');
    }
    
    function rns_section_text($args) {
        echo '<p>Configure main settings for the Random Name Selector.</p>';
    }
    
    function rns_field_output($args) {
        // $options = get_option('rns_options');
    
        // Fetch data from the database table
        global $wpdb;
        global $rns_db_name;
        $table_name = $wpdb->prefix . $rns_db_name;
        $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        if ( $wpdb->last_error ) {
            echo 'wpdb error: ' . $wpdb->last_error;
        }

        $headers = ['datetime', 'username', 'email', 'admin'];
        echo '<table>';
        echo '<tr>';

        foreach ($headers as $header) {
            echo '<th>' . $header . '</th>';
        }
        echo '</tr>';

        foreach ($data as $row) {
            echo '<tr>';
            echo '<td>' . esc_html($row['time']) . '</td>';
            echo '<td>' . esc_html($row['username']) . '</td>';
            echo '<td>' . esc_html($row['email']) . '</td>';
            echo '<td>' . esc_html($row['admin']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }

    function rns_options_validate($input) {
        // Add validation logic here if needed
        return $input;
    }

    /*
     * This function adds the winner to the database.
     * User is the winner.
     * Admin is who called the function.
     */
    function add_winner_to_db($user, $admin){
        global $wpdb;
        global $rns_db_name;

        $table_name = $wpdb->prefix . $rns_db_name;
        $wpdb->insert( 
            $table_name, 
            array( 
                'time' => current_time( 'mysql' ), 
                'username' => $user->display_name, 
                'userid' => $user->ID,
                'email' => $user->user_email,
                'admin' => $admin->display_name,
            ) 
        );
    }

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

        $random_user = get_random_user();

        // Load and include the template file
        ob_start();
        include(plugin_dir_path(__FILE__) . 'rns-template.html.php');
        $html_content = ob_get_clean();

        add_winner_to_db($random_user, $current_user);

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
            $new_user->email = "jimothy@example.com";
            return $new_user;
        }

        // Get a random user
        $random_user = $users[array_rand($users)];

        return $random_user;
    }

    /*
     * Loads our CSS and JS into the plugin.
     */
    function enqueue_random_name_selector_styles() {
        wp_register_style( 'random-name-selector-style', plugins_url( '/css/style.css' , __FILE__ ) );
        // wp_register_script( 'custom-gallery', plugins_url( '/js/gallery.js' , __FILE__ ) );    
        wp_enqueue_style( 'random-name-selector-style' );
        // wp_enqueue_script( 'custom-gallery' );
    }

    add_action('wp_enqueue_scripts', 'enqueue_random_name_selector_styles');
    // DO NOT ADD EMPTY LINES AFTER THE PHP CLOSING BRACE OR THE PLUGIN INSTALLER WILL KILL YOUR FAMILY.
?>