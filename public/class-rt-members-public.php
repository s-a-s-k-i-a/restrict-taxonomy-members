<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.saskialund.de
 * @since      0.0.1
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 * @author     Saskia Lund <hello@saskialund.de>
 */
class Rt_Members_Public {

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
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/rt-members-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/rt-members-public.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( $this->plugin_name, 'rt_members', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}


	/**
	 * Check if user can view post.
	 *
	 * @param bool $can_view The bool if user can view post or not.
	 * @param int $user_id The user ID.
	 * @param int $post_id The post ID.
	 *
	 * @return bool
	 */
	public function can_user_view_post( $can_view, $user_id, $post_id ) {

		$can_view = $this->can_user_view_post_term( $can_view, $user_id, $post_id );

		if($can_view){
			return $can_view;
		}else{
			$can_view = $this->can_user_view_post_single( $can_view, $user_id, $post_id );
		}

		return $can_view;
	}

	/**
	 * Check if user can view single access restricted post.
	 *
	 * @param bool $can_view The bool if user can view post or not.
	 * @param int $user_id The user ID.
	 * @param int $post_id The post ID.
	 *
	 * @return bool
	 */
	public function can_user_view_post_single( $can_view, $user_id, $post_id ) {
		if ( ! $user_id ) {
			return $can_view;
		} else {
			$post_ids = $this->get_user_single_access( $user_id );
			if ( empty( $post_ids ) ) {
				return $can_view;
			} else {
				if(get_post_meta( $post_id, '_wpim_single_access',true) ){
					return in_array( $post_id, $post_ids );
				}else{
					return $can_view;
				}

			}
		}
	}


	/**
	 * Get the array of post ids the user has single access to.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return array
	 */
	public function get_user_single_access( $user_id ) {
		$post_ids = get_user_meta( $user_id, '_wpi_single_access', true );

		if ( ! empty( $post_ids ) ) {
			$post_ids = explode( ",", $post_ids );
		}

		return $post_ids;
	}


	/**
	 * Check if a user can view a post from a term.
	 *
	 * @param bool $can_view The bool if user can view post or not.
	 * @param int $user_id The user ID.
	 * @param int $post_id The post ID.
	 *
	 * @since 0.0.1
	 * @return bool
	 */
	public function can_user_view_post_term( $can_view, $user_id, $post_id ) {
		$rt_members_term_error = array();
		global $rt_members_term_error;
		// @todo we should add a settings to enable content restriction on certain taxonomies
//		$taxonomies = get_taxonomies('','names');
//		print_r($taxonomies);

		$taxonomies = array(
			'category' => 'category',
			'post_tag' => 'post_tag'
		);

		$terms = wp_get_object_terms( $post_id, $taxonomies );

		$restricted_roles   = array();
		$restricted_term_id = 0;
		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				// Get the roles saved for the post.
				$roles = get_term_meta( $term->term_id, '_rt_members_access_role', false );

				if ( ! empty( $roles ) ) {
					foreach ( $roles as $role ) {
						$restricted_roles[] = $role;
						$restricted_term_id = $term->term_id;
					}
				}
			}
		}


		// if post has restriction and user is not logged in then false
		if ( ! empty( $restricted_roles ) && ! $user_id ) {
			$rt_members_term_error[ $restricted_term_id ] = $post_id;

			return false;
		} elseif ( ! empty( $restricted_roles ) && $user_id ) {
			$user_meta = get_userdata( $user_id );

			$user_roles = $user_meta->roles;

			$result = array_intersect( $restricted_roles, $user_roles );

			if ( empty( $result ) ) {
				$rt_members_term_error[ $restricted_term_id ] = $post_id;

				return false;
			} else {
				return $can_view;
			}

		}

		return $can_view;
	}

	/**
	 * Return the term specific error message if availiable.
	 *
	 * @param string $message The error message.
	 *
	 * @since 0.0.1
	 * @return string
	 */
	public function term_error_message( $message ) {
		global $rt_members_term_error, $post;

		// post error message should take priority
		if ( isset($post->ID) && $term_error = get_post_meta( $post->ID, '_members_access_error', true ) ) {
			if ( $term_error ) {
				return sprintf( '<div class="members-access-error">%s</div>', $term_error  );
			}
		}
		else if ( $term_id = array_search( $post->ID, $rt_members_term_error ) ) {
			$term_error = get_term_meta( $term_id, '_members_access_error', true );
			if ( $term_error ) {
				return sprintf( '<div class="members-access-error">%s</div>', $term_error  );
			}
		}

		return sprintf( '<div class="members-access-error">%s</div>', $message );

		//return $message;
	}

}
