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
require_once plugin_dir_path( plugin_dir_path( __FILE__ ) ) . 'models/class-how-goes-it-model-followers.php';
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
				if ( $hgia_score < 4 ) {
					$this->hgi_send_notification_to_followers( $hgia_current_user, $hgia_score );
				}
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

	public function hgi_send_notification_to_followers( $current_user, $score ) {
		$user_info  = get_userdata( $current_user );
		$first_name = $user_info->first_name;
		$last_name  = $user_info->last_name;

		$followers_o = new How_Goes_It_Model_Followers();
		$followers_a = $followers_o->hgi_get_followers_of_user( $current_user );
		// TODO: store these messages into table and send them by cron?
		foreach ( $followers_a as $follower ) {
			$body = sprintf(
				'Hi %1$s,'
				. '<p>%2$s just changed his score to %3$d and because scores 1-3 on our scale are the "need help" scores we are sending you a notification.</p>'
				. '<p>If you and %2$s have arranged for what %2$s finds most helpful in these situation, now would be the time to do that.</p>'
				. '<p>If you feel uncertain how to help %2$s, take a quick glance at our <a href="#">Resource section</a> under <a href="#">How to help someone that\'s hurting</a>.</p>',
				$follower['follower_first_name'], $first_name, $score
			);

			$to      = $follower['follower_email'];
			$subject = 'Your friend needs help';
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			wp_mail( $to, $subject, $body, $headers );
		}
	}
}
