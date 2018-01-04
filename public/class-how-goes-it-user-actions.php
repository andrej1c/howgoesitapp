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

	function cs_redirect_from_wp_login() {
		if ( strpos( filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL ), 'wp-login.php' ) !== false && 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {

			$login_url = home_url( LOGIN_URL );

			wp_safe_redirect( $login_url );
			exit;
		}
	}

	function redirect_after_login( $redirect_to, $request, $user ) {
		// Do we have a user to check against?
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			if ( in_array( 'administrator', $user->roles, true ) ) {
				// We'll need to redirect this to the admin page once the plugin is finished.
				wp_safe_redirect( home_url( '/wp-admin/' ) );
			} else {
				wp_safe_redirect( home_url( SCORE_URL ) );
			}
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
		$login_url = home_url( '/login/' );

		if ( 'POST' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
			if ( is_wp_error( $user ) ) {
				wp_safe_redirect( $login_url . '?login=failed' );
			}
		}
		return $user;
	}

	function cs_do_register_user() {
		if ( 'POST' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
			$registration_url = home_url( '/register/' );

			// Check to see if we allow new users to be registered.
			if ( ! get_option( 'users_can_register' ) ) {
				// Display error if registrations are closed.
				$redirect_url = add_query_arg( 'error', 'closed', $registration_url );
			} else {
				$user_login = filter_input( INPUT_POST, 'hgia_user' );
				$user_email = filter_input( INPUT_POST, 'hgia_email' );

				$result = register_new_user( $user_login, $user_email );
				if ( is_wp_error( $result ) ) {
					$errors       = join( ',', $result->get_error_codes() );
					$redirect_url = add_query_arg( 'error', $errors, $registration_url );
				} else {
					// Successful registration, redirect to log in page.
					wp_safe_redirect( home_url( LOGIN_URL ) );
				}
			}

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	function cs_redirect_from_wp_register() {
		if ( 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
			if ( is_user_logged_in() ) {
				wp_safe_redirect( home_url( SCORE_URL ) );
			} else {
				wp_safe_redirect( home_url( SCORE_URL ) );
			}
			exit;
		}
	}

}
