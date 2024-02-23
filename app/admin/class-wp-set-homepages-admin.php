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

			// Add field to reading page.
			add_settings_field(
				'front-static-pages-logged-in',
				esc_html__( 'Homepage for logged-in Users', 'bpwp-set-homepages' ),
				array( $this, 'bpbpwpsh_setting_callback_function' ),
				'reading',
				'default',
				array( 'label_for' => 'front-static-pages-logged-in' )
			);

			// Add field to reading page.
			add_settings_field(
				'front-static-pages-user-roles',
				esc_html__( 'Homepage for Users roles', 'bpwp-set-homepages' ),
				array( $this, 'bpwpsh_user_role_setting_cb' ),
				'reading',
				'default',
				array( 'label_for' => 'front-static-pages-logged-in' )
			);
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
		 * @return void
		 */
		public function bpwpsh_user_role_setting_cb() {
			// Get list of user roles that the current user is allowed to edit.
			$editable_roles = array_reverse( get_editable_roles() );
			?>
			<fieldset>
				<legend class="screen-reader-text">
					<span><?php esc_html_e( 'Users roles' ); ?></span>
				</legend>
				<?php
				if ( ! empty( $editable_roles ) ) {
					$values = get_option( 'page_on_user_role' );
					echo '<ul>';
					foreach ( $editable_roles as $role => $details ) {
						$name = translate_user_role( $details['name'] );
						printf(
							'<li><label>%1$s: %2$s</label></li>',
							esc_html( $name ),
							wp_dropdown_pages(
								array(
									'name'              => "page_on_user_role[$role]",
									'echo'              => 0,
									'show_option_none'  => __( '&mdash; Select &mdash;', 'bpwp-set-homepages' ),
									'option_none_value' => '0',
									'selected'          => ! empty( $values[ $role ] ) ? (int) $values[ $role ] : 0,
								)
							)
						);
					}
					echo '</ul>';
				}
				?>
			</fieldset>
			<?php
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
				$allowed_options['reading'][] = 'page_on_user_role';
			}

			return $allowed_options;
		}
	}
}

new BPWP_Set_Homepages_Admin();
