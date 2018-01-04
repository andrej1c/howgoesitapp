<?php
/**
 * Follower actions.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 */

// require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-last-score.php';
/**
 * Follower actions.
 *
 * Trigger action when clicked on follow code.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Admin_Follower_Actions extends How_Goes_It_Admin {

	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}

	public function hgi_add_follower() {

		$code = filter_input( INPUT_GET, 'c' );
		if ( ( is_home() || is_front_page() ) && ! empty( $code ) ) {
			if ( is_user_logged_in() ) {
				// process code
				require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-codes.php';
				$code_o          = new How_Goes_It_Model_Codes();
				$request_user_id = $code_o->hgi_get_user_by_code( $code );
				if ( false === $request_user_id ) {
					wp_die(
						__( 'The code is incorrect, sorry, request new one please.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
							'response' => 403,
						)
					);
				}
				if ( (int) $request_user_id === get_current_user_id() ) {
					wp_die(
						__( 'You can\'t follow yourself. Sorry.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
							'response' => 403,
						)
					);

				}
				// If all good, notify the user who sent the link to approve and redirect to Home?.
				require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
				$followers_o = new How_Goes_It_Model_Followers();
				$followers_o->hgi_store_follower( $request_user_id, get_current_user_id(), 'nonactive' );

				$request_user = get_userdata( $request_user_id );
				$email        = $request_user->user_email;

				$c_user_id    = get_current_user_id();
				$f_first_name = get_user_meta( $c_user_id, 'first_name', true );
				$f_last_name  = get_user_meta( $c_user_id, 'last_name', true );
				$f_user       = get_userdata( $c_user_id );
				$f_email      = $f_user->user_email;
				$approve_link = add_query_arg(
					array(
						'action'         => 'hgi_add_follower',
						'code'           => $code,
						'following_user' => $c_user_id,
					), esc_url( admin_url( 'admin-post.php' ) )
				);
				$headers      = array( 'Content-Type: text/html; charset=UTF-8' );
				$message      = sprintf( '%s %s (%s) used the authorization code %s you sent them to create their account and is requesting to stay in tune with your score.<br />To approve click the link bellow. [%s]', $f_first_name, $f_last_name, $f_email, $code, $approve_link );
				wp_mail( $email, 'Approve authorization code', $message, $headers );
				wp_safe_redirect( home_url( FOLLOWING_URL ) );
				die();

			} else {
				// redirect user to login page with code parameter
				$redirect = add_query_arg(
					array(
						'c' => rawurlencode( $code ),
					), LOGIN_URL
				);
				wp_safe_redirect( $redirect );
				die();
			}
		} else {
			return;
		}

	}

	public function hgi_approve_follower() {
		$action   = filter_input( INPUT_GET, 'action' );
		$code     = filter_input( INPUT_GET, 'code' );
		$follower = filter_input( INPUT_GET, 'following_user' );
		if ( 'hgi_add_follower' !== $action ) {
			wp_die(
				__( 'Invalid action.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		}
		if ( ! is_user_logged_in() ) {
			$redirect = add_query_arg(
				array(
					'redirect_to' => rawurlencode( get_site_url() . $_SERVER['REQUEST_URI'] ),
				), LOGIN_URL
			);
			wp_safe_redirect( $redirect );
			die();
		} else {
			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-codes.php';
			$followers_o   = new How_Goes_It_Model_Codes();
			$user_id_check = $followers_o->hgi_get_user_by_code( $code );
			if ( false === $user_id_check || (int) $user_id_check !== get_current_user_id() ) {
				wp_die(
					__( 'Invalid action.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
						'response' => 403,
					)
				);
			}

			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
			$followers_o = new How_Goes_It_Model_Followers();
			$result      = $followers_o->hgi_update_follower( get_current_user_id(), $follower, 'active' );
			wp_safe_redirect( home_url( FOLLOWERS_URL ) );
			die();
		}

	}

	public function hgi_login_url_redirect( $redirect_to, $request, $user ) {
		if ( stripos( $redirect_to, 'hgi_add_follower' ) ) {
			wp_safe_redirect( $redirect_to );
			die();
		}
		if ( stripos( $redirect_to, '?c=' ) ) {
			wp_safe_redirect( $redirect_to );
			die();
		}
	}

	public function hgi_disconnect_follower_from_user() {
		$action   = esc_attr( filter_input( INPUT_POST, 'action' ) );
		$follower = absint( filter_input( INPUT_POST, 'follower_id' ) );
		$nonce    = filter_input( INPUT_POST, 'hgi_disconnect_nonce_field' );
		if ( 'hgi_disconnect_follower' !== $action ) {
			return;
		}
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'hgi_disconnect' ) ) {
			wp_die(
				__( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		}
		if ( ! is_user_logged_in() ) {
			$redirect = add_query_arg(
				array(
					'redirect_to' => rawurlencode( home_url( FOLLOWERS_URL ) ),
				), LOGIN_URL
			);
			wp_safe_redirect( $redirect );
			die();
		} else {
			$current_user = get_current_user_id();
			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
			$followers_o = new How_Goes_It_Model_Followers();
			$result      = $followers_o->hgi_remove_follower( $current_user, $follower );

			wp_safe_redirect( home_url( FOLLOWERS_URL ) );
			die();
		}

	}

	public function hgi_disconnect_user_from_follower() {
		$action         = esc_attr( filter_input( INPUT_POST, 'action' ) );
		$user_to_delete = absint( filter_input( INPUT_POST, 'user_id' ) );
		$nonce          = filter_input( INPUT_POST, 'hgi_disconnect_nonce_field' );
		if ( 'hgi_disconnect_user' !== $action ) {
			return;
		}
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'hgi_disconnect' ) ) {
			wp_die(
				__( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		}
		if ( ! is_user_logged_in() ) {
			$redirect = add_query_arg(
				array(
					'redirect_to' => rawurlencode( home_url( FOLLOWING_URL ) ),
				), LOGIN_URL
			);
			wp_safe_redirect( $redirect );
			die();
		} else {
			$current_follower = get_current_user_id();
			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
			$followers_o = new How_Goes_It_Model_Followers();
			$result      = $followers_o->hgi_remove_follower( $user_to_delete, $current_follower );

			wp_safe_redirect( home_url( FOLLOWING_URL ) );
			die();
		}

	}


}
