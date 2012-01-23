

(function($) {
$(document).ready(function() {   
   var doList = function() {
     var currentModel = $('#ModelClassSelector').children('select');
     var currentModelName = $('option:selected', currentModel).val();
     var strFormname = "#Form_SearchForm" + currentModelName.replace('Form','');
     $(strFormname).submit();
     return false;
   }
   
   $('#ModelClassSelector').live("change",doList);
   $('#list_view').live("click",doList);
   if($('#list_view_loading').length) {
     doList();
   }
   $('button[name=action_clearsearch]').click(doList);

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