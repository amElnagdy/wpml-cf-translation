<?php

class WPML_Custom_Fields_Translation {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'wpml_custom_fields_helper_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function enqueue_scripts() {
		wp_enqueue_script(
			'wpml_custom_fields_helper_script',
			plugin_dir_url( __FILE__ ) . '/js/wpml_custom_fields_helper.js',
			array( 'jquery' ),
			false,
			true
		);

		wp_localize_script(
			'wpml_custom_fields_helper_script',
			'wpmlData',
			array(
				'wpml_config' => $this->generate_wpml_config(),
			)
		);
	}

	public function wpml_custom_fields_helper_menu() {
		add_menu_page(
			esc_html__( 'WPML CF Translation', 'wpml-cf-translation' ),
			esc_html__( 'WPML CF Translation', 'wpml-cf-translation' ),
			'manage_options',
			'wpml-cf-translation',
			array( $this, 'wpml_cf_helper_admin_page' ),
			'dashicons-code-standards',
			99
		);
	}

	public function wpml_cf_helper_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpml-cf-translation' ) );
		}

		echo '<h1>' . esc_html__( 'WPML Custom Fields Translation', 'wpml-cf-translation' ) . '</h1>';

		$custom_fields = $this->get_custom_fields();

		if ( ! empty( $custom_fields ) ) {
			echo esc_html__( 'We have found custom fields in your website. Press Generate XML to generate the translation preferences for them.', 'wpml-cf-translation' );
			echo '<br >';
			echo '<button id="generate">' . esc_html__( 'Generate XML', 'wpml-cf-translation' ) . '</button>';
			echo '<button id="copy">' . esc_html__( 'Copy XML', 'wpml-cf-translation' ) . '</button>';
			echo '<textarea id="xml_output" readonly style="width:100%;min-height:200px;"></textarea>';
		} else {
			echo esc_html__( 'The custom fields in this website have their translation preferences', 'wpml-cf-translation' );
		}
	}

	public function get_custom_fields() {
		global $wpdb;

		// We don't need system fields starting with _

		$meta_keys = $wpdb->get_results( "SELECT DISTINCT meta_key FROM $wpdb->postmeta WHERE meta_key NOT LIKE '\_%' ORDER BY meta_key ASC" );

		$custom_fields = array();

		foreach ( $meta_keys as $meta_key ) {
			$custom_fields[] = $meta_key->meta_key;
		}

		// We need to exclude the fields with defined translation preference in WPML

		$excluded_custom_fields = array();

		$settings = get_option( 'icl_sitepress_settings' );

		if ( ! empty( $settings['translation-management']['custom_fields_translation'] ) ) {
			foreach ( $settings['translation-management']['custom_fields_translation'] as $custom_field => $value ) {
				$excluded_custom_fields[] = $custom_field;
			}
		}

		// Providing a filter to add more fields to be excluded

		/**
		 * Example
		 *
		 * function my_custom_excluded_fields($excluded_fields) {
		 * $excluded_fields[] = 'my_custom_field_1';
		 * $excluded_fields[] = 'my_custom_field_2';
		 * return $excluded_fields;
		 * }
		 * add_filter('wpml_custom_fields_helper_excluded_custom_fields', 'my_custom_excluded_fields');
		 */

		$excluded_custom_fields = apply_filters( 'wpml_custom_fields_helper_excluded_custom_fields', $excluded_custom_fields );

		$custom_fields = array_diff( $custom_fields, $excluded_custom_fields );

		// We don't need these fields wpml_, attribute_pa-, or acfml-

		foreach ( $custom_fields as $key => $field ) {
			if ( strpos( $field, 'acfml' ) !== false ||
			     strpos( $field, 'attribute_pa' ) !== false ||
			     strpos( $field, 'wpml' ) !== false ) {
				unset( $custom_fields[ $key ] );
			}
		}

		return $custom_fields;
	}

	public function determine_translation_preference() {

		global $wpdb;

		$custom_fields           = $this->get_custom_fields();
		$translation_preferences = array();

		foreach ( $custom_fields as $custom_field ) {

			$value = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->postmeta WHERE meta_key = %s LIMIT 1", $custom_field ) );

			// Check if value is numeric, a date string, or specific strings
			$date        = DateTime::createFromFormat( 'd-m-Y', $value );
			$date_errors = DateTime::getLastErrors();

			$copy_values = [ 'yes', 'no', 'on', 'off', 'true', 'false', 'default' ];

			if ( is_numeric( $value ) ||
			     ( $date && $date_errors['warning_count'] == 0 && $date_errors['error_count'] == 0 ) ||
			     in_array( $value, $copy_values ) ||
			     is_serialized( $value )
			) {
				$translation_preferences[ $custom_field ] = 'copy';
			} else {
				$translation_preferences[ $custom_field ] = 'translate';
			}

		}

		return $translation_preferences;
	}

	public function format_xml( $xml_string ) {
		$dom                     = new DOMDocument;
		$dom->preserveWhiteSpace = false;
		$dom->loadXML( $xml_string );
		$dom->formatOutput = true;

		return htmlentities( $dom->saveXML( $dom->documentElement ) );
	}

	public function generate_wpml_config() {
		$translation_preferences = $this->determine_translation_preference();

		$wpml_config = '<wpml-config><custom-fields>';

		foreach ( $translation_preferences as $custom_field => $preference ) {
			$wpml_config .= "<custom-field action=\"$preference\">$custom_field</custom-field>";
		}

		$wpml_config .= '</custom-fields></wpml-config>';

		return $this->format_xml( $wpml_config );
	}
}
