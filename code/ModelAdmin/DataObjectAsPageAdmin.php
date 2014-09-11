<?php

class DataObjectAsPageAdmin extends ModelAdmin 
{
	
	public function init() 
	{
	    parent::init();

	    //if versioned we need to tell ModelAdmin to read from stage
		if(Singleton($this->modelClass)->isVersioned)
		{		
			Versioned::reading_stage('Stage');
		}
		//Styling for preview links and status
		Requirements::CSS(MOD_DOAP_DIR . '/css/dataobjectaspageadmin.css');
	}
	
	public function getEditForm($id = null, $fields = null) {

	    $form = parent::getEditForm($id = null, $fields = null);    
	    
		if(Singleton($this->modelClass)->isVersioned)
		{
		    $listfield = $form->Fields()->fieldByName($this->modelClass);
		
			$gridFieldConfig = $listfield->getConfig();
		
		    $gridFieldConfig->getComponentByType('GridFieldDetailForm')
		        ->setItemRequestClass('VersionedGridFieldDetailForm_ItemRequest');		
				
			$gridFieldConfig->removeComponentsByType('GridFieldDeleteAction');
			$gridFieldConfig->addComponent(new VersionedGridFieldDeleteAction());
		}

	    return $form;
	}
}

/*
 * Temporary Fix for HTML editor Image/Link popup
 */
class ModelAdminHtmlEditorField_Toolbar extends HtmlEditorField_Toolbar {
	 
   public function forTemplate() { 
      return sprintf( 
         '<div id="cms-editor-dialogs" data-url-linkform="%s" data-url-mediaform="%s"></div>', 
         Controller::join_links($this->controller->Link(), $this->name, 'LinkForm', 'forTemplate'), 
         Controller::join_links($this->controller->Link(), $this->name, 'MediaForm', 'forTemplate') 
      ); 
   } 
}