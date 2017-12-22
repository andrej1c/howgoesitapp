<?php
/**
 * Model for Score.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 */

/**
 * Define model for score.
 *
 * Define actions for save score.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Model_Score {
	/**
	 * Name of the table.
	 *
	 * @var string
	 */
	private $table_name = 'scores';
	/**
	 * User Id. Not used, represents table column.
	 *
	 * @var int
	 */
	private $user_id;
	/**
	 * Score. Not used, represents table column.
	 *
	 * @var int
	 */
	private $score;
	/**
	 * Current timestamp. Not used, represents table column.
	 *
	 * @var timestamp
	 */
	private $timestamp;

	/**
	 * Add new score into the scores table.
	 *
	 * @param int $user_id User Id.
	 * @param int $score New score sent from form.
	 */
	public function add_new_score( $user_id, $score ) {
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'hgi_user_id' => $user_id,
				'hgi_score'   => $score,
			),
			array(
				'%d',
				'%d',
			)
		);
		return $result;
	}

	// TODO: add get_list_of_scores function.
}
