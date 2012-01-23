<?php

class DataObjectAsPageAdmin extends ModelAdmin 
{
	public static $record_controller_class = "DataObjectAsPageAdmin_RecordController";
	
	public function init() 
	{
	    parent::init();
		
	    // Remove all the junk that will break ModelAdmin
	    Requirements::javascript(MOD_DOAP_DIR . '/javascript/jquery.dataobjectaspageadmin.js');
	}
}

class DataObjectAsPageAdmin_RecordController extends ModelAdmin_RecordController
{	
	public function doPublish($data, $form, $request)
	{
		$form->saveInto($this->currentRecord);
		
		$this->currentRecord->Status = 'Published';	

		$this->currentRecord->write();
		
        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
	}	
	
	public function doUnpublish($data, $form, $request)
	{
		$form->saveInto($this->currentRecord);
		
		$this->currentRecord->Status = 'Draft';	

		$this->currentRecord->write();
		
        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
	}	
	
	public function duplicate($data, $form, $request) { 
         
        //Duplicate the object
        $Clone = $this->currentRecord->duplicate();
 
        //Change the title so we know we are looking at the copy
        $Clone->Title = 'Copy of ' . $this->currentRecord->Title;
        $Clone->Status = 'Draft';
         
        $Clone->write();
 
        //Set the view to be our new duplicate
        $this->currentRecord = $Clone;
 
        if(Director::is_ajax()) {
           	return $this->edit($request);
        } else {
            Director::redirectBack();
        }
    }	
}