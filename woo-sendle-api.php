<?php
/**
 * Plugin Name: Woo Sendle API
 * Plugin URI: https://www.oldlabel.com/woo-sendle-api
 * Description: Woocommerce Sendle API Plugin by oldlabel web design
 * 
 * Version: 1.0.1
 * Author: oldlabel
 * Author URI: https://www.oldlabel.com
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * @category   woo-sendle-api
 * @package    woo-sendle-api
 * @author     oldlabel <developer@oldlabel.com>
 * @license    http://www.gnu.org/licenses/  GNU General Public License
 * @link       https://www.oldlabel.com/woo-sendle-api
 */


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	add_action( 'admin_init', function() {
		include( plugin_dir_path( __FILE__ ) . 'includes/class.woosendle.settings.php');
		
		$SendleAPI_Settings = new SendleAPI_Settings();
		if(get_option('sendle_api_conf_enable') == 'yes'){
			include( plugin_dir_path( __FILE__ ) . 'includes/class.woosendle.api.php');
			$api = new SendleAPI();
		}
	});
}
?>
