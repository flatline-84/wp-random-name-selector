<?php

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
            mobile varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            admin varchar(255) NOT NULL,
            csvid int(11) NOT NULL,
            PRIMARY KEY  (id)
        ) ENGINE=InnoDB $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        add_option('rns_db_version', $rns_db_version);
    }

    register_activation_hook(RNS_PLUGIN_FILE_URL, 'rns_db_install');

    // Add a menu page for the plugin settings
    function rns_plugin_menu() {
        add_menu_page(
            'RNS Plugin',              // page title
            'RNS Plugin',              // menu title
            'manage_options',          // capability (use manage_options for admins)
            'rns-plugin-settings',     // menu slug
            'rns_plugin_settings_page',// callback function
            'dashicons-admin-generic',  // menu icon
            30                         // menu position
        );
    }
    add_action('admin_menu', 'rns_plugin_menu');

    // Register Settings
    function rns_plugin_settings() {
        // Register settings
        register_setting('rns_plugin_settings', 'selected_csv_id');

        // Add sections and fields
        add_settings_section('rns_main', 'Main Settings', 'rns_section_text', 'rns-plugin-settings');
        add_settings_field('rns_field', 'Database Data', 'rns_field_output', 'rns-plugin-settings', 'rns_main');
    }
    add_action('admin_init', 'rns_plugin_settings');

    // Settings Page
    function rns_plugin_settings_page() {
        ?>
        <div class="wrap">
            <h1>RNS Plugin Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rns_plugin_settings');
                do_settings_sections('rns-plugin-settings');
                ?>
                <label for="selected_csv_id">Select CSV File:</label>
                <?php
                // Display a dropdown with CSV files from the media library
                $csv_files = get_uploaded_csv_files();
                ?>
                <select id="selected_csv_id" name="selected_csv_id">
                    <option value="">Select CSV File</option>
                    <?php foreach ($csv_files as $file): ?>
                        <option value="<?php echo esc_attr($file['id']); ?>" <?php selected(get_option('selected_csv_id'), $file['id']); ?>>
                            <?php echo esc_html($file['title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php submit_button(); ?>
            </form>
    
            <!-- Display the selected CSV file on the dashboard -->
            <h2>Selected CSV File</h2>
            <?php
            $selected_csv_id = get_option('selected_csv_id');
            if ($selected_csv_id) {
                $selected_csv_url = wp_get_attachment_url($selected_csv_id);
                $selected_csv_title = get_the_title($selected_csv_id);
                echo '<p>'. esc_html($selected_csv_title) . " : " . esc_html($selected_csv_url) . '</p>';
            } else {
                echo '<p>No CSV file selected.</p>';
            }
            ?>
        </div>
        <?php
    }

    // Custom function to get the list of uploaded CSV files
    function get_uploaded_csv_files() {
        $args = array(
            'post_type'      => 'attachment',
            'post_mime_type' => 'text/csv',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        );

        $query = new WP_Query($args);

        $csv_files = array();

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $csv_files[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                );
            }
            wp_reset_postdata();
        }

        return $csv_files;
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
        $data = $wpdb->get_results("SELECT * FROM $table_name ORDER BY time DESC LIMIT 3", ARRAY_A);
        if ($wpdb->last_error) {
            echo 'wpdb error: ' . $wpdb->last_error;
        }

        $headers = ['datetime', 'username', 'mobile', 'email', 'admin', 'csv_file'];
        echo '<table>';
        echo '<tr>';

        foreach ($headers as $header) {
            echo '<th>' . $header . '</th>';
        }
        echo '</tr>';

        foreach ($data as $row) {
            $timestamp = strtotime($row['time']); // Convert the database timestamp to a Unix timestamp
            $formatted_time = wp_date('Y-m-d H:i:s', $timestamp);

            $csv_name = get_the_title($row['csvid']);
            $admin_name = get_user_by('id', $row['admin']);

            echo '<tr>';
            echo '<td>' . esc_html($row['time']) . '</td>';
            echo '<td>' . esc_html($row['username']) . '</td>';
            echo '<td>' . esc_html($row['mobile']) . '</td>';
            echo '<td>' . esc_html($row['email']) . '</td>';
            echo '<td>' . esc_html($admin_name->display_name) . '</td>';
            echo '<td>' . esc_html($csv_name) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    
?>