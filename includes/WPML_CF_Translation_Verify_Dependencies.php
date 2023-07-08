<?php
class WPML_CF_Translation_Verify_Dependencies {

	/**
	 * @param string|false $wpml_core_version
	 */
	function verify_wpml( $wpml_core_version ) {
		if ( false === $wpml_core_version ) {
			add_action(
				'admin_notices',
				array(
					$this,
					'notice_no_wpml',
				)
			);
		}
	}

	function notice_no_wpml() {
		?>
		<div class="error wpml-admin-notice wpml-st-inactive wpml-inactive">
			<p><?php esc_html_e( 'Please activate WPML Multilingual CMS to have WPML Custom Fields Translation.', 'wpml-troubleshoot' ); ?></p>
		</div>
		<?php
	}
}
