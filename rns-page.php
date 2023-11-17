<?php
    /*
     * This function adds the winner to the database.
     * User is the winner.
     * Admin is who called the function.
     */
    function add_winner_to_db($user, $admin, $csv){
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
                'csvid' => $csv, 
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

        // Retrieve the CSV file option
        $rns_csv_file = get_option('selected_csv_id');

        $random_user = get_random_user();

        // Load and include the template file
        ob_start();
        include(plugin_dir_path(__FILE__) . 'rns-template.html.php');
        $html_content = ob_get_clean();

        add_winner_to_db($random_user, $current_user, $rns_csv_file);

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