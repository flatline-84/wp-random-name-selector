# Random Name Selector

The Random Name Selector is a simple project that allows you to generate random names. It can be useful for scenarios like prize selection, team assignments, or any situation where random name selection is required.

## Features

- Generates random names for various use cases.
- Selects users from a CSV file that is uploaded to Media. It only requires `first_name`, `last_name`, `email`, and mobil`e headers. All other data is ignored. It does not distinctly select users so there can be multiple entries in the CSV.
- Provides a WordPress plugin that can be easily integrated into your WordPress site.

## Usage

### WordPress Plugin

1. Download the files in this repository as a `.zip`.
2. Upload the plugin with Wordpress's in-built plugin installer by selecting the zip you downloaded.
3. Activate the plugin from the WordPress admin panel.
4. Create a page and use the shortcode `[random_name_selector]` to display the random name selector. Make sure to lock this down to admins only / private.

The RNS Plugin Settings page only shows the last 3 database entries.