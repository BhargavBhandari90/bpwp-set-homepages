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
if ( ! class_exists( 'BPWP_Set_Homepages_Admin' ) ) {

	/**
	 * Class for Activity Re-post.
	 */
	class BPWP_Set_Homepages_Admin {

		/**
		 * Constructor for class.
		 */
		public function __construct() {

			// Register setting.
			add_action( 'admin_init', array( $this, 'bpwpsh_register_homepage_setting' ) );

			// Add field to allowed options to save value in options data.
			add_filter( 'allowed_options', array( $this, 'bpwpsh_allowed_options' ) );

		}

		/**
		 * Add setting to set homepage for logged-in users.
		 *
		 * @return void
		 */
		public function bpwpsh_register_homepage_setting() {

			$show_on_front = get_option( 'show_on_front' );
			$page_on_front = get_option( 'page_on_front' );

			// Bail, if anything goes wrong.
			if ( ( ! empty( $show_on_front ) && 'page' !== $show_on_front ) ||
				 ( empty( $page_on_front ) || '0' === $page_on_front ) ) {

				return;
			}

			// Add setting section to reading page.
			add_settings_section(
				'bpwpsh_user_role_setting_section',
				esc_html__( 'Homepage for Users roles', 'bpwp-set-homepages' ),
				'',
				'reading'
			);

			// Add field to reading page.
			add_settings_field(
				'front-static-pages-logged-in',
				esc_html__( 'Homepage for logged-in Users ( Default )', 'bpwp-set-homepages' ),
				array( $this, 'bpbpwpsh_setting_callback_function' ),
				'reading',
				'bpwpsh_user_role_setting_section',
				array( 'label_for' => 'front-static-pages-logged-in' )
			);

			// Get list of user roles that the current user is allowed to edit.
			$editable_roles = array_reverse( get_editable_roles() );

			if ( ! empty( $editable_roles ) && array_key_exists( 'administrator', $editable_roles ) ) {
				unset( $editable_roles['administrator'] );
			}

			if ( ! empty( $editable_roles ) ) {
				// Get stored values.
				$values = get_option( 'page_on_front_user_role' );

				// Loop through all roles.
				foreach ( $editable_roles as $role => $details ) {

					// Get role name.
					$name = translate_user_role( $details['name'] );

					// Add setting field.
					add_settings_field(
						"page_on_front_user_role[$role]",
						esc_html( $name ),
						array( $this, 'bpwpsh_user_role_setting_cb' ),
						'reading',
						'bpwpsh_user_role_setting_section',
						array(
							'label_for' => "page_on_front_user_role[$role]",
							'value'     => ! empty( $values[ $role ] ) ? (int) $values[ $role ] : 0,
							'role'      => $role,
						)
					);
				}
			}
		}

		/**
		 * Callback to display page selection.
		 *
		 * @return void
		 */
		public function bpbpwpsh_setting_callback_function() {

			// Page list dropdown.
			echo wp_dropdown_pages(
				array(
					'name'              => 'page_on_front_logged_in',
					'echo'              => 0,
					'show_option_none'  => __( '&mdash; Select &mdash;', 'bpwp-set-homepages' ),
					'option_none_value' => '0',
					'selected'          => get_option( 'page_on_front_logged_in' ),
				)
			);

			// Field description.
			echo wp_sprintf(
				/* translators: %s: Setting description. */
				'<p class="description">%s</p>',
				esc_html__( 'Redirect logged-in users to this page when they try to access homepage.', 'bpwp-set-homepages' )
			);
		}

		/**
		 * Callback to display page selection.
		 *
		 * @param array $args Extra arguments that get passed to the callback function.
		 * @return void
		 */
		public function bpwpsh_user_role_setting_cb( $args ) {

			$role  = ! empty( $args['role'] ) ? $args['role'] : '';
			$value = ! empty( $args['value'] ) ? $args['value'] : '';

			wp_dropdown_pages(
				array(
					'name'              => esc_html( "page_on_front_user_role[$role]" ),
					'show_option_none'  => esc_html__( '&mdash; Select &mdash;', 'bpwp-set-homepages' ),
					'option_none_value' => '0',
					'selected'          => esc_attr( $value ),
				)
			);
		}

		/**
		 * Add new field to allowed option.
		 * By adding this field to allowed option, WP handles saving data to options.
		 *
		 * @param array $allowed_options
		 * @return array
		 */
		public function bpwpsh_allowed_options( $allowed_options ) {

			// Add new option to allowed list.
			if ( isset( $allowed_options['reading'] ) ) {
				$allowed_options['reading'][] = 'page_on_front_logged_in';
				$allowed_options['reading'][] = 'page_on_front_user_role';
			}

			return $allowed_options;
		}
	}
}

new BPWP_Set_Homepages_Admin();
