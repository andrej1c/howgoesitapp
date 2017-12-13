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

		$this->plugin_name	 = $plugin_name;
		$this->version		 = $version;
	}

	public function init_shortcodes() {
		add_shortcode( 'howgoesit_my_last_score', [$this, 'howgoesit_my_last_score_func'] );
	}

	public function howgoesit_my_last_score_func( $atts ) {
		$a = shortcode_atts( array(
			'formid'		 => -1,
			'user_id_field'	 => 1,
			'score_field'	 => 2,
		), $atts );

		$form_id		 = $a['formid'];
		$user_id_field	 = $a['user_id_field'];
		$score_field	 = $a['score_field'];

		if ( -1 === $form_id ) {
			return 'No form found.';
		}
		if ( ! is_user_logged_in() ) {
			return 'Please login first.';
		}
		$current_user						 = wp_get_current_user();
		$search_criteria['field_filters'][]	 = array('key' => $user_id_field, 'value' => $current_user->user_login);
		$sorting							 = array();
		$paging								 = array('offset' => 0, 'page_size' => 1);

		$entries = GFAPI::get_entries( $form_id, $search_criteria, $sorting, $paging );
		if ( 0 < count( $entries ) ) {
			$entry = $entries[0];
		} else {
			return 'No saved scores yet.';
		}
		$created_at	 = $entry['date_created'];
		$status		 = $entry[$score_field];

		$html = sprintf( '<p>Your last score: %s, set %s ago.</p>', $status, human_time_diff( strtotime( $created_at ), time() ) );

		return $html;
	}

}
