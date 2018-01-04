<?php
/**
 * Model for Last Score.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 */

/**
 * Define model for last score.
 *
 * Define actions for save, and read for last score.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Model_Last_Score {

	/**
	 * Name of the table.
	 *
	 * @var string
	 */
	private $table_name = 'hgi_last_score';

	/**
	 * User Id. Not used, represents table column.
	 *
	 * @var int
	 */
	private $user_id;

	/**
	 * Last score timestamp. Not used, represents table column.
	 *
	 * @var timestamp
	 */
	private $last_score_timestamp;

	/**
	 * Last set score. Not used, represents table column.
	 *
	 * @var int
	 */
	private $last_score;

	/**
	 * Set new last score, insert or replace existing result.
	 *
	 * @param int $user_id   User id.
	 * @param int $last_score New Score passed from form.
	 * @return mixed $result false or int
	 */
	public function set_new_last_score( $user_id, $last_score ) {
		global $wpdb;
		$result = $wpdb->replace(
			$wpdb->prefix . $this->table_name,
			array(
				'hgi_user_id'    => $user_id,
				'hgi_last_score' => $last_score,
			),
			array(
				'%d',
				'%d',
			)
		);
		return $result;
	}

	/**
	 * Retrieve last score set by usre.
	 *
	 * @param  int $user_id User Id.
	 * @return object Object of user_id, last_score and timestamp from the db row.
	 */
	public function get_last_score( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;
		$last_score = $wpdb->get_row( $wpdb->prepare( "SELECT hgi_user_id, hgi_last_score, UNIX_TIMESTAMP( $table_name.hgi_timestamp ) as hgi_timestamp FROM $table_name WHERE hgi_user_id = %d", $user_id ) );
		return $last_score;
	}

}
