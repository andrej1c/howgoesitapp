<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Admin_Registration extends How_Goes_It_Admin {

	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}

	public function hgi_register_user_action() {
		if ( 'hgi_create_user' !== filter_input( INPUT_POST, 'action' ) ) {
			return;
		}
		if ( ! filter_input( INPUT_POST, 'hgi_nonce_field' ) || ! wp_verify_nonce( filter_input( INPUT_POST, 'hgi_nonce_field' ), 'hgi_registration' ) ) {
			wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
				'response'	 => 403,
				'back_link'	 => get_site_url( '/register' ),
			) );
		} else {
			$message		 = '';
			$first_name		 = filter_input( INPUT_POST, 'hgia_first_name' );
			$last_name		 = filter_input( INPUT_POST, 'hgia_last_name' );
			$email			 = filter_input( INPUT_POST, 'hgia_email' );
			$password		 = filter_input( INPUT_POST, 'hgia_password' );
			$timezone		 = filter_input( INPUT_POST, 'hgia_timezone' );
			$email_invalid	 = true;
			if ( is_email( $email ) ) {
				$email_invalid = false;
			}
			if ( empty( $first_name ) || empty( $last_name ) || empty( $email ) || empty( $password ) || empty( $timezone ) || $email_invalid ) {
				$message = __( 'Some of the data are not correct, check it again please.', $this->plugin_name );
			}
			$user_exists = get_user_by( 'email', $email );
			if ( false !== $user_exists ) {
				$message = __( 'This user already exists.', $this->plugin_name );
			}
			if ( ! empty( $message ) ) {
				$redirect = add_query_arg( array(
					'hgia_first_name'	 => urlencode( $first_name ),
					'hgia_last_name'	 => urlencode( $last_name ),
					'hgia_email'		 => urlencode( $email ),
					'hgia_timezone'		 => urlencode( $timezone ),
					'error'				 => urlencode( $message ),
				), '/register' );
				wp_safe_redirect( $redirect );
				die();
			}

			$user_login	 = strtolower( $first_name . '_' . $last_name );
			$user_exists = get_user_by( 'login', $user_login );
			if ( false !== $user_exists ) {
				$user_login = strtolower( $first_name . '_' . $last_name . random_int( 1, 20 ) );
			}

			$user_id = wp_insert_user( array(
				'first_name'	 => $first_name,
				'last_name'		 => $last_name,
				'user_email'	 => $email,
				'user_nicename'	 => $first_name . ' ' . $last_name,
				'user_pass'		 => $password,
				'user_login'	 => $user_login
			) );
			//On success
			if ( ! is_wp_error( $user_id ) ) {
				update_user_meta( $user_id, 'hgi_user_timezone', $timezone );
				wp_new_user_notification( $user_id, null, 'both' );

				$user = get_user_by( 'login', $user_login );

				if ( ! is_wp_error( $user ) ) {
					wp_clear_auth_cookie();
					wp_set_current_user( $user->ID );
					wp_set_auth_cookie( $user->ID );

					$redirect_to = get_site_url();
					wp_safe_redirect( $redirect_to );
					exit();
				} else {
					$redirect = add_query_arg( array(
						'error' => urlencode( 'Something wrong happend, try again later please.' ),
					), '/register' );
					wp_safe_redirect( $redirect );
					exit();
				}
			} else {
				$redirect = add_query_arg( array(
					'error' => urlencode( 'Something wrong happend, try again later please.' ),
				), '/register' );
				wp_safe_redirect( $redirect );
				exit();
			}
		}
	}

}
