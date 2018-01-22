<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.saskialund.de
 * @since      0.0.1
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.0.1
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 * @author     Saskia Lund <hello@saskialund.de>
 */
class Rt_Members_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    0.0.1
	 */
	public static function activate() {
		if (! wp_next_scheduled ( 'rt_members_expire_check' )) {
			wp_schedule_event(time(), 'twicedaily', 'rt_members_expire_check');
		}

		// Set activation redirect flag, we set it for 30 minutes in case they need to install other required plugins.
		set_transient( '_rt_members_activation_redirect', true, 1800 );

	}

}
