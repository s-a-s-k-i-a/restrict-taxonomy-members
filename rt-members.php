<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.saskialund.de
 * @since             0.0.1
 * @package           Rt_Members
 *
 * @wordpress-plugin
 * Plugin Name:       Restrict Taxonomy for Members
 * Plugin URI:        https://www.saskialund.de/
 * Description:       A plugin that adds restriction to taxonomies on a membership site.
 * Version:           0.0.1-dev
 * Author:            Saskia Lund
 * Author URI:        https://www.saskialund.de
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       rt-members
 * Domain Path:       /languages
 * Update URL:        https://github.com/s-a-s-k-i-a/restrict-taxonomy-members
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define the version number
if ( ! defined( 'RT_MEMBERS_VERSION' ) ) {
	define( 'RT_MEMBERS_VERSION', '0.0.1' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-rt-members-activator.php
 */
function activate_rt_members() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rt-members-activator.php';
	Rt_Members_Activator::activate();
}

// Check if Members is installed and active
if ( ! class_exists( 'Members_Plugin' ) ) {
	function rt_members_show_members_requirement() {
		?>
		<div class="notice notice-error">
			<p>
				<strong>
					<?php
					echo sprintf( __( 'Restrict Taxonomy for Members requires the plugin %sMembers%s please install and activate it now.', 'rt-members' ), '<a href="https://wordpress.org/plugins/members/" target="_blank" title="Members">', '</a>' );
					?>
				</strong>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'rt_members_show_members_requirement' );

	return;
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-rt-members-deactivator.php
 */
function deactivate_rt_members() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-rt-members-deactivator.php';
	Rt_Members_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_rt_members' );
register_deactivation_hook( __FILE__, 'deactivate_rt_members' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-rt-members.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_rt_members() {

	$plugin = new Rt_Members();
	$plugin->run();

}

run_rt_members();