<?php
/**
 * Model for Followers.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 */

/**
 * Define model for followers.
 *
 * Actions for saving followers for the user and reading the data.
 * Can be queried also by follower id.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/models
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Model_Followers {
	/**
	 * Name of the table.
	 *
	 * @var string
	 */
	private $table_name = 'followers';
	/**
	 * User Id. Not used, represents table column.
	 *
	 * @var int
	 */
	private $user_id;
	/**
	 * Follower Id. Not used, represents table column.
	 *
	 * @var int
	 */
	private $follower_id;

	/**
	 * Status of the follower. Active or nonactive. Not user, represents table column.
	 *
	 * @var string
	 */
	private $status;

	/**
	 * Store follower to the user.
	 *
	 * @param  int $user_id  User id.
	 * @param  int $follower_id Follower id.
	 * @return mixed return success or failure.
	 */
	public function hgi_store_follower( $user_id, $follower_id, $status ) {
		global $wpdb;
		$result = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'hgi_user_id'          => $user_id,
				'hgi_follower_user_id' => $follower_id,
				'hgi_status'           => $status,
			),
			array(
				'%d',
				'%d',
				'%s',
			)
		);
		return $result;
	}

	/**
	 * Get followers of the user.
	 *
	 * @param int $user_id User Id
	 * @return array Array of follower id and their names for the user.
	 */
	public function hgi_get_followers_of_user( $user_id ) {
		global $wpdb;
		$followers_a = [];
		$table_name  = $wpdb->prefix . $this->table_name;
		$followers   = $wpdb->get_results( $wpdb->prepare( "SELECT hgi_user_id, hgi_follower_user_id, hgi_status FROM $table_name WHERE hgi_user_id = %d", $user_id ) );
		if ( 0 < count( $followers ) ) {
			foreach ( $followers as $row ) {
				$first_name    = get_user_meta( $row->hgi_follower_user_id, 'first_name', true );
				$last_name     = get_user_meta( $row->hgi_follower_user_id, 'last_name', true );
				$followers_a[] = [
					'follower_id'     => $row->hgi_follower_user_id,
					'follower_name'   => $first_name . ' ' . $last_name,
					'follower_status' => $row->hgi_status,
				];
			}
		}

		return $followers_a;
	}

	/**
	 * Get all users for the follower.
	 *
	 * @param  int $follower_id Follower id.
	 * @return array Array of users where the follower is listed.
	 */
	public function hgi_get_users_by_follower( $follower_id ) {
		global $wpdb;
		$users_a    = [];
		$table_name = $wpdb->prefix . $this->table_name;
		$users      = $wpdb->get_results( $wpdb->prepare( "SELECT hgi_user_id, hgi_follower_user_id, hgi_status FROM $table_name WHERE hgi_follower_user_id = %d AND hgi_status = 'active'", $follower_id ) );
		if ( 0 < count( $users ) ) {
			foreach ( $users as $row ) {
				$first_name = get_user_meta( $row->hgi_user_id, 'first_name', true );
				$last_name  = get_user_meta( $row->hgi_user_id, 'last_name', true );
				$users_a[]  = [
					'user_id'   => $row->hgi_user_id,
					'user_name' => $first_name . ' ' . $last_name,
				];
			}
		}

		return $users_a;
	}

	public function hgi_check_waiting_for_approval( $follower_id ) {
		global $wpdb;
		$users_a    = [];
		$table_name = $wpdb->prefix . $this->table_name;
		$users      = $wpdb->get_results( $wpdb->prepare( "SELECT hgi_user_id FROM $table_name WHERE hgi_follower_user_id = %d AND hgi_status = 'nonactive'", $follower_id ) );
		if ( 0 < count( $users ) ) {
			foreach ( $users as $row ) {
				// TODO: get codes from the users where there is nonactive link and show it for the follower awaiting approval.
			}
		}
	}

	public function hgi_update_follower( $requested_user_id, $follower_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . $this->table_name;
		$result     = $wpdb->update(
			$table_name,
			array(
				'hgi_status' => $status,  // string
			),
			array(
				'hgi_user_id'          => $requested_user_id,
				'hgi_follower_user_id' => $follower_id,
			),
			array(
				'%s',   // value1
			),
			array(
				'%d',
				'%d',
			)
		);
		return $result;
	}

	// TODO: implement function for removing follower from the user.
	public function hgi_remove_follower( $user_id, $follower_id ) {

	}


}
