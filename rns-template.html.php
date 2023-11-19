<!-- Including a Promise polyfill just in case this is used in an old browser -->
<script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=Promise"></script>

<div class="custom-container">
    <?php
        // Check if we have a CSV has a value
        if (empty($rns_csv_file)) {
            // Output the CSV file value
            echo '<div class="warning-box"><p>No CSV file selected in admin settings so no winner can be chosen!</p></div>';
        }
    ?>
    <div id="winnerBox">
        <button id="fetchButton">Pick a winner!</button>
        <div id="winnerResult"></div>
    </div>
</div>
