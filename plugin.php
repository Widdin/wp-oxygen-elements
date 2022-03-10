<?php

/*
Plugin Name: Oxygen Custom Elements
Plugin URI: https://github.com/Widdin/wp-oxygen-elements
Description: Adds custom elements to Oxygen.
Author: Simon Vidman
Author URI: https://github.com/Widdin/
Version: 1.0
*/

add_action('plugins_loaded', 'oxygen_custom_elements_init');

function oxygen_custom_elements_init()
{

    if (!class_exists('OxygenElement')) {
        return;
    }

    foreach ( glob(plugin_dir_path(__FILE__) . "elements/*/*.php" ) as $filename) {
        include $filename;
    }

}
