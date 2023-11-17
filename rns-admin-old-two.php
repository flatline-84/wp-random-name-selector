<?php
    // rns-admin.php

    // Database Installation
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
        dbDelta($sql);

        add_option('rns_db_version', $rns_db_version);
        add_option('csv_file', '');

    }

    register_activation_hook(__FILE__, 'rns_db_install');

    // Add a menu page for the plugin settings
    function custom_rns_menu() {
        add_menu_page(
            'Random Name Selector',     // page title
            'Random Name Selector',     // menu title
            'manage_options',           // capability (use manage_options for admins)
            'rns-settings',             // menu slug
            'rns_settings_page',        // callback function
            'dashicons-admin-generic',  // menu icon
            30                          // menu position
        );
    }
    add_action('admin_menu', 'custom_rns_menu');

    // Register Settings
    function rns_settings() {
        // Register settings
        register_setting('rns_settings_page', 'csv_file');

        // Add sections and fields
        add_settings_section('rns_main', 'Main Settings', 'rns_section_text', 'rns-settings');
        add_settings_field('rns_field', 'Database Data', 'rns_field_output', 'rns-settings', 'rns_main');

        // Add a section for CSV settings
        add_settings_section('csv_settings', 'CSV Settings', 'csv_section_text', 'rns-settings');
        // Add the file upload field to the CSV settings section
        // add_settings_field('csv_upload', 'Upload CSV File', 'csv_upload_field', 'rns-settings', 'csv_settings');
        add_settings_field('csv_select', 'CSV Files', 'csv_upload_field', 'rns-settings', 'csv_settings');
    }

    add_action('admin_init', 'rns_settings');

    // Settings Page
    function rns_settings_page() {
        ?>
        <div class="wrap">
            <h1>RNS Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rns_settings');
                do_settings_sections('rns-settings');
                ?>
                <label for="csv_file">CSV File:</label>
                <input type="text" id="csv_file" name="csv_file" value="<?php echo esc_attr(get_option('csv_file')); ?>">
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // Main Settings Section
    function rns_section_text() {
        echo '<p>Configure main settings for the Random Name Selector.</p>';
    }

    // Display Database Data
    function rns_field_output() {
        global $wpdb;
        global $rns_db_name;
        $table_name = $wpdb->prefix . $rns_db_name;
        $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
        if ($wpdb->last_error) {
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

    // CSV Settings Section
    function csv_section_text() {
        echo '<p>Configure CSV upload settings.</p>';
        echo '<p>Currently selected CSV file: ' . get_selected_csv_file() . '</p>';

        // Get all options
        $options = wp_load_alloptions();

        // Output all options for debugging
        echo '<pre>';
        print_r($options);
        echo '</pre>';
    }

    // Add the CSV file option to the rns_options
    function rns_options($options) {
        $options['csv_file'] = get_option('csv_file');
        return $options;
    }


    // Helper function to get option values
    function get_rns_option($key) {
        $options = get_option('rns_options');
        return isset($options[$key]) ? $options[$key] : '';
    }

    function csv_upload_field() {
        $option_name = 'csv_file';
        $csv_file = get_option($option_name);
        $all_csv_files = get_uploaded_csv_files(); // Custom function to get the list of CSV files
    
        ?>
        <select id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_name); ?>">
            <option value="">Select CSV File</option>
            <?php
            foreach ($all_csv_files as $file) {
                $selected = selected($file['id'], $csv_file, false);
                echo '<option value="' . esc_attr($file['id']) . '" ' . $selected . '>' . esc_html($file['title']) . '</option>';
            }
            ?>
        </select>
        <?php
        if ($csv_file) {
            echo '<p>Selected CSV file: <a href="' . esc_url($csv_file) . '" target="_blank">' . esc_html(basename($csv_file)) . '</a></p>';
        }
    }
    
    // Custom function to get the list of uploaded CSV files
    
    function get_uploaded_csv_files() {
        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'text/csv', // Adjust mime type if needed
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        );
    
        $query = new WP_Query($args);
    
        $csv_files = array();
    
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $csv_files[] = array(
                    'title' => get_the_title(),
                    'url'   => wp_get_attachment_url(get_the_ID()),
                );
            }
            wp_reset_postdata();
        }
    
        return $csv_files;
    }
    
    // Validate and save the CSV file option
    function rns_options_validate($input) {
        $new_input = array();

        // Handle the CSV file selection
        if (!empty($input['csv_file'])) {
            $new_input['csv_file'] = $input['csv_file'];
        }

        return $new_input;
    }

    
    // Custom function to get the selected CSV file URL
    
    function get_selected_csv_file() {
        $options = get_option('csv_file');
        return isset($options) ? $options : '';
    }
?>