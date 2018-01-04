<?php
/**
 * Shortcodes for How Goes It.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/public
 */

/**
 * On initialize the plugin will create new tables required for the plugin.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Admin_Initialize extends How_Goes_It_Admin {

	private $hgi_db_version = '1.0.3';

	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}

	function init() {
		register_activation_hook( __FILE__, [ $this, 'hgi_install' ] );
	}

	function hgi_install() {
		global $wpdb;

		$table_scores = $wpdb->prefix . 'hgi_scores';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_scores (
	   `hgi_id` INT NOT NULL AUTO_INCREMENT,
	   `hgi_user_id` INT NOT NULL,
	   `hgi_score` INT NOT NULL,
	   `hgi_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	   PRIMARY KEY (`hgi_id`, `hgi_user_id`),
	   UNIQUE INDEX `id_UNIQUE` (`hgi_id` ASC))
		 ENGINE = InnoDB $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( $sql );

		$table_codes = $wpdb->prefix . 'hgi_codes';
		$sql         = "CREATE TABLE $table_codes (
        `hgi_user_id` INT NOT NULL,
        `hgi_code` VARCHAR(20) NOT NULL,
        PRIMARY KEY (`hgi_user_id`))
		ENGINE = InnoDB $charset_collate;";

		dbDelta( $sql );

		$table_followers = $wpdb->prefix . 'hgi_followers';

		$sql = "CREATE TABLE $table_followers (
        `hgi_user_id` INT NOT NULL,
        `hgi_follower_user_id` INT NOT NULL,
		`hgi_status` VARCHAR(10) NOT NULL,
        PRIMARY KEY (`hgi_user_id`, `hgi_follower_user_id`))
		ENGINE = InnoDB $charset_collate;";

		dbDelta( $sql );

		$table_last_score = $wpdb->prefix . 'hgi_last_score';

		$sql = "CREATE TABLE $table_last_score (
        `hgi_user_id` INT NOT NULL,
        `hgi_last_score` INT NOT NULL,
        `hgi_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`hgi_user_id`),
        UNIQUE INDEX `hgi_user_id_UNIQUE` (`hgi_user_id` ASC))
		ENGINE = InnoDB $charset_collate;";

		dbDelta( $sql );

		update_option( 'hgi_db_version', $this->hgi_db_version );
	}

	function hgi_update_db_check() {
		if ( get_site_option( 'hgi_db_version' ) != $this->hgi_db_version ) {
			$this->hgi_install();
		}
	}

}
