<?php

class DataObjectAsPageAdmin extends ModelAdmin 
{
	public static $record_controller_class = "DataObjectAsPageAdmin_RecordController";
	protected $resultsTableClassName = 'DataObjectAsPageTableField';
	
	public function init() 
	{
	    parent::init();
		
	    // Remove all the junk that will break ModelAdmin
	    Requirements::javascript(MOD_DOAP_DIR . '/javascript/jquery.dataobjectaspageadmin.js');
		Requirements::CSS(MOD_DOAP_DIR . '/css/dataobjectaspageadmin.css');
	}
}

class DataObjectAsPageAdmin_RecordController extends ModelAdmin_RecordController
{	
	public function doPublish($data, $form, $request)
	{
		$record = $this->currentRecord;
		
		if($record && !$record->canPublish())
		        return Security::permissionFailure($this);	
			
		$form->saveInto($record);

		$record->doPublish();

        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
	}
	
	public function doSaveToDraft($data, $form, $request) {
		$form->saveInto($this->currentRecord);
		
		try {
			$this->currentRecord->write();
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
		}
			
		// Behaviour switched on ajax.
		if(Director::is_ajax()) {
			return $this->edit($request);
		} else {
			Director::redirectBack();
		}
	}
	
	public function doUnpublish($data, $form, $request)
	{
		$record = $this->currentRecord;
		
		if($record && !$record->canUnPublish())
		        return Security::permissionFailure($this);

		$record->doUnpublish();
		
        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
	}	
	
	public function doDeleteItem($data, $form, $request)
	{
		$record = $this->currentRecord;
		
		if($record && !$record->canDelete())
        	return Security::permissionFailure();
		
		$record->doDelete();
		
        if(Director::is_ajax()) {
            $this->edit($request);
        } else {
            Director::redirectBack();
        }
	}	
	
	public function duplicate($data, $form, $request) { 
        
        //Duplicate the object
        $Clone = $this->currentRecord->duplicate();

        //Set the view to be our new duplicate
        $this->currentRecord = $Clone;
 
        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
    	}
    	
    	public function EditForm() {
		$form = parent::EditForm();
		$fields = $form->Actions();
		$fields->removeByName('action_doSave');
		$fields->removeByName('action_doDelete');
		$fields->removeByName('action_goForward');
		$fields->removeByName('action_goBack');
		$form->setActions ($fields);	
		return $form;
	}	
}