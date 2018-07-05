<?php
/**
 * Woo Sendle API - SendleAPI Class
 * 
 * This class carries out the function calls to the Sendle API to submit
 * and retrieve data. The Class will also store and retrieve data from
 * the WordPress options table.
 *
 * @category   woo-sendle-api
 * @package    sendle-api
 * @version	   1.03
 * @author     JRS <developer@oldlabel.com>
 * @license    http://www.gnu.org/licenses/  GNU General Public License
 * @link       https://www.oldlabel.com/woo-sendle-api
 * 
 * v1.03
 * fixed Dashboard offset error
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	if ( ! class_exists( 'SendleAPI')){

		class SendleAPI {
			/**
			 * Constructor
			 *
			 * Loads the configuration and setting information
			 * Loads the scripts and AJAX
			 * 
			 */ 
			public function __construct(){
				
				$this->prefix = 'sendle_api_';
				$this->conf['enabled'] = get_option($this->prefix.'conf_enable');
				$this->conf['url'] = 'https://'.get_option($this->prefix.'conf_mode').".sendle.com";
				$this->conf['mode'] = get_option($this->prefix.'conf_mode');
				$this->conf['user'] = get_option($this->prefix.'conf_'.$this->conf['mode'].'_user');
				$this->conf['apikey'] =  get_option($this->prefix.'conf_'.$this->conf['mode'].'_key');
				$this->conf['prefix'] =  get_option($this->prefix.'conf_prefix');
				$this->conf['pickup']['name'] = get_option($this->prefix.'default_name');
				$this->conf['pickup']['phone'] = get_option($this->prefix.'default_phone');
				$this->conf['pickup']['instructions'] = get_option($this->prefix.'default_instructions');
				$this->conf['pickup']['address_1'] = get_option($this->prefix.'default_address_1');
				$this->conf['pickup']['address_2'] = get_option($this->prefix.'default_address_2');
				$this->conf['pickup']['city'] = get_option($this->prefix.'default_suburb');
				$this->conf['pickup']['postcode'] = get_option($this->prefix.'default_postcode');
				$this->conf['pickup']['state'] = get_option($this->prefix.'default_state');
				$this->conf['pickup']['country'] = get_option($this->prefix.'default_country');
				$this->conf['pickup']['volume'] = get_option($this->prefix.'default_volume');
				$this->conf['pickup']['weight'] = get_option($this->prefix.'default_weight');
				
				//$this->response = '{"order_id":"1f157cff-69ce-45af-9f5b-27e4e17fcbff","state":"Booking","order_url":"https://sendle-sandbox.herokuapp.com/api/orders/1f157cff-69ce-45af-9f5b-27e4e17fcbff","sendle_reference":"SS5H5Q","tracking_url":"https://sendle-sandbox.herokuapp.com/tracking?ref=SS5H5Q","metadata":{"your_data":"LL-7"},"labels":null,"scheduling":{"is_cancellable":true,"pickup_date":"2018-07-06","picked_up_on":null,"delivered_on":null,"estimated_delivery_date_minimum":null,"estimated_delivery_date_maximum":null},"description":"LL-7","kilogram_weight":"0.5","cubic_metre_volume":null,"customer_reference":"LL-7","sender":{"contact":{"name":"oldlabel web design","phone":"0412345678","email":"jeremy@oldlabel.com","sendle_id":"jeremy_oldlabel_com"},"address":{"address_line1":"1 Brisbane St","address_line2":null,"suburb":"Brisbane","state_name":"QLD","postcode":"4000","country":"Australia"},"instructions":"pickup at door"},"receiver":{"contact":{"name":"Thomas Tank Engine","phone":null,"email":"spam@oldlabel.com"},"address":{"address_line1":"1 Perth St","address_line2":null,"suburb":"Perth","state_name":"WA","postcode":"6000","country":"Australia"},"instructions":"Authority to Leave (ATL)"},"route":{"type":"national","description":"Brisbane to Perth"}}';
				$this->response = '{"messages":{"sender":[{"address":[{"suburb":["can\t be blank"],"state_code":["can\t be blank"],"state_name":["can\t be blank"]},"is not yet serviced by Sendle"]}],"receiver":[{"address":[{"state_name":["We found your postcode and suburb, but they aren\t in the state you gave: NSW instead they are in: WA, perhaps you could change the state to match?"]}]}]},"error":"unprocessable_entity","error_description":"The data you supplied is invalid. Error messages are in the messages section. Please fix those fields and try again."}';
				
				add_action( 'admin_footer', array( &$this,'sendle_api_script') );
				add_action( 'admin_notices', array( &$this,'sendle_add_html') );
				add_action( 'wp_ajax_sendle_api_ajax_label', array( &$this,'api_label_script_handler') );
				add_action( 'wp_ajax_nopriv_sendle_api_ajax_label', array( &$this,'api_label_script_handler') );
				
				add_action( 'wp_ajax_sendle_api_ajax', array( &$this,'sendle_api_ajax') );
				add_action( 'wp_ajax_nopriv_sendle_api_ajax', array( &$this,'sendle_api_ajax') );

				add_filter( 'woocommerce_admin_order_actions', array( &$this,'sendle_api_order_list_actions_button'), 50, 2 );
				add_action( 'admin_head', array( &$this,'add_sendle_api_order_list_actions_button_css') );
				add_action('load-edit.php', array(&$this, 'sendle_admin_message'));
				add_action('load-post.php', array(&$this, 'sendle_admin_message'));
				
				add_action('wp_dashboard_setup', array(&$this, 'sendle_dashboard_widget'));
				
			}
			
			/**
			 * sendle_add_html
			 *
			 * Loads modal template in the background of the admin section. Perhaps this should be modified to only load on the required pages.
			 * 
			 * @return included document
			 */ 
			function sendle_add_html(){
				include ( plugin_dir_path( __FILE__ ) . 'templates/booking-modal.php');
				
			}
			/**
			 * sendle_admin_message
			 *
			 * Not required?
			 * 
			 * @return Status
			 */ 
			function sendle_admin_message(){
				//echo print_r(get_current_screen());
				//echo print_r(get_post_type( $_GET['post'] ));
				if ('shop_order' == get_current_screen()->post_type){
					
					add_action('all_admin_notices', function(){
						echo '<div id="spinner" class="loader"></div><div class="sendle_form"><div id="sendle_feedback"></div></div>';
					});
				}
			}
			

			/**
			 * add_sendle_column
			 *
			 * Add Sendle column to WooCommerce order screen after order_total column
			 * 
			 * @param array   $columns  Existing WooCommerce columns
			 * 
			 * 
			 * @return $new_columns
			 */ 
			public function add_sendle_column($columns){
				$new_columns = array();

				foreach ( $columns as $column_name => $column_info ) {

					$new_columns[ $column_name ] = $column_info;

					if ( 'order_total' === $column_name ) {
						$new_columns['sendle_orders'] = __( 'Sendle', 'woocommerce' );
					}
				}

				return $new_columns;
				
			}
	
			/*
			function sendle_convert_state($state){
				switch($state){
					case "Lost":
					case "Unable to Book":
					case "Return to Sender":
						$state = "Error";
						break;
				}
				return $state;
			}*/
			
			
			/**
			 * sendle_dashboard_widget
			 *
			 * Create a dashboard element to display related widget information  
			 *
			 * @return 
			 */ 
			function sendle_dashboard_widget() {
				global $wp_meta_boxes;
				wp_add_dashboard_widget('sendle_dashboard_count', 'Sendle Widget', array(&$this, 'sendle_dashboard_count'));
			}
			
			/**
			 * sendle_dashboard_count
			 *
			 * select Sendle transactions and count by status and output basic HTML list
			 *
			 * @return HTML
			 */ 
			function sendle_dashboard_count() {
				global $wpdb;
				 $querystr = "
					SELECT $wpdb->postmeta.* 
					FROM $wpdb->postmeta
					WHERE $wpdb->postmeta.meta_key = '_sendle_order_state' 
				 ";

				$pageposts = $wpdb->get_results($querystr, OBJECT);
				$result = array();
				foreach ($pageposts as $key => $value){	
					if(isset($result[$value->meta_value])){
						$result[$value->meta_value]++;
					}else{
						$result[$value->meta_value] = 1;
					}
					
				}
				echo "<ul class='wc_status_list'>";
				foreach($result as $key => $value){
					echo "<li class='processing_orders'>$key $value</li>";
					
				}
				echo "</ul>";			
			
			}

			/**
			 * sendle_api_script
			 *
			 * register and enqueue the jQuery and CSS supporting scripts for the API
			 * 
			 * 
			 * @return 
			 */ 
			function sendle_api_script(){
				
				wp_register_script( 'sendle_api_ajs', plugins_url('/js/sendle-api-script.js', __FILE__), '', '1.01', true);
				wp_enqueue_script('sendle_api_ajs');
				wp_localize_script('sendle_api_ajs', 'ajax_var', array(
					'url' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('sendle-api-nonce')
				));
				wp_enqueue_style( 'sendle_api_style', plugins_url('/sendle_api_style.css', __FILE__), array(), '1.01' ); 
				
			}
			
			/**
			 * sendle_api_ajax
			 *
			 * Manipulate the incoming actions from the AJAX front end and direct the appropriate API action after checking the nonce. 
			 * 
			 * 
			 *
			 * @return json encoded array
			 */ 
			function sendle_api_ajax(){
				// define parameters used to control dat
				$operation = $_REQUEST['op'];
				$order_id = (isset($_REQUEST['id']) ? $_REQUEST['id'] : $_REQUEST['general_id']);

				if (wp_verify_nonce($_REQUEST['_wpnonce'], 'sendle-api-nonce')){
					if(isset($operation)&&isset($order_id)){
						$output = array();
						$api = new SendleAPI();
						
						switch($operation){
							case "status":
								$output = $this->get_order($order_id);
								break;
							case "book":
								// get and set specific order data to return via AJAX
								$details = $api->get_sendle_meta($order_id);
								$order = new WC_Order($order_id);
								$order_data = $order->get_data();
								$result['receiver'] = $order_data['shipping'];
								$result['receiver']['email'] = $order_data['billing']['email'];
								$result['general']['pickup_date'] = date("Y-m-d", strtotime('today +1 Weekday'));
								$result['general']['description'] = $this->conf['prefix'].$order_id;
								$result['general']['customer_reference'] = $this->conf['prefix'].$order_id;
								$result['receiver']['instructions'] = 'authority to leave';
								$result['general']['id'] = $order_id;
								$conf = $api->get_conf();
								$result['pickup'] = $conf['pickup'];
								
								// turn associative array into single dimension array to suit the HTML form field names
								foreach ($result as $key => $value){
									foreach ($value as $data => $field){
										$output[$key."_".$data] = $field;
									}
								}
								break;
							case "order":
								$output = $this->create_order($order_id, $_POST);
								break;
							case "cancel":
								$output = $this->cancel_order($order_id);
								break;
							case "label":
								$output = $this->get_label($order_id);
								break;
							default:
								$output = array('success' => 'false', 'header'=>'<h1>Error</h1>', 'msg'=>'Missing data.');
								break;
						}
						echo json_encode($output, true);
					}else{					 
					 echo json_encode(array('success' => 'false', 'header'=>'<h1>Error</h1>', 'msg'=>'Missing data.'));
					}
				}else{
					echo json_encode(array('success' => 'false', 'header'=>'<h1>Error</h1>', 'msg'=>$_REQUEST));
				}
				wp_die();
			}
			
			/**
			 * sendle_api_order_list_actions_button
			 *
			 * add the Sendle action to the WooCommerce orders page and set the CSS classes to enable the correct icon to be used based on Sendle order status
			 *
			 * @param array   $actions  The existing WooCommerce action icons
			 * @param integer $order The WooCommerce order id
			 * 
			 * @return array $actions updated with the Sendle button
			 */ 
			 
			function sendle_api_order_list_actions_button($actions, $order){
					$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
					$sendle_state = $this->get_sendle_meta($order_id,'_sendle_order_state');
					if($sendle_state != 'Cancelled' && $sendle_state){
						$actions['sendle_api'] = array(
							'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=sendle_api_ajax&op=status&order_id=' . $order_id ), 'sendle-api-nonce' ),
							'name'      => __( "Sendle Order - $sendle_state", 'woocommerce' ),
							'action'    => "view ".$this->prefix." sendle_order_id_$order_id sendle_icon_$sendle_state", // keep "view" class for a clean button CSS
						);
					}else if($order->get_status() == 'processing'){
						$actions['sendle_api'] = array(
							'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=sendle_api_ajax&op=book&order_id=' . $order_id ), 'sendle-api-nonce' ),
							'name'      => __( "Book Sendle", 'woocommerce' ),
							'action'    => "view ".$this->prefix." sendle_order_id_$order_id sendle_icon_".($sendle_state ? $sendle_state : "Book"), // keep "view" class for a clean button CSS
						);
					}
				return $actions;
			}
			
			/*
			function sendle_column_content( $column ) {
			    if ( 'sendle_orders' === $column ) {
			        echo '<a class="button sendle_api_ Cancelled" title="Sendle Order - Cancelled" href="https://dev.lovingloudly.com.au/wp-admin/admin-ajax.php?action=sendle_api_ajax&amp;op=status&amp;order_id=642&amp;_wpnonce=fc4aff815c"></a>';
			    }
			}*/

			/**
			 * add_sendle_api_order_list_actions_button_css
			 *
			 * Bit self explanatory, really...
			 *
			 * 
			 * @return HTML with CSS styles for the different button icons
			 */ 
			 
			function add_sendle_api_order_list_actions_button_css() {
				/*State	Description
				Booking	Order is still being created and has not yet been scheduled for delivery.
				Pickup	Booking has been consigned and Courier is scheduled to pick up the parcel.
				Pickup Attempted	An unsuccessful parcel pickup was attempted.
				Transit	Parcel is in transit.
				Delivered	Parcel has been successfully delivered.
				Cancelled	A cancelled order.
				Unable to Book	An order which cannot be booked.
				Lost	An order marked as missing or lost.
				Return to Sender	An order which is being returned to the sender. */
				/*$icons = array(
					"Book"				=> "\\e006",
					"Booking" 			=> "\\e012",
					"Pickup" 			=> "\\e009",
					"Pickup Attempted"	=> "\\e031",
					"Transit"			=> "\\e019",
					"Delivered"			=> "\\e015",
					"Cancelled"			=> "\\e602",
					"Unable to Book"	=> "\\e016",
					"Lost"				=> "\\e018",
					"Return to Sender" 	=> "\\e00b"
					);*/
				$icons = array(
					"Book"				=> "package.svg",
					"Booking" 			=> "circle.svg",
					"Pickup" 			=> "arrow-right-circle.svg",
					"Pickup Attempted"	=> "info.svg",
					"Transit"			=> "truck.svg",
					"Delivered"			=> "check-circle.svg",
					"Cancelled"			=> "slash.svg",
					"Unable to Book"	=> "alert-triangle.svg",
					"Lost"				=> "alert-triangle.svg",
					"Return to Sender" 	=> "rotate-ccw.svg"
					);
				echo "<style>";
				foreach ($icons as $key => $value){
					//echo ".view.sendle_api_.sendle_icon_".str_replace(" ", "_", $key)."::after { font-family: woocommerce; content: \"$value\" !important; }";
					echo ".view.sendle_api_.sendle_icon_".str_replace(" ", "_", $key)."::after { content:'' !important; background-image:url(".plugins_url("/images/$value", __FILE__)."); }";
					
				}
				echo "</style>";
			}
			
			/**
			 * ping
			 *
			 * function to check connection, currently not used
			 * 
			 * @return JSON
			 */ 
			public function ping(){
				$url = $this->conf['url']."/api/ping";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER,  "Content-Type: multipart/form-data;");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				$output = curl_exec($ch);
				return json_decode($output, true);
			}
			
			
			public function get_conf(){
				return $this->conf;
			}
			
			/*
			function api_label_script_handler(){
				//$api = new SendleAPI();
				$order_id = $_POST['id'];
				$data = $this->get_label($order_id);
				echo base64_encode($data);
				
				
				wp_die();
			}*/
			
			/**
			 * get_label
			 *
			 * Retreive the sendle label link from the saved metadata and return PDF binary. This
			 * function is called in a new window direct to ajax.php and therefore can re-write the
			 * header information
			 * 
			 *
			 * @param integer   $wc_order_id  The WooCommerce order id
			 * @param string 	$size default is cropped, alternative is a4
			 * 
			 * @return PDF binary of label
			 */ 
			
			public function get_label($wc_order_id, $size='cropped'){
				
				$sendle_order_id = $this->get_sendle_meta($wc_order_id, '_sendle_order_id');
				$url = $this->conf['url']."/api/orders/$sendle_order_id/";
				
				switch ($size){
					case 'a4':
						$url .= "labels/$size.pdf";
						break;
					default:
						// if cropped or anything else, return cropped
						$url .= "labels/cropped.pdf";
						break;
				}
				if(isset($url)){
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
					$output = curl_exec($ch);
					$info = curl_getinfo($ch);
					curl_close($ch);

					header('Cache-Control: public'); 
					header('Content-type: application/pdf');
					header("Content-Disposition: inline; filename='".$wc_order_id.".pdf");
					header('Content-Length: '.strlen($output));
				}else
					$output = "Data error.";
				
				echo ($output);
				return null;
			}
			
			/**
			 * get_order
			 *
			 * Retreive order information from the API for an already created booking. On success
			 * the returned order information is saved into the order metadata
			 * 
			 *
			 * @param integer   $wc_order_id  The WooCommerce order id
			 * @param string 	$order_id 		optional sendle order id
			 * 
			 * @return array with formatted result
			 */ 
			 
			public function get_order($wc_order_id, $order_id = null){
				if(!$order_id){
					$order_id = $this->get_sendle_meta($wc_order_id, '_sendle_order_id');
				}
				$url = $this->conf['url']."/api/orders/$order_id";
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER,  "Content-Type: multipart/form-data;");
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				$output = curl_exec($ch);
				$info = curl_getinfo($ch);
				curl_close($ch);
				$result = $this->error_handler($output,$info);
				if ($result['success'] == true){
					$this->save_order($wc_order_id, $result['output']);
				}
				return $result;

			}
			
			
			/**
			 * cancel_order
			 *
			 * Cancels a booking. Checks if the order is cancellable first. This meta should be up 
			 * to date as the only way to reach the cancel button is after running the get_order function
			 * that polls the API for the latest information.
			 *
			 * @param integer   $wc_order_id  The WooCommerce order id
			 * @param string 	$size default is cropped, alternative is a4
			 * 
			 * @return JSON of result
			 */ 
			public function cancel_order($wc_order_id, $order_id = null){
				$is_cancellable = $this->get_sendle_meta($wc_order_id)['details']['scheduling']['is_cancellable'];
				if(!$order_id){
					$order_id = $this->get_sendle_meta($wc_order_id, '_sendle_order_id');
				}
				
				if($is_cancellable){
					$url = $this->conf['url']."/api/orders/$order_id";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
					curl_setopt($ch, CURLOPT_HEADER,  "Content-Type: multipart/form-data;");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
					$output = curl_exec($ch);
					$info = curl_getinfo($ch);
					curl_close($ch);
					$result = $this->error_handler($output,$info);
					if ($result['success']){
						$this->save_order($wc_order_id, $result['output']);
					}
					return $result;
				}else{
					$result['success'] = false;
					$result['output'] = '';
					return $false;
				}

			}
			
			/**
			 * get_quote
			 *
			 * Retreives the quote information. Not implemented.
			 * 
			 *
			 * @param string   $address  Receivers address
			 * @param float 	$volume pickup volume
			 * @param float		$weight pickup weight
			 * 
			 * @return JSON array of result
			 */ 
			public function get_quote($address, $volume = null, $weight = null){
				//$var_is_greater_than_two = ($var > 2 ? true : false); // returns true
				$volume = ($volume ? $volume : $this->conf['pickup']['volume']);
				$weight = ($weight ? $weight : $this->conf['pickup']['weight']);
				$url = $this->conf['url']."/api/quote?";
				$data = array(
					"pickup_suburb" => $this->conf['pickup']['city'],
					"pickup_postcode" => $this->conf['pickup']['postcode'],
					"delivery_suburb" => $address['city'],
					"delivery_postcode" => $address['postcode'],
					"kilogram_weight" => $weight,
					"cubic_metre_volume" => $volume
				);
				foreach ($data as $value => $key){
					$url .= $value."=".$key."&";
				}
				$url=substr($url, 0, -1);
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data;","Accept: application/json;"));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				$output = curl_exec($ch);
				$info = curl_getinfo($ch);
				curl_close($ch);
				// this type of error handling is old and needs to be updated!!
				return $this->error_handler(json_decode($output, true), $info);
				
				
			}
			/**
			 * get_sendle_meta
			 *
			 * Retreive the meta from the post's meta. Needs to be re-written for efficiency
			 * 
			 *
			 * @param int   $order_id  The WooCommerce order id
			 * @param string 	$meta_key optional, will return on the specified key value
			 * 
			 * @return array meta data
			 */ 
			public function get_sendle_meta($order_id, $meta_key = null){
				$result['details'] = get_post_meta($order_id, '_sendle_order_details', true);
				$result['order_id'] = get_post_meta($order_id, '_sendle_order_id', true);
				$result['state'] = get_post_meta($order_id, '_sendle_order_state', true);
				switch ($meta_key){
					case '_sendle_order_details':
						return $result['details'];
						break;
					case '_sendle_order_id':
						return $result['order_id'];
						break;
					case '_sendle_order_state':
						return $result['state'];
						break;
					default:
						return $result;
				}
				return $result;
			}
			
			/**
			 * save_order
			 *
			 * Save Sendle booking information into the post's meta
			 * 
			 *
			 * @param integer   $order_id  The WooCommerce order id
			 * @param string 	$details booking details returned by API
			 * 
			 * @return null
			 */ 
			public function save_order($order_id, $details){

				if (!add_post_meta($order_id, '_sendle_order_details', $details, true)) {
					update_post_meta($order_id, '_sendle_order_details', $details);
				}
				if (!add_post_meta($order_id, '_sendle_order_id', $details['order_id'], true)) {
					update_post_meta($order_id, '_sendle_order_id', $details['order_id']);
				}
				if (!add_post_meta($order_id, '_sendle_order_state', $details['state'], true)) {
					update_post_meta($order_id, '_sendle_order_state', $details['state']);
				}
				
			}
			
			/**
			 * create_order
			 *
			 * This is the main purpose of the API, creating a booking with Sendle! Takes the WooCommerce
			 * order address and creates a Sendle booking. 
			 * 
			 *
			 * @param integer   $order_id  The WooCommerce order id
			 * @param array 	$details the WooCommerce order customer shipping details
			 * 
			 * @return JSON of result
			 */ 
			public function create_order($order_id, $details){
				
				$order_exists = $this->get_sendle_meta($order_id, '_sendle_order_state');
				
				if ($order_exists && $order_exists != 'Cancelled'){
					// this will break the jQuery response, needs to be modified to a consistent error response
					return false;
				}
				
				$url = $this->conf['url']."/api/orders";
				
				// Sendle won't accept receiver_instructions for any weight less than 0.5 kg
				if ($details['pickup_weight'] <= 0.5){
					$details['receiver_instructions'] = '';
				}
				
				// build array of data in the format required by the API
				$data = array("pickup_date" => $details['general_pickup_date'], 
				"description" => $details['general_description'], "kilogram_weight" => $details['pickup_weight'],
					"cubic_metre_volume" => $details['pickup_volume'],
					"customer_reference" => $details['general_customer_reference'],
					"metadata" => array(
					  "your_data" => $details['general_customer_reference']
					),
					"sender" => array(
					  "contact" => array(
						"name" => $details['pickup_name'],
						"phone" => $details['pickup_phone']
					  ),
					  "address" => array(
						"address_line1" => $details['pickup_address_1'],
						"address_line2" => $details['pickup_address_2'],
						"suburb" => $details['pickup_city'],
						"state_name" => $details['pickup_state'],
						"postcode" => $details['pickup_postcode'],
						"country" => "Australia"	
					  ),
					  "instructions" => $details['pickup_instructions']
					),
					"receiver" => array(
					  "contact" => array(
						"name" => $details['receiver_first_name']." ".$details['receiver_last_name'],
						"email" => $details['receiver_email']
					  ),
					  "address" => array(
						"address_line1" => $details['receiver_address_1'],
						"address_line2" => $details['receiver_address_2'],
						"suburb" => $details['receiver_city'],
						"state_name" => $details['receiver_state'],
						"postcode" => $details['receiver_postcode'],
						"country" => "Australia"
					  ),
					  "instructions" => $details['receiver_instructions']
					)
				  );
				
				
				$data_json = json_encode($data);
				
				
				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
				curl_setopt($ch, CURLOPT_USERPWD, $this->conf['user'].":".$this->conf['apikey']);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: '.strlen($data_json),"Accept: application/json;"));
				curl_setopt($ch, CURLOPT_POSTFIELDS, ($data_json));
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
			
				$output = curl_exec($ch);
				$info = curl_getinfo($ch);

				curl_close($ch);
				$result = $this->error_handler($output, $info);
				if ($result['success']){
					$this->save_order($order_id, json_decode($output, true));
				}
				return $result;
	
			}
			
			/**
			 * error_handler
			 *
			 * This function checks for a curl error and handles the response in a standardised way.
			 * Response is used by AJAX to assist with front end information.
			 * 
			 *
			 * @param integer   $wc_order_id  The WooCommerce order id
			 * @param string 	$size default is cropped, alternative is a4
			 * 
			 * @return associative array (success =>, output =>, header=>, msg=>)
			 */ 
						
			public function error_handler($curl_input, $curl_info){
				
				
				$curl_result = json_decode($curl_input, true);
				
				$result['curl_info'] =  $curl_info;
				
				switch ($curl_info['http_code']){
					case 200:
						$result['success'] = true;
						$result['output'] = $curl_result;
						$result['header'] = "<h3>Status - ".$result['output']['state']."</h3><p>Please review details below.</p>";
						$result['msg'] = "<p><h4>Details:</h4></p>".$curl_input;//.$this->format_message($result['output'], false);
						break;
					case 201:
						$result['success'] = true;
						$result['output'] = $curl_result;
						$result['header'] = "<h3>Success</h3><p>Please review returned details below.</p>";
						$result['msg'] = "<p><h4>Details:</h4></p>".$curl_input;//.$this->format_message($result['output'], false);
						break;
					case 401:
						$result['success'] = false;
						$result['output'] = $curl_result;
						$result['header'] = "<h3>Authorisation Error</h3><p>Please check API User and API Key is correct in the WooCommerce settings page <a href='".admin_url("admin.php?page=wc-settings&tab=sendle_api")."'>here</a>.</p>";
						$result['msg'] .= "<p><b>Error: </b>".$result['output']['error']."<br>";
						$result['msg'] .= $result['output']['error_description']."</p>";
						if(isset($result['output']['messages'])){
							$result['msg'] .= "<p><h4>Details:</h4></p>".$curl_input;							
						}
						break;
					case 422:
						$result['success'] = false;
						$result['output'] = $curl_result;
						$result['header'] = "<h3>Unprocessable Field</h3><p>Please review and resolve the messages below before re-submitting the form.</p>";
						$result['msg'] .= "<p><b>Error: </b>".$result['output']['error']."<br>";
						$result['msg'] .= $result['output']['error_description']."</p>";
						if(isset($result['output']['messages'])){
							$result['msg'] .= "<p><h4>Details:</h4></p>".$curl_input;
							
						}
						break;
					case 400:
							$result['success'] = false;
						$result['output'] = $curl_result;
						$result['header'] = "<h3>Unprocessable Field</h3><p>Please review and resolve the messages below before re-submitting the form.</p>";
						$result['msg'] .= "<p><b>Error: </b>".$result['output']['error']."<br>";
						$result['msg'] .= $result['output']."</p>";
						
						break;
					default:		
						$result['success'] = false;
						$result['header'] = "<h3>Error</h3><p>Please review and resolve the messages below before re-submitting the form.</p>";
						$result['msg'] = $curl_result;
						break;
				}
				
				return $result;
			}
			
			public function format_message($input, $iterate=true){
				$html = "";
				$depth = "&nbsp;";
				foreach ($input as $key => $item){
					
					$html .= "<b>$key</b>".(is_array($item) ? "" : ": $item")."<br>";
					while((is_array($item) || is_object($item)) && $iterate){
						foreach($item as $key => $value){
							if(is_array($value) || is_object($value))
								$html.= $depth."<b>$key</b><br>";
							else
								$html .= $depth."<b>$key</b>: $value<br>";
						}
						$item = $value;	
						$depth .= "&nbsp;";
					}
				}
				return $html;
			}					
		}
	}
	
}



?>
