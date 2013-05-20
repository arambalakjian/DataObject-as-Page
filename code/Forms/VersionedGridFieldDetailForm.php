<?php

class VersionedGridFieldDetailForm extends GridFieldDetailForm {
	
	    
}

class VersionedGridFieldDetailForm_ItemRequest extends GridFieldDetailForm_ItemRequest {
	
	public function ItemEditForm() 
	{
    $form = parent::ItemEditForm();
    $actions = $this->record->getCMSActions();
    $form->setActions($actions);
    return $form;
	}
	
	/* 
	//Unable to get preview working for now
	public function LinkPreview() 
	{
		$record = $this->record;
				
		$baseLink = $record->CMSEditLink();
				
		return $baseLink;
	}
	*/
	
	public function doSave($data, $form) {
		$new_record = $this->record->ID == 0;
		$controller = Controller::curr();

		try {
			$form->saveInto($this->record);
			$this->record->write();
			$this->gridField->getList()->add($this->record);
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			$responseNegotiator = new PjaxResponseNegotiator(array(
				'CurrentForm' => function() use(&$form) {
					return $form->forTemplate();
				},
				'default' => function() use(&$controller) {
					return $controller->redirectBack();
				}
			));
			if($controller->getRequest()->isAjax()){
				$controller->getRequest()->addHeader('X-Pjax', 'CurrentForm');
			}
			return $responseNegotiator->respond($controller->getRequest());
		}

		// TODO Save this item into the given relationship

		if(isset($data['publish']) && $data['publish'])
		{
			$this->record->doPublish();
		}
		else
		{
			$message = sprintf(
				_t('GridFieldDetailForm.Saved', 'Saved %s %s'),
				$this->record->singular_name(),
				'<a href="' . $this->Link('edit') . '">"' . htmlspecialchars($this->record->Title, ENT_QUOTES) . '"</a>'
			);
			
			$form->sessionMessage($message, 'good');
	
			if($new_record) {
				return Controller::curr()->redirect($this->Link());
			} elseif($this->gridField->getList()->byId($this->record->ID)) {
				// Return new view, as we can't do a "virtual redirect" via the CMS Ajax
				// to the same URL (it assumes that its content is already current, and doesn't reload)
				return $this->edit(Controller::curr()->getRequest());
			} else {
				// Changes to the record properties might've excluded the record from
				// a filtered list, so return back to the main view if it can't be found
				$noActionURL = $controller->removeAction($data['url']);
				$controller->getRequest()->addHeader('X-Pjax', 'Content'); 
				return $controller->redirect($noActionURL, 302); 
			}			
		}
	}	 

	public function publish($data, $form)
	{
		try {
			
			if($record = $this->record)
			{
				if (!$record->canPublish()) {
					throw new ValidationException(_t('GridFieldDetailForm.DeletePermissionsFailure',"No publish permissions"),0);
				}
				
				$data['publish'] = true;	
				$this->doSave($data, $form);		
			}
			
		} catch(ValidationException $e) {
			$this->executeException($form,$e);
		}

		return $this->completeAction($form, $data, 'Published');
	}

	
	public function unpublish($data, $form)
	{
		try {
			if($record = $this->record)
			{				
				if (!$record->canDeleteFromLive()) {
					throw new ValidationException(_t('GridFieldDetailForm.DeletePermissionsFailure',"No unpublish permissions"),0);
				}

				$record->doUnpublish();
			}
		} catch(ValidationException $e) {
			$this->executeException($form,$e);
		}

		return $this->completeAction($form, $data, 'Unplublished');
	}
	
	public function delete($data, $form) {
		try {
			$record = $this->record;
			if (!$record->canDelete()) {
				throw new ValidationException(_t('GridFieldDetailForm.DeletePermissionsFailure',"No delete permissions"),0);
			}

			//This extra line is needed to remove the records with this ID from the versions table.
			DB::query("DELETE FROM DataObjectAsPage_versions WHERE RecordID = '$record->ID'");
			$record->doDelete();
		} catch(ValidationException $e) {
			$this->executeException($form,$e);
		}

		return $this->completeAction($form, $data, 'Deleted');
	}	
	
	public function rollback($data, $form) {
		try {
			if($record = $this->record)
			{
      	$reverted = $record->doRevertToLive();					
			}
		} catch(ValidationException $e) {
			$this->executeException($form,$e);
		}        
		
		$this->record = $reverted;
		return $this->completeAction($form, $data, 'Draft changed cancelled for');
  }	
	
	public function duplicate($data, $form, $request) {
		try {
			if($record = $this->record)
			{
				//Duplicate the object
				$clone = $record->doDuplicate();					
			}
		} catch(ValidationException $e) {
			$this->executeException($form,$e);
		}
    $this->record = $clone;
		
		return $this->completeAction($form, $data, 'Duplicated');
	}	

	/*
	 * Consolidating code, repeated in each action funciton above
	 */
	private function completeAction($form, $data, $message)
	{
 		$fullMessage = $message . " " . $this->record->singular_name() . " " . htmlspecialchars($this->record->Title, ENT_QUOTES);
				
		$form->sessionMessage($fullMessage, 'good');
		
		$controller = Controller::curr();
		
		if($this->gridField->getList()->byId($this->record->ID)) 
		{
			return $this->edit($controller->getRequest());
		} 
		else 
		{
			// Changes to the record properties might've excluded the record from
			// a filtered list, so return back to the main view if it can't be found
			$noActionURL = $controller->removeAction($data['url']);
			$controller->getRequest()->addHeader('X-Pjax', 'Content'); 
			return $controller->redirect($noActionURL, 302); 
		}		
	}
	

	/*
	 * Consolidating code, repeated in each action funciton above
	 */	
	 private function executeException($form, $e)
	 {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			return Controller::curr()->redirectBack();	 	
	 }
}