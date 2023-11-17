<div class="custom-container">
    <h1>Welcome to goon of fortune!</h1>
    <h2>Congrats to: <?php echo esc_html($random_user->display_name); ?></h2>
    <?php
        // Check if the option has a value
        if (!empty($rns_csv_file)) {
            // Output the CSV file value
            echo 'CSV File: ' . esc_html($rns_csv_file);
        } else {
            // Output a message if the option is empty
            echo 'No CSV file selected.';
        }
    ?>
</div>
