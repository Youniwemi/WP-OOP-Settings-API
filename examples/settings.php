<?php
/**
 * This is an example of how to use WP_Settings_Kit class to add a settings page
 * @package WP_Settings_Kit
 */

// Make sure class is loaded or load via composer
require_once  '../class-wp-settings-kit.php';


// You can override WP_Settings_Kit class to have a finer control over the admin menu page
class MySettings extends WP_Settings_Kit
{
    protected $settings_name = 'awesome_plugin';
    public function admin_menu()
    {
        add_menu_page(
            'My Awesome Plugin',
            'Awesome Plugin',
            'manage_options',
            'awesome-plugin',
            [$this, 'plugin_page']
        );
    }

    public function plugin_page()
    {
        echo '<div class="wrap">';
        echo '<h1>My Awesome plugin</h1>';
        $this->show_navigation();
        $this->show_forms();
        echo '</div>';
    }
}

$settings_config = [
    'id'    => 'wpskit_basic',
    'title' => __('Basic Settings'),
    'fields' => [
        [
            'id'      => 'text',
            'type'    => 'text',
            'name'    => __('Text Input'),
            'desc'    => __('Text input description'),
            'default' => 'Default Text',
        ],
        [
            'id'                => 'text_no',
            'type'              => 'number',
            'name'              => __('Number Input'),
            'desc'              => __('Number field with validation callback `intval`'),
            'default'           => 1,
            'sanitize_callback' => 'intval',
        ],
        [
            'id'   => 'password',
            'type' => 'password',
            'name' => __('Password Input'),
            'desc' => __('Password field description'),
        ],
        [
            'id'   => 'textarea',
            'type' => 'textarea',
            'name' => __('Textarea Input'),
            'desc' => __('Textarea description'),
        ],
        [
             'id'   => 'title',
            'type' => 'title',
            'name' => '<h1>Title</h1>',
        ],
         [
              'id'   => 'checkbox',
            'type' => 'checkbox',
            'name' => __('Checkbox'),
            'desc' => __('Checkbox Label'),
        ],
        [
            'id'      => 'radio',
            'type'    => 'radio',
            'name'    => __('Radio'),
            'desc'    => __('Radio Button'),
            'options' => array(
                'yes' => 'Yes',
                'no'  => 'No',
            ),
        ],
        [
            'id'      => 'multicheck',
            'type'    => 'multicheck',
            'name'    => __('Multile checkbox'),
            'desc'    => __('Multile checkbox description'),
            'options' => array(
                'yes' => 'Yes',
                'no'  => 'No',
            ),
        ],
        [
            'id'      => 'select',
            'type'    => 'select',
            'name'    => __('A Dropdown'),
            'desc'    => __('A Dropdown description'),
            'options' => array(
                'yes' => 'Yes',
                'no'  => 'No',
            ),
        ],
        [
            'id'      => 'image',
            'type'    => 'image',
            'name'    => __('Image'),
            'desc'    => __('Image description'),
            'options' => array(
                'button_label' => 'Choose Image',
            ),
        ],
        [
            'id'      => 'file',
            'type'    => 'file',
            'name'    => __('File'),
            'desc'    => __('File description'),
            'options' => array(
                'button_label' => 'Choose file',
            ),
        ],
        [
            'id'          => 'color',
            'type'        => 'color',
            'name'        => __('Color'),
            'desc'        => __('Color description'),
            'placeholder' => __('#5F4B8B'),
        ],
        [
             'id'   => 'wysiwyg',
            'type' => 'wysiwyg',
            'name' => __('WP_Editor'),
            'desc' => __('WP_Editor description'),
        ]
    ],
];

// This will init the settings page
$my_settings = new MySettings([ 'wpskit_basic' => $settings_config ]);


// Your constants {SECTION_ID}_{FIELD_ID} will be ready after the instanciation of your class MySettings.
// If you need translation, make sure to prepare your options after the translation is loaded.
// If you need to use those constants as soon as they are loaded in other plugins, you can use settings_ready_{settings_name} hook
//
// do_action('settings_ready_awesome_plugin' , function(){
// use constant wposa_basic_text
// });
