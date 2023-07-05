<?php
/**
 * Plugin Name: WPML Custom Fields Translation
 * Description: This plugin helps to set custom fields translation preferences.
 * Version: 1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once 'includes/WPML_Custom_Fields_Helper.php';

new WPML_Custom_Fields_Translation();
