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
		add_shortcode( 'leo_score_login', [ $this, 'cs_login_shortcode' ] );
		add_shortcode( 'leo_score_entry', [ $this, 'cs_score_entry' ] );
		add_shortcode( 'howgoesit_my_last_score', [ $this, 'howgoesit_my_last_score_func' ] );
		add_shortcode( 'leoscore_register', [ $this, 'cs_register_shortcode' ] );
	}

	public function howgoesit_my_last_score_func( $atts ) {
		$a = shortcode_atts(
			array(
				'formid'        => -1,
				'user_id_field' => 1,
				'score_field'   => 2,
			), $atts
		);

		$form_id       = $a['formid'];
		$user_id_field = $a['user_id_field'];
		$score_field   = $a['score_field'];

		if ( -1 === $form_id ) {
			return 'No form found.';
		}
		if ( ! is_user_logged_in() ) {
			return 'Please login first.';
		}
		$current_user                       = wp_get_current_user();
		$search_criteria['field_filters'][] = array(
			'key'   => $user_id_field,
			'value' => $current_user->user_login,
		);
		$sorting                            = array();
		$paging                             = array(
			'offset'    => 0,
			'page_size' => 1,
		);

		$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging );
		if ( 0 < count( $entries ) ) {
			$entry = $entries[0];
		} else {
			return 'No saved scores yet.';
		}
		$created_at = $entry['date_created'];
		$status     = $entry[ $score_field ];

		$html = sprintf( '<p>Your last score: %s, set %s ago.</p>', $status, human_time_diff( strtotime( $created_at ), time() ) );

		return $html;
	}

	function cs_login_shortcode() {

		$login = ( filter_input( INPUT_GET, 'login' ) ) ? filter_input( INPUT_GET, 'login' ) : 0;

		if ( 'failed' === $login ) {
			// We need to give a vague reason for the Login error.
			echo '<p class="login-msg"><strong>ERROR:</strong> Invalid Username and/or Password or your account is not validated.</p>';
		} elseif ( is_user_logged_in() ) {
			$login_url = esc_url( home_url( '/login/?login=false' ) );
			printf(
				'<p class="login-msg">Already logged in! Maybe try <a href="%s">Logging out</a> and logging in again?</p>', esc_url( wp_logout_url( get_permalink() ) )
			);
			return;
		}

		do_action( 'wordpress_social_login' );

		wp_login_form();

		if ( get_option( 'users_can_register' ) ) {
			echo '<a href="' . esc_url( home_url( '/register/' ) ) . '">Create new Account</a>';
		}
	}

	function cs_score_entry() {
		return 'form will be here';
	}

	function cs_register_shortcode() {
		if ( ! get_option( 'users_can_register' ) ) {
			return __( 'Registration is currently closed.', 'wpskilltestplugin' );
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
		include_once plugin_dir_path( __FILE__ ) . 'partials/how-goes-it-public-registration.php';
		$output = ob_get_clean();

		return $output;
	}

}
