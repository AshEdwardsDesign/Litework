jQuery(function(){
	
	var debug = false;
	
	var form = jQuery('#cfg-form-4');
	
	function cfgenwp_isScrolledIntoView(elem){
		
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
	
	form.find('.cfg-uploadfilename').val(''); // FF may keep the file name in the cfg-uploadfilename input after submitting and refreshing the page
	
	form.on('click', 'img.cfg-captcha-refresh', function(){
		
		form.find('img.cfg-captcha-img').prop('src','cfg-form-4/inc/captcha.php?r='+Math.random());
		
	});
	

	form.on('click', '.cfgenwp-submit', function(){
		
		var submit_btn =  jQuery(this);
		submit_btn.hide();
		
		var formcontainer = submit_btn.closest('div.cfg-form-container');
		
		var loading = formcontainer.find('div.cfg-loading');
		loading.show();
		
		formcontainer.find('div.cfg-errormessage').hide().remove();
		
		var form_values = [];
		var deleteuploadedfile_value = [];
		var isempty = {};

		formcontainer.find('.cfg-form-value').each(function(){

			var this_element = jQuery(this);
			var element_container = this_element.closest('div.cfg-element-container');
			var element_label = element_container.find('.cfgenwp-label-value');
			var element_label_val = element_label.html();

			if(!element_label_val){
				element_label_val = this_element.prop('placeholder');
			}
			
			var element_properties = {};
			element_properties['elementlabel_id'] = element_label.closest('label').prop('id');
			element_properties['elementlabel_value'] = element_label_val;
			element_properties['element_value'] = ''; // default value	
			
			if(this_element.is('input[type="radio"]')){ element_properties['element_type'] = 'radio'; }
			if(this_element.is('input[type="checkbox"]')){ element_properties['element_type'] = 'checkbox'; }
			if(this_element.hasClass('cfg-type-selectmultiple')){ element_properties['element_type'] = 'selectmultiple'; }
			if(this_element.hasClass('cfg-uploadfilename')){ element_properties['element_type'] = 'upload'; }
		
			
			// Uploads
			if(this_element.hasClass('cfg-uploadfilename')){
			
				element_properties['element_id'] = this_element.prop('name');
				
				element_properties['element_value'] = jQuery.trim(this_element.val());
				
				var deletefile = this_element.closest('div.cfg-element-content').find('.cfg-uploaddeletefile').val();
				
				jQuery.extend(element_properties, {'deletefile':deletefile});
				
				form_values.push(element_properties);
			}
			
			// Input text, Textarea, Select
			if(this_element.is('.cfg-type-text, .cfg-type-email, .cfg-type-date, .cfg-type-url, .cfg-type-textarea, .cfg-type-select, .cfg-type-hidden')){
			
				element_properties['element_id'] = this_element.prop('id');
				
				element_properties['element_value'] = this_element.val();
				
				form_values.push(element_properties);
			}			
			
			if(jQuery.inArray(element_properties['element_type'], ['radio', 'checkbox', 'selectmultiple']) != -1){
			
				element_properties['element_id'] = this_element.prop('name');
				
				if(!isempty.hasOwnProperty(element_properties['element_id'])){ isempty[element_properties['element_id']] = true;}
			}
			
			// Radio, Checkbox
			if(element_properties['element_type'] == 'radio' || element_properties['element_type'] == 'checkbox'){				
				
				element_properties['element_value'] = this_element.val();
				
				if(this_element.is(':checked') && element_properties['element_value']){
					// ^^ && value to prevent the required field error message from appearing twice if the value of the checkbox is ""
					isempty[element_properties['element_id']] = false;
					form_values.push(element_properties);
				}
				
				if(this_element.is(element_container.find('input[name='+element_properties['element_id']+']:last'))){
					if(isempty[element_properties['element_id']]){
						element_properties['element_value'] = '';
						form_values.push(element_properties);
					}
				}
			}			
			
			// Select multiple
			if(element_properties['element_type'] == 'selectmultiple'){

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
			if(this_element.hasClass('cfg-type-time')){
				
				var time_hour = element_container.find('select.cfg-time-hour');
				var time_minute = element_container.find('select.cfg-time-minute');
				var time_ampm_v = element_container.find('select.cfg-time-ampm').val();
				
				if(time_ampm_v == undefined) time_ampm_v = ''; // no quote on undefined
				
				element_properties['element_id'] = time_hour.prop('name');
				
				element_properties['element_value'] = time_hour.val()+':'+time_minute.val()+' '+time_ampm_v;
				
				form_values.push(element_properties);
			}
			
		});
		
		// Catch the list of uploaded files to delete
		formcontainer.find('.cfg-deleteuploadedfile').each(function(){
			deleteuploadedfile_value.push(jQuery(this).val());
		});
		
		
		// Captcha
		var captcha_img = '';
		
		var captcha_input = '';
		
		if(formcontainer.find('img.cfg-captcha-img').length){
			captcha_img = 1;
			captcha_input = formcontainer.find('input[type="text"].cfg-captcha-input').val();
		}
		
		
		//console.log(deleteuploadedfile_value);
		//console.log(form_values);
		
		var post = { 
					'captcha_img':captcha_img,
					'captcha_input':captcha_input,
					'form_values':form_values,
					'screen_width':screen.width,
					'screen_height': screen.height,
					'deleteuploadedfile':deleteuploadedfile_value
					};

		jQuery.post('cfg-form-4/inc/form-validation.php',
					post,
					function(data){

						if(debug){ console.log(data); }
						
						var response = jQuery.parseJSON(data);
							
						if(response['status'] == 'ok'){
							
							if(response['redirect_url']){
								// we do not hide the loading animation because the redirection can take some time (prevents wondering what is happening)
								window.location.href = response['redirect_url'];
							} else{
								
								loading.hide();
								
								var elementcontainer_collection = formcontainer.find('.cfg-element-container');
								
								elementcontainer_collection.each(function(){
								
									jQuery(this).slideUp('fast', function(){
		
										if(!--elementcontainer_collection.length){
											
											if(!cfgenwp_isScrolledIntoView('.cfg-form-container')){
												jQuery('html, body').animate({scrollTop:formcontainer.offset().top}, 'fast');
											}	
											
											// not using text() to allow html tags like <br> or <a>
											jQuery('<div class="cfg-validationmessage">'+response['message']+'</div>').appendTo(formcontainer.find('.cfg-form-content'));
											
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
								//var optioncontainer = jQuery('[name*='+response['message'][i]['element_id']+']').first().closest('div.cfg-element-content');
								var optioncontainer = jQuery('[name='+response['message'][i]['element_id']+']').first().closest('div.cfg-element-set');
								
								// text() escape special characters like < or > that would break the message formatting
								jQuery('<div class="cfg-errormessage"></div>').text(response['message'][i]['errormessage']).prependTo(optioncontainer).fadeIn();
							}	
							
							// scrolls to the first error message
							if(!cfgenwp_isScrolledIntoView('#'+response['message'][0]['elementlabel_id'])){
								jQuery('html, body').animate({scrollTop: jQuery('#'+response['message'][0]['elementlabel_id']).offset().top},'fast'); 	
							}
	
						}
					} /* end function data */
				); /* end jQuery.post */
	}); /* end click submit */
	
	
	
	// DELETE UPLOADED FILE
	if(form.find('.cfg-uploadfilename').length){
		
		form.on('click', '.cfg-deleteupload', function(){
			
			var delete_btn = jQuery(this);
			
			var uploadsuccess_c = delete_btn.closest('div.cfg-uploadsuccess-container');
			
			var filename = uploadsuccess_c.find('.cfg-deleteupload-filename').val();
			
			var element_c = delete_btn.closest('div.cfg-element-content');
			
			// to add the filename to the list of files to delete
			// the .cfg-deleteuploadedfile input can also be added in case of chain upload (handlers.js)
			element_c.append('<input value="'+filename+'" type="hidden" class="cfg-deleteuploadedfile" />');
			
			// reset the upload input that contains the filename value
			element_c.find('.cfg-uploadfilename').val('');
			
			uploadsuccess_c.remove();
	
		});
	}
	
});
