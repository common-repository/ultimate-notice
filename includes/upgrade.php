<?php
// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
	exit;

new Ultimate_Notice_Upgrade( $ultimate_notice );

class Ultimate_Notice_Upgrade {

	private $defaults;

	public function __construct( $ultimate_notice ) {
		// attributes
		$this->defaults = $ultimate_notice->get_defaults();

		// actions
		add_action( 'init', array( $this, 'check_upgrade' ) );
	}

	public function check_upgrade() {
		if ( ! current_user_can( 'manage_options' ) )
			return;

		// gets current database version
		$current_db_version = get_option( 'ultimate_notice_version', '1.0.0' );

		// new version?
		if ( version_compare( $current_db_version, $this->defaults['version'], '<' ) ) {
			// updates plugin version
			update_option( 'ultimate_notice_version', $this->defaults['version'], false );
		}
	}

}