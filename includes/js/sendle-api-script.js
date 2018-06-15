/***
 * jQuery to support Sendle API
 * 
 * @version	   1.01
 * @author     JRS <developer@oldlabel.com>
 * @license    http://www.gnu.org/licenses/  GNU General Public License
 * @link       https://www.oldlabel.com/woo-sendle-api
 
 */
jQuery(document).ready(function($) {

	// clear modals when background clicked
	$(".modal-background, .modal-close").click(function (e) {
		e.preventDefault();
		$('#modal-booking,.modal-background, #booking_form, #sendle_feedback, .sendle_	label').removeClass("active");
		
	});
	
	
	// when a sendle form is clicked
	$(".sendle_api_").click(function (e) {
		e.preventDefault();
		$(".modal-background").toggleClass("active");
		
		// get and parse url parameters
		paramURL = $(this).attr('href');
		params={};paramURL.replace(/[?&]+([^=&]+)=([^&]*)/gi,function(s,k,v){params[k]=v});
		
		// trying to book
		if (params['op']=='book'){
			// clean up previous HTML and toggle classes
			$("#sendle_feedback").empty();
			$("#modal-booking, #booking_form").toggleClass("active");
			if($("form#sendle_booking :input[name=general_id]").val()!=params['order_id']){
				$("form#sendle_booking")[0].reset();
			}
			
			// run ajax
			$.ajax({
			method: "GET",
			url: ajaxurl,
			data: {'action': 'sendle_api_ajax', 'id' : params['order_id'], 'op' : params['op'],'_wpnonce' : params['_wpnonce']}
			})
			.done(function( data ) {
				data = JSON.parse( data );
				// insert returned order data into booking form
				$("form#sendle_booking :input, form#sendle_booking :hidden").each(function(index) { var input = $(this); 
					if(input.attr("type")!= 'submit' &&  data[input.attr("name")] && input.attr("name") != 'pickup_weight')	
						input.val(data[input.attr("name")]);
					});
			}).fail(function( data ) {
				console.log('Failed AJAX Call :( /// Return Data: ' + data);
			});	
			
		}else if(params['op']=='status'){
			// clean previous HTML and toggle class
			$("#sendle_feedback").empty();
			$("#modal-booking").toggleClass("active");
			
			// run ajax
			$.ajax({
			method: "GET",
			url: ajaxurl,
			data: {'action': 'sendle_api_ajax', 'id' : params['order_id'], 'op' : params['op'], '_wpnonce' : params['_wpnonce'] }
			})
			.done(function( data ) {
				data = JSON.parse( data );

				$("#sendle_feedback").addClass("active");
				$("#sendle_feedback").html(data['header']+data['msg']);
				
				
				if(data['success']){
					if('is_cancellable' in data){
						$(".sendle_cancel").addClass("active");
						$("form#sendle_cancel_booking :input[name=general_id]").val(params['order_id']);
					}
				
					$(".sendle_label").addClass("active");
					$("form#sendle_label :input[name=general_id]").val(params['order_id']);

					$(".sendle_order_id_"+params['order_id']).removeClass (function (index, className) {
						return (className.match (/(^|\s)sendle_icon\S+/g) || []).join(' ');
					});
					// unnecessarily complex but this updates the icon and URL of the booking to the next stage without needing a page refresh
					$(".sendle_order_id_"+params['order_id']).addClass('sendle_icon_'+data['output']['state']).html('Sendle Order - '+data['output']['state']).attr("data-tip", 'Sendle Order - '+data['output']['state']);
				}
			})
			.fail(function( data ) {
				console.log('Failed AJAX Call :( /// Return Data: ' + data);
			});	
		}
	});
	
	
	$("#sendle_label").submit(function (e){
		// run ajax to retreive label	
		$.ajax({
		method: "POST",
		url: ajaxurl,
		data: $(this).serialize()
		})
		.done(function( data ) {
			// if successful, do nothing as the PHP response creates the binary PDF
		})
		.fail(function( data ) {
			console.log('Failed AJAX Call :( /// Return Data: ' + data);
		});
	});
	
	// create or cancel the booking
	$("#sendle_booking, #sendle_cancel_booking").submit(function (e){
		 //$('#spinner').addClass('loader');
		 e.preventDefault();
		 $('#modal-booking').zIndex(997);
		 $('.loader').css('display', 'block');
		 
		 // get operation to perform from form
		 var op = ($('input[name=op]', (this)).val());
		 
		 // run ajax
		 $.ajax({
			method: "POST",
			url: ajaxurl,
			data: $(this).serialize()
			})
			.done(function( data ) {
					
				data = JSON.parse( data );
			
			
				$('.loader').css('display', 'none');
				
				if(data['success'] == true){
					// if we're ordering
					if(op == "order"){
						$('form#sendle_booking :input').attr("disabled", true);
						if('is_cancellable' in data){
							$(".sendle_cancel, .sendle_label").toggleClass("active");
							$("form#sendle_cancel_booking :input[name=general_id]").val(params['order_id']);
						}
					}else{ 
						$('form#sendle_booking :input, form#sendle_cancel_booking :input,form#sendle_label :input').attr("disabled", true);
						$('.sendle_cancel, .sendle_label').removeClass('active');
					}
					
					// update icon to next order stage and change URL to 'get status'
					var a = $("a.sendle_order_id_"+params['order_id']).attr("href");
					$("a.sendle_order_id_"+params['order_id']).attr("href", a.replace(/\bop=([a-z0-9]*)/g, 'op=status'));
					$(".sendle_order_id_"+params['order_id']).removeClass (function (index, className) {
						return (className.match (/(^|\s)sendle_icon\S+/g) || []).join(' ');
					});
					$(".sendle_order_id_"+params['order_id']).addClass('sendle_icon_'+data['output']['state']).html('Sendle Order - '+data['output']['state']).attr("data-tip", 'Sendle Order - '+data['output']['state']);
					
					// if operation is cancel, change icon and url back to booking
					if (op=="cancel"){
						$("a.sendle_order_id_"+params['order_id']).attr("href", a.replace(/\bop=([a-z0-9]*)/g, 'op=book'));
						$('form#sendle_booking :input').attr("disabled", false);
					}
					$("#sendle_feedback").addClass("active sendle_success");
					$("#sendle_feedback").html(data['header']+data['msg']);
					
					$("form#sendle_label :input[name=general_id]").val(params['order_id']);
					
				}else{

					// output error
					$("#sendle_feedback").addClass("active sendle_error");
					$("#sendle_feedback").html(data['header']+data['msg']);
					
				}

			})
			.fail(function( data ) {
				
				console.log('Failed AJAX Call :( /// Return Data: ' + JSON.stringify(data));
			
			});
			$('.loader').css('display', 'none');
			$('#modal-booking').zIndex(1000);
		 
		e.preventDefault(); 
	 });
  });
