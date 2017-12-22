<?php
/**
 * Model for codes.
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
class How_Goes_It_Model_Codes {
	/**
	 * Name of the table.
	 *
	 * @var string
	 */
	private $table_name = 'codes';
	/**
	 * User Id. Not used, represents table column.
	 *
	 * @var int
	 */
	private $user_id;
	/**
	 * Code for the user. Not used, represents table column.
	 *
	 * @var int
	 */
	private $code;

	/**
	 * Store follower to the user if not exists already.
	 *
	 * @param  int $user_id  User id.
	 * @param  int $follower_id Follower id.
	 * @return mixed return code or false.
	 */
	public function hgi_store_code( $user_id ) {
		global $wpdb;

		$has_code = $this->hgi_get_code( $user_id );
		if ( false !== $has_code ) {
			return $has_code;
		}
		$new_code = $this->get_token( 20 );
		$result   = $wpdb->insert(
			$wpdb->prefix . $this->table_name,
			array(
				'hgi_user_id' => $user_id,
				'hgi_code'    => $new_code,
			),
			array(
				'%d',
				'%s',
			)
		);
		if ( false !== $result ) {
			return $new_code;
		}
		return false;
	}

	/**
	 * Get code for the user.
	 *
	 * @param  int $user_id User id.
	 * @return mixed false or the code.
	 */
	public function hgi_get_code( $user_id ) {
		global $wpdb;
		$users_a    = [];
		$table_name = $wpdb->prefix . $this->table_name;
		$code       = $wpdb->get_row( $wpdb->prepare( "SELECT hgi_code FROM $table_name WHERE hgi_user_id = %d", $user_id ) );
		if ( is_null( $code ) ) {
			return false;
		} else {
			return $code->hgi_code;
		}
	}

	public function crypto_rand_secure( $min, $max ) {
		$range = $max - $min;
		if ( $range < 1 ) {
			return $min; // not so random...
		}
		$log    = ceil( log( $range, 2 ) );
		$bytes  = (int) ( $log / 8 ) + 1; // length in bytes.
		$bits   = (int) $log + 1; // length in bits.
		$filter = (int) ( 1 << $bits ) - 1; // set all lower bits to 1.
		do {
			$rnd = hexdec( bin2hex( openssl_random_pseudo_bytes( $bytes ) ) );
			$rnd = $rnd & $filter; // discard irrelevant bits.
		} while ( $rnd > $range );
		return $min + $rnd;
	}

	public function get_token( $length ) {
		$token          = '';
		$code_alphabet  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code_alphabet .= 'abcdefghijklmnopqrstuvwxyz';
		$code_alphabet .= '0123456789';
		$max            = strlen( $code_alphabet ); // edited.

		for ( $i = 0; $i < $length; $i++ ) {
			$token .= $code_alphabet[ $this->crypto_rand_secure( 0, $max - 1 ) ];
		}

		return $token;
	}




}
