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
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/public
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Public_User_Actions extends How_Goes_It_Public {

	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}

	function cs_redirect_from_wp_login( $redirect_to, $request, $user ) {
		if ( strpos( filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL ), 'wp-login.php' ) !== false && 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {

			$login_url = home_url( LOGIN_URL );

			wp_safe_redirect( $login_url );
			exit;
		}
	}

	function redirect_after_login( $redirect_to, $request, $user ) {
		$redirect = filter_input( INPUT_POST, 'redirect_to' );
		if ( $redirect && strpos( $redirect, home_url( LOGIN_URL ) ) === false ) {
			wp_safe_redirect( esc_url( $redirect ) );
			die();
		}
		// Do we have a user to check against?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			if ( in_array( 'administrator', $user->roles, true ) ) {
				// We'll need to redirect this to the admin page once the plugin is finished.
				wp_safe_redirect( home_url( '/wp-admin/' ) );
			} else {
				wp_safe_redirect( home_url( SCORE_URL ) );
			}
			die();
		}
	}

	function cs_logout_redirect() {
		$login_url = home_url( LOGIN_URL );

		wp_safe_redirect( $login_url . '?login=false' );

		exit;
	}

	function cs_disable_admin_bar() {
		// add_filter( 'show_admin_bar', '__return_false' );
	}

	function cs_maybe_redirect_at_authentication( $user, $username, $password ) {
		if ( 'POST' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
			if ( is_wp_error( $user ) ) {
				$redirect_to = filter_input( INPUT_POST, 'redirect_to' );
				$redirect    = add_query_arg(
					array(
						'login'       => 'failed',
						'redirect_to' => rawurlencode( $redirect_to ),
					), LOGIN_URL
				);
				wp_safe_redirect( $redirect );
				die();
			}
		}
		return $user;
	}

	public function hgi_update_user() {
		if ( 'hgi_update_user' !== filter_input( INPUT_POST, 'action' ) ) {
			return;
		}
		if ( ! filter_input( INPUT_POST, 'hgi_my-account-nonce_field' ) || ! wp_verify_nonce( filter_input( INPUT_POST, 'hgi_my-account-nonce_field' ), 'hgi_my-account' ) ) {
			wp_die(
				__( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		} else {
			$message         = '';
			$password        = esc_attr( filter_input( INPUT_POST, 'hgi_password' ) );
			$password_retype = esc_attr( filter_input( INPUT_POST, 'hgi_retype_password' ) );
			$timezone        = esc_attr( filter_input( INPUT_POST, 'hgia_timezone' ) );

			if ( empty( $timezone ) ) {
				$message = __( 'Time zone can\'t be empty.', $this->plugin_name );
			}
			if ( ! empty( $password ) && $password !== $password_retype ) {
				$message = __( 'Passwords doesn\'t match.', $this->plugin_name );
			}

			if ( ! empty( $message ) ) {
				$redirect = add_query_arg(
					array(
						'msg' => rawurlencode( $message ),
					), LOGIN_URL
				);
				wp_safe_redirect( $redirect );
				die();
			}
			$message = 'Successfully updated data.';
			update_user_meta( get_current_user_id(), 'hgi_user_timezone', $timezone );
			if ( ! empty( $password ) ) {
				wp_set_password( $password, get_current_user_id() );
			}

			$redirect = add_query_arg(
				array(
					'msg' => rawurlencode( $message ),
				), LOGIN_URL
			);
			wp_safe_redirect( $redirect );
			die();
		}
	}

	function hgi_template_redirect() {
		if ( ! is_user_logged_in() ) {
			$pagename = get_query_var( 'pagename' );
			if ( ! $pagename && get_queried_object_id() > 0 ) {
				global $wp_query;
				// If a static page is set as the front page, $pagename will not be set. Retrieve it from the queried object
				$post     = $wp_query->get_queried_object();
				$pagename = $post->post_name;
			}
			switch ( $pagename ) {
				case FOLLOWERS_URL:
					$redirect = add_query_arg(
						array(
							'redirect_to' => rawurlencode( site_url( FOLLOWERS_URL ) ),
						), LOGIN_URL
					);
					wp_safe_redirect( $redirect );
					die();
					break;
				case FOLLOWING_URL:
					$redirect = add_query_arg(
						array(
							'redirect_to' => rawurlencode( site_url( FOLLOWING_URL ) ),
						), LOGIN_URL
					);
					wp_safe_redirect( $redirect );
					die();
					break;
				case SCORE_URL;
					$redirect = add_query_arg(
						array(
							'redirect_to' => rawurlencode( site_url( SCORE_URL ) ),
						), LOGIN_URL
					);
					wp_safe_redirect( $redirect );
					die();
					break;
				default:
					break;
			}
		}

	}


}
