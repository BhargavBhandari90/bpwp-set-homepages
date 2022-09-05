<?php
/**
 * Class for repost methods.
 *
 * @package Wp_Set_Homepages
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// If class is exist, then don't execute this.
if ( ! class_exists( 'BPWP_Set_Homepages' ) ) {

	/**
	 * Class for Activity Re-post.
	 */
	class BPWP_Set_Homepages {

		/**
		 * Constructor for class.
		 */
		public function __construct() {
			add_action( 'template_redirect', array( $this, 'bpwpsh_homepage_redirect' ), 999 );
		}

		/**
		 * Redirect logged-in user to selected page.
		 * Redirect non-logged-in user to homepage selected in WordPress Settings.
		 *
		 * @return void
		 */
		function bpwpsh_homepage_redirect() {

			// Don't execute further, if user is not logged-in.
			if ( ! is_user_logged_in() ) {
				return;
			}

			$show_on_front           = get_option( 'show_on_front' );
			$page_on_front_logged_in = get_option( 'page_on_front_logged_in' );

			// Reset homepage.
			if ( ! empty( $show_on_front ) && 'page' !== $show_on_front ) {

				if ( ! empty( $page_on_front_logged_in ) ) {
					update_option( 'page_on_front_logged_in', 0 );
				}

				return;
			}

			$page_on_front           = get_option( 'page_on_front' );
			$page_on_front_logged_in = empty( $page_on_front_logged_in )
				? intval( $page_on_front )
				: intval( $page_on_front_logged_in );

			// Redirect to selected page when got to homepage if user is logged-in.
			if ( is_front_page() &&
				! empty( $page_on_front_logged_in ) &&
				'publish' === get_post_status( $page_on_front_logged_in ) &&
				get_the_ID() !== $page_on_front_logged_in ) {

				wp_redirect( get_permalink( $page_on_front_logged_in ) );
				exit;
			}
		}
	}
}

new BPWP_Set_Homepages();
