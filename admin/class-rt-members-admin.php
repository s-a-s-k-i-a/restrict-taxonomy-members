<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.saskialund.de
 * @since      0.0.1
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/admin
 * @author     Saskia Lund <hello@saskialund.de>
 */
class Rt_Members_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;


	}


	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rt_Members_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rt_Members_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rt-members-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Rt_Members_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Rt_Members_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rt-members-admin.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Register meta box for the Membership settings.
	 *
	 * @since 1.0.0
	 */
	public function register_membership_meta_boxes() {

		// Membership settings
		add_meta_box(
			'wpiprice',
			__( 'Membership Settings', 'rt-members' ),
			array( $this, 'membership_display_callback' ),
			array( 'users_page_roles', 'users_page_role-new' ),
			'side',
			'high'
		);

	}

	/**
	 * Save the settings for the single access meta info.
	 *
	 * @since 0.0.1
	 *
	 * @param int $post_id The post id.
	 */
	public function save_single_access_settings( $post_id ) {

		if ( isset( $_POST['rt-members-single-access'] ) ) {
			update_post_meta( $post_id, '_rt_single_access', (int) $_POST['rt-members-single-access'] );
		}
	}

	/**
	 * Adds a post_id to the allowed single access array for a user.
	 *
	 * @param int $user_id The user ID.
	 * @param int $post_id The post ID.
	 *
	 * @since 1.0.0
	 */
	public function user_single_access_add( $user_id, $post_id ) {
		$post_ids = get_user_meta( $user_id, '_rt_single_access', true );

		if ( ! empty( $post_ids ) ) {
			$post_ids = explode( ",", $post_ids );
			if ( ! in_array( $post_id, $post_ids ) ) {
				$post_ids[] = $post_id;
			}
			$post_ids = implode( ",", $post_ids );
		} else {
			$post_ids = $post_id;
		}

		update_user_meta( $user_id, '_rt_single_access', $post_ids );

	}

	/**
	 * Add a new user role to the user from the item ID.
	 *
	 * @param $user_id
	 * @param $item_id
	 *
	 * @return bool
	 */
	public function user_role_add( $user_id, $role ) {

		if ( ! $user_id || ! $role ) {
			return false;
		}

		$user = new WP_User( $user_id );

		$user->add_role( $role );

		return true;

	}

	/**
	 * Get the role settings for the user.
	 *
	 * @param $role
	 * @param int $user_id
	 * @return mixed|string
	 */
	public function get_role_settings( $role, $user_id = 0 ) {

		$role_settings = '';
		if ( $user_id ) {

			$roles = get_user_meta( $user_id, '_wpi_role_settings', true );
			if ( is_array( $roles ) && isset( $roles[ $role ] ) ) {
				$role_settings = $roles[ $role ];
			}
		}

		if ( ! $role_settings ) {
			$role_settings = $this->get_membership_settings( $role );

			if ( $user_id && $role_settings ) {
				$roles = get_user_meta( $user_id, '_wpi_role_settings', true );
				$roles = is_array( $roles ) ? $roles : array();
				$roles[ $role ] = $role_settings;
				update_user_meta( $user_id, '_wpi_role_settings', $roles );
				$role_settings = $roles[ $role ];
			}

		}

		return $role_settings;

	}

	/**
	 * Remove a new user role to the user from the item ID.
	 *
	 * @param $user_id
	 * @param $item_id
	 *
	 * @return bool
	 */
	public function user_role_remove( $user_id, $role ) {

		if ( ! $user_id || ! $role ) {
			return false;
		}

		$user = new WP_User( $user_id );

		$user->remove_role( $role );

		return true;

	}

	/**
	 * Remove the role settings for a user.
	 *
	 * @param string $role The role id string.
	 * @param int $user_id The user ID.
	 */
	public function remove_role_settings( $role, $user_id ) {
		$roles = get_user_meta( $user_id, '_rt_role_settings', true );
		if ( is_array( $roles ) && isset( $roles[ $role ] ) ) {
			unset( $roles[ $role ] );
			update_user_meta( $user_id, '_rt_role_settings', $roles );
		}
	}

	/**
	 * Removes a post_id to the allowed single access array for a user.
	 *
	 * @param int $user_id The user ID.
	 * @param int $post_id The post ID.
	 *
	 * @since 0.0.1
	 */
	public function user_single_access_remove( $user_id, $post_id ) {
		$post_ids = get_user_meta( $user_id, '_rt_single_access', true );

		if ( ! empty( $post_ids ) ) {
			$post_ids = explode( ",", $post_ids );


			if ( ( $key = array_search( $post_id, $post_ids ) ) !== false ) {
				unset( $post_ids[ $key ] );
			}

			$post_ids = implode( ",", $post_ids );
		}

		update_user_meta( $user_id, '_rt_single_access', $post_ids );

	}


	/**
	 * Adds the membership and single access user info to the profile page.
	 *
	 * @since 0.0.1
	 *
	 * @param WP_User $user The user object.
	 */
	public function profile_fields( $user ) {
		global $wp_roles;

		if ( ! current_user_can( 'promote_users' ) || ! current_user_can( 'edit_user', $user->ID ) ) {
			return;
		}

		$user_roles = (array) $user->roles;

		$roles = members_get_roles();

		ksort( $roles );

		$user_single_access = $this->get_user_single_access( $user->ID );

		//wp_nonce_field( 'new_user_roles', 'members_new_user_roles_nonce' ); ?>

		<h2><?php esc_html_e( 'Membership Info', 'rt-members' ); ?></h2>

		<table class="form-table">

			<tr>
				<th><?php esc_html_e( 'Memberships', 'members' ); ?></th>

				<td>
					<div class="wp-tab-panel">
						<ul>
							<?php foreach( $roles as $role ) { 

								$expire_date = __( 'Never', 'rt-members' ); ?>
									<li>
										<?php echo esc_html( $role->label . ": ( " . __( 'expires:', 'rt-members' ) . " " . $expire_date . " )" ); ?>
									</li>
								<?php } ?>

						</ul>
					</div>
				</td>
			</tr>


			<tr>
				<th><?php esc_html_e( 'Single Post Access', 'members' ); ?></th>

				<td>
					<div class="wp-tab-panel">
						<ul>
							<?php

							if ( ! empty( $user_single_access ) ) {

								foreach ( $user_single_access as $post_id ) {

									$title     = get_the_title( $post_id );
									$edit_link = get_edit_post_link( $post_id );
									echo "<li><a href='" . esc_html( $edit_link ) . "' target='_blank' >" . esc_html( $title ) . "</a> (" . esc_html( $post_id ) . ")</li>";
								}

							} else {
								_e( 'No single post access.', 'rt-members' );
							}
							?>
						</ul>
					</div>
				</td>
			</tr>

		</table>

	<?php }

	/**
	 * Get the users allowed singel access post id array.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @since 0.0.1
	 * @return array The array of posts the user has single access to.
	 */
	public function get_user_single_access( $user_id ) {
		$post_ids = get_user_meta( $user_id, '_rt_single_access', true );

		if ( ! empty( $post_ids ) ) {
			$post_ids = explode( ",", $post_ids );
		}

		return $post_ids;
	}


	/**
	 * Output the term restriction metabox.
	 *
	 * @param $term
	 */
	public function taxonomy_meta_box( $term ) {


		wp_enqueue_script( 'members-edit-post' );
		wp_enqueue_style( 'members-admin' );
		global $wp_roles;

		// Get roles and sort.
		$_wp_roles = $wp_roles->role_names;
		asort( $_wp_roles );

		// Get the roles saved for the post.
		$roles = isset($term->term_id) ? get_term_meta( $term->term_id, '_rt_members_access_role', false ) : array();


		// Nonce field to validate on save.
		wp_nonce_field( 'rt_members_term_meta_nonce', 'members_cp_meta' );

		// Hook for firing at the top of the meta box.
		do_action( 'rt_members_term_meta_box_before', $term ); ?>

		<tr>
			<th scope="row"><label
					for="term-meta-text"><?php esc_html_e( 'Content Permissions', 'rt-members' ); ?></label></th>
			<td>
				<div class="members-tabs members-cp-tabs">

					<?php wp_nonce_field( basename( __FILE__ ), 'rt_members_term_meta_nonce' ); ?>

					<ul class="members-tab-nav">
						<li class="members-tab-title">
							<a href="#members-tab-cp-roles">
								<i class="dashicons dashicons-groups"></i>
								<span class="label"><?php esc_html_e( 'Roles', 'rt-members' ); ?></span>
							</a>
						</li>
						<li class="members-tab-title">
							<a href="#members-tab-cp-message">
								<i class="dashicons dashicons-edit"></i>
								<span class="label"><?php esc_html_e( 'Error Message', 'rt-members' ); ?></span>
							</a>
						</li>
					</ul>

					<div class="members-tab-wrap">

						<div id="members-tab-cp-roles" class="members-tab-content">

					<span class="members-tabs-label">
						<?php esc_html_e( 'Limit access to the content to users of the selected roles.', 'rt-members' ); ?>
					</span>

							<div class="members-cp-role-list-wrap">

								<ul class="members-cp-role-list">

									<?php foreach ( $_wp_roles as $role => $name ) : ?>
										<li>
											<label>
												<input type="checkbox"
												       name="members_access_role[]" <?php checked( is_array( $roles ) && in_array( $role, $roles ) ); ?>
												       value="<?php echo esc_attr( $role ); ?>"/>
												<?php echo esc_html( translate_user_role( $name ) ); ?>
											</label>
										</li>
									<?php endforeach; ?>

								</ul>
							</div>

					<span class="members-tabs-description">
						<?php printf( esc_html__( 'If no roles are selected, everyone can view the content. The author, any users who can edit the content, and users with the %s capability can view the content regardless of role.', 'rt-members' ), '<code>restrict_content</code>' ); ?>
					</span>

						</div>

						<div id="members-tab-cp-message" class="members-tab-content">

							<?php
							$error_message = isset($term->term_id) ? get_term_meta( $term->term_id, '_members_access_error', true ) : '';
							wp_editor(
								$error_message,
								'members_access_error',
								array(
									'drag_drop_upload' => true,
									'editor_height'    => 200
								)
							); ?>

						</div>

					</div><!-- .members-tab-wrap -->

				</div><!-- .members-tabs -->
			<td>
		</tr>
		<?php

		// Hook that fires at the end of the meta box.
		do_action( 'rt_members_term_meta_box_after', $term );
	}


	/**
	 * Save the term meta content restriction settings.
	 *
	 * @param int $term_id The term ID.
	 */
	function save_term_meta( $term_id ) {

		// verify the nonce --- remove if you don't care
		if ( ! isset( $_POST['rt_members_term_meta_nonce'] ) || ! wp_verify_nonce( $_POST['rt_members_term_meta_nonce'], basename( __FILE__ ) ) ) {
			return;
		}

		/* === Roles === */

		// Get the current roles.
		$current_roles = get_term_meta( $term_id, '_rt_members_access_role', false );


		// Get the new roles.
		$new_roles = isset( $_POST['members_access_role'] ) ? $_POST['members_access_role'] : '';

		// If we have an array of new roles, set the roles.
		if ( is_array( $new_roles ) ) {
			$this->set_term_roles( $term_id, array_map( 'members_sanitize_role', $new_roles ) );
		} // Else, if we have current roles but no new roles, delete them all.
		elseif ( ! empty( $current_roles ) ) {
			delete_term_meta( $term_id, '_rt_members_access_role' );
		}

		/* === Error Message === */

		// Get the old access message.
		$old_message = get_term_meta( $term_id, '_members_access_error', true );

		// Get the new message.
		$new_message = isset( $_POST['members_access_error'] ) ? wp_kses_post( wp_unslash( $_POST['members_access_error'] ) ) : '';


		// If we have don't have a new message but do have an old one, delete it.
		if ( '' == $new_message && $old_message ) {
			delete_term_meta( $term_id, '_members_access_error' );
		} // If the new message doesn't match the old message, set it.
		else if ( $new_message !== $old_message ) {
			update_term_meta( $term_id, '_members_access_error', $new_message );
		}

	}


	/**
	 * Set the term roles for content restriction.
	 *
	 * @param int $term_id The term ID.
	 * @param array $roles The array of roles.
	 */
	function set_term_roles( $term_id, $roles ) {
		global $wp_roles;

		// Get the current roles.
		$current_roles = get_term_meta( $term_id, '_rt_members_access_role', false );

		// Loop through new roles.
		foreach ( $roles as $role ) {

			// If new role is not already one of the current roles, add it.
			if ( ! in_array( $role, $current_roles ) ) {
				add_term_meta( $term_id, '_rt_members_access_role', $role, false );
			}
		}

		// Loop through all WP roles.
		foreach ( $wp_roles->role_names as $role => $name ) {

			// If the WP role is one of the current roles but not a new role, remove it.
			if ( ! in_array( $role, $roles ) && in_array( $role, $current_roles ) ) {
				delete_term_meta( $term_id, '_rt_members_access_role', $role );
			}
		}
	}


	/**
	 * Register a setting page view in Members setting to show our welcome screen.
	 *
	 * @since 0.0.1
	 *
	 * @param $manager
	 */
	public function register_settings_page( $manager ) {

		// Bail if not on the settings screen.
		if ( 'members-settings' !== $manager->name ) {
			return;
		}


		// Register Paid Members view.
		$manager->register_view(
			new \Members\Admin\View_Rt_Members(
				'rt-members',
				array(
					'label'    => esc_html__( 'Restricted Taxonomy', 'rt-members' ),
					'priority' => 195
				)
			)
		);
	}

}