<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.saskialund.de
 * @since      0.0.1
 *
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      0.0.1
 * @package    Rt_Members
 * @subpackage Rt_Members/includes
 * @author     Saskia Lund <hello@saskialund.de>
 */
class Rt_Members_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {

		// remove the expire check hook.
		wp_clear_scheduled_hook('rt_members_expire_check');
	}

}
