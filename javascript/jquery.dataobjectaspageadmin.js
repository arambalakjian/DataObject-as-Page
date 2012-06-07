(function($) {
$(document).ready(function() {   
	var doList = function() {
		var currentModel = $('#SearchForm_holder .tabstrip a').attr('href');
		var currentModelName = currentModel.substr(currentModel.indexOf("_") + 1);
		var strFormname = "#Form_SearchForm_" + currentModelName;
		$(strFormname).submit();
		return false;
	}
   
   	$('#ModelClassSelector').live("change",doList);
   	$('#list_view').live("click",doList);
   	if($('#list_view_loading').length) {
		doList();
   	}
   	$('button[name=action_clearsearch]').click(doList);
   
   	//Go back to list button
   	$('#Form_EditForm_action_listview').live('click',doList);
	
	//Save to draft Button
	$('#right input[name=action_doSaveToDraft],#right input[name=action_doCreate]').live('click', function(){
		var form = $('#right form');
		var formAction = form.attr('action') + '?' + $(this).fieldSerialize();
		
		// @todo TinyMCE coupling
		if(typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
		
		// Post the data to save
		$.post(formAction, form.formToArray(), function(result){
			// @todo TinyMCE coupling
			tinymce_removeAll();
			
			$('#right #ModelAdminPanel').html(result);

			if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
				statusMessage(ss.i18n._t('ModelAdmin.VALIDATIONERROR', 'Validation Error'), 'bad');
			} else {
				statusMessage(ss.i18n._t('ModelAdmin.SAVED', 'Saved'), 'good');
			}

			// TODO/SAM: It seems a bit of a hack to have to list all the little updaters here. 
			// Is jQuery.live a solution?
			Behaviour.apply(); // refreshes ComplexTableField
			if(window.onresize) window.onresize();
		}, 'html');

		return false;
	});
    
	//Do Delete Item button 
	$('#right input[name=action_doDeleteItem]').live('click', function(){
		var confirmed = confirm(ss.i18n._t('ModelAdmin.REALLYDELETE', 'Really delete?'));
		if(!confirmed) {
			$(this).removeClass('loading')
			return false;
		}

		var form = $('#right form');
		var formAction = form.attr('action') + '?' + $(this).fieldSerialize();

        // The POST actually handles the delete
		$.post(formAction, form.formToArray(), function(result){
		    // On success, the panel is refreshed and a status message shown.
			$('#right #ModelAdminPanel').html(result);
			
			statusMessage(ss.i18n._t('ModelAdmin.DELETED', 'Successfully deleted'));
    		$('#form_actions_right').remove();
            // To do - convert everything to jQuery so that this isn't needed
			Behaviour.apply(); // refreshes ComplexTableField
			
			return doList();
		});
		return false();
	});
	
	//Duplicate Button
	$('#right input[name=action_duplicate],#right input[name=action_doPublish],#right input[name=action_doUnpublish]').live('click', function(){
		var form = $('#right form');
		var formAction = form.attr('action') + '?' + $(this).fieldSerialize();
		
		// @todo TinyMCE coupling
		if(typeof tinyMCE != 'undefined') tinyMCE.triggerSave();
		
		// Post the data to save
		$.post(formAction, form.formToArray(), function(result){
			// @todo TinyMCE coupling
			tinymce_removeAll();
			
			$('#right #ModelAdminPanel').html(result);

			if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
				statusMessage('Validation Error', 'bad');
			} else {
				statusMessage('Done!', 'good');
			}

			// TODO/SAM: It seems a bit of a hack to have to list all the little updaters here. 
			// Is jQuery.live a solution?
			Behaviour.apply(); // refreshes ComplexTableField
			if(window.onresize) window.onresize();
		}, 'html');

		return false;
	});
	
});
})(jQuery);