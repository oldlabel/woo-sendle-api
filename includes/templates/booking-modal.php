
<div id="spinner" class="loader"></div>
<div id="modal-background" class="modal-background"></div>
<div id="modal-booking" class="modal-content">
    
    <div class="modal-header"><h2 id="modal-title">Sendle Booking Form<button id="modal-close" class="modal-close right">&times;</button></h2></div>
    <div class="modal-body">
		<div class="buttons"><p>
		<div class="sendle_title"></div>
		<div class="sendle_label">
			<form name="sendle_label" id="sendle_label" target="_blank" action="<?php echo admin_url( 'admin-ajax.php' ) ; ?>" method="post">
			<input type="submit" name="submit_label" class="sendle_button" value="Get Label">
			<?php echo wp_nonce_field('sendle-api-nonce'); ?>
			<input type="hidden" name="action" value="sendle_api_ajax">
			<input type="hidden" name="general_id" value="">
			<input type="hidden" name="op" value="label">
			</form>
		</div>
		<div class="sendle_cancel">
			<form name="sendle_cancel_booking" id="sendle_cancel_booking" action="" method="post">
			<input type="submit" name="submit_cancel" class="sendle_button_cancel" value="Cancel Order">
			<?php echo wp_nonce_field('sendle-api-nonce'); ?>
			<input type="hidden" name="action" value="sendle_api_ajax">
			<input type="hidden" name="general_id" value="">
			<input type="hidden" name="op" value="cancel">
			</form>
		</div>
		</p>
		</div>
		<div class="clearfix"></div>
		<div id="sendle_feedback"></div>
		
	
		<div id="booking_form" class="booking_form">
			<form name='sendle_booking' id='sendle_booking' action='' method='post'>
			<input type='submit' name='submit' class="sendle_button" value='Review then Submit!'>
			<?php echo wp_nonce_field('sendle-api-nonce'); ?>
			<input type="hidden" name="action" value="sendle_api_ajax">
			<input type="hidden" name="general_id" value="">
			<input type="hidden" name="op" value="order">
			<table>
				<tr><td colspan=2><b>General Booking Details</b></td></tr>
				<tr><td>Pickup Date:</td><td colspan=3><input type='text' name='general_pickup_date' id='general_pickup_date' value=''></td></tr>
				<tr><td>Description:</td><td colspan=3><input type='text' name='general_description' id='general_description' value=''></td></tr>
				<tr><td>Customer Reference</td><td colspan=3><input type='text' name='general_customer_reference' id='general_customer_reference' value=''></td></tr>
				<tr><td>Weight:</td><td><select name='pickup_weight' id='pickup_weight'>	
					<option value='0.5'>Satchel - 0.5KG, 0.002m3</option>
					<option value='2' >Shoebox - 2KG, 0.008m3</option>
					<option value='5' >Briefcase - 5KG, 0.02m3</option>
					<option value='10' >Carry-on - 10KG, 0.04m3</option>
					<option value='25' >Luggage - 25KG, 0.1m3</option>
					</select></td></tr>
				<tr><td>volume:</td><td colspan=3><input type='text' value='' name='pickup_volume' id='pickup_volume'></td></tr>
			</table>

			<div class="sendle-row">
				<div class="sendle-column">
				<table id="receiver">
					<tr><td colspan=2><b>Receiver details</b></td></tr>
					<tr><td>first_name:</td><td><input type='text' value='' name='receiver_first_name' id='receiver_first_name'></td></tr>
					<tr><td>last_name:</td><td><input type='text' value='' name='receiver_last_name' id='receiver_last_name'></td></tr>
					<tr><td>company:</td><td><input type='text' value='' name='receiver_company' id='receiver_company'></td></tr>
					<tr><td>address_1:</td><td><input type='text' value='' name='receiver_address_1' id='receiver_address_1'></td></tr>
					<tr><td>address_2:</td><td><input type='text' value='' name='receiver_address_2' id='receiver_address_2'></td></tr>
					<tr><td>city:</td><td><input type='text' value='' name='receiver_city' id='receiver_city'></td></tr>
					<tr><td>state:</td><td><input type='text' value='' name='receiver_state' id='receiver_state'></td></tr>
					<tr><td>postcode:</td><td><input type='text' value='' name='receiver_postcode' id='receiver_postcode'></td></tr>
					<tr><td>country:</td><td><input type='text' value='' name='receiver_country' id='receiver_country'></td></tr>
					<tr><td>Instructions</td><td><input type='text' name='receiver_instructions' value=''></td></tr>
					<tr><td>Email</td><td><input type='text' name='receiver_email' value=''></td></tr>
				</table>
				</div>
				<div class="sendle-column">
				<table id="pickup">
					<tr><td colspan=2><b>Sender details</b></td></tr>
					<tr><td>name:</td><td><input type='text' value='' name='pickup_name' id='pickup_name'></td></tr>
					<tr><td>phone:</td><td><input type='text' value='' name='pickup_phone' id='pickup_phone'></td></tr>
					<tr><td>address_1:</td><td><input type='text' value='' name='pickup_address_1' id='pickup_address_1'></td></tr>
					<tr><td>address_2:</td><td><input type='text' value='' name='pickup_address_2' id='pickup_address_2'></td></tr>
					<tr><td>city:</td><td><input type='text' value='' name='pickup_city' id='pickup_city'></td></tr>
					<tr><td>postcode:</td><td><input type='text' value='' name='pickup_postcode' id='pickup_postcode'></td></tr>
					<tr><td>state:</td><td><input type='text' value='' name='pickup_state' id='pickup_state'></td></tr>
					<tr><td>country:</td><td><input type='text' value='' name='pickup_country' id='pickup_country'></td></tr>
					<tr><td>instructions:</td><td><input type='text' value='' name='pickup_instructions' id='pickup_instructions'></td></tr>
				</table>
				</div>
			</form>
			</div>
    
		</div>
	</div>
    
</div>










