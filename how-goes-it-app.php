<?php
/*
Plugin Name: How Goes It? App
*/

function cs_redirect_from_wp_login() {
	if ( strpos( filter_input( INPUT_SERVER, 'REQUEST_URI', FILTER_SANITIZE_URL ), 'wp-login.php' ) !== false && 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {

		$login_url = home_url( '/login/' );

		wp_safe_redirect( $login_url );
		exit;
	}
}
add_action( 'login_redirect', 'cs_redirect_from_wp_login' );

function redirect_after_login( $redirect_to, $request, $user ) {
	// Do we have a user to check against?
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if ( in_array( 'administrator', $user->roles, true ) ) {
			// We'll need to redirect this to the admin page once the plugin is finished.
			wp_safe_redirect( home_url( '/wp-admin/' ) );
		} else {
			wp_safe_redirect( home_url( '/score/' ) );
		}
	}
}
add_filter( 'login_redirect', 'redirect_after_login', 10, 3 );

function cs_logout_redirect() {
	$login_url = home_url( '/login/' );

	wp_safe_redirect( $login_url . '?login=false' );

	exit;
}
add_action( 'wp_logout', 'cs_logout_redirect' );

function cs_login_shortcode() {

	$login = ( filter_input( INPUT_GET, 'login' ) ) ? filter_input( INPUT_GET, 'login' ) : 0;

	if ( 'failed' === $login ) {
		// We need to give a vague reason for the Login error.
		echo '<p class="login-msg"><strong>ERROR:</strong> Invalid Username and/or Password.</p>';
	} elseif ( is_user_logged_in() ) {
		$login_url = esc_url( home_url( '/login/?login=false' ) );
		printf(
			'<p class="login-msg">Already logged in! Maybe try <a href="%s">Logging out</a> and logging in again?</p>',
			esc_url( wp_logout_url( get_permalink() ) )
		);
		return;
	}

	do_action( 'wordpress_social_login' );

	wp_login_form();

	if ( get_option( 'users_can_register' ) ) {
		echo '<a href="' . esc_url( home_url( '/register/' ) ) . '">Create new Account</a>';
	}

}
add_shortcode( 'leo_score_login', 'cs_login_shortcode' );

function cs_score_entry() {
	return 'form will be here';
}
add_shortcode( 'leo_score_entry', 'cs_score_entry' );

function cs_disable_admin_bar() {
	// add_filter( 'show_admin_bar', '__return_false' );
}
add_action( 'init', 'cs_disable_admin_bar' );

function cs_maybe_redirect_at_authentication( $user, $username, $password ) {
	$login_url = home_url( '/login/' );

	if ( 'POST' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
		if ( is_wp_error( $user ) ) {
			wp_safe_redirect( $login_url . '?login=failed' );
		}
	}
	return $user;
}
add_filter( 'authenticate', 'cs_maybe_redirect_at_authentication', 101, 3 );

function do_register_user() {
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
				wp_safe_redirect( home_url( '/login/' ) );
			}
		}

		wp_safe_redirect( $redirect_url );
		exit;
	}
}
add_action( 'login_form_register', 'cs_do_register_user' );

function cs_redirect_from_wp_register() {
	if ( 'GET' === filter_input( INPUT_SERVER, 'REQUEST_METHOD', FILTER_SANITIZE_STRING ) ) {
		if ( is_user_logged_in() ) {
			wp_safe_redirect( home_url( '/score/' ) );
		} else {
			wp_safe_redirect( home_url( '/score/' ) );
		}
		exit;
	}
}
add_action( 'login_form_register', 'cs_redirect_from_wp_register' );

function cs_register_shortcode() {
	if ( ! get_option( 'users_can_register' ) ) {
		return __( 'Registration is currently closed.', 'wpskilltestplugin' );
	} else {

		if ( filter_input( INPUT_GET, 'error' ) ) {
			$errors = explode( ',', filter_input( INPUT_GET, 'error' ) );

			foreach ( $errors as $error ) {
				echo '<p>';
					echo '<strong>Error:</strong> ' . esc_html( $this->error_message( $error ) );
				echo '</p>';
			}
		}

		do_action( 'wordpress_social_login' );

		echo cs_render_form_html(); // xss OK.
	}
}
add_shortcode( 'leoscore_register', 'cs_register_shortcode' );

function cs_render_form_html() {
	$output = '';

	$output .= '<form name="registrationform" id="loginform" action="' . wp_registration_url() . '" method="post">';
		$output .= '<p>';
			$output .= '<label for="hgia_user">Username</label>';
			$output .= '<input type="text" name="hgia_user" id="hgia_user" class="input" size="20" autocomplete="off" />';
		$output .= '</p>';
		$output .= '<p>';
			$output .= '<label for="hgia_email">Email</label>';
			$output .= '<input type="email" name="hgia_email" id="hgia_email" class="input" size="20" autocomplete="off" />';
		$output .= '</p>';

		$output .= '<input type="submit" name="submit" value="Register"/>';
	$output .= '</form>';

	return $output;
}
