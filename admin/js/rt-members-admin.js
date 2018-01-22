// on doc ready
jQuery(function() {
	
	jQuery('#rt-members-type').change( function() {

		wpi_members_form_change();

	});	


});

function rt_members_form_change(){



	var $expire_action = jQuery('#rt-members-type').val();
	if($expire_action=='1'){// never
		jQuery("#rt-members-duration-settings, #rt-members-subscription-settings, #t-members-trial-settings").hide();
	}else if($expire_action=='2'){// after time
		jQuery("#rt-members-duration-settings,#rt-members-expire-settings").show();
		jQuery("#rt-members-subscription-settings, #rt-members-trial-settings").hide();
	}else if($expire_action=='3'){// sub canceled
		jQuery("#rt-members-duration-settings, #rt-members-subscription-settings, #rt-members-trial-settings,#rt-members-expire-settings").show();
	}


	var $expire_result = jQuery('#rt-members-expire-result').val();
	if($expire_result=='remove'){// never
		jQuery("#rt-members-add-role-settings").hide();
	}else if($expire_result=='add'){// after time
		jQuery("#rt-members-add-role-settings").show();
	}else if($expire_result=='remove-add'){// sub canceled
		jQuery("#rt-members-add-role-settings").show();
	}
}