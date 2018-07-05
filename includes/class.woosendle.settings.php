<?php
/**
 * Woo Sendle API - Settings Class
 * 
 * This class adds the specific Sendle API settings fields into the 
 * existing WooCommerce settings page. The settings are retreived from
 * within the API Class.
 *
 * @category   woo-sendle-api
 * @package    sendle-settings
 * @version	   1.02
 * @author     JRS <developer@oldlabel.com>
 * @license    http://www.gnu.org/licenses/  GNU General Public License
 * @link       https://www.oldlabel.com/woo-sendle-api
 * 
 * v1.02
 * added prefix
 */

class SendleAPI_Settings {
	
	/**
	 * construct
	 *
	 * Enqueue scripts and set id as 'sendle_api_'
	 * 
	 *
	 * 
	 * @return none
	 */ 
	public function __construct() {

		$this->id    = 'sendle_api';
		$this->label = __( 'Sendle API', 'woocommerce' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 100 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		$this->get_sections();
		
		// if API is not enabled display a admin notice warning.
		if(get_option('sendle_api_conf_enable') != 'yes'){
			add_action( 'admin_notices', array(&$this,'sendle_admin_notice_not_enabled') );
		}
	}
	
	/**
	 * Return new sections.
	 */ 
	public function get_sections() {

		$sections = array(
			''          	=> __( 'API Configuration', 'woocommerce' ),
			'default'       => __( 'Default Values', 'woocommerce' )
		);

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}
	
	
	/**
	 * Output the settings.
	 */
	public function output() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		woocommerce_admin_fields($settings);
		
	}

	/**
	 * Save settings.
	 */
	public function save() {
		global $current_section;

		$settings = $this->get_settings( $current_section );
		woocommerce_update_options($settings);
		 
	}
	
	/***
	 * Add settings tab.
	 */
	public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['sendle_api'] = __( 'Sendle API', 'woocommerce_settings_sendle_api' );
        return $settings_tabs;
    }
	
	/***
	 * Get specific sendle settings.
	 */
	public function get_settings( $current_section = '' ) {
		if ( 'display' == $current_section ) {
			
		} else {
			$settings = apply_filters( 'woocommerce-sendle-api-settings', 
			array(
				array(
					'title' 	=> __( 'API Configuration', 'woocommerce-sendle-api-settings' ),
					'type' 		=> 'title',
					'id' 		=> $this->id.'_conf',
					),
					'enabled' => array(
						'name' => __( 'Enabled?', 'woocommerce-sendle-api-settings' ),
						'type' => 'checkbox',
						'desc' => __( 'Tick this to enable the API.', 'woocommerce-sendle-api-settings' ),
						'desc_tip' => true,
						'id'   => $this->id.'_conf_enable'
					),
					'apimode' => array(
						'name' => __( 'Connection Mode', 'woocommerce-sendle-api-settings' ),
						'type' => 'select',
						'desc' => __( 'Choose live or sandbox connection to Sendle API'),
						'desc_tip' => true,
						'default' => 'live',
						'options' => array(
						  'api'        => __( 'Live', 'woocommerce-sendle-api-settings' ),
						  'sandbox'       => __( 'Sandbox', 'woocommerce-sendle-api-settings' )
							),
						'id' => $this->id.'_conf_mode'
					),
					'api_user' => array(
						'name' => __( 'Live API User', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc' => __( 'Live API User name', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_conf_api_user',
						'desc_tip' => true
					),
					'api_key' => array(
						'name' => __( 'Live API Key', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Live API Key', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_conf_api_key'
					),
					'sandbox_user' => array(
						'name' => __( 'Sandbox API User', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Sandbox API User name', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_conf_sandbox_user'
					),
					'sandbox_key' => array(
						'name' => __( 'Sandbox API Key', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Sandbox API Key', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_conf_sandbox_key'
					),
					'prefix' => array(
						'name' => __( 'Sendle Meta Order Prefix', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'The content of this field will be prefixed to the order number in Sendle\'s meta fields. ', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_conf_prefix'
					),
					array(
						'type' 	=> 'sectionend',
						'id' 	=> $this->id.'_conf',
					),
					array(
						'title' => __( 'Default Pick-up Settings', 'woocommerce-sendle-api-settings' ),
						'type' 	=> 'title',
						'desc' 	=> '',
						'id' 	=> $this->id.'_default',
					),
					'name' => array(
						'name' => __( 'Name', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Your name', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_name'
					),
					'phone' => array(
						'name' => __( 'Phone Number', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Your phone number', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_phone'
					),
					'instructions' => array(
						'name' => __( 'Instructions', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Default instructions for pickup driver.', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_instructions'
					),
					'address_1' => array(
						'name' => __( 'Address Line 1', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Address Line 1', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_address_1'
					),
					'address_2' => array(
						'name' => __( 'Address Line 2', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Address Line 2', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_address_2'
					),
					'suburb' => array(
						'name' => __( 'Suburb', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Suburb', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_suburb'
					),
					'state' => array(
						'name' => __( 'State', 'woocommerce-sendle-api-settings' ),
						'type' => 'select',
						'default' => 'ACT',
						'desc_tip' => true,
						'desc' => __( 'State', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_state',
						'options' => array(
							'ACT'        => __( 'ACT', 'woocommerce-sendle-api-settings' ),
							'NSW'       => __( 'NSW', 'woocommerce-sendle-api-settings' ),
							'NT'        => __( 'NT', 'woocommerce-sendle-api-settings' ),
							'QLD'        => __( 'QLD', 'woocommerce-sendle-api-settings' ),
							'SA'        => __( 'SA', 'woocommerce-sendle-api-settings' ),
							'TAS'        => __( 'TAS', 'woocommerce-sendle-api-settings' ),
							'VIC'        => __( 'VIC', 'woocommerce-sendle-api-settings' ),
							'WA'        => __( 'WA', 'woocommerce-sendle-api-settings' )
						)
					),
					'postcode' => array(
						'name' => __( 'Postcode', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Postcode', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_postcode'
					),
					'country' => array(
						'name' => __( 'Country', 'woocommerce-sendle-api-settings' ),
						'type' => 'select',
						'desc_tip' => true,
						'desc' => __( 'Country. Sendle only works in Australia.', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_country',
						'options' => array(
							'Australia' => __('Australia', 'woocommerce-sendle-api-settings')
						),
						'default' => 'Australia'
					),
					'weight' => array(
						'name' => __( 'Default package weight', 'woocommerce-sendle-api-settings' ),
						'type' => 'select',
						'desc_tip' => true,
						'desc' => __( 'Choose from the standard weights (used if no package measurements are found)', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_weight',
						'options' => array(
							'0.5' => __('Satchel - 0.5KG, 0.002m3', 'woocommerce-sendle-api-settings'),
							'2' => __('Shoebox - 2KG, 0.008m3', 'woocommerce-sendle-api-settings'),
							'5' => __('Briefcase - 5KG, 0.02m3', 'woocommerce-sendle-api-settings'),
							'10' => __('Carry-on - 10KG, 0.04m3', 'woocommerce-sendle-api-settings'),
							'25' => __('Luggage - 25KG, 0.1m3', 'woocommerce-sendle-api-settings')
						),
						'default' => '2'
					),
					'volume' => array(
						'name' => __( 'Volume (m3)', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Volume in cubic metres', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_volume'
					),
					/*
					 * not used
					 * 
					 * 'weight' => array(
						'name' => __( 'Weight (kg)', 'woocommerce-sendle-api-settings' ),
						'type' => 'text',
						'desc_tip' => true,
						'desc' => __( 'Weight in kilograms', 'woocommerce-sendle-api-settings' ),
						'id'   => $this->id.'_default_weight'
					),*/
					
					array(
						'type' 	=> 'sectionend',
						'id' 	=> $this->id.'_default',
					)
				)
			);
				
				
		}
		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}
	/***
	 * HTML for admin notice if API settings are not enabled.
	 */
	public function sendle_admin_notice_not_enabled() {
		?>
		<div class="notice notice-warning is-dismissible">
			<p><?php _e( 'Woo Sendle API is not enabled. Please review the settings <a href='.admin_url("admin.php?page=wc-settings&tab=sendle_api").'>here</a>.', 'woocommerce-sendle-api-settings' ); ?></p>
		</div>
		<?php
	}
	
}

?>

