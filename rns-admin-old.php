<?php
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
        register_setting('rns_settings_page', 'rns_options', 'rns_options_validate');
    
        // Add sections and fields
        add_settings_section('rns_main', 'Main Settings', 'rns_section_text', 'rns-settings');
        add_settings_field('rns_settings', 'Settings Data', 'rns_settings_page', 'rns-settings', 'rns_main');

        add_settings_field('rns_field', 'Database Data', 'rns_field_output', 'rns-settings', 'rns_main');

        // Add a section for CSV settings
        add_settings_section('csv_settings', 'CSV Settings', 'csv_section_text', 'rns-settings');
        // Add the file upload field to the CSV settings section
        add_settings_field('csv_upload', 'Upload CSV File', 'csv_upload_field', 'rns-settings', 'csv_settings');


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

    // Callback function for the CSV settings section text
    function csv_section_text() {
        echo '<p>Configure CSV upload settings.</p>';
    }
    
    // Callback function for the CSV file upload field
    function csv_upload_field($args) {
        $option_name = 'csv_file';
        $csv_file = get_option($option_name);
        ?>
        <input type="file" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_name); ?>" accept=".csv">
        <p>Current CSV file: <a href="<?php echo esc_url($csv_file); ?>" target="_blank"><?php echo esc_html(basename($csv_file)); ?></a></p>
        <?php
    }

    function rns_settings_page() {
        ?>
        <div class="wrap">
            <h1>RNS Settings</h1>
            <form method="post" action="options.php" enctype="multipart/form-data">
                <?php
                settings_fields('');
                do_settings_sections('');
                submit_button('Save Settings');
                ?>
            </form>
        </div>
        <?php
    }

    // Add the CSV file option to the rns_options
    function rns_options($options) {
        $options['csv_file'] = '';
        return $options;
    }

    
    // Validate and save the CSV file option
    function rns_options_validate($input) {
        $new_input = array();

        // Handle the CSV file upload
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $file = wp_handle_upload($_FILES['csv_file'], array('test_form' => false));
            if ($file && !isset($file['error'])) {
                $new_input['csv_file'] = $file['url'];
            } else {
                add_settings_error('csv_file', 'csv_file_error', 'Error uploading CSV file.', 'error');
            }
        } else {
            // Keep the existing CSV file option if no new file is uploaded
            $new_input['csv_file'] = get_option('csv_file');
        }

        return $new_input;
    }

?>