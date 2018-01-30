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

	/**
	 * Registration action when form on /register is submited.
	 *
	 * @return wp_die or redirects user depending on validation.
	 */
	public function hgi_register_user_action() {
		if ( 'hgi_create_user' !== filter_input( INPUT_POST, 'action' ) ) {
			return;
		}
		if ( ! filter_input( INPUT_POST, 'hgi_nonce_field' ) || ! wp_verify_nonce( filter_input( INPUT_POST, 'hgi_nonce_field' ), 'hgi_registration' ) ) {
			wp_die(
				__( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		} else {
			$message       = '';
			$first_name    = esc_attr( filter_input( INPUT_POST, 'hgia_first_name' ) );
			$last_name     = esc_attr( filter_input( INPUT_POST, 'hgia_last_name' ) );
			$email         = esc_attr( filter_input( INPUT_POST, 'hgia_email' ) );
			$password      = esc_attr( filter_input( INPUT_POST, 'hgia_password' ) );
			$timezone      = esc_attr( filter_input( INPUT_POST, 'hgia_timezone' ) );
			$follower_code = esc_attr( filter_input( INPUT_POST, 'follower_code' ) );
			$email_invalid = true;
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
				$redirect = add_query_arg(
					array(
						'hgia_first_name' => rawurlencode( $first_name ),
						'hgia_last_name'  => rawurlencode( $last_name ),
						'hgia_email'      => rawurlencode( $email ),
						'hgia_timezone'   => rawurlencode( $timezone ),
						'error'           => rawurlencode( $message ),
						'c'               => rawurlencode( $follower_code ),
					), REGISTER_URL
				);
				wp_safe_redirect( $redirect );
				die();
			}

			$user_login  = strtolower( $first_name . '_' . $last_name );
			$user_exists = get_user_by( 'login', $user_login );
			if ( false !== $user_exists ) {
				$user_login = strtolower( $first_name . '_' . $last_name . random_int( 1, 20 ) );
			}

			$user_id = wp_insert_user(
				array(
					'first_name'    => $first_name,
					'last_name'     => $last_name,
					'user_email'    => $email,
					'user_nicename' => $first_name . ' ' . $last_name,
					'user_pass'     => $password,
					'user_login'    => $user_login,
				)
			);
			// On success.
			if ( ! is_wp_error( $user_id ) ) {
				update_user_meta( $user_id, 'hgi_user_timezone', $timezone );
				update_user_meta( $user_id, 'hgi_user_flag', 'nonactive' );
				wp_new_user_notification( $user_id, null, 'admin' );

				$code = sha1( $user_id . time() );
				global $wpdb;
				$wpdb->update(
					$wpdb->prefix . 'users', array( 'user_activation_key' => $code ), array( 'ID' => $user_id ), array( '%s' )
				);

				$activation_link = add_query_arg(
					array(
						'action' => 'hgi_validate_user',
						'key'    => $code,
						'user'   => $user_id,
						'c'      => $follower_code,
					), esc_url( admin_url( 'admin-post.php' ) )
				);
				$headers         = array( 'Content-Type: text/html; charset=UTF-8' );
				wp_mail( $email, 'Activate your account on How Goes It', 'This is your Activation link: ' . $activation_link, $headers );

				wp_die(
					__( 'Account have been created, check your email for activation link.', $this->plugin_name ), __( 'Success', $this->plugin_name ), array(
						'response' => 200,
					)
				);
			} else {
				$redirect = add_query_arg(
					array(
						'error' => rawurlencode( 'Something wrong happend, try again later please.' ),
						'c'     => $follower_code,
					), REGISTER_URL
				);
				wp_safe_redirect( $redirect );
				exit();
			}
		}
	}
	/**
	 * Validates user when they click on link from email.
	 *
	 * @return wp_die or redirect to login depending on validation of the key. If success, changes user meta field to 'active'.
	 */
	public function hgi_validate_user_action() {
		if ( 'hgi_validate_user' !== filter_input( INPUT_GET, 'action' ) ) {
			wp_die(
				__( 'Invalid action.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		}
		$user = absint( filter_input( INPUT_GET, 'user' ) );
		$key  = esc_attr( filter_input( INPUT_GET, 'key' ) );
		$code = esc_attr( filter_input( INPUT_GET, 'c' ) );
		if ( 0 === $user || empty( $key ) ) {
			wp_die(
				__( 'Invalid key or user.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		}

		global $wpdb;
		$user_to_validate = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE ID = %d AND user_activation_key = %s", $user, $key ) );
		if ( is_null( $user_to_validate ) ) {
			wp_die(
				__( 'Cannot be activated, check the key.', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		} else {
			update_user_meta( $user, 'hgi_user_flag', 'active' );
			if ( ! empty( $code ) ) {
				$login_url = add_query_arg(
					array(
						'c' => $code,
					),
					LOGIN_URL
				);
			} else {
				$login_url = wp_login_url();
			}
			wp_safe_redirect( $login_url );
			die();
		}
	}

	/**
	 * Disallow user to login if they do not have validated account. Checking user meta hgi_user_flag.
	 *
	 * @param object $user User object.
	 * @return $user or thorws WP Error.
	 */
	public function hgi_validate_user_on_login( $user ) {
		if ( in_array( 'administrator', $user->roles, true ) ) {
			return $user;
		}
		if ( get_user_meta( $user->ID, 'hgi_user_flag', true ) === 'active' ) {
			return $user;
		}
		$login_url   = home_url( LOGIN_URL );
		$redirect_to = filter_input( INPUT_POST, 'redirect_to' );
		$redirect    = add_query_arg(
			array(
				'login'       => 'failed',
				'redirect_to' => rawurlencode( $redirect_to ),
			), $login_url
		);
		wp_safe_redirect( $redirect );

		exit;
	}

}
