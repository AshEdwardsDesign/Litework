jQuery(function(){
	
	var form = jQuery('#cfgen-form-2');
	
	function cfgen_isScrolledIntoView(elem){
		
		var docViewTop = jQuery(window).scrollTop();
		var docViewBottom = docViewTop + jQuery(window).height();
	
		var elemTop = jQuery(elem).offset().top;
		var elemBottom = elemTop + jQuery(elem).height();
	
		return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
	}
	
	if(!jQuery.isFunction(jQuery.fn.on)){
	
		var jquery_version_error_message = 'The form requires at least jQuery 1.7.2 to work properly.<br>jQuery '+jQuery().jquery+' has been loaded instead.';
		
		form.prepend('<p style="background-color:#FF0000; color:#fff; font-family:Verdana; Arial; font-size:12px; padding:4px; ">'+jquery_version_error_message+'</p>');
	}
	
	form.find('.cfgen-uploadfilename').val(''); // FF may keep the file name in the cfgen-uploadfilename input after submitting and refreshing the page
	
	form.on('click', 'img.cfgen-captcha-refresh', function(){
		
		form.find('img.cfgen-captcha-img').prop('src','cfgen-form-2/inc/captcha.php?r='+Math.random());
		
	});
	

	form.on('click', 'input[type="submit"].cfgen-submit', function(){
		
		var submit_btn =  jQuery(this);
		submit_btn.hide();
		
		var formcontainer = submit_btn.closest('div.cfgen-form-container');
		
		var loading = formcontainer.find('div.cfgen-loading');
		loading.show();
		
		formcontainer.find('div.cfgen-errormessage').hide().remove();
		
		var form_values = [];
		var deleteuploadedfile_value = [];
		var isempty = {};

		formcontainer.find('.cfgen-form-value').each(function(){

			var this_element = jQuery(this);
			var e_c = this_element.closest('div.cfgen-e-c');
			var e_label = e_c.find('.cfgen-label-value');
			var e_label_val = e_label.html();

			if(!e_label_val){
				e_label_val = this_element.prop('placeholder');
			}
			
			var element_properties = {};
			element_properties['elementlabel_id'] = e_label.closest('label').prop('id');
			element_properties['elementlabel_value'] = e_label_val;
			element_properties['element_value'] = ''; // default value	
			
			if(this_element.is('input[type="radio"]')){ element_properties['element_type'] = 'radio'; }
			if(this_element.is('input[type="checkbox"]')){ element_properties['element_type'] = 'checkbox'; }
			if(this_element.hasClass('cfgen-type-selectmultiple')){ element_properties['element_type'] = 'selectmultiple'; }
			if(this_element.hasClass('cfgen-uploadfilename')){ element_properties['element_type'] = 'upload'; }
			if(this_element.hasClass('cfgen-type-date')){ element_properties['element_type'] = 'date'; }
			if(this_element.hasClass('cfgen-rating-c')){ element_properties['element_type'] = 'rating'; }
			if(this_element.hasClass('cfgen-type-terms')){ element_properties['element_type'] = 'terms'; }

			// Terms & conditions
			if(element_properties['element_type'] === 'terms'){

				element_properties['elementlabel_id'] = e_c.find('div.cfgen-e-set').prop('id');
				element_properties['elementlabel_value'] = e_c.find('label').text();

				if(e_c.find('input[type="checkbox"]:checked').length){
					element_properties['element_value'] = 'Checked';
				} // We don't set a default value when it is unchecked to prevent false positive validation when the element is required
				
				element_properties['element_id'] = this_element.prop('id');

				form_values.push(element_properties);
			}

			// Rating
			if(element_properties['element_type'] === 'rating'){
				var ratings = this_element.find('.fa');
				var rating_val = ratings.filter('.cfgen-rating-selected').length;
				var rating_count = ratings.length;
				rating_val = (rating_val === 0) ? '' : rating_val;
				rating_val = rating_val ? rating_val+('/'+rating_count) : '';
				element_properties['element_id'] = this_element.prop('id');
				element_properties['element_value'] = rating_val;
				form_values.push(element_properties);
			}
			
			// Uploads
			if(element_properties['element_type'] === 'upload'){
			
				element_properties['element_id'] = this_element.prop('name');

				element_properties['element_value'] = jQuery.trim(this_element.val());

				var deletefile = this_element.closest('div.cfgen-input-group').find('.cfgen-uploaddeletefile').val();

				jQuery.extend(element_properties, {'deletefile':deletefile});

				form_values.push(element_properties);
			}
			
			// Input text, Textarea, Select
			if(this_element.is('.cfgen-type-text, .cfgen-type-email, .cfgen-type-date, .cfgen-type-url, .cfgen-type-textarea, .cfgen-type-select, .cfgen-type-hidden')){

				element_properties['element_id'] = this_element.prop('id');

				element_properties['element_value'] = this_element.val();

				form_values.push(element_properties);
			}			
			
			if(jQuery.inArray(element_properties['element_type'], ['radio', 'checkbox', 'selectmultiple']) != -1){

				element_properties['element_id'] = this_element.prop('name');

				if(!isempty.hasOwnProperty(element_properties['element_id'])){ isempty[element_properties['element_id']] = true;}
			}
			
			// Radio, Checkbox
			if(element_properties['element_type'] === 'radio' || element_properties['element_type'] === 'checkbox'){
				
				element_properties['element_value'] = this_element.val();
				
				if(this_element.is(':checked') && element_properties['element_value']){
					// ^^ && value to prevent the required field error message from appearing twice if the value of the checkbox is ""
					isempty[element_properties['element_id']] = false;
					form_values.push(element_properties);
				}
				
				if(this_element.is(e_c.find('input[name='+element_properties['element_id']+']:last'))){
					if(isempty[element_properties['element_id']]){
						element_properties['element_value'] = '';
						form_values.push(element_properties);
					}
				}
			}			
			
			// Select multiple
			if(element_properties['element_type'] === 'selectmultiple'){

				this_element.find('option:selected').each(function(){
					
					var option = jQuery(this);
					
					element_properties['element_value'] = option.val();
					
					if(element_properties['element_value']){
						
						isempty[element_properties['element_id']] = false;
						
						// Passing a new object instead of passing a reference to the same object multiple times
						var o = jQuery.extend({}, element_properties , {'element_type':'selectmultiple'});
						
						form_values.push(o);
					}
				});
				
				if(isempty[element_properties['element_id']]){
					form_values.push(element_properties);
				}
			}
			
			// Time
			if(this_element.hasClass('cfgen-type-time')){
				
				var time_hour = e_c.find('select.cfgen-time-hour');
				var time_minute = e_c.find('select.cfgen-time-minute');
				var time_ampm_v = e_c.find('select.cfgen-time-ampm').val();
				
				if(time_ampm_v == undefined) time_ampm_v = ''; // no quote on undefined
				
				element_properties['element_id'] = time_hour.prop('name');
				element_properties['element_value'] = time_hour.val()+':'+time_minute.val()+' '+time_ampm_v;
				
				form_values.push(element_properties);
			}
			
		});
		
		// Catch the list of uploaded files to delete
		formcontainer.find('.cfgen-deleteuploadedfile').each(function(){
			deleteuploadedfile_value.push(jQuery(this).val());
		});
		
		
		// Captcha
		var captcha_img = '';
		
		var captcha_input = '';
		
		if(formcontainer.find('img.cfgen-captcha-img').length){
			captcha_img = 1;
			captcha_input = formcontainer.find('input[type="text"].cfgen-captcha-input').val();
		}
		
		
		//console.log(deleteuploadedfile_value);
		// console.log(form_values);
		
		var post = { 
					'captcha_img':captcha_img,
					'captcha_input':captcha_input,
					'form_values':form_values,
					'screen_width':screen.width,
					'screen_height': screen.height,
					'deleteuploadedfile':deleteuploadedfile_value
					};

		jQuery.post('cfgen-form-2/inc/form-validation.php',
					post,
					function(data){

						// console.log(data);

						var response = jQuery.parseJSON(data);

						if(response['status'] === 'ok'){
							
							if(response['redirect_url']){
								// we do not hide the loading animation because the redirection can take some time (prevents wondering what is happening)
								window.location.href = response['redirect_url'];
							} else{
								
								loading.hide();
								
								var elementcontainer_collection = formcontainer.find('.cfgen-e-c');
								
								elementcontainer_collection.each(function(){
								
									jQuery(this).slideUp('fast', function(){
		
										if(!--elementcontainer_collection.length){
											
											if(!cfgen_isScrolledIntoView('.cfgen-form-container')){
												jQuery('html, body').animate({scrollTop:formcontainer.offset().top}, 'fast');
											}	
											
											// not using text() to allow html tags like <br> or <a>
											jQuery('<div class="cfgen-validationmessage">'+response['message']+'</div>').appendTo(formcontainer.find('.cfgen-form-content'));
											
										}
										
									});
									
								});
							}

						} else{
							
							loading.hide();
							
							submit_btn.show();
							
							for(var i=0; i<response['message'].length; i++){
								
								// first removed: error messages is positionned above the first name* found
								// IE does not like (name[]:first), we use .first() instead ( .filter(':first') is ok too)
								// id selector for rating element
								var optioncontainer = jQuery('[name='+response['message'][i]['element_id']+'], [id='+response['message'][i]['element_id']+']').first().closest('div.cfgen-e-set');
								
								// text() escape special characters like < or > that would break the message formatting
								jQuery('<div class="cfgen-errormessage"></div>').text(response['message'][i]['errormessage']).prependTo(optioncontainer).fadeIn();
							}	
							
							// scrolls to the first error message
							if(!cfgen_isScrolledIntoView('#'+response['message'][0]['elementlabel_id'])){
								jQuery('html, body').animate({scrollTop: jQuery('#'+response['message'][0]['elementlabel_id']).offset().top},'fast'); 	
							}
	
						}
					} /* end function data */
				); /* end jQuery.post */
	}); /* end click submit */
	
	
	
	// DELETE UPLOADED FILE
	if(form.find('.cfgen-uploadfilename').length){
		
		form.on('click', '.cfgen-deleteupload', function(){
			
			var delete_btn = jQuery(this);
			
			var uploadsuccess_c = delete_btn.closest('div.cfgen-uploadsuccess-c');
			
			var filename = uploadsuccess_c.find('.cfgen-deleteupload-filename').val();
			
			var element_c = delete_btn.closest('div.cfgen-input-group');
			
			// to add the filename to the list of files to delete
			// the .cfgen-deleteuploadedfile input can also be added in case of chain upload (handlers.js)
			element_c.append('<input value="'+filename+'" type="hidden" class="cfgen-deleteuploadedfile" />');
			
			// reset the upload input that contains the filename value
			element_c.find('.cfgen-uploadfilename').val('');
			
			uploadsuccess_c.remove();
	
		});
	}
	
});