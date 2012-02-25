<?php

class DataObjectAsPageTableField extends TableListField {
	
	function handleItem($request) {
		return new DataObjectAsPageTableField_ItemRequest($this, $request->param('ID'));
	}
}

class DataObjectAsPageTableField_ItemRequest extends TableListField_ItemRequest {

	function delete($request) {
		// Protect against CSRF on destructive action
		$token = $this->ctf->getForm()->getSecurityToken();
		if(!$token->checkRequest($request)) return $this->httpError('400');
		
		if($this->ctf->Can('delete') !== true) {
			return false;
		}

		$this->dataObj()->doDelete();
	}
}
?>
