<?php
/**
 * Score actions.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 */

require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-last-score.php';
require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-score.php';
/**
 * Score actions.
 *
 * Defines actions for setting up score and reading it.
 *
 * @package    How_Goes_It
 * @subpackage How_Goes_It/admin
 * @author     Jakub <jakub.triska@cihosolutions.com>
 */
class How_Goes_It_Admin_Score_Actions extends How_Goes_It_Admin {

	public function __construct( $plugin_name, $version ) {
		parent::__construct( $plugin_name, $version );
	}

	public function hgi_set_new_score_action() {
		if ( 'hgi_add_score' !== filter_input( INPUT_POST, 'action' ) ) {
			return;
		}
		if ( ! filter_input( INPUT_POST, 'hgi_score_nonce_field' ) || ! wp_verify_nonce( filter_input( INPUT_POST, 'hgi_score_nonce_field' ), 'hgi_score' ) ) {
			wp_die(
				__( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
					'response' => 403,
				)
			);
		} else {
			$message           = '';
			$hgia_score        = (int) filter_input( INPUT_POST, 'hgia_score' );
			$hgia_current_user = get_current_user_id();
			if ( 0 === $hgia_current_user ) {
				$message = 'You have to be logged in to set your score.';
			}
			if ( 0 >= $hgia_score ) {
				$message = 'Your score should be at least 1.';
			}
			if ( wp_get_referer() ) {
				$ref_url = wp_parse_url( wp_get_referer() );
				$ref_url = $ref_url['path'];
			} else {
				$ref_url = get_site_url();
			}
			if ( ! empty( $message ) ) {
				$redirect = add_query_arg(
					array(
						'error' => rawurlencode( $message ),
					), $ref_url
				);
				wp_safe_redirect( $redirect );
				die();
			}
			$new_score = new How_Goes_It_Model_Score();
			$result    = $new_score->add_new_score( $hgia_current_user, $hgia_score );
			if ( false !== $result ) {
				$new_last_score = new How_Goes_It_Model_Last_Score();
				$result         = $new_last_score->set_new_last_score( $hgia_current_user, $hgia_score );
			}
			if ( false === $result ) {
				$message  = 'There was an error while saving the score, please try again later.';
				$redirect = add_query_arg(
					array(
						'error' => rawurlencode( $message ),
					), $ref_url
				);
				wp_safe_redirect( $redirect );
				die();
			} else {
				// TODO: if $hgia_score < 4, find followers and send them emails.
				$message  = 'Your score was successfully set.';
				$redirect = add_query_arg(
					array(
						'success' => rawurlencode( $message ),
					), $ref_url
				);
				wp_safe_redirect( $redirect );
				die();
			}
		}
	}

}
