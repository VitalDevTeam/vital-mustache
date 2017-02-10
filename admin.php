<?php
/**
 * Plugin Options Page
 * https://developer.wordpress.org/plugins/settings/custom-settings-page/
 */

// Exit if accessed directly
if (! defined('ABSPATH')) exit;

class Vital_Mustache_Settings_Page {

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Initialize
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'), 100);
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options subpage to Settings.
     * You can add top-level pages using add_options_page().
     */
    public function add_plugin_page() {

        add_submenu_page(
            // The slug name for the parent menu (or the file name
            // of a standard WordPress admin page)
            'options-general.php',
            // Page Title
            'Mustache Settings',
            // Menu Title
            'Mustache',
            // Capability needed to access this page
            'manage_options',
            // Menu Slug - Used by WP to idenify this page
            'vital_mustache',
            // Callback that renders page
            array($this, 'page_render')
        );

    }

    /**
     * Render the options page
     */
    public function page_render() {
        $this->options = get_option('vital_mustache_option');
        ?>
        <div class="wrap">
            <h2>Mustache Settings</h2>
            <form method="post" action="options.php">
            <?php
                // Output nonce, action, and option_page fields.
                // Use the name of the appropriate options group.
                settings_fields('vital_mustache_options_group');
                // Prints settings section.
                // Use the slug name of the page whose settings sections you want to output.
                // This should match the page name used in add_settings_section().
                do_settings_sections('vital_mustache');
                // Submit button
                submit_button('Save');
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {

        /*
            Register a setting and its sanitization callback.
            Defines the actual option the setting fields will be stored in
         */
        register_setting(
            // A name for the options group
            'vital_mustache_options_group',
            // A name for your option
            'vital_mustache_option',
            // Callback function that sanitizes the option's value
            array($this, 'sanitize')
        );

        /*
            Adds a section of settings. Use as many as you need.
         */
        add_settings_section(
            // String for use in the 'id' attribute of tags
            'mustache_settings_section_1',
            // Title of the section
            'Mustache Settings Section 1',
            // Function that renders the section with content
            array($this, 'print_section_1_info'),
            // The menu page on which to display this section
            'vital_mustache'
        );

        /*
            Add a single setting field (an option in the wp_options table)
         */
        add_settings_field(
            // String for use in the 'id' attribute of tags
            'vital_mustache_template_folder',
            // Title of the field
            'Template Folder',
            // Function that fills the field with the desired inputs
            // as part of the larger form
            array($this, 'vital_mustache_template_folder_callback'),
            // The menu page on which to display this field
            'vital_mustache',
            // The section of the settings page in which to show the box
            'mustache_settings_section_1'
        );
    }

    /**
     * Sanitize each setting field as needed
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();

        if (isset( $input['vital_mustache_template_folder']))
            $new_input['vital_mustache_template_folder'] = sanitize_text_field($input['vital_mustache_template_folder']);

        return $new_input;
    }

    /**
     * Print section text
     */
    public function print_section_1_info() {
        print 'This is description text for the section 1.';
    }

    /**
     * Get the settings and print its values
     */
    public function vital_mustache_template_folder_callback() {

        if (isset($this->options['vital_mustache_template_folder'])) {
            $vital_mustache_template_folder = esc_attr($this->options['vital_mustache_template_folder']);
        } else {
            $vital_mustache_template_folder = '';
        }

        $placeholder = VitalMustache::$DEFAULT_TEMPLATE_FOLDER;

        $output = "<input style='width: 100%;' type='text' id='vital_mustache_template_folder' name='vital_mustache_option[vital_mustache_template_folder]' value='{$vital_mustache_template_folder}' placeholder='{$placeholder}'/>";
        $output .= '<p class="description">A description for the field (optional).</p>';

        echo $output;
    }
}

if (is_admin())
    $mustache_settings_page = new Vital_Mustache_Settings_Page();
