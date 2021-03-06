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
class How_Goes_It_Public_Shortcodes extends How_Goes_It_Public {

	public function __construct( $plugin_name, $version ) {

		parent::__construct( $plugin_name, $version );
	}

	public function init_shortcodes() {
		add_shortcode( 'hgi_login_form', [ $this, 'cs_login_shortcode' ] );
		add_shortcode( 'hgi_score_form', [ $this, 'cs_score_entry' ] );
		add_shortcode( 'hgi_my_last_score', [ $this, 'howgoesit_my_last_score_func' ] );
		add_shortcode( 'hgi_register_form', [ $this, 'cs_register_shortcode' ] );
		add_shortcode( 'hgi_followers_or_code', [ $this, 'hgi_display_followers_or_invite_code' ] );
		add_shortcode( 'hgi_users_for_follower', [ $this, 'hgi_users_for_follower_func' ] );
	}

	public function howgoesit_my_last_score_func( $atts ) {
		require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-last-score.php';

		$current_user = get_current_user_id();

		$last_score = new How_Goes_It_Model_Last_Score();
		$entry      = $last_score->get_last_score( $current_user );

		if ( empty( $entry ) ) {
			return 'No saved scores yet.';
		}

		$created_at = $entry->hgi_timestamp;
		$status     = $entry->hgi_last_score;
		$html       = sprintf( '<p>Your last score: %s, set %s ago.</p>', $status, human_time_diff( $created_at, time() ) );

		return $html;
	}

	function cs_login_shortcode() {

		if ( is_user_logged_in() ) {
			$output = sprintf(
				'<p>You can logout by clicking on this link: <a href="%s">Log out</a></p>', esc_url( wp_logout_url( get_permalink() ) )
			);
			$tzlist = DateTimeZone::listIdentifiers( DateTimeZone::ALL );
			$tz_a   = [];
			foreach ( $tzlist as $tz ) {
				$tz_name     = str_replace( '/', ' - ', $tz );
				$tz_name     = str_replace( '_', ' ', $tz_name );
				$tz_a[ $tz ] = $tz_name;
			}
			$timezone = get_user_meta( get_current_user_id(), 'hgi_user_timezone', true );

			ob_start();
			include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-my-account-form.php';
			$output .= ob_get_clean();

			return $output;
		}
		$login = ( filter_input( INPUT_GET, 'login' ) ) ? filter_input( INPUT_GET, 'login' ) : 0;
		ob_start();
		if ( 'failed' === $login ) {
			// We need to give a vague reason for the Login error.
			echo '<p class="login-msg"><strong>ERROR:</strong> Invalid Username and/or Password or your account is not validated.</p>';
		}

		do_action( 'wordpress_social_login' );
		$redirect = ( filter_input( INPUT_GET, 'redirect_to' ) ) ? filter_input( INPUT_GET, 'redirect_to' ) : '';
		$code     = ( filter_input( INPUT_GET, 'c' ) ) ? filter_input( INPUT_GET, 'c' ) : '';
		$args     = [];
		if ( ! empty( $redirect ) ) {
			$args['redirect'] = esc_url( $redirect );
		} elseif ( ! empty( $code ) ) {
			$args['redirect'] = esc_url( get_site_url() . '?c=' . $code );
		}
		wp_login_form( $args );

		if ( get_option( 'users_can_register' ) ) {
			$register_url = home_url( REGISTER_URL );
			if ( ! empty( $code ) ) {
				$register_url .= '?c=' . $code;
			}
			echo '<a href="' . esc_url( $register_url ) . '">Create new Account</a>';
		}
		$content = ob_get_clean();
		return $content;
	}

	function cs_score_entry() {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		ob_start();
		include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-score-form.php';
		$output = ob_get_clean();

		return $output;
	}

	function cs_register_shortcode() {
		if ( is_user_logged_in() ) {
			return sprintf(
				'<p class="login-msg">Already logged in! Maybe try <a href="%s">Logging out</a> and logging in again?</p>', esc_url( wp_logout_url( get_permalink() ) )
			);
		}
		if ( ! get_option( 'users_can_register' ) ) {
			return __( 'Registration is currently closed.', $this->plugin_name );
		} else {
			$content = '';
			if ( filter_input( INPUT_GET, 'error' ) ) {
				$error = filter_input( INPUT_GET, 'error' );

				$content .= '<p>';
				$content .= '<strong>Error:</strong> ' . esc_html( $error );
				$content .= '</p>';
			}

			do_action( 'wordpress_social_login' );

			$content .= $this->cs_render_form_html(); // xss OK.
			return $content;
		}
	}

	function cs_render_form_html() {
		$tzlist = DateTimeZone::listIdentifiers( DateTimeZone::ALL );
		$tz_a   = [];
		foreach ( $tzlist as $tz ) {
			$tz_name     = str_replace( '/', ' - ', $tz );
			$tz_name     = str_replace( '_', ' ', $tz_name );
			$tz_a[ $tz ] = $tz_name;
		}

		ob_start();
		include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-registration-form.php';
		$output = ob_get_clean();

		return $output;
	}

	function hgi_display_followers_or_invite_code( $atts ) {
		if ( ! is_user_logged_in() ) {
			return 'Please login first.';
		}
		$show_code = filter_input( INPUT_GET, 'action' );
		if ( 'show_code' !== $show_code ) {
			// Show list of followers.
			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
			$follower_c      = new How_Goes_It_Model_Followers();
			$current_user_id = get_current_user_id();
			$followers       = $follower_c->hgi_get_followers_of_user( $current_user_id );
			$follow_url      = add_query_arg(
				array(
					'action' => 'show_code',
				), get_permalink()
			);
			ob_start();
			include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-followers.php';
			$output = ob_get_clean();
		} else {
			// Show user code.
			require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-codes.php';
			$codes           = new How_Goes_It_Model_Codes();
			$current_user_id = get_current_user_id();
			$code            = $codes->hgi_store_code( $current_user_id );
			if ( false === $code ) {
				return 'There was an error while getting your code, please refresh page to try again.';
			}
			$code_url = add_query_arg(
				array(
					'c' => $code,
				), get_site_url()
			);
			ob_start();
			include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-show-code.php';
			$output = ob_get_clean();
		}
		return $output;
	}

	function hgi_users_for_follower_func( $atts ) {
		require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
		$follower_c             = new How_Goes_It_Model_Followers();
		$current_user_id        = get_current_user_id();
		$nonactive_user_codes_a = $follower_c->hgi_check_waiting_for_approval( $current_user_id );
		$users                  = $follower_c->hgi_get_users_by_follower( $current_user_id );

		ob_start();
		include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-following-users.php';
		$output = ob_get_clean();
		return $output;
	}

}
