<?php
/**
 * Plugin Name: WPML Custom Fields Translation
 * Description: This plugin helps to set custom fields translation preferences.
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class WPML_Custom_Fields_Translation_Plugin {
	function __construct() {
		add_action( 'admin_init', [ $this, 'wpml_cf_translation_verify_wpml' ] );

		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			require_once 'includes/WPML_Custom_Fields_Helper.php';
			new WPML_Custom_Fields_Translation();
		}
	}

	function wpml_cf_translation_verify_wpml() {
		if ( ! class_exists( 'WPML_CF_Translation_Verify_Dependencies' ) ) {
			require_once 'includes/WPML_CF_Translation_Verify_Dependencies.php';
		}

		$verifier     = new WPML_CF_Translation_Verify_Dependencies();
		$wpml_version = defined( 'ICL_SITEPRESS_VERSION' ) ? ICL_SITEPRESS_VERSION : false;
		$verifier->verify_wpml( $wpml_version );
	}
}

new WPML_Custom_Fields_Translation_Plugin();
