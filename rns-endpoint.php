<?php

    // The actual endpoint function that returns the winner
    function rns_get_winner() {
        $winner = read_csv_and_select_random_item();
        $redacted_name = $winner['first_name'] . " " . $winner['last_name'][0] . ".";

        $csv_id = get_option('selected_csv_id');

        // Safely load the current user.
        $admin = wp_get_current_user();
        if ( ! ( $admin instanceof WP_User ) ) {
            return;
        }
        add_winner_to_db($winner, $admin, $csv_id);

        return $redacted_name;
    }

    /*
     * This function adds the winner to the database.
     * Winner is the winner.
     * Admin is who called the function.
     */
    function add_winner_to_db($winner, $admin, $csv){
        global $wpdb;
        global $rns_db_name;

        $table_name = $wpdb->prefix . $rns_db_name;
        $wpdb->insert( 
            $table_name, 
            array( 
                'time' => current_time( 'mysql' ), 
                'username' => $winner['first_name'] . " " . $winner['last_name'], 
                'mobile' => $winner['mobile'],
                'email' => $winner['email'],
                'admin' => $admin->ID,
                'csvid' => $csv, 
            ) 
        );
    }

    function read_csv_and_select_random_item() {

        $csv_id = get_option('selected_csv_id');
        if (empty($csv_id)){
            return 'no CSV set in admin page!';
        }

        $csv_file_path = get_attached_file($csv_id);

        // Check if the file exists
        if (!file_exists($csv_file_path)) {
            return 'CSV file not found.';
        }
    
        // Read the CSV file
        $csv_data = array_map('str_getcsv', file($csv_file_path));
    
        // Extract headers
        $headers = array_shift($csv_data);
    
        // Initialize the data array
        $data = array();
    
        // Loop through each row and store data
        foreach ($csv_data as $row) {
            $row_data = array();
            foreach ($headers as $index => $header) {
                $row_data[$header] = isset($row[$index]) ? $row[$index] : '';
            }
            $data[] = $row_data;
        }
    
        // Select a random item
        $random_item = $data[array_rand($data)];
        return $random_item;
    }    

    // Permissions check so only admins can hit the endpoint
    function rns_check_permissions() {
        // check_ajax_referer('get_winner', 'nonce');
        return current_user_can('manage_options'); // only admins can do this
    }

    add_action('rest_api_init', function () {
        register_rest_route( 'rns/v1', '/winner', array(
        'methods' => 'GET',
        'callback' => 'rns_get_winner',
        'permission_callback' => 'rns_check_permissions',
        ));
    });
?>